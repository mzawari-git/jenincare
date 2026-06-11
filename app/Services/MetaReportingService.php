<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CapiEventLog;
use App\Models\MarketingSetting;
use App\Models\Meta\MetaAdAccount;
use App\Models\Meta\MetaCampaign;
use Illuminate\Support\Facades\DB;

class MetaReportingService
{
    public function getOverview(int $days = 30): array
    {
        $since = now()->subDays($days);
        $campaigns = MetaCampaign::where('created_at', '>=', $since)->get();
        $accounts = MetaAdAccount::where('is_active', true)->get();
        $totalSpend = 0;
        $activeCampaigns = 0;
        foreach ($campaigns as $c) {
            $insights = $c->insights;
            if ($insights && isset($insights['spend'])) {
                $totalSpend += (float) $insights['spend'];
            }
            if ($c->status === 'ACTIVE') $activeCampaigns++;
        }
        if ($totalSpend === 0) {
            $totalSpend = (float) $accounts->sum('amount_spent');
        }

        $bookings = Booking::where('created_at', '>=', $since)
            ->whereIn('status', ['confirmed', 'completed'])
            ->get();
        $totalRevenue = $bookings->sum('total_amount');
        $totalOrders = $bookings->count();
        $aov = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;
        $roas = $totalSpend > 0 ? round($totalRevenue / $totalSpend, 2) : 0;

        $capiTotal = CapiEventLog::where('created_at', '>=', $since)->count();
        $capiSuccess = CapiEventLog::where('created_at', '>=', $since)->where('success', true)->count();
        $capiRate = $capiTotal > 0 ? round(($capiSuccess / $capiTotal) * 100, 1) : 0;

        $purchaseEvents = CapiEventLog::where('event_name', 'Purchase')
            ->where('created_at', '>=', $since)
            ->where('success', true)
            ->count();

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'aov' => $aov,
            'total_spend' => $totalSpend,
            'roas' => $roas,
            'active_campaigns' => $activeCampaigns,
            'total_campaigns' => $campaigns->count(),
            'connected_accounts' => $accounts->count(),
            'capi_events' => $capiTotal,
            'capi_success_rate' => $capiRate,
            'purchase_events' => $purchaseEvents,
            'period_days' => $days,
        ];
    }

    public function getRevenueTrend(int $days = 30): array
    {
        $rows = Booking::selectRaw(
            "DATE(created_at) as date,
             SUM(CASE WHEN status IN ('confirmed','completed') THEN total_amount ELSE 0 END) as revenue,
             COUNT(*) as orders,
             SUM(CASE WHEN status IN ('confirmed','completed') THEN 1 ELSE 0 END) as conversions"
        )
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy('date')
            ->get();

        $labels = [];
        $revenue = [];
        $orders = [];
        $conversions = [];

        foreach ($rows as $r) {
            $labels[] = $r->date;
            $revenue[] = (float) $r->revenue;
            $orders[] = (int) $r->orders;
            $conversions[] = (int) $r->conversions;
        }

        return ['labels' => $labels, 'revenue' => $revenue, 'orders' => $orders, 'conversions' => $conversions];
    }

    public function getCapiTrend(int $days = 30): array
    {
        $rows = CapiEventLog::selectRaw(
            "DATE(created_at) as date,
             COUNT(*) as total,
             SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as success,
             SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed"
        )
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy('date')
            ->get();

        $labels = [];
        $success = [];
        $failed = [];

        foreach ($rows as $r) {
            $labels[] = $r->date;
            $success[] = (int) $r->success;
            $failed[] = (int) $r->failed;
        }

        return ['labels' => $labels, 'success' => $success, 'failed' => $failed];
    }

    public function getCampaignPerformance(): array
    {
        $campaigns = MetaCampaign::with('adAccount')->orderByDesc('created_at')->limit(20)->get();
        $results = [];
        foreach ($campaigns as $c) {
            $insights = $c->insights ?? [];
            $results[] = [
                'id' => $c->id,
                'name' => $c->name,
                'objective' => $c->objective,
                'status' => $c->status,
                'account' => $c->adAccount?->name ?? '—',
                'daily_budget' => (float) ($c->daily_budget ?? 0),
                'spend' => (float) ($insights['spend'] ?? 0),
                'impressions' => (int) ($insights['impressions'] ?? 0),
                'clicks' => (int) ($insights['clicks'] ?? 0),
                'ctr' => (float) ($insights['ctr'] ?? 0),
                'cpc' => (float) ($insights['cpc'] ?? 0),
                'cpm' => (float) ($insights['cpm'] ?? 0),
                'reach' => (int) ($insights['reach'] ?? 0),
                'frequency' => (float) ($insights['frequency'] ?? 0),
                'conversions' => (int) ($insights['conversions'] ?? 0),
                'cpa' => (float) ($insights['cpa'] ?? 0),
                'roas' => (float) ($insights['roas'] ?? 0),
            ];
        }
        return $results;
    }

    public function getSourceBreakdown(int $days = 30): array
    {
        $bookings = Booking::selectRaw(
            "COALESCE(source, 'direct') as source,
             COUNT(*) as orders,
             SUM(total_amount) as revenue"
        )
            ->where('created_at', '>=', now()->subDays($days))
            ->whereIn('status', ['confirmed', 'completed'])
            ->groupBy('source')
            ->orderByDesc('revenue')
            ->get();

        return $bookings->toArray();
    }

    public function getHealthScores(): array
    {
        $health = app(AdAccountHealthService::class);
        return $health->getAllScores();
    }

    public function getHourlyCapiVolume(int $days = 7): array
    {
        $rows = CapiEventLog::where('created_at', '>=', now()->subDays($days))
            ->selectRaw("HOUR(created_at) as hour, COUNT(*) as count")
            ->groupBy(DB::raw("HOUR(created_at)"))
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $labels = [];
        $values = [];
        for ($h = 0; $h < 24; $h++) {
            $labels[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
            $values[] = (int) ($rows[$h] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getBookingStatusDistribution(int $days = 30): array
    {
        $stats = Booking::selectRaw("status, COUNT(*) as count")
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return $stats;
    }

    public function getExportData(int $days = 30): array
    {
        $events = CapiEventLog::where('created_at', '>=', now()->subDays($days))
            ->orderByDesc('created_at')
            ->limit(1000)
            ->get()
            ->toArray();

        return $events;
    }
}
