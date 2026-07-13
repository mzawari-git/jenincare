<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AnalysisStatus;
use App\Events\ScanApproved;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BatchApproveRequest;
use App\Http\Requests\Admin\RejectScanRequest;
use App\Models\SkinAnalysis;
use App\Models\SkinAnalysisPin;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\AI\BroadcastService;

class ScanApprovalController extends Controller
{
    public function __construct(
        private readonly BroadcastService $broadcastService,
    ) {}

    public function approve(int $id): JsonResponse
    {
        $scan = SkinAnalysis::where('status', AnalysisStatus::PENDING->value)->findOrFail($id);

        DB::transaction(function () use ($scan) {
            $scan->update([
                'status' => AnalysisStatus::APPROVED->value,
                'approved_at' => Carbon::now(),
            ]);

            event(new ScanApproved($scan));
        });

        $scan->refresh()->load(['user', 'accessPin']);

        return response()->json([
            'message' => 'Scan approved successfully.',
            'data' => $scan,
        ]);
    }

    public function reject(int $id, RejectScanRequest $request): JsonResponse
    {
        $scan = SkinAnalysis::where('status', AnalysisStatus::PENDING->value)->findOrFail($id);

        $scan->update([
            'status' => AnalysisStatus::REJECTED->value,
            'custom_arabic_analysis' => $request->input('reason'),
        ]);

        return response()->json([
            'message' => 'Scan rejected successfully.',
            'data' => $scan->fresh(),
        ]);
    }

    public function generatePin(int $id): JsonResponse
    {
        $scan = SkinAnalysis::with('accessPin')->findOrFail($id);

        if ($scan->status === AnalysisStatus::PENDING->value) {
            return response()->json([
                'message' => 'Cannot generate PIN for a pending scan. Approve or reject first.',
            ], 422);
        }

        if ($scan->accessPin && $scan->accessPin->isValid()) {
            return response()->json([
                'message' => 'An active PIN already exists for this scan.',
                'data' => [
                    'pin_code' => $scan->accessPin->pin_code,
                    'expires_at' => $scan->accessPin->expires_at,
                ],
            ]);
        }

        $pinCode = SkinAnalysisPin::generatePin();

        $pin = SkinAnalysisPin::create([
            'skin_analysis_id' => $scan->id,
            'pin_code' => $pinCode,
            'is_used' => false,
            'expires_at' => Carbon::now()->addMinutes(config('skinanalyzer.pin.expiry_minutes', 30)),
        ]);

        return response()->json([
            'message' => 'PIN generated successfully.',
            'data' => [
                'pin_code' => $pin->pin_code,
                'expires_at' => $pin->expires_at,
                'pin_id' => $pin->id,
            ],
        ], 201);
    }

    public function batchApprove(BatchApproveRequest $request): JsonResponse
    {
        $ids = $request->input('ids');

        $scans = SkinAnalysis::whereIn('id', $ids)
            ->where('status', AnalysisStatus::PENDING->value)
            ->get();

        if ($scans->isEmpty()) {
            return response()->json([
                'message' => 'No pending scans found with the provided IDs.',
            ], 404);
        }

        $approvedCount = 0;

        DB::transaction(function () use ($ids, &$approvedCount) {
            $now = Carbon::now();

            SkinAnalysis::whereIn('id', $ids)
                ->where('status', AnalysisStatus::PENDING->value)
                ->update([
                    'status' => AnalysisStatus::APPROVED->value,
                    'approved_at' => $now,
                ]);

            $scans = SkinAnalysis::whereIn('id', $ids)->get();
            foreach ($scans as $scan) {
                event(new ScanApproved($scan));
            }

            $approvedCount = $scans->count();
        });

        return response()->json([
            'message' => "{$approvedCount} scan(s) approved successfully.",
            'approved_count' => $approvedCount,
        ]);
    }

    public function broadcastResult(int $id): JsonResponse
    {
        $scan = SkinAnalysis::with(['user', 'accessPin', 'recommendedProducts'])->findOrFail($id);

        if ($scan->status !== AnalysisStatus::APPROVED->value) {
            return response()->json([
                'message' => 'Only approved scans can be broadcast.',
            ], 422);
        }

        $broadcastData = [
            'scan_id' => $scan->id,
            'status' => $scan->status,
            'overall_health_score' => $scan->overall_health_score,
            'radar_metrics' => $scan->radar_metrics,
            'custom_arabic_analysis' => $scan->custom_arabic_analysis,
            'expert_free_tips' => $scan->expert_free_tips,
            'products' => $scan->recommendedProducts->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'name_ar' => $p->name_ar,
                'image_url' => $p->image_url,
                'price' => $p->price,
                'matching_reason' => $p->pivot->matching_reason,
            ]),
            'approved_at' => $scan->approved_at,
            'pin_code' => $scan->accessPin?->pin_code,
        ];

        $this->broadcastService->sendToUser($scan->user_id, 'scan.approved', $broadcastData);

        return response()->json([
            'message' => 'Result broadcast successfully.',
            'data' => $broadcastData,
        ]);
    }
}
