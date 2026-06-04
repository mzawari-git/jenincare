<?php

namespace App\Services;

use App\Models\MarketingSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleAdsService
{
    private bool $enabled;
    private ?string $conversionId;
    private ?string $conversionLabel;
    private ?string $googleAdsId;
    private ?string $developerToken;
    private ?string $clientId;
    private ?string $clientSecret;
    private ?string $refreshToken;

    private const API_BASE = 'https://googleads.googleapis.com';

    public function __construct()
    {
        $this->loadSettings();
    }

    public function loadSettings(): void
    {
        $this->enabled = MarketingSetting::get('google_ads_enabled', false);
        $this->conversionId = MarketingSetting::get('google_conversion_id');
        $this->conversionLabel = MarketingSetting::get('google_conversion_label');
        $this->googleAdsId = MarketingSetting::get('google_ads_cid');
        $this->developerToken = MarketingSetting::get('google_ads_developer_token');
        $this->clientId = config('services.google.client_id');
        $this->clientSecret = config('services.google.client_secret');
        $this->refreshToken = MarketingSetting::get('google_ads_refresh_token');
    }

    public function isEnabled(): bool
    {
        return $this->enabled && $this->conversionId;
    }

    public function trackConversion(string $eventName, array $eventData, ?array $userData = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'reason' => 'google_ads_disabled'];
        }

        try {
            $mappedEvent = $this->mapEventName($eventName);
            $conversionData = $this->buildConversionData($mappedEvent, $eventData, $userData);

            if (config('tracking.async_mode', true)) {
                \App\Jobs\SendGoogleConversion::dispatch($conversionData)->onQueue('capi-events');
                return ['success' => true, 'queued' => true];
            }

            return $this->sendConversion($conversionData);
        } catch (\Exception $e) {
            Log::error('Google Ads conversion error', [
                'event' => $eventName,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getGoogleTagScript(): string
    {
        if (!$this->isEnabled()) return '';

        $conversionId = e($this->conversionId);
        return <<<JS
<script async src="https://www.googletagmanager.com/gtag/js?id={$conversionId}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{$conversionId}');
</script>
JS;
    }

    public function getEventSnippet(string $eventName, array $eventData = []): string
    {
        if (!$this->isEnabled()) return '';

        $mapped = $this->mapEventName($eventName);
        $value = $eventData['value'] ?? 0;
        $currency = $eventData['currency'] ?? 'ILS';
        $conversionId = e($this->conversionId);
        $label = e($this->conversionLabel ?? '');
        $orderId = e($eventData['order_id'] ?? '');

        return <<<JS
<script>
gtag('event', '{$mapped}', {
  'send_to': '{$conversionId}/{$label}',
  'value': {$value},
  'currency': '{$currency}',
  'transaction_id': '{$orderId}',
});
</script>
JS;
    }

    private function mapEventName(string $eventName): string
    {
        $map = [
            'Purchase' => 'purchase',
            'AddToCart' => 'add_to_cart',
            'ViewContent' => 'view_item',
            'InitiateCheckout' => 'begin_checkout',
            'Lead' => 'generate_lead',
            'Subscribe' => 'sign_up',
            'Search' => 'search',
            'Contact' => 'contact',
            'AddPaymentInfo' => 'add_payment_info',
            'AddToWishlist' => 'add_to_wishlist',
        ];

        return $map[$eventName] ?? strtolower($eventName);
    }

    private function buildConversionData(string $mappedEvent, array $eventData, ?array $userData): array
    {
        $conversion = [
            'conversionAction' => $this->conversionId . '/' . $this->conversionLabel,
            'conversionDateTime' => now()->toRfc3339String(),
            'conversionValue' => $eventData['value'] ?? 0,
            'currencyCode' => $eventData['currency'] ?? 'ILS',
        ];

        if (!empty($eventData['order_id'])) {
            $conversion['orderId'] = (string) $eventData['order_id'];
        }

        if ($userData) {
            $hashedData = [];
            if (!empty($userData['email'])) {
                $hashedData['hashedEmail'] = base64_encode(hash('sha256', strtolower(trim($userData['email'])), true));
            }
            if (!empty($userData['phone'])) {
                $phone = preg_replace('/[^0-9]/', '', $userData['phone']);
                $hashedData['hashedPhone'] = base64_encode(hash('sha256', $phone, true));
            }
            if (!empty($hashedData)) {
                $conversion['userIdentifiers'] = [$hashedData];
            }
        }

        return $conversion;
    }

    private function sendConversion(array $conversionData): array
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return ['success' => false, 'error' => 'no_access_token'];
            }

            $customerId = $this->googleAdsId;
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'developer-token' => $this->developerToken,
                    'Content-Type' => 'application/json',
                ])
                ->post(self::API_BASE . "/v17/customers/{$customerId}/offlineUserDataJobs:addOperations", [
                    'operations' => [[
                        'create' => [
                            'conversionAction' => $conversionData['conversionAction'],
                            'conversionDateTime' => $conversionData['conversionDateTime'],
                            'conversionValue' => $conversionData['conversionValue'],
                            'currencyCode' => $conversionData['currencyCode'],
                            'orderId' => $conversionData['orderId'] ?? null,
                            'userIdentifiers' => $conversionData['userIdentifiers'] ?? [],
                        ],
                    ]],
                ]);

            $success = $response->successful();
            $body = $response->json();

            if (!$success) {
                Log::warning('Google Ads API failed', [
                    'status' => $response->status(),
                    'body' => $body,
                ]);
            }

            return [
                'success' => $success,
                'response' => $body,
            ];
        } catch (\Exception $e) {
            Log::error('Google Ads API exception', [
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function getAccessToken(): ?string
    {
        if (!$this->refreshToken) return null;

        try {
            $response = Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $this->refreshToken,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Google OAuth token refresh failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }
}
