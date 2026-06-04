<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\SkinScan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $totalScans = SkinScan::count();
        $pendingScans = SkinScan::where('status', 'pending')->count();
        $approvedScans = SkinScan::where('status', 'approved')->count();
        $rejectedScans = SkinScan::where('status', 'rejected')->count();
        $totalUsers = User::count();

        return response()->json([
            'total_scans' => $totalScans,
            'pending_scans' => $pendingScans,
            'approved_scans' => $approvedScans,
            'rejected_scans' => $rejectedScans,
            'total_users' => $totalUsers,
            'today_scans' => SkinScan::whereDate('created_at', today())->count(),
            'processing_rate' => $totalScans > 0 ? round(($approvedScans / $totalScans) * 100, 1) : 0,
        ]);
    }

    public function recentScans(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 5);
        $scans = SkinScan::with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($scan) {
                return [
                    'id' => $scan->id,
                    'user_name' => $scan->user?->name ?? 'مجهول',
                    'user_email' => $scan->user?->email,
                    'status' => $scan->status,
                    'health_score' => $scan->overall_health_score,
                    'created_at' => $scan->created_at,
                ];
            });

        return response()->json(['scans' => $scans]);
    }

    public function charts(Request $request): JsonResponse
    {
        $days = $request->input('days', 7);
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $data[] = [
                'date' => $date,
                'count' => SkinScan::whereDate('created_at', $date)->count(),
            ];
        }

        return response()->json(['chart' => $data]);
    }

    public function quotaUsage(): JsonResponse
    {
        return response()->json([
            'used' => SkinScan::where('status', '!=', 'pending')->count(),
            'total' => 1000,
            'percentage' => min(100, round((SkinScan::where('status', '!=', 'pending')->count() / 1000) * 100, 1)),
        ]);
    }
}
