<?php

namespace App\Jobs;

use App\Enums\AnalysisStatus;
use App\Events\QuotaExceeded;
use App\Models\SkinAnalysis;
use App\Services\AI\AIOrchestrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Middleware\EncryptScanImages;

class ProcessSkinScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 3;

    public int $backoff = 30;

    public function __construct(
        public readonly int $scanId,
    ) {}

    public function handle(AIOrchestrator $orchestrator): void
    {
        $scan = SkinAnalysis::findOrFail($this->scanId);

        try {
            $imageData = $this->decryptImage($scan->image_path);

            $result = $orchestrator->processScan($imageData);

            $normalizedResult = $this->normalizeResult($result, $scan);

            $scan->update([
                'radar_metrics' => $normalizedResult['radar_metrics'],
                'heatmap_coordinates' => $normalizedResult['heatmap_coordinates'],
                'overall_health_score' => $normalizedResult['overall_health_score'],
                'custom_arabic_analysis' => $normalizedResult['custom_arabic_analysis'],
                'expert_free_tips' => $normalizedResult['expert_free_tips'],
                'raw_vendor_response' => $result,
                'status' => AnalysisStatus::PENDING->value,
            ]);

            GenerateProductRecommendations::dispatch($scan->id);
        } catch (QuotaExceeded $e) {
            event(new \App\Events\QuotaExceeded($scan->aiProvider));

            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff * $this->attempts());
            } else {
                $scan->update([
                    'status' => AnalysisStatus::PENDING->value,
                    'custom_arabic_analysis' => 'Analysis delayed: AI provider quota reached. Admin will review shortly.',
                ]);

                Log::error('ProcessSkinScan exceeded retries due to quota.', [
                    'scan_id' => $scan->id,
                    'attempts' => $this->attempts(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('ProcessSkinScan failed.', [
                'scan_id' => $scan->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $scan->update([
                    'status' => AnalysisStatus::PENDING->value,
                    'custom_arabic_analysis' => 'Analysis failed. Admin review required.',
                ]);
            }

            throw $e;
        }
    }

    private function decryptImage(string $imagePath): array
    {
        return [
            'path' => $imagePath,
            'contents' => EncryptScanImages::decryptFile($imagePath),
            'mime_type' => Storage::disk('local')->mimeType($imagePath) ?: 'image/jpeg',
        ];
    }

    private function normalizeResult(array $result, SkinAnalysis $scan): array
    {
        return [
            'radar_metrics' => $result['radar_metrics'] ?? [
                'brightness' => 0,
                'texture' => 0,
                'hydration' => 0,
                'pigmentation' => 0,
                'pores' => 0,
                'sensitivity' => 0,
            ],
            'heatmap_coordinates' => $result['heatmap_coordinates'] ?? [],
            'overall_health_score' => $result['overall_health_score']
                ?? $result['health_score']
                ?? $this->calculateScore($result['radar_metrics'] ?? []),
            'custom_arabic_analysis' => $result['custom_arabic_analysis']
                ?? $result['analysis_text']
                ?? $result['arabic_analysis']
                ?? '',
            'expert_free_tips' => $result['expert_free_tips']
                ?? $result['tips']
                ?? $result['recommendations']
                ?? [],
        ];
    }

    private function calculateScore(array $metrics): int
    {
        if (empty($metrics)) {
            return 0;
        }

        $total = array_sum(array_values($metrics));
        $count = count($metrics);

        return $count > 0 ? (int) round($total / $count) : 0;
    }
}
