<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AnalysisStatus;
use App\Http\Controllers\Controller;
use App\Models\AIProvider;
use App\Models\SkinAnalysis;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $today = Carbon::today();

        $stats = [
            'total_scans' => SkinAnalysis::count(),
            'pending_scans' => SkinAnalysis::pending()->count(),
            'approved_today' => SkinAnalysis::approved()
                ->whereDate('approved_at', $today)
                ->count(),
            'active_providers' => AIProvider::active()->count(),
            'total_users' => User::count(),
            'rejected_scans' => SkinAnalysis::where('status', AnalysisStatus::REJECTED->value)->count(),
            'quota_usage' => $this->getQuotaUsage(),
            'scans_by_day' => $this->getScansByDay(),
        ];

        return response()->json(['data' => $stats]);
    }

    public function pendingScans(): JsonResponse
    {
        $scans = SkinAnalysis::with(['user:id,name,email,phone', 'aiProvider:id,name,driver_key'])
            ->pending()
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($scans);
    }

    public function allScans(Request $request): JsonResponse
    {
        $query = SkinAnalysis::with(['user:id,name,email,phone', 'aiProvider:id,name,driver_key']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('user_search')) {
            $search = $request->input('user_search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('provider')) {
            $query->whereHas('aiProvider', function ($q) use ($request) {
                $q->where('driver_key', $request->input('provider'));
            });
        }

        if ($request->filled('sort_by')) {
            $sortableColumns = ['created_at', 'overall_health_score', 'status', 'id'];
            $sortBy = in_array($request->input('sort_by'), $sortableColumns)
                ? $request->input('sort_by')
                : 'created_at';
            $direction = in_array($request->input('sort_dir', 'desc'), ['asc', 'desc'])
                ? $request->input('sort_dir')
                : 'desc';
            $query->orderBy($sortBy, $direction);
        } else {
            $query->orderByDesc('created_at');
        }

        $scans = $query->paginate($request->input('per_page', 25));

        return response()->json($scans);
    }

    public function scanDetail(int $id): JsonResponse
    {
        $scan = SkinAnalysis::with([
            'user:id,name,email,phone,device_id',
            'aiProvider:id,name,driver_key,engine_type',
            'accessPin',
            'recommendedProducts',
        ])->findOrFail($id);

        $data = [
            'scan' => $scan,
            'can_approve' => $scan->status === AnalysisStatus::PENDING->value,
            'can_generate_pin' => $scan->status !== AnalysisStatus::PENDING->value
                && ! $scan->accessPin,
            'decrypted_image_url' => $scan->image_path
                ? route('admin.scans.image', $scan->id)
                : null,
        ];

        return response()->json(['data' => $data]);
    }

    private function getQuotaUsage(): array
    {
        $providers = AIProvider::all();
        $usage = [];

        foreach ($providers as $provider) {
            $usage[] = [
                'id' => $provider->id,
                'name' => $provider->name,
                'driver_key' => $provider->driver_key,
                'quota_used' => $provider->quota_used,
                'quota_limit' => $provider->quota_limit,
                'percentage' => $provider->quota_limit > 0
                    ? round(($provider->quota_used / $provider->quota_limit) * 100, 1)
                    : 0,
                'is_active' => $provider->is_active,
            ];
        }

        return $usage;
    }

    private function getScansByDay(): array
    {
        return SkinAnalysis::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }
}
