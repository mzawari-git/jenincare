<?php

namespace App\Jobs;

use App\Services\GoogleAdsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendGoogleConversion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 60, 120];
    public int $timeout = 30;

    public function __construct(
        public array $conversionData
    ) {
        $this->onQueue('capi-events');
    }

    public function handle(GoogleAdsService $googleAds): void
    {
        $result = $googleAds->trackConversion(
            $this->conversionData['event_name'] ?? 'purchase',
            $this->conversionData
        );

        if (!$result['success']) {
            Log::warning('SendGoogleConversion failed', $result);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendGoogleConversion failed permanently', [
            'error' => $e->getMessage(),
            'conversion_data' => $this->conversionData,
        ]);
    }
}
