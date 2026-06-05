<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\AnalysisStatus;
use App\Http\Controllers\Controller;
use App\Models\SkinAnalysisPin;
use App\Models\SkinScan;
use App\Models\ScanDefect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScanManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SkinScan::with('user')
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->analysis_status, fn($q, $v) => $q->where('analysis_status', $v))
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
        $scan = SkinScan::with(['user', 'defects', 'heatmapPoints', 'generalTips', 'timelineEvents', 'pins', 'analysisImages'])->findOrFail($id);
        return response()->json(['scan' => $this->formatScanDetail($scan)]);
    }

    public function pending(Request $request): JsonResponse
    {
        $query = SkinScan::with('user')
            ->where('analysis_status', AnalysisStatus::PENDING->value)
            ->latest();

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
        $scan->update([
            'status' => 'approved',
            'analysis_status' => AnalysisStatus::APPROVED->value,
            'reviewed_at' => now(),
        ]);

        $scan->refresh();

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
        $scan->update([
            'status' => 'rejected',
            'analysis_status' => AnalysisStatus::REJECTED->value,
            'reviewed_at' => now(),
        ]);

        $scan->refresh();

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

    public function deleteScan(Request $request, $id): JsonResponse
    {
        $scan = SkinScan::findOrFail($id);
        $scan->defects()->delete();
        $scan->heatmapPoints()->delete();
        $scan->generalTips()->delete();
        $scan->timelineEvents()->delete();
        $scan->pins()->delete();
        $scan->analysisImages()->delete();
        $scan->delete();

        return response()->json(['message' => 'تم حذف الفحص بنجاح']);
    }

    public function batchApprove(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:skin_scans,id']);

        SkinScan::whereIn('id', $request->ids)->update([
            'status' => 'approved',
            'analysis_status' => AnalysisStatus::APPROVED->value,
            'reviewed_at' => now(),
        ]);

        return response()->json(['message' => 'تمت الموافقة على الفحوصات المحددة']);
    }

    public function broadcast(Request $request, $id): JsonResponse
    {
        $scan = SkinScan::with('user')->findOrFail($id);
        $scan->update(['status' => 'broadcast']);

        return response()->json(['message' => 'تم إرسال النتيجة للعميل', 'scan' => $this->formatScan($scan)]);
    }

    public function pinScan(Request $request, $id): JsonResponse
    {
        $request->validate([
            'pin_type' => 'required|string|in:featured,showcase',
            'admin_note' => 'nullable|string|max:500',
            'admin_note_ar' => 'nullable|string|max:500',
        ]);

        $scan = SkinScan::findOrFail($id);

        $pin = SkinAnalysisPin::updateOrCreate(
            ['scan_id' => $id, 'pin_type' => $request->pin_type],
            [
                'user_id' => $request->user()->id,
                'admin_note' => $request->admin_note,
                'admin_note_ar' => $request->admin_note_ar,
                'pinned_at' => now(),
            ]
        );

        return response()->json([
            'pin' => $pin,
            'message' => 'Scan pinned successfully / تم تثبيت الفحص بنجاح',
        ]);
    }

    public function unpinScan(Request $request, $id): JsonResponse
    {
        $request->validate(['pin_type' => 'required|string']);

        SkinAnalysisPin::where('scan_id', $id)
            ->where('pin_type', $request->pin_type)
            ->delete();

        return response()->json(['message' => 'Scan unpinned successfully / تم إلغاء تثبيت الفحص بنجاح']);
    }

    public function pinnedScans(Request $request): JsonResponse
    {
        $pins = SkinAnalysisPin::with('scan.user')
            ->where('pin_type', $request->type ?? 'featured')
            ->latest('pinned_at')
            ->paginate($request->input('per_page', 20));

        $pins->getCollection()->transform(function ($pin) {
            return [
                'id' => $pin->id,
                'scan_id' => $pin->scan_id,
                'pin_type' => $pin->pin_type,
                'admin_note' => $pin->admin_note,
                'admin_note_ar' => $pin->admin_note_ar,
                'pinned_at' => $pin->pinned_at,
                'scan' => $pin->scan ? $this->formatScan($pin->scan) : null,
            ];
        });

        return response()->json($pins);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total' => SkinScan::count(),
            'pending' => SkinScan::where('analysis_status', AnalysisStatus::PENDING->value)->count(),
            'processing' => SkinScan::where('analysis_status', AnalysisStatus::PROCESSING->value)->count(),
            'completed' => SkinScan::where('analysis_status', AnalysisStatus::COMPLETED->value)->count(),
            'approved' => SkinScan::where('analysis_status', AnalysisStatus::APPROVED->value)->count(),
            'rejected' => SkinScan::where('analysis_status', AnalysisStatus::REJECTED->value)->count(),
            'failed' => SkinScan::where('analysis_status', AnalysisStatus::FAILED->value)->count(),
            'today' => SkinScan::whereDate('created_at', today())->count(),
            'this_week' => SkinScan::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => SkinScan::whereMonth('created_at', now()->month)->count(),
            'pinned_scans' => SkinAnalysisPin::count(),
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
        $analysisData = $scan->analysis_data ?? [];

        return [
            'id' => $scan->id,
            'user_id' => $scan->user_id,
            'user_name' => $scan->user?->name,
            'user_email' => $scan->user?->email,
            'status' => $scan->status,
            'analysis_status' => $scan->analysis_status,
            'analysis_status_label' => $scan->analysis_status_label,
            'overall_health_score' => $analysisData['overall_health_score'] ?? $scan->overall_health_score,
            'image_url' => $scan->image_url,
            'is_locked' => $scan->is_locked,
            'has_pin' => !is_null($scan->pin_code),
            'defect_count' => count($analysisData['defects'] ?? []),
            'confidence' => $scan->confidence_score,
            'analyzed_by' => $scan->analyzed_by_provider,
            'created_at' => $scan->created_at,
            'analyzed_at' => $scan->analyzed_at,
            'reviewed_at' => $scan->reviewed_at,
        ];
    }

    private function formatScanDetail($scan): array
    {
        $analysisData = $scan->analysis_data ?? [];

        return [
            'id' => $scan->id,
            'user_id' => $scan->user_id,
            'user' => $scan->user ? ['id' => $scan->user->id, 'name' => $scan->user->name, 'email' => $scan->user->email, 'phone' => $scan->user->phone] : null,
            'status' => $scan->status,
            'analysis_status' => $scan->analysis_status,
            'analysis_status_label' => $scan->analysis_status_label,
            'analysis_status_color' => $scan->analysis_status_color,
            'overall_health_score' => $analysisData['overall_health_score'] ?? $scan->overall_health_score,
            'radar_metrics' => $analysisData['radar_metrics'] ?? [
                'hydration' => $scan->hydration,
                'sebum' => $scan->sebum,
                'pigmentation' => $scan->pigmentation,
                'pores' => $scan->pores,
                'elasticity' => $scan->elasticity,
            ],
            'advanced_metrics' => $analysisData['advanced_metrics'] ?? [],
            'defects' => $analysisData['defects'] ?? $scan->defects,
            'heatmap_coordinates' => $analysisData['heatmap_coordinates'] ?? $scan->heatmapPoints,
            'facial_zone_analysis' => $analysisData['facial_zone_analysis'] ?? [],
            'spectral_analysis' => $analysisData['spectral_analysis'] ?? [],
            'custom_arabic_analysis_text' => $analysisData['custom_arabic_analysis_text'] ?? $scan->custom_arabic_analysis,
            'expert_free_tips' => $analysisData['expert_free_tips'] ?? $scan->expert_free_tips,
            'recommended_products' => $analysisData['recommended_products'] ?? $scan->recommended_products ?? [],
            'image_url' => $scan->image_url,
            'image_path' => $scan->image_path,
            'is_locked' => $scan->is_locked,
            'pin_code' => $scan->pin_code,
            'pin_attempts' => $scan->pin_attempts,
            'confidence' => $scan->confidence_score,
            'analyzed_by' => $scan->analyzed_by_provider,
            'analyzed_at' => $scan->analyzed_at,
            'lighting_quality' => $scan->lighting_quality,
            'face_confidence' => $scan->face_confidence,
            'analysis_data' => $analysisData,
            'analysis_images' => $scan->relationLoaded('analysisImages') ? $scan->analysisImages : [],
            'pins' => $scan->relationLoaded('pins') ? $scan->pins : [],
            'general_tips' => $scan->generalTips,
            'timeline' => $scan->timelineEvents,
            'created_at' => $scan->created_at,
            'reviewed_at' => $scan->reviewed_at,
        ];
    }

    public function stream(): StreamedResponse
    {
        $response = new StreamedResponse(function () {
            $lastMaxId = SkinScan::max('id') ?? '';

            while (true) {
                if (connection_aborted()) break;

                $newScans = SkinScan::with('user')
                    ->where('id', '>', $lastMaxId)
                    ->orderBy('id')
                    ->get();

                foreach ($newScans as $scan) {
                    $data = json_encode([
                        'type' => 'new_scan',
                        'scan' => $this->formatScan($scan),
                    ]);
                    echo "data: {$data}\n\n";
                    ob_flush();
                    flush();
                    if ($scan->id > $lastMaxId) {
                        $lastMaxId = $scan->id;
                    }
                }

                echo ": heartbeat\n\n";
                ob_flush();
                flush();

                sleep(2);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
