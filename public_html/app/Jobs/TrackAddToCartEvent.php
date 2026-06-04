<?php

namespace App\Jobs;

use App\Services\AdvertisingTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TrackAddToCartEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue = 'capi-events';

    public int $tries = 2;
    public int $backoff = 30;

    public function __construct(
        public int $productId,
        public string $productName,
        public float $price,
        public int $quantity,
        public ?int $userId = null
    ) {}

    public function handle(AdvertisingTrackingService $trackingService): void
    {
        try {
            $productData = [
                'id' => $this->productId,
                'name' => $this->productName,
                'price' => $this->price,
                'sku' => (string) $this->productId,
            ];
            $trackingService->trackAddToCart($productData, $this->quantity);

            Log::info('AddToCart tracking job completed', [
                'product_id' => $this->productId,
            ]);

        } catch (\Exception $e) {
            Log::error('AddToCart tracking job failed', [
                'product_id' => $this->productId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
