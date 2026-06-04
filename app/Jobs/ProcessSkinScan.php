<?php

namespace App\Jobs;

use App\Models\SkinScan;
use App\Notifications\ScanCompleted;
use App\Services\AI\AIOrchestrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSkinScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;

    public int $tries = 3;

    public int $backoff = 5;

    protected SkinScan $skinScan;

    protected ?string $preferredProvider;

    public function __construct(SkinScan $skinScan, ?string $preferredProvider = null)
    {
        $this->skinScan = $skinScan;
        $this->preferredProvider = $preferredProvider;
    }

    public function handle(AIOrchestrator $orchestrator): void
    {
        try {
            Log::info('ProcessSkinScan started', [
                'scan_id' => $this->skinScan->id,
                'provider' => $this->preferredProvider ?? 'auto',
            ]);

            $this->skinScan->updateQuietly([
                'analysis_status' => \App\Enums\AnalysisStatus::PROCESSING,
                'analyzed_at' => null,
            ]);

            $result = $orchestrator->analyze($this->skinScan, $this->preferredProvider);

            Log::info('ProcessSkinScan completed', [
                'scan_id' => $this->skinScan->id,
                'score' => $result->overallHealthScore,
                'defects' => count($result->defects),
                'provider' => $result->provider,
            ]);

            try {
                $this->skinScan->user?->notify(new ScanCompleted($this->skinScan));
            } catch (\Throwable $e) {
                Log::warning('Failed to send scan completion notification', [
                    'scan_id' => $this->skinScan->id,
                    'error' => $e->getMessage(),
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('ProcessSkinScan failed', [
                'scan_id' => $this->skinScan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->skinScan->updateQuietly([
                'analysis_status' => \App\Enums\AnalysisStatus::FAILED,
                'analysis_data' => [
                    'error' => true,
                    'message' => $e->getMessage(),
                    'failed_at' => now()->toIso8601String(),
                ],
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ProcessSkinScan job failed permanently', [
            'scan_id' => $this->skinScan->id,
            'error' => $e->getMessage(),
        ]);

        $this->skinScan->updateQuietly([
            'analysis_status' => \App\Enums\AnalysisStatus::FAILED,
        ]);
    }
}
