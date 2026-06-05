<?php

namespace App\Services;

use App\Models\MarketingSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinkedInService
{
    private bool $enabled = false;
    private ?string $partnerId = null;
    private ?string $conversionRuleId = null;
    private ?string $accessToken = null;
    private bool $loaded = false;

    private const API_BASE = 'https://api.linkedin.com/v2';

    public function __construct()
    {
    }

    public function loadSettings(): void
    {
        if ($this->loaded) return;
        try {
            $this->enabled = MarketingSetting::get('linkedin_insight_enabled', false);
            $this->partnerId = MarketingSetting::get('linkedin_partner_id');
            $this->conversionRuleId = MarketingSetting::get('linkedin_conversion_rule_id');
            $this->accessToken = MarketingSetting::get('linkedin_access_token');
        } catch (\Exception $e) {
            $this->enabled = false;
            $this->partnerId = null;
            $this->conversionRuleId = null;
            $this->accessToken = null;
        }
        $this->loaded = true;
    }

    public function isEnabled(): bool
    {
        $this->loadSettings();
        return $this->enabled && $this->partnerId;
    }

    public function trackEvent(string $eventName, array $eventData, ?array $userData = null): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'reason' => 'linkedin_disabled'];
        }

        try {
            $mappedEvent = $this->mapEventName($eventName);
            $payload = $this->buildPayload($mappedEvent, $eventData, $userData);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                    'LinkedIn-Version' => '202401',
                ])
                ->post(self::API_BASE . '/conversionEvents', $payload);

            $success = $response->successful();

            if (!$success) {
                Log::warning('LinkedIn CAPI failed', [
                    'event' => $eventName,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            }

            return ['success' => $success, 'response' => $response->json()];
        } catch (\Exception $e) {
            Log::error('LinkedIn CAPI exception', [
                'event' => $eventName,
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getInsightTag(): string
    {
        if (!$this->isEnabled()) return '';

        $partnerId = e($this->partnerId);
        return <<<JS
<script>
(function(){var e=window,r=document;i='_linkedin_data_partner_id',o='$partnerId';var n=r.getElementsByTagName('script')[0],t=r.createElement('script');t.type='text/javascript';t.async=!0;t.src='https://snap.licdn.com/li.lms-analytics/insight.min.js';n.parentNode.insertBefore(t,n);})();
</script>
<noscript><img height="1" width="1" style="display:none;" alt="" src="https://px.ads.linkedin.com/collect/?pid={$partnerId}&fmt=gif"/></noscript>
JS;
    }

    private function mapEventName(string $eventName): string
    {
        $map = [
            'Purchase' => 'Purchase',
            'AddToCart' => 'AddToCart',
            'ViewContent' => 'PageVisit',
            'InitiateCheckout' => 'StartCheckout',
            'Lead' => 'Lead',
            'Subscribe' => 'SignUp',
            'Search' => 'Search',
        ];
        return $map[$eventName] ?? $eventName;
    }

    private function buildPayload(string $mappedEvent, array $eventData, ?array $userData): array
    {
        return [
            'conversionRuleId' => $this->conversionRuleId,
            'conversionValue' => $eventData['value'] ?? 0,
            'currencyCode' => $eventData['currency'] ?? 'ILS',
            'eventTime' => now()->toRfc3339String(),
            'eventId' => $eventData['order_id'] ?? uniqid('li_', true),
            'user' => [
                'emails' => !empty($userData['email']) ? [$userData['email']] : [],
                'phones' => !empty($userData['phone']) ? [$userData['phone']] : [],
            ],
        ];
    }
}
