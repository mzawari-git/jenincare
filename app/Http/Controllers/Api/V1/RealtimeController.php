<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SkinScan;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RealtimeController extends Controller
{
    public function stream(): StreamedResponse
    {
        $response = new StreamedResponse(function () {
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');

            $lastScanCount = 0;
            $lastProcessingCount = 0;

            while (true) {
                if (connection_aborted()) break;

                $stats = Cache::remember('realtime_dashboard_stats', 5, function () {
                    return [
                        'total_scans' => SkinScan::count(),
                        'processing' => SkinScan::where('analysis_status', 'processing')->count(),
                        'completed_today' => SkinScan::whereDate('analyzed_at', today())->count(),
                        'failed' => SkinScan::where('analysis_status', 'failed')->count(),
                        'avg_score' => SkinScan::whereNotNull('overall_health_score')
                            ->avg('overall_health_score'),
                    ];
                });

                $recentScans = SkinScan::with('user')
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn($s) => [
                        'id' => $s->id,
                        'status' => $s->analysis_status,
                        'score' => $s->overall_health_score,
                        'user' => $s->user?->name ?? 'Unknown',
                        'time' => $s->created_at->diffForHumans(),
                    ]);

                $data = json_encode([
                    'stats' => $stats,
                    'recent' => $recentScans,
                    'timestamp' => now()->toIso8601String(),
                ]);

                echo "data: {$data}\n\n";
                ob_flush();
                flush();

                sleep(3);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');

        return $response;
    }

    public function stats()
    {
        $total = SkinScan::count();
        $processing = SkinScan::where('analysis_status', 'processing')->count();
        $completedToday = SkinScan::whereDate('analyzed_at', today())->count();
        $failed = SkinScan::where('analysis_status', 'failed')->count();
        $avgScore = SkinScan::whereNotNull('overall_health_score')->avg('overall_health_score');

        $pendingReviews = SkinScan::where('analysis_status', 'completed')
            ->whereNull('reviewed_at')
            ->count();

        $providerStats = SkinScan::whereNotNull('analyzed_by_provider')
            ->selectRaw('analyzed_by_provider, count(*) as total, avg(overall_health_score) as avg_score')
            ->groupBy('analyzed_by_provider')
            ->get();

        return response()->json([
            'total_scans' => $total,
            'processing' => $processing,
            'completed_today' => $completedToday,
            'failed' => $failed,
            'avg_score' => round((float) $avgScore, 1),
            'pending_review' => $pendingReviews,
            'provider_stats' => $providerStats,
        ]);
    }

    public function trends()
    {
        $days = 30;
        $dailyStats = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyStats[] = [
                'date' => $date,
                'total' => SkinScan::whereDate('created_at', $date)->count(),
                'completed' => SkinScan::whereDate('analyzed_at', $date)->count(),
                'avg_score' => round(
                    (float) SkinScan::whereDate('analyzed_at', $date)->avg('overall_health_score') ?? 0,
                    1
                ),
            ];
        }

        $topDefects = SkinScan::whereNotNull('defects')
            ->get()
            ->flatMap(fn($s) => collect($s->defects ?? [])->pluck('type'))
            ->countBy()
            ->sortDesc()
            ->take(10)
            ->map(fn($count, $type) => ['type' => $type, 'count' => $count])
            ->values();

        return response()->json([
            'daily' => $dailyStats,
            'top_defects' => $topDefects,
        ]);
    }
}
