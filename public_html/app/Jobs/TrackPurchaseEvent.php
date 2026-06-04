<?php

namespace App\Jobs;

use App\Services\AdvertisingTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TrackPurchaseEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];
    public $timeout = 30;

    private $order;
    private array $userData;

    public function __construct($order, array $userData = [])
    {
        $this->order = $order;
        $this->userData = $userData;
        $this->onQueue('capi-events');
    }

    public function handle(AdvertisingTrackingService $tracking): void
    {
        if (!$tracking->isEnabled()) {
            Log::info('TrackPurchaseEvent skipped: tracking disabled');
            return;
        }

        $result = $tracking->trackPurchase($this->order, $this->userData);

        Log::info('TrackPurchaseEvent completed', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number ?? 'N/A',
            'result' => $result,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('TrackPurchaseEvent failed', [
            'order_id' => $this->order->id,
            'error' => $e->getMessage(),
        ]);
    }
}
