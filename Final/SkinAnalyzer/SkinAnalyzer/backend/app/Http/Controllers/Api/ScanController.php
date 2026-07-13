<?php

namespace App\Http\Controllers\Api;

use App\Enums\AnalysisStatus;
use App\Events\ScanCreated;
use App\Http\Controllers\Controller;
use App\Models\SkinAnalysis;
use App\Models\SkinAnalysisPin;
use App\Jobs\ProcessSkinScan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $scans = SkinAnalysis::with(['recommendedProducts'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 10));

        return response()->json([
            'data' => $scans->items(),
            'meta' => [
                'current_page' => $scans->currentPage(),
                'last_page' => $scans->lastPage(),
                'per_page' => $scans->perPage(),
                'total' => $scans->total(),
            ],
        ]);
    }

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

        return response()->json([
            'message' => 'Scan submitted successfully.',
            'data' => [
                'id' => $scan->id,
                'status' => $scan->status,
            ],
        ], 201);
    }

    public function uploadWithProgress(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpeg,png,webp,bmp', 'max:' . config('skinanalyzer.upload.max_size', 10240)],
            'metadata' => ['nullable', 'string'],
        ]);

        $imagePath = $request->file('file')->store('scans', 'local');

        $metadata = null;
        if ($request->filled('metadata')) {
            $metadata = json_decode($request->input('metadata'), true);
        }

        $scan = SkinAnalysis::create([
            'user_id' => $request->user()->id,
            'image_path' => $imagePath,
            'status' => AnalysisStatus::PENDING->value,
            'ai_provider_id' => $this->resolveProviderId(),
            'metadata' => $metadata ? json_encode($metadata) : null,
        ]);

        if ($request->hasFile('spectral_0')) {
            $spectralPaths = [];
            $i = 0;
            while ($request->hasFile("spectral_$i")) {
                $spectralPaths[] = $request->file("spectral_$i")->store('spectral', 'local');
                $i++;
            }
            $scan->spectral_image_paths = json_encode($spectralPaths);
            $scan->save();
        }

        event(new ScanCreated($scan));
        ProcessSkinScan::dispatch($scan->id);

        return response()->json([
            'scan_id' => (string) $scan->id,
            'status' => $scan->status,
            'message' => 'Scan submitted successfully.',
        ], 201);
    }

    public function uploadChunk(Request $request): JsonResponse
    {
        $request->validate([
            'scan_id' => ['required', 'string'],
            'chunk_index' => ['required', 'integer', 'min:0'],
            'total_chunks' => ['required', 'integer', 'min:1'],
            'is_last_chunk' => ['required', 'boolean'],
            'chunk' => ['required', 'file', 'max:5120'],
        ]);

        $scanId = $request->input('scan_id');
        $chunkIndex = (int) $request->input('chunk_index');
        $totalChunks = (int) $request->input('total_chunks');
        $isLastChunk = filter_var($request->input('is_last_chunk'), FILTER_VALIDATE_BOOLEAN);

        $chunkDir = storage_path("app/chunks/{$scanId}");
        if (!is_dir($chunkDir)) {
            mkdir($chunkDir, 0755, true);
        }

        $request->file('chunk')->move($chunkDir, "chunk_{$chunkIndex}");

        if ($isLastChunk) {
            $finalPath = $this->assembleChunks($scanId, $chunkDir, $totalChunks);

            $scan = SkinAnalysis::create([
                'user_id' => $request->user()->id,
                'image_path' => $finalPath,
                'status' => AnalysisStatus::PENDING->value,
                'ai_provider_id' => $this->resolveProviderId(),
            ]);

            $this->cleanupChunks($chunkDir);

            event(new ScanCreated($scan));
            ProcessSkinScan::dispatch($scan->id);

            return response()->json([
                'scan_id' => (string) $scan->id,
                'chunk_index' => $chunkIndex,
                'status' => $scan->status,
            ]);
        }

        return response()->json([
            'scan_id' => $scanId,
            'chunk_index' => $chunkIndex,
            'status' => 'chunk_received',
        ]);
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

    public function status(Request $request, int $id): JsonResponse
    {
        $scan = SkinAnalysis::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json([
            'scan_id' => (string) $scan->id,
            'status' => $scan->status,
            'message' => null,
        ]);
    }

    public function report(Request $request, int $id): JsonResponse
    {
        $scan = SkinAnalysis::with(['recommendedProducts', 'accessPin'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        if ($scan->getIsLockedAttribute()) {
            return response()->json(['message' => 'Scan is locked.'], 403);
        }

        return response()->json([
            'scan' => [
                'id' => (string) $scan->id,
                'user_id' => (string) $scan->user_id,
                'image_url' => $scan->image_path ? Storage::url($scan->image_path) : null,
                'spectral_image_urls' => $scan->spectral_image_paths ? json_decode($scan->spectral_image_paths, true) : null,
                'status' => $scan->status,
                'analysis_status' => $scan->status,
                'overall_score' => $scan->overall_health_score ?? 0,
                'confidence' => $scan->analysis_confidence,
                'analyzed_by' => $scan->aiProvider?->name,
                'created_at' => $scan->created_at?->toIso8601String(),
                'reviewed_at' => $scan->approved_at?->toIso8601String(),
                'analyzed_at' => $scan->analyzed_at?->toIso8601String(),
            ],
            'metrics' => [
                'hydration' => $scan->metrics_hydration ?? 0,
                'sebum' => $scan->metrics_sebum ?? 0,
                'pigmentation' => $scan->metrics_pigmentation ?? 0,
                'pores' => $scan->metrics_pores ?? 0,
                'elasticity' => $scan->metrics_elasticity ?? 0,
            ],
            'advanced_metrics' => $scan->advanced_metrics ? json_decode($scan->advanced_metrics, true) : null,
            'heatmap_points' => $scan->heatmap_coordinates ? json_decode($scan->heatmap_coordinates, true) : [],
            'defects' => [],
            'general_tips' => [],
            'spectral_analysis' => null,
            'facial_zone_analysis' => null,
            'custom_arabic_analysis' => $scan->custom_arabic_analysis,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $scans = SkinAnalysis::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($s) => [
                'id' => (string) $s->id,
                'user_id' => (string) $s->user_id,
                'image_url' => $s->image_path ? Storage::url($s->image_path) : null,
                'spectral_image_urls' => $s->spectral_image_paths ? json_decode($s->spectral_image_paths, true) : null,
                'status' => $s->status,
                'analysis_status' => $s->status,
                'overall_score' => $s->overall_health_score ?? 0,
                'confidence' => $s->analysis_confidence,
                'analyzed_by' => $s->aiProvider?->name,
                'created_at' => $s->created_at?->toIso8601String(),
                'reviewed_at' => $s->approved_at?->toIso8601String(),
                'analyzed_at' => $s->analyzed_at?->toIso8601String(),
            ]);

        return response()->json(['scans' => $scans]);
    }

    public function timeline(Request $request, int $id): JsonResponse
    {
        $scan = SkinAnalysis::where('user_id', $request->user()->id)->findOrFail($id);

        $events = [
            [
                'event' => 'submitted',
                'status' => 'تم رفع الصورة',
                'status_en' => 'Image uploaded',
                'timestamp' => $scan->created_at?->toIso8601String(),
            ],
        ];

        if ($scan->analyzed_at) {
            $events[] = [
                'event' => 'analyzed',
                'status' => 'تم التحليل',
                'status_en' => 'Analysis complete',
                'timestamp' => $scan->analyzed_at->toIso8601String(),
            ];
        }

        if ($scan->approved_at) {
            $events[] = [
                'event' => 'approved',
                'status' => 'تمت المراجعة',
                'status_en' => 'Review complete',
                'timestamp' => $scan->approved_at->toIso8601String(),
            ];
        }

        return response()->json(['events' => $events]);
    }

    public function unlock(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'pin_code' => ['required', 'string', 'size:4'],
        ]);

        $scan = SkinAnalysis::where('user_id', $request->user()->id)->findOrFail($id);

        $pin = $scan->accessPin;

        if (! $pin) {
            return response()->json(['message' => 'لم يتم العثور على رمز PIN لهذا الفحص.'], 400);
        }

        if ($pin->isLocked()) {
            return response()->json([
                'message' => 'تم قفل PIN مؤقتاً بسبب محاولات خاطئة كثيرة. حاول مرة أخرى بعد 15 دقيقة.',
                'locked_until' => $pin->locked_until->toIso8601String(),
            ], 429);
        }

        if ($pin->pin_code !== $request->input('pin_code')) {
            $pin->recordFailedAttempt();

            return response()->json([
                'message' => 'رمز PIN غير صحيح.',
                'attempts_remaining' => $pin->getRemainingAttempts(),
            ], 422);
        }

        if (! $pin->isValid()) {
            $status = $pin->is_used ? 'مستخدم بالفعل' : 'منتهي الصلاحية';
            return response()->json(['message' => "رمز PIN {$status}."], 422);
        }

        $pin->markUsed();

        return response()->json([
            'message' => 'تم فتح الفحص بنجاح.',
            'data' => [
                'scan_id' => $scan->id,
                'overall_health_score' => $scan->overall_health_score,
                'radar_metrics' => $scan->radar_metrics,
                'custom_arabic_analysis' => $scan->custom_arabic_analysis,
                'expert_free_tips' => $scan->expert_free_tips,
            ],
        ]);
    }

    public function addToCart(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'string'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $scan = SkinAnalysis::where('user_id', $request->user()->id)->findOrFail($id);

        $productId = $request->input('product_id');

        $existingPivot = $scan->recommendedProducts()
            ->where('product_id', $productId)
            ->first();

        if (! $existingPivot) {
            $scan->recommendedProducts()->attach($productId, [
                'matching_reason' => $request->input('matching_reason', 'أضيف بواسطة المستخدم'),
            ]);
        }

        return response()->json([
            'message' => 'أضيف إلى السلة بنجاح',
            'scan_id' => (string) $scan->id,
        ]);
    }

    private function resolveProviderId(): ?int
    {
        $provider = \App\Models\AIProvider::active()->first();

        return $provider?->id;
    }

    private function assembleChunks(string $scanId, string $chunkDir, int $totalChunks): string
    {
        $finalDir = storage_path("app/scans");
        if (!is_dir($finalDir)) {
            mkdir($finalDir, 0755, true);
        }

        $finalPath = "{$finalDir}/{$scanId}_assembled.jpg";
        $out = fopen($finalPath, 'wb');

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkFile = "{$chunkDir}/chunk_{$i}";
            if (file_exists($chunkFile)) {
                $in = fopen($chunkFile, 'rb');
                stream_copy_to_stream($in, $out);
                fclose($in);
            }
        }

        fclose($out);

        return "scans/{$scanId}_assembled.jpg";
    }

    private function cleanupChunks(string $chunkDir): void
    {
        array_map('unlink', glob("{$chunkDir}/*"));
        rmdir($chunkDir);
    }
}
