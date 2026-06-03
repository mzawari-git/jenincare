<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\SkinScan;
use App\Models\ScanDefect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ScanManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SkinScan::with('user')
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->search, function ($q, $v) {
                $q->whereHas('user', fn($uq) => $uq->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%"));
            })
            ->latest();

        $perPage = $request->input('per_page', 20);
        $scans = $query->paginate($perPage);

        $scans->getCollection()->transform(function ($scan) {
            return $this->formatScan($scan);
        });

        return response()->json($scans);
    }

    public function show($id): JsonResponse
    {
        $scan = SkinScan::with(['user', 'defects', 'heatmapPoints', 'generalTips', 'timelineEvents'])->findOrFail($id);
        return response()->json(['scan' => $this->formatScanDetail($scan)]);
    }

    public function pending(Request $request): JsonResponse
    {
        $query = SkinScan::with('user')->where('status', 'pending')->latest();
        $perPage = $request->input('per_page', 20);
        $scans = $query->paginate($perPage);

        $scans->getCollection()->transform(function ($scan) {
            return $this->formatScan($scan);
        });

        return response()->json($scans);
    }

    public function allScans(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    public function approve($id): JsonResponse
    {
        $scan = SkinScan::findOrFail($id);
        $scan->update(['status' => 'approved', 'reviewed_at' => now()]);

        $scan->timelineEvents()->create([
            'status' => 'approved',
            'description' => 'Scan approved by admin',
            'description_ar' => 'تمت الموافقة على الفحص من قبل المشرف',
        ]);

        return response()->json(['scan' => $this->formatScan($scan), 'message' => 'تمت الموافقة على الفحص']);
    }

    public function reject(Request $request, $id): JsonResponse
    {
        $request->validate(['reason' => 'sometimes|string|max:500']);

        $scan = SkinScan::findOrFail($id);
        $scan->update(['status' => 'rejected', 'reviewed_at' => now()]);

        $scan->timelineEvents()->create([
            'status' => 'rejected',
            'description' => $request->reason ?? 'Scan rejected by admin',
            'description_ar' => $request->reason ?? 'تم رفض الفحص من قبل المشرف',
        ]);

        return response()->json(['scan' => $this->formatScan($scan), 'message' => 'تم رفض الفحص']);
    }

    public function generatePin($id): JsonResponse
    {
        $scan = SkinScan::findOrFail($id);
        $pin = strtoupper(Str::random(8));
        $scan->update([
            'pin_code' => $pin,
            'pin_attempts' => 0,
            'locked_until' => null,
        ]);

        return response()->json(['pin' => $pin, 'scan_id' => $scan->id, 'message' => 'تم إنشاء رمز PIN بنجاح']);
    }

    public function batchApprove(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'string|exists:skin_scans,id']);

        SkinScan::whereIn('id', $request->ids)->update(['status' => 'approved', 'reviewed_at' => now()]);

        return response()->json(['message' => 'تمت الموافقة على الفحوصات المحددة']);
    }

    public function broadcast(Request $request, $id): JsonResponse
    {
        $scan = SkinScan::with('user')->findOrFail($id);
        $scan->update(['status' => 'broadcast']);

        return response()->json(['message' => 'تم إرسال النتيجة للعميل', 'scan' => $this->formatScan($scan)]);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total' => SkinScan::count(),
            'pending' => SkinScan::where('status', 'pending')->count(),
            'approved' => SkinScan::where('status', 'approved')->count(),
            'rejected' => SkinScan::where('status', 'rejected')->count(),
            'today' => SkinScan::whereDate('created_at', today())->count(),
            'this_week' => SkinScan::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => SkinScan::whereMonth('created_at', now()->month)->count(),
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $scans = SkinScan::with('user')->latest()->get()->map(function ($scan) {
            return $this->formatScan($scan);
        });

        return response()->json(['scans' => $scans, 'total' => $scans->count()]);
    }

    private function formatScan($scan): array
    {
        return [
            'id' => $scan->id,
            'user_id' => $scan->user_id,
            'user_name' => $scan->user?->name,
            'user_email' => $scan->user?->email,
            'status' => $scan->status,
            'overall_health_score' => $scan->overall_health_score,
            'image_url' => $scan->image_url,
            'is_locked' => $scan->is_locked,
            'has_pin' => !is_null($scan->pin_code),
            'created_at' => $scan->created_at,
            'reviewed_at' => $scan->reviewed_at,
        ];
    }

    private function formatScanDetail($scan): array
    {
        return [
            'id' => $scan->id,
            'user_id' => $scan->user_id,
            'user' => $scan->user ? ['id' => $scan->user->id, 'name' => $scan->user->name, 'email' => $scan->user->email, 'phone' => $scan->user->phone] : null,
            'status' => $scan->status,
            'overall_health_score' => $scan->overall_health_score,
            'hydration' => $scan->hydration,
            'sebum' => $scan->sebum,
            'pigmentation' => $scan->pigmentation,
            'pores' => $scan->pores,
            'elasticity' => $scan->elasticity,
            'image_url' => $scan->image_url,
            'is_locked' => $scan->is_locked,
            'pin_code' => $scan->pin_code,
            'pin_attempts' => $scan->pin_attempts,
            'custom_arabic_analysis' => $scan->custom_arabic_analysis,
            'expert_free_tips' => $scan->expert_free_tips,
            'lighting_quality' => $scan->lighting_quality,
            'face_confidence' => $scan->face_confidence,
            'defects' => $scan->defects,
            'heatmap_points' => $scan->heatmapPoints,
            'general_tips' => $scan->generalTips,
            'timeline' => $scan->timelineEvents,
            'created_at' => $scan->created_at,
            'reviewed_at' => $scan->reviewed_at,
        ];
    }
}
