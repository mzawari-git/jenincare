<?php

namespace App\Services\Meta;

use App\Models\Meta\MetaAttributionEvent;
use App\Models\Meta\MetaCampaign;
use App\Models\Meta\MetaAutomatedReport;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MetaAnalyticsService
{
    public function getFunnelData($campaignId = null, $days = 30)
    {
        $query = MetaAttributionEvent::where('created_at', '>=', now()->subDays($days));
        
        if ($campaignId) {
            $query->where('campaign_id', $campaignId);
        }

        $funnel = [
            'impressions' => $query->where('event_type', 'view')->count(),
            'clicks' => $query->where('event_type', 'click')->count(),
            'landing_pages' => $query->where('event_type', 'landing')->count(),
            'add_to_cart' => $query->where('event_type', 'add_to_cart')->count(),
            'checkouts' => $query->where('event_type', 'checkout')->count(),
            'purchases' => $query->where('event_type', 'purchase')->count(),
        ];

        $conversionRates = [];
        $previous = null;
        foreach ($funnel as $stage => $count) {
            if ($previous !== null && $previous > 0) {
                $conversionRates[$stage] = round(($count / $previous) * 100, 2);
            } else {
                $conversionRates[$stage] = 100;
            }
            $previous = $count;
        }

        return [
            'funnel' => $funnel,
            'conversion_rates' => $conversionRates,
            'total_conversion_rate' => $funnel['impressions'] > 0 
                ? round(($funnel['purchases'] / $funnel['impressions']) * 100, 2) 
                : 0,
        ];
    }

    public function getAttributionReport($campaignId = null, $days = 30, $model = 'last_click')
    {
        $query = MetaAttributionEvent::where('created_at', '>=', now()->subDays($days))
            ->where('event_type', 'purchase');

        if ($campaignId) {
            $query->where('campaign_id', $campaignId);
        }

        $purchases = $query->with(['campaign', 'adSet', 'ad'])->get();

        $attribution = [];
        
        foreach ($purchases as $purchase) {
            $key = $purchase->campaign_id ?? 'direct';
            
            if (!isset($attribution[$key])) {
                $attribution[$key] = [
                    'campaign_name' => $purchase->campaign?->name ?? 'Direct',
                    'conversions' => 0,
                    'revenue' => 0,
                ];
            }
            
            $attribution[$key]['conversions']++;
            $attribution[$key]['revenue'] += $purchase->value ?? 0;
        }

        return collect($attribution)->sortByDesc('revenue')->values()->toArray();
    }

    public function getDateComparison($metric, $currentDays = 7, $previousDays = 7)
    {
        $currentPeriod = $this->getMetricData($metric, $currentDays, 0);
        $previousPeriod = $this->getMetricData($metric, $previousDays, $currentDays);

        $change = $previousPeriod > 0 
            ? round((($currentPeriod - $previousPeriod) / $previousPeriod) * 100, 2)
            : 0;

        return [
            'current' => $currentPeriod,
            'previous' => $previousPeriod,
            'change' => $change,
            'trend' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'flat'),
        ];
    }

    protected function getMetricData($metric, $days, $offset)
    {
        $start = now()->subDays($days + $offset);
        $end = now()->subDays($offset);

        switch ($metric) {
            case 'purchases':
                return MetaAttributionEvent::where('event_type', 'purchase')
                    ->whereBetween('created_at', [$start, $end])
                    ->count();
            case 'revenue':
                return MetaAttributionEvent::where('event_type', 'purchase')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('value');
            case 'clicks':
                return MetaAttributionEvent::where('event_type', 'click')
                    ->whereBetween('created_at', [$start, $end])
                    ->count();
            default:
                return 0;
        }
    }

    public function generateReport($reportId)
    {
        $report = MetaAutomatedReport::findOrFail($reportId);
        
        $data = [];
        foreach ($report->metrics as $metric) {
            $data[$metric] = $this->getMetricValue($metric, $report->filters);
        }

        $report->update([
            'last_sent_at' => now(),
            'next_send_at' => $this->calculateNextSend($report),
        ]);

        return $data;
    }

    protected function getMetricValue($metric, $filters = null)
    {
        $days = $filters['days'] ?? 30;
        $campaignId = $filters['campaign_id'] ?? null;

        $query = MetaAttributionEvent::where('created_at', '>=', now()->subDays($days));
        
        if ($campaignId) {
            $query->where('campaign_id', $campaignId);
        }

        switch ($metric) {
            case 'total_purchases':
                return $query->where('event_type', 'purchase')->count();
            case 'total_revenue':
                return $query->where('event_type', 'purchase')->sum('value');
            case 'total_clicks':
                return $query->where('event_type', 'click')->count();
            case 'total_impressions':
                return $query->where('event_type', 'view')->count();
            case 'conversion_rate':
                $impressions = $query->where('event_type', 'view')->count();
                $purchases = $query->where('event_type', 'purchase')->count();
                return $impressions > 0 ? round(($purchases / $impressions) * 100, 2) : 0;
            default:
                return 0;
        }
    }

    protected function calculateNextSend($report)
    {
        $now = now();
        
        switch ($report->type) {
            case 'daily':
                return $now->addDay()->setTimeFromTimeString($report->send_time ?? '09:00');
            case 'weekly':
                return $now->addWeek()->setTimeFromTimeString($report->send_time ?? '09:00');
            case 'monthly':
                return $now->addMonth()->setTimeFromTimeString($report->send_time ?? '09:00');
            default:
                return null;
        }
    }

    public function getTopPerformingCampaigns($days = 30, $limit = 10)
    {
        return MetaAttributionEvent::select(
                'campaign_id',
                DB::raw('COUNT(CASE WHEN event_type = "purchase" THEN 1 END) as purchases'),
                DB::raw('SUM(CASE WHEN event_type = "purchase" THEN value ELSE 0 END) as revenue'),
                DB::raw('COUNT(CASE WHEN event_type = "click" THEN 1 END) as clicks')
            )
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('campaign_id')
            ->groupBy('campaign_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->with('campaign:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'campaign_id' => $item->campaign_id,
                    'campaign_name' => $item->campaign?->name ?? 'Unknown',
                    'purchases' => $item->purchases,
                    'revenue' => $item->revenue,
                    'clicks' => $item->clicks,
                    'conversion_rate' => $item->clicks > 0 
                        ? round(($item->purchases / $item->clicks) * 100, 2) 
                        : 0,
                ];
            });
    }
}
