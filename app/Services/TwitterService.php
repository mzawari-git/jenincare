<?php

namespace App\Services;

use App\Models\MarketingSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwitterService
{
    private bool $enabled = false;
    private ?string $pixelId = null;
    private ?string $apiKey = null;
    private bool $loaded = false;

    private const API_BASE = 'https://ads-api.x.com';

    public function __construct()
    {
    }

    public function loadSettings(): void
    {
        if ($this->loaded) return;
        try {
            $this->enabled = MarketingSetting::get('twitter_pixel_enabled', false);
            $this->pixelId = MarketingSetting::get('twitter_pixel_id');
            $this->apiKey = MarketingSetting::get('twitter_api_key');
        } catch (\Exception $e) {
            $this->enabled = false;
            $this->pixelId = null;
            $this->apiKey = null;
        }
        $this->loaded = true;
    }

    public function isEnabled(): bool
    {
        $this->loadSettings();
        return $this->enabled && $this->pixelId;
    }

    public function trackEvent(string $eventName, array $eventData, ?array $userData = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'reason' => 'twitter_disabled'];
        }

        try {
            $mappedEvent = $this->mapEventName($eventName);
            $payload = $this->buildPayload($mappedEvent, $eventData, $userData);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post(self::API_BASE . '/2/measurement/conversions', $payload);

            $success = $response->successful();

            if (!$success) {
                Log::warning('Twitter CAPI failed', [
                    'event' => $eventName,
                    'status' => $response->status(),
                ]);
            }

            return ['success' => $success, 'response' => $response->json()];
        } catch (\Exception $e) {
            Log::error('Twitter CAPI exception', [
                'event' => $eventName,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getPixelScript(): string
    {
        if (!$this->isEnabled()) return '';

        $pixelId = e($this->pixelId);
        return <<<JS
<script>
!function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments);},s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,u.src='https://static.ads-twitter.com/uwt.js',a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script');
twq('config','{$pixelId}');
</script>
JS;
    }

    private function mapEventName(string $eventName): string
    {
        $map = [
            'Purchase' => 'Purchase',
            'AddToCart' => 'AddToCart',
            'ViewContent' => 'ViewContent',
            'InitiateCheckout' => 'StartCheckout',
            'Lead' => 'Lead',
            'Subscribe' => 'SignUp',
            'Search' => 'Search',
            'AddToWishlist' => 'AddToWishlist',
        ];
        return $map[$eventName] ?? $eventName;
    }

    private function buildPayload(string $mappedEvent, array $eventData, ?array $userData): array
    {
        return [
            'pixel_id' => $this->pixelId,
            'event' => $mappedEvent,
            'timestamp' => now()->toIso8601String(),
            'value' => $eventData['value'] ?? 0,
            'currency' => $eventData['currency'] ?? 'ILS',
            'num_items' => $eventData['num_items'] ?? 1,
            'conversion_id' => $eventData['order_id'] ?? uniqid('tw_', true),
            'email' => $userData['email'] ?? null,
            'phone' => $userData['phone'] ?? null,
        ];
    }
}
