<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CapiEventLog;
use App\Models\MarketingSetting;
use Illuminate\Support\Facades\DB;

class CapiDiagnosticsController extends Controller
{
    public function index()
    {
        $settings = MarketingSetting::getAllTrackingSettings();
        $stats = $this->aggregateStats($settings);

        return view('admin.meta-marketing.diagnostics', compact('stats', 'settings'));
    }

    public function data()
    {
        $settings = MarketingSetting::getAllTrackingSettings();
        return response()->json($this->aggregateStats($settings));
    }

    private function aggregateStats(array $settings = []): array
    {
        $total = CapiEventLog::count();
        $successCount = CapiEventLog::where('success', true)->count();
        $failedCount = CapiEventLog::where('success', false)->count();
        $pendingCount = CapiEventLog::whereNull('success')->count();
        $todayCount = CapiEventLog::whereDate('created_at', today())->count();
        $todaySuccess = CapiEventLog::whereDate('created_at', today())->where('success', true)->count();
        $avgDuration = CapiEventLog::whereNotNull('duration_ms')->avg('duration_ms');
        $uniqueTypes = CapiEventLog::distinct('event_name')->count('event_name');

        $successRate = $total > 0 ? round(($successCount / $total) * 100, 1) : 0;
        $todayRate = $todayCount > 0 ? round(($todaySuccess / $todayCount) * 100, 1) : 0;

        $eventsByType = CapiEventLog::selectRaw('event_name, COUNT(*) as count')
            ->groupBy('event_name')
            ->orderByDesc('count')
            ->pluck('count', 'event_name')
            ->toArray();

        $dailyData = CapiEventLog::selectRaw(
            "DATE(created_at) as date,
             SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as success,
             SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed"
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy('date')
            ->get()
            ->toArray();

        $durationByType = CapiEventLog::selectRaw('event_name, AVG(duration_ms) as avg_duration')
            ->whereNotNull('duration_ms')
            ->groupBy('event_name')
            ->pluck('avg_duration', 'event_name')
            ->toArray();

        $recentEvents = CapiEventLog::orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->toArray();

        $errors = CapiEventLog::selectRaw('error_message, COUNT(*) as count, MAX(created_at) as last_occurrence')
            ->whereNotNull('error_message')
            ->where('error_message', '!=', '')
            ->groupBy('error_message')
            ->orderByDesc('count')
            ->limit(20)
            ->get()
            ->toArray();

        $platformCounts = CapiEventLog::selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->pluck('count', 'platform')
            ->toArray();

        $lastEventPerPlatform = CapiEventLog::selectRaw('platform, MAX(created_at) as last_event')
            ->groupBy('platform')
            ->pluck('last_event', 'platform')
            ->toArray();

        $platformSuccessRates = [];
        foreach (['facebook', 'tiktok', 'google', 'snapchat', 'pinterest', 'twitter', 'linkedin', 'custom'] as $p) {
            $pTotal = CapiEventLog::where('platform', $p)->count();
            if ($pTotal > 0) {
                $pSuccess = CapiEventLog::where('platform', $p)->where('success', true)->count();
                $platformSuccessRates[$p] = round(($pSuccess / $pTotal) * 100, 1);
            } else {
                $platformSuccessRates[$p] = null;
            }
        }

        $hourlyDistribution = [];
        $todayHourly = [];
        try {
            $weekEvents = CapiEventLog::where('created_at', '>=', now()->subDays(7))
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as slot, COUNT(*) as count")
                ->groupBy('slot')
                ->orderBy('slot')
                ->pluck('count', 'slot')
                ->toArray();
            foreach ($weekEvents as $slot => $count) {
                $h = (int) substr($slot, 11, 2);
                $hourlyDistribution[$h] = ($hourlyDistribution[$h] ?? 0) + $count;
            }

            $todaySlots = CapiEventLog::whereDate('created_at', today())
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as slot, COUNT(*) as count")
                ->groupBy('slot')
                ->orderBy('slot')
                ->pluck('count', 'slot')
                ->toArray();
            foreach ($todaySlots as $slot => $count) {
                $h = (int) substr($slot, 11, 2);
                $todayHourly[$h] = ($todayHourly[$h] ?? 0) + $count;
            }
        } catch (\Exception $e) {
            $hourlyDistribution = [];
            $todayHourly = [];
        }

        $alerts = [];
        if ($total > 0 && $successRate < 70) {
            $alerts[] = ['type' => 'danger', 'icon' => 'fa-exclamation-triangle', 'message' => "نسبة النجاح {$successRate}% — أقل من 70%، يرجى مراجعة الأخطاء."];
        } elseif ($total > 0 && $successRate < 90) {
            $alerts[] = ['type' => 'warning', 'icon' => 'fa-exclamation-circle', 'message' => "نسبة النجاح {$successRate}% — أقل من 90%، يُنصح بالمراجعة."];
        }
        if ($todayCount === 0 && $total > 0) {
            $alerts[] = ['type' => 'warning', 'icon' => 'fa-clock', 'message' => 'لا توجد أحداث اليوم — تأكد من أن النظام يرسل الأحداث بشكل طبيعي.'];
        }
        foreach (['facebook', 'tiktok', 'google', 'snapchat', 'pinterest', 'twitter', 'linkedin'] as $p) {
            $pSettings = $settings[$p] ?? [];
            $capiEnabled = $pSettings['capi_enabled'] ?? $pSettings['enabled'] ?? false;
            $pCount = $platformCounts[$p] ?? 0;
            if ($capiEnabled && $pCount === 0) {
                $alerts[] = ['type' => 'info', 'icon' => 'fa-info-circle', 'message' => "منصة {$p} مفعّلة ولكن لا توجد أحداث CAPI مسجلة."];
            }
        }
        if ($failedCount > $successCount && $total > 10) {
            $alerts[] = ['type' => 'danger', 'icon' => 'fa-times-circle', 'message' => 'عدد الأحداث الفاشلة أكبر من الناجحة — يوجد خلل جدي في الإرسال.'];
        }
        $recentFailed = CapiEventLog::where('success', false)
            ->where('created_at', '>=', now()->subHours(2))
            ->count();
        if ($recentFailed >= 5) {
            $alerts[] = ['type' => 'danger', 'icon' => 'fa-bolt', 'message' => "{$recentFailed} حدث فاشل في آخر ساعتين — يرجى التحقق فوراً."];
        }

        return [
            'total' => $total,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'pending_count' => $pendingCount,
            'today_count' => $todayCount,
            'today_success' => $todaySuccess,
            'success_rate' => $successRate,
            'today_rate' => $todayRate,
            'avg_duration_ms' => round($avgDuration ?? 0, 1),
            'unique_event_types' => $uniqueTypes,
            'events_by_type' => $eventsByType,
            'daily_data' => $dailyData,
            'duration_by_type' => $durationByType,
            'recent_events' => $recentEvents,
            'errors' => $errors,
            'hourly_distribution' => $hourlyDistribution,
            'today_hourly' => $todayHourly,
            'platform_counts' => $platformCounts,
            'last_event_per_platform' => $lastEventPerPlatform,
            'platform_success_rates' => $platformSuccessRates,
            'alerts' => $alerts,
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
