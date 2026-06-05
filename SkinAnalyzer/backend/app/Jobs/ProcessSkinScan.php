<?php

namespace App\Jobs;

use App\Events\QuotaExceeded;
use App\Models\SkinAnalysis;
use App\Services\AI\AIOrchestrator;
use App\Http\Middleware\EncryptScanImages;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

            $processed = $orchestrator->processScan($imageData);
            $result = $processed['result'];
            $providerKey = $processed['provider'];

            $orchestrator->persistScanResult($scan, $result, $providerKey);

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
}
