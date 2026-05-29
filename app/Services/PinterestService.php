<?php

namespace App\Services;

use App\Models\MarketingSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PinterestService
{
    private bool $enabled;
    private ?string $tagId;
    private ?string $accessToken;
    private ?string $adAccountId;

    private const API_BASE = 'https://api.pinterest.com/v5';

    public function __construct()
    {
        $this->loadSettings();
    }

    public function loadSettings(): void
    {
        $this->enabled = MarketingSetting::get('pinterest_tag_enabled', false);
        $this->tagId = MarketingSetting::get('pinterest_tag_id');
        $this->accessToken = MarketingSetting::get('pinterest_access_token');
        $this->adAccountId = MarketingSetting::get('pinterest_ad_account_id');
    }

    public function isEnabled(): bool
    {
        return $this->enabled && $this->tagId;
    }

    public function trackEvent(string $eventName, array $eventData, ?array $userData = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'reason' => 'pinterest_disabled'];
        }

        try {
            $mappedEvent = $this->mapEventName($eventName);
            $payload = $this->buildPayload($mappedEvent, $eventData, $userData);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ])
                ->post(self::API_BASE . '/ad_accounts/' . $this->adAccountId . '/events', $payload);

            $success = $response->successful();

            if (!$success) {
                Log::warning('Pinterest CAPI failed', [
                    'event' => $eventName,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            }

            return ['success' => $success, 'response' => $response->json()];
        } catch (\Exception $e) {
            Log::error('Pinterest CAPI exception', [
                'event' => $eventName,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getTagScript(): string
    {
        if (!$this->isEnabled()) return '';

        $tagId = e($this->tagId);
        return <<<JS
<script>
!function(e){if(!window.pintrk){window.pintrk=function(){window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var n=window.pintrk;n.queue=[],n.version="3.0";var t=document.createElement("script");t.async=!0,t.src=e;var r=document.getElementsByTagName("script")[0];r.parentNode.insertBefore(t,r)}}("https://s.pinimg.com/ct/core.js");
pintrk('load', '{$tagId}', {np: 'jenincare'});
pintrk('page');
</script>
<noscript><img height="1" width="1" style="display:none" alt="" src="https://ct.pinterest.com/v3/?event=init&tid={$tagId}&noscript=1"/></noscript>
JS;
    }

    private function mapEventName(string $eventName): string
    {
        $map = [
            'Purchase' => 'checkout',
            'AddToCart' => 'add_to_cart',
            'ViewContent' => 'page_visit',
            'InitiateCheckout' => 'checkout',
            'Lead' => 'lead',
            'Subscribe' => 'signup',
            'Search' => 'search',
            'AddToWishlist' => 'add_to_cart',
        ];
        return $map[$eventName] ?? strtolower($eventName);
    }

    private function buildPayload(string $mappedEvent, array $eventData, ?array $userData): array
    {
        $data = [
            'event_names' => [$mappedEvent],
            'event_source_url' => request()->fullUrl(),
            'opt_out' => false,
            'partner_name' => 'jenincare',
        ];

        if ($userData && !empty($userData['email'])) {
            $data['user_emails'] = [hash('sha256', strtolower(trim($userData['email'])))];
        }

        if (!empty($eventData['value'])) {
            $data['value'] = (float) $eventData['value'];
            $data['currency'] = $eventData['currency'] ?? 'ILS';
        }

        if (!empty($eventData['order_id'])) {
            $data['order_id'] = (string) $eventData['order_id'];
        }

        return $data;
    }
}
