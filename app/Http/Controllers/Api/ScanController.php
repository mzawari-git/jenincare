<?php

namespace App\Http\Controllers\Api;

use App\Enums\AnalysisStatus;
use App\Events\ScanCreated;
use App\Events\ScanApproved;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SkinAnalysis;
use App\Models\SkinAnalysisPin;
use App\Jobs\ProcessSkinScan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScanController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,webp,bmp', 'max:' . config('skinanalyzer.upload.max_size', 10240)],
        ]);

        $imagePath = $request->file('image')->store('scans', 'local');

        if ($request->get('_encrypt_image', false)) {
            $imagePath = \App\Http\Middleware\EncryptScanImages::encryptFile($imagePath);
        }

        $scan = SkinAnalysis::create([
            'user_id' => $request->user()->id,
            'image_path' => $imagePath,
            'status' => AnalysisStatus::PENDING->value,
            'ai_provider_id' => $this->resolveProviderId(),
        ]);

        event(new ScanCreated($scan));

        ProcessSkinScan::dispatch($scan->id);

        $freeMode = Setting::get('skinanalyzer.free_scan_mode', false);

        return response()->json([
            'message' => $freeMode ? 'Scan submitted and auto-approved.' : 'Scan submitted successfully.',
            'data' => [
                'id' => $scan->id,
                'status' => $freeMode ? 'approved' : $scan->status,
                'free_mode' => $freeMode,
            ],
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $scans = SkinAnalysis::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json(['data' => $scans]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $scan = SkinAnalysis::with(['recommendedProducts'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        $accessPin = $scan->accessPin;

        if ($scan->getIsLockedAttribute()) {
            return response()->json([
                'message' => 'This scan is locked. Enter the PIN or wait for it to be unlocked.',
                'data' => [
                    'id' => $scan->id,
                    'status' => $scan->status,
                    'is_locked' => true,
                    'created_at' => $scan->created_at,
                ],
            ]);
        }

        return response()->json([
            'data' => [
                'id' => $scan->id,
                'status' => $scan->status,
                'overall_health_score' => $scan->overall_health_score,
                'formatted_score' => $scan->formatted_score,
                'radar_metrics' => $scan->radar_metrics,
                'heatmap_coordinates' => $scan->heatmap_coordinates,
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
                'pin' => $accessPin ? [
                    'pin_code' => $accessPin->pin_code,
                    'is_used' => $accessPin->is_used,
                    'expires_at' => $accessPin->expires_at,
                ] : null,
                'approved_at' => $scan->approved_at,
                'created_at' => $scan->created_at,
            ],
        ]);
    }

    public function unlock(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'pin_code' => ['required', 'string', 'size:4'],
        ]);

        $scan = SkinAnalysis::where('user_id', $request->user()->id)->findOrFail($id);

        $pin = $scan->accessPin;

        if (! $pin) {
            return response()->json(['message' => 'No PIN exists for this scan.'], 400);
        }

        if ($pin->pin_code !== $request->input('pin_code')) {
            return response()->json([
                'message' => 'Invalid PIN code.',
                'attempts_remaining' => null,
            ], 422);
        }

        if (! $pin->isValid()) {
            $status = $pin->is_used ? 'already used' : 'expired';
            return response()->json(['message' => "This PIN has been {$status}."], 422);
        }

        $pin->markUsed();

        return response()->json([
            'message' => 'Scan unlocked successfully.',
            'data' => [
                'scan_id' => $scan->id,
                'overall_health_score' => $scan->overall_health_score,
                'radar_metrics' => $scan->radar_metrics,
                'custom_arabic_analysis' => $scan->custom_arabic_analysis,
                'expert_free_tips' => $scan->expert_free_tips,
            ],
        ]);
    }

    private function resolveProviderId(): ?int
    {
        $provider = \App\Models\AIProvider::active()->first();

        return $provider?->id;
    }
}
