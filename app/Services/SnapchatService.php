<?php

namespace App\Services;

use App\Models\MarketingSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SnapchatService
{
    private bool $enabled;
    private ?string $pixelId;
    private ?string $apiToken;

    private const API_BASE = 'https://tr.snapchat.com/v2';

    public function __construct()
    {
        $this->loadSettings();
    }

    public function loadSettings(): void
    {
        $this->enabled = MarketingSetting::get('snapchat_pixel_enabled', false);
        $this->pixelId = MarketingSetting::get('snapchat_pixel_id');
        $this->apiToken = MarketingSetting::get('snapchat_api_token');
    }

    public function isEnabled(): bool
    {
        return $this->enabled && $this->pixelId;
    }

    public function trackEvent(string $eventName, array $eventData, ?array $userData = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'reason' => 'snapchat_disabled'];
        }

        try {
            $mappedEvent = $this->mapEventName($eventName);
            $payload = $this->buildPayload($mappedEvent, $eventData, $userData);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Api-Token' => $this->apiToken,
                ])
                ->post(self::API_BASE . '/conversion', $payload);

            $success = $response->successful();

            if (!$success) {
                Log::warning('Snapchat CAPI failed', [
                    'event' => $eventName,
                    'status' => $response->status(),
                ]);
            }

            return ['success' => $success, 'response' => $response->json()];
        } catch (\Exception $e) {
            Log::error('Snapchat CAPI exception', [
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
(function(){var e=!1;var t=document.createElement("script");t.src="https://tr.snapchat.com/s/${pixelId}.js";t.async=!0;t.onload=function(){e=!0;snaptr("init","${pixelId}",{});snaptr("track","PAGE_VIEW")};document.getElementsByTagName("head")[0].appendChild(t)})();
</script>
JS;
    }

    private function mapEventName(string $eventName): string
    {
        $map = [
            'Purchase' => 'PURCHASE',
            'AddToCart' => 'ADD_CART',
            'ViewContent' => 'VIEW_CONTENT',
            'InitiateCheckout' => 'START_CHECKOUT',
            'Lead' => 'LEAD',
            'Subscribe' => 'SIGN_UP',
            'Search' => 'SEARCH',
            'AddPaymentInfo' => 'ADD_BILLING',
            'AddToWishlist' => 'SAVE',
        ];
        return $map[$eventName] ?? strtoupper($eventName);
    }

    private function buildPayload(string $mappedEvent, array $eventData, ?array $userData): array
    {
        return [
            'integration' => 'serverside',
            'event_type' => $mappedEvent,
            'event_conversion_type' => 'web',
            'timestamp' => now()->toIso8601String(),
            'pixel_id' => $this->pixelId,
            'hashed_email' => $this->hashUserField($userData['email'] ?? null),
            'hashed_phone_number' => $this->hashUserField($userData['phone'] ?? null),
            'item_ids' => [$eventData['content_ids'] ?? $eventData['order_id'] ?? ''],
            'item_category' => $eventData['content_category'] ?? '',
            'price' => $eventData['value'] ?? 0,
            'currency' => $eventData['currency'] ?? 'ILS',
            'number_items' => $eventData['num_items'] ?? 1,
        ];
    }

    private function hashUserField(?string $value): ?string
    {
        if (empty($value)) return null;
        return hash('sha256', strtolower(trim($value)));
    }
}
