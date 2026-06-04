<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AnalysisStatus;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateProductRecommendations;
use App\Jobs\ProcessSkinScan;
use App\Models\ScanAuditLog;
use App\Models\ScanDefect;
use App\Models\ScanGeneralTip;
use App\Models\ScanHeatmapPoint;
use App\Models\ScanTimelineEvent;
use App\Models\SkinScan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ScanController extends Controller
{
    protected function storeEncrypted($file, string $path): string
    {
        $raw = file_get_contents($file->getRealPath());
        $encrypted = Crypt::encryptString($raw);
        $encPath = $path . '.enc';
        Storage::disk('public')->put($encPath, $encrypted);
        return $encPath;
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $user = $request->user();

        $path = $request->file('file')->store('scans/' . $user->id, 'public');

        $scan = SkinScan::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'image_url' => Storage::url($path),
            'image_path' => $path,
            'is_locked' => false,
            'analysis_status' => AnalysisStatus::PENDING,
        ]);

        $scan->timelineEvents()->create([
            'status' => 'pending',
            'description' => 'Scan uploaded successfully',
            'description_ar' => 'تم رفع الفحص بنجاح',
            'created_at' => now(),
        ]);

        ProcessSkinScan::dispatch($scan);

        return response()->json([
            'scan' => $this->formatScan($scan),
            'message' => 'Scan uploaded successfully',
        ], 201);
    }

    public function uploadWithMetadata(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'metadata' => 'required|json',
        ]);

        $user = $request->user();
        $metadata = json_decode($request->metadata, true);

        $path = $request->file('file')->store('scans/' . $user->id, 'public');

        $scan = SkinScan::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'image_url' => Storage::url($path),
            'image_path' => $path,
            'is_locked' => false,
            'analysis_status' => AnalysisStatus::PENDING,
            'lighting_quality' => $metadata['lighting_quality'] ?? null,
            'face_confidence' => $metadata['face_confidence'] ?? null,
            'image_width' => $metadata['image_width'] ?? null,
            'image_height' => $metadata['image_height'] ?? null,
            'metadata' => $metadata,
        ]);

        $scan->timelineEvents()->create([
            'status' => 'pending',
            'description' => 'Scan uploaded successfully',
            'description_ar' => 'تم رفع الفحص بنجاح',
            'created_at' => now(),
        ]);

        ProcessSkinScan::dispatch($scan);

        return response()->json([
            'scan_id' => $scan->id,
            'status' => 'pending',
            'message' => 'Scan uploaded successfully',
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = $request->integer('page', 1);

        $scans = SkinScan::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'page', $page);

        return response()->json([
            'scans' => array_map(fn($s) => $this->formatScan($s), $scans->items()),
            'total' => $scans->total(),
            'page' => $scans->currentPage(),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $scan = SkinScan::where('user_id', $user->id)->findOrFail($id);

        return response()->json([
            'scan' => $this->formatScan($scan),
        ]);
    }

    public function status(Request $request, string $scanId): JsonResponse
    {
        $user = $request->user();
        $scan = SkinScan::where('user_id', $user->id)->findOrFail($scanId);

        return response()->json([
            'scan_id' => $scan->id,
            'status' => $scan->status,
            'analysis_status' => $scan->analysis_status,
            'analysis_status_label' => $scan->analysis_status_label,
            'message' => 'Scan is ' . $scan->analysis_status,
        ]);
    }

    public function unlock(Request $request, string $scanId): JsonResponse
    {
        $request->validate(['pin' => 'required|string']);

        $user = $request->user();
        $scan = SkinScan::where('user_id', $user->id)->findOrFail($scanId);

        if (!$scan->is_locked) {
            return response()->json([
                'scan_id' => $scan->id,
                'status' => $scan->status,
                'unlocked' => true,
            ]);
        }

        if ($scan->locked_until && $scan->locked_until->isFuture()) {
            throw ValidationException::withMessages([
                'pin' => ['Too many attempts. Try again later.'],
            ]);
        }

        if ($scan->pin_code !== $request->pin) {
            $scan->increment('pin_attempts');

            if ($scan->pin_attempts >= 5) {
                $scan->update(['locked_until' => now()->addMinutes(30)]);
            }

            return response()->json([
                'unlocked' => false,
                'message' => 'Invalid PIN code',
            ], 403);
        }

        $scan->update([
            'is_locked' => false,
            'pin_attempts' => 0,
            'locked_until' => null,
        ]);

        return response()->json([
            'scan_id' => $scan->id,
            'status' => $scan->status,
            'unlocked' => true,
        ]);
    }

    public function report(Request $request, string $scanId): JsonResponse
    {
        $user = $request->user();
        $scan = SkinScan::where('user_id', $user->id)->findOrFail($scanId);

        if ($scan->is_locked) {
            return response()->json(['message' => 'Scan is locked. Unlock first.'], 403);
        }

        ScanAuditLog::record($scan->id, 'report_viewed', [
            'client_ip' => $request->ip(),
        ]);

        $scan->load(['heatmapPoints', 'defects.products', 'generalTips']);

        $analysisData = $scan->analysis_data ?? [];

        return response()->json([
            'scan' => [
                'id' => $scan->id,
                'user_id' => (string) $scan->user_id,
                'image_url' => $scan->image_url,
                'status' => $scan->status,
                'analysis_status' => $scan->analysis_status,
                'overall_score' => (int) ($analysisData['overall_health_score'] ?? $scan->overall_health_score),
                'confidence' => $scan->confidence_score,
                'analyzed_by' => $scan->analyzed_by_provider,
                'created_at' => $scan->created_at->toISOString(),
                'reviewed_at' => $scan->reviewed_at?->toISOString(),
                'analyzed_at' => $scan->analyzed_at?->toISOString(),
            ],
            'metrics' => $analysisData['radar_metrics'] ?? [
                'hydration' => $scan->hydration,
                'sebum' => $scan->sebum,
                'pigmentation' => $scan->pigmentation,
                'pores' => $scan->pores,
                'elasticity' => $scan->elasticity,
            ],
            'advanced_metrics' => $analysisData['advanced_metrics'] ?? [],
            'spectral_analysis' => $analysisData['spectral_analysis'] ?? [],
            'facial_zone_analysis' => $analysisData['facial_zone_analysis'] ?? [],
            'heatmap_points' => $analysisData['heatmap_coordinates'] ?? $scan->heatmapPoints->map(fn($p) => [
                'x' => $p->x,
                'y' => $p->y,
                'severity' => $p->severity,
                'label' => $p->label,
                'label_ar' => $p->label_ar,
                'description' => $p->description,
                'description_ar' => $p->description_ar,
            ]),
            'defects' => $analysisData['defects'] ?? $scan->defects->map(fn($d) => [
                'id' => $d->id,
                'name_ar' => $d->name_ar,
                'name_en' => $d->name_en,
                'severity' => $d->severity,
                'tip_ar' => $d->tip_ar,
                'tip_en' => $d->tip_en,
                'icon_name' => $d->icon_name,
                'recommended_products' => $d->products->map(fn($p) => [
                    'id' => $p->id,
                    'name_ar' => $p->name_ar,
                    'name_en' => $p->name_en,
                    'price' => (float) $p->final_b2c_price,
                    'image_url' => $p->main_image_url ?? '',
                    'shop_url' => '',
                    'matching_reason' => $p->pivot->matching_reason,
                    'matching_reason_ar' => $p->pivot->matching_reason_ar,
                ]),
            ]),
            'recommended_products' => $analysisData['recommended_products'] ?? $scan->recommended_products ?? [],
            'general_tips' => $analysisData['expert_free_tips'] ?? $scan->generalTips->map(fn($t) => [
                'ar' => $t->tip_ar,
                'en' => $t->tip_en,
            ]),
            'custom_arabic_analysis' => $analysisData['custom_arabic_analysis_text'] ?? $scan->custom_arabic_analysis,
        ]);
    }

    public function timeline(Request $request, string $scanId): JsonResponse
    {
        $user = $request->user();
        $scan = SkinScan::where('user_id', $user->id)->findOrFail($scanId);

        $events = $scan->timelineEvents()->orderBy('created_at', 'asc')->get();

        return response()->json([
            'events' => $events->map(fn($e) => [
                'id' => $e->id,
                'status' => $e->status,
                'timestamp' => $e->created_at->toISOString(),
                'description' => $e->description,
                'description_ar' => $e->description_ar,
            ]),
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $scans = SkinScan::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'scans' => $scans->map(fn($s) => [
                'id' => $s->id,
                'user_id' => (string) $s->user_id,
                'image_url' => $s->image_url,
                'status' => $s->status,
                'analysis_status' => $s->analysis_status,
                'overall_score' => (int) $s->overall_health_score,
                'confidence' => $s->confidence_score,
                'created_at' => $s->created_at->toISOString(),
                'reviewed_at' => $s->reviewed_at?->toISOString(),
                'analyzed_at' => $s->analyzed_at?->toISOString(),
            ]),
        ]);
    }

    public function uploadChunk(Request $request): JsonResponse
    {
        $request->validate([
            'scan_id' => 'required|string',
            'chunk_index' => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1',
            'is_last_chunk' => 'required|boolean',
            'chunk' => 'required|file|max:5120',
        ]);

        $scanId = $request->scan_id;

        $chunkDir = storage_path("app/chunks/{$scanId}");
        if (!is_dir($chunkDir)) {
            mkdir($chunkDir, 0755, true);
        }

        $request->file('chunk')->move($chunkDir, "chunk_{$request->chunk_index}");

        if ($request->boolean('is_last_chunk')) {
            $user = $request->user();
            $finalPath = $this->assembleChunks($chunkDir, $scanId, $user->id);

            return response()->json([
                'scan_id' => $scanId,
                'chunk_index' => $request->chunk_index,
                'status' => 'completed',
                'final_path' => $finalPath,
            ]);
        }

        return response()->json([
            'scan_id' => $scanId,
            'chunk_index' => $request->chunk_index,
            'status' => 'receiving',
        ]);
    }

    private function assembleChunks(string $chunkDir, string $scanId, int $userId): string
    {
        $files = glob($chunkDir . '/chunk_*');
        $totalChunks = count($files);
        $finalPath = "scans/{$userId}/{$scanId}.jpg";

        $outFile = fopen(storage_path("app/public/{$finalPath}"), 'wb');
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunk = file_get_contents("{$chunkDir}/chunk_{$i}");
            fwrite($outFile, $chunk);
        }
        fclose($outFile);

        array_map('unlink', glob($chunkDir . '/*'));
        rmdir($chunkDir);

        $scan = SkinScan::find($scanId);
        if ($scan) {
            $scan->update([
                'image_url' => Storage::url($finalPath),
                'image_path' => $finalPath,
            ]);
        }

        return $finalPath;
    }

    private function formatScan(SkinScan $scan): array
    {
        $analysisData = $scan->analysis_data ?? [];

        return [
            'id' => $scan->id,
            'status' => $scan->status,
            'analysis_status' => $scan->analysis_status,
            'analysis_status_label' => $scan->analysis_status_label,
            'image_url' => $scan->image_url,
            'overall_health_score' => $analysisData['overall_health_score'] ?? $scan->overall_health_score,
            'radar_metrics' => $analysisData['radar_metrics'] ?? [
                'hydration' => $scan->hydration,
                'sebum' => $scan->sebum,
                'pigmentation' => $scan->pigmentation,
                'pores' => $scan->pores,
                'elasticity' => $scan->elasticity,
            ],
            'advanced_metrics' => $analysisData['advanced_metrics'] ?? [],
            'defects' => $analysisData['defects'] ?? [],
            'heatmap_coordinates' => $analysisData['heatmap_coordinates'] ?? $scan->heatmapPoints->map(fn($p) => [
                'x' => $p->x,
                'y' => $p->y,
                'label' => $p->label ?? '',
                'severity' => (string) $p->severity,
            ]),
            'custom_arabic_analysis' => $analysisData['custom_arabic_analysis_text'] ?? $scan->custom_arabic_analysis,
            'expert_free_tips' => $analysisData['expert_free_tips'] ?? $scan->expert_free_tips ?? [],
            'recommended_products' => $analysisData['recommended_products'] ?? [],
            'confidence' => $scan->confidence_score,
            'analyzed_by' => $scan->analyzed_by_provider,
            'created_at' => $scan->created_at->toISOString(),
            'analyzed_at' => $scan->analyzed_at?->toISOString(),
            'approved_at' => $scan->reviewed_at?->toISOString(),
            'is_locked' => $scan->is_locked,
            'pin_required' => !is_null($scan->pin_code),
        ];
    }
}
