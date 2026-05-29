<?php

namespace App\Services;

use App\Models\MarketingSetting;
use App\Services\DeduplicationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class AdvertisingTrackingService
{
    private bool $trackingEnabled;
    private bool $testMode;
    private bool $asyncMode;
    private bool $gdprOptOut;

    private bool $fbPixelEnabled;
    private bool $fbCapiEnabled;
    private ?string $fbPixelId;
    private ?string $fbAccessToken;
    private ?string $fbTestCode;

    private bool $ttPixelEnabled;
    private bool $ttCapiEnabled;
    private ?string $ttPixelId;
    private ?string $ttAccessToken;

    private ?DeduplicationService $dedup;

    private const FB_API_VERSION = 'v22.0';

    private const FB_EVENT_ACTIONS = [
        'ViewContent', 'AddToCart', 'InitiateCheckout', 'Purchase',
        'Lead', 'Subscribe', 'Search', 'Contact', 'CustomEvent',
        'CompleteRegistration', 'AddPaymentInfo', 'AddToWishlist',
    ];

    private const FB_USER_FIELDS = [
        'em', 'ph', 'fn', 'ln', 'db', 'ct', 'st', 'zp',
        'country', 'gender', 'birthday', 'external_id',
    ];

    public function __construct(?DeduplicationService $dedup = null)
    {
        $this->dedup = $dedup ?? app(DeduplicationService::class);
        $this->loadSettings();
    }

    public function loadSettings(): void
    {
        $this->trackingEnabled = MarketingSetting::get('tracking_enabled', true);
        $this->testMode = MarketingSetting::get('tracking_test_mode', false);
        $this->asyncMode = MarketingSetting::get('tracking_async_mode', true);

        $this->fbPixelEnabled = MarketingSetting::get('facebook_pixel_enabled', false);
        $this->fbCapiEnabled = MarketingSetting::get('facebook_capi_enabled', false);
        $this->fbPixelId = MarketingSetting::get('facebook_pixel_id');
        $this->fbAccessToken = MarketingSetting::get('facebook_access_token');
        $this->fbTestCode = MarketingSetting::get('facebook_test_event_code');

        $this->ttPixelEnabled = MarketingSetting::get('tiktok_pixel_enabled', false);
        $this->ttCapiEnabled = MarketingSetting::get('tiktok_capi_enabled', false);
        $this->ttPixelId = MarketingSetting::get('tiktok_pixel_id');
        $this->ttAccessToken = MarketingSetting::get('tiktok_access_token');
    }

    public function isOptedOut(): bool
    {
        return request()->cookie('_tracking_optout') === '1'
            || request()->header('X-Tracking-Optout') === '1'
            || $this->gdprOptOut;
    }

    public function getBrowserPixelScript(): string
    {
        if (!$this->trackingEnabled || $this->isOptedOut()) return '';

        $scripts = [];

        if ($this->fbPixelEnabled && $this->fbPixelId) {
            $scripts[] = $this->buildFacebookPixelScript();
        }

        if ($this->ttPixelEnabled && $this->ttPixelId) {
            $scripts[] = $this->buildTikTokPixelScript();
        }

        try {
            if (app(\App\Services\GoogleAdsService::class)->isEnabled()) {
                $scripts[] = app(\App\Services\GoogleAdsService::class)->getGoogleTagScript();
            }
            if (app(\App\Services\SnapchatService::class)->isEnabled()) {
                $scripts[] = app(\App\Services\SnapchatService::class)->getPixelScript();
            }
            if (app(\App\Services\PinterestService::class)->isEnabled()) {
                $scripts[] = app(\App\Services\PinterestService::class)->getTagScript();
            }
            if (app(\App\Services\TwitterService::class)->isEnabled()) {
                $scripts[] = app(\App\Services\TwitterService::class)->getPixelScript();
            }
            if (app(\App\Services\LinkedInService::class)->isEnabled()) {
                $scripts[] = app(\App\Services\LinkedInService::class)->getInsightTag();
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::debug('Error loading platform scripts', ['error' => $e->getMessage()]);
        }

        return implode("\n", $scripts);
    }

    public function getBrowserPixelNoscript(): string
    {
        if (!$this->trackingEnabled || $this->isOptedOut() || !$this->fbPixelEnabled || !$this->fbPixelId) return '';

        return '<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id='
            . e($this->fbPixelId)
            . '&ev=PageView&noscript=1'
            . ($this->fbTestCode ? '&test_event_code=' . e($this->fbTestCode) : '')
            . '"/></noscript>';
    }

    private function buildFacebookPixelScript(): string
    {
        $pixelId = e($this->fbPixelId);
        $testCode = $this->fbTestCode ? "'testEventCode': '" . e($this->fbTestCode) . "'," : '';

        return <<<JS
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init','{$pixelId}',{ {$testCode} });
fbq('track','PageView');
</script>
JS;
    }

    private function buildTikTokPixelScript(): string
    {
        $pixelId = e($this->ttPixelId);
        return <<<JS
<script>
!function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};ttq.load('{$pixelId}');ttq.page();}(window,document,'ttq');
</script>
JS;
    }

    private function generateEventId(string $eventName, ?string $suffix = null): string
    {
        $uuid = Uuid::uuid4()->toString();
        return $eventName . '_' . $uuid . ($suffix ? '_' . $suffix : '');
    }

    private function getFbclidFromUrl(): ?string
    {
        if (!request()->has('fbclid')) return null;
        return request()->input('fbclid');
    }

    private function getGclidFromUrl(): ?string
    {
        if (!request()->has('gclid')) return null;
        return request()->input('gclid');
    }

    private function getTtclidFromUrl(): ?string
    {
        if (!request()->has('ttclid')) return null;
        return request()->input('ttclid');
    }

    private function getEventSourceUrl(): string
    {
        return request()->fullUrl();
    }

    private function shouldDispatchEvent(string $eventName): bool
    {
        if (!$this->trackingEnabled || $this->isOptedOut()) return false;
        return true;
    }

    public function trackEvent(string $eventName, array $eventData = [], ?array $userData = null, ?string $actionSource = 'website'): array
    {
        if (!$this->shouldDispatchEvent($eventName)) {
            return ['success' => false, 'reason' => $this->isOptedOut() ? 'gdpr_optout' : 'tracking_disabled'];
        }

        $eventId = $this->generateEventId($eventName, $eventData['order_id'] ?? null);
        $results = [];

        $dedupCheck = $this->dedup ? $this->dedup->checkAndMark($eventName, $eventId) : true;
        if (!$dedupCheck) {
            return ['success' => false, 'reason' => 'duplicate', 'event_id' => $eventId];
        }

        $commonContext = [
            'event_id' => $eventId,
            'event_time' => time(),
            'event_source_url' => $this->getEventSourceUrl(),
            'action_source' => $actionSource,
        ];

        if ($this->fbCapiEnabled && $this->fbAccessToken && $this->fbPixelId) {
            $results['facebook'] = $this->sendFacebookCAPI($eventName, $eventData, $userData, $commonContext);
        }

        if ($this->ttCapiEnabled && $this->ttAccessToken && $this->ttPixelId) {
            $results['tiktok'] = $this->sendTikTokEventsAPI($eventName, $eventData, $commonContext);
        }

        return $results + ['event_id' => $eventId];
    }

    public function trackViewContent(array $product, ?array $userData = null, ?array $options = []): array
    {
        if (!$this->shouldDispatchEvent('ViewContent')) {
            return ['success' => false, 'reason' => $this->isOptedOut() ? 'gdpr_optout' : 'tracking_disabled'];
        }
        $eventData = $this->buildViewContentData($product, $options);
        $eventId = $this->generateEventId('ViewContent', $product['sku'] ?? $product['id'] ?? null);
        $results = [];

        $dedupCheck = $this->dedup ? $this->dedup->checkAndMark('ViewContent', $eventId) : true;
        if (!$dedupCheck) {
            return ['success' => false, 'reason' => 'duplicate', 'event_id' => $eventId];
        }

        $ctx = [
            'event_id' => $eventId,
            'event_time' => time(),
            'event_source_url' => $this->getEventSourceUrl(),
            'action_source' => $options['action_source'] ?? 'website',
        ];

        if ($this->fbCapiEnabled && $this->fbAccessToken) {
            $results['facebook'] = $this->sendFacebookCAPI('ViewContent', $eventData, $userData, $ctx);
        }

        if ($this->ttCapiEnabled && $this->ttAccessToken) {
            $results['tiktok'] = $this->sendTikTokEventsAPI('ViewContent', $eventData, $ctx);
        }

        return $results + ['event_id' => $eventId];
    }

    public function trackAddToCart(array $product, int $quantity = 1, ?array $userData = null, ?array $options = []): array
    {
        if (!$this->shouldDispatchEvent('AddToCart')) {
            return ['success' => false, 'reason' => $this->isOptedOut() ? 'gdpr_optout' : 'tracking_disabled'];
        }
        $eventData = $this->buildAddToCartData($product, $quantity, $options);
        $eventId = $this->generateEventId('AddToCart', $product['sku'] ?? $product['id'] ?? null);
        $results = [];

        $dedupCheck = $this->dedup ? $this->dedup->checkAndMark('AddToCart', $eventId) : true;
        if (!$dedupCheck) {
            return ['success' => false, 'reason' => 'duplicate', 'event_id' => $eventId];
        }

        $ctx = [
            'event_id' => $eventId,
            'event_time' => time(),
            'event_source_url' => $this->getEventSourceUrl(),
            'action_source' => $options['action_source'] ?? 'website',
        ];

        if ($this->fbCapiEnabled && $this->fbAccessToken) {
            $results['facebook'] = $this->sendFacebookCAPI('AddToCart', $eventData, $userData, $ctx);
        }

        if ($this->ttCapiEnabled && $this->ttAccessToken) {
            $results['tiktok'] = $this->sendTikTokEventsAPI('AddToCart', $eventData, $ctx);
        }

        return $results + ['event_id' => $eventId];
    }

    public function trackInitiateCheckout(array $cartData, ?array $userData = null, ?array $options = []): array
    {
        if (!$this->shouldDispatchEvent('InitiateCheckout')) {
            return ['success' => false, 'reason' => $this->isOptedOut() ? 'gdpr_optout' : 'tracking_disabled'];
        }
        $eventData = $this->buildCheckoutData($cartData, $options);
        $eventId = $this->generateEventId('InitiateCheckout');
        $results = [];

        $dedupCheck = $this->dedup ? $this->dedup->checkAndMark('InitiateCheckout', $eventId) : true;
        if (!$dedupCheck) {
            return ['success' => false, 'reason' => 'duplicate', 'event_id' => $eventId];
        }

        $ctx = [
            'event_id' => $eventId,
            'event_time' => time(),
            'event_source_url' => $this->getEventSourceUrl(),
            'action_source' => $options['action_source'] ?? 'website',
        ];

        if ($this->fbCapiEnabled && $this->fbAccessToken) {
            $results['facebook'] = $this->sendFacebookCAPI('InitiateCheckout', $eventData, $userData, $ctx);
        }

        if ($this->ttCapiEnabled && $this->ttAccessToken) {
            $results['tiktok'] = $this->sendTikTokEventsAPI('InitiateCheckout', $eventData, $ctx);
        }

        return $results + ['event_id' => $eventId];
    }

    public function trackPurchase($order, ?array $userData = null, ?array $options = []): array
    {
        if (!$this->shouldDispatchEvent('Purchase')) {
            return ['success' => false, 'reason' => $this->isOptedOut() ? 'gdpr_optout' : 'tracking_disabled'];
        }

        $this->loadSettings();
        $orderId = $order->order_number ?? $order->id ?? null;
        $eventData = $this->buildPurchaseData($order, $options);
        $eventId = $this->generateEventId('Purchase', (string) $orderId);
        $results = [];

        $dedupCheck = $this->dedup ? $this->dedup->checkAndMark('Purchase', $eventId) : true;
        if (!$dedupCheck) {
            return ['success' => false, 'reason' => 'duplicate', 'event_id' => $eventId];
        }

        $ctx = [
            'event_id' => $eventId,
            'event_time' => time(),
            'event_source_url' => $this->getEventSourceUrl(),
            'action_source' => $options['action_source'] ?? 'website',
        ];

        if ($this->fbCapiEnabled && $this->fbAccessToken && $this->fbPixelId) {
            $fbResult = $this->sendFacebookCAPI('Purchase', $eventData, $userData, $ctx);
            $results['facebook'] = $fbResult;

            if ($order instanceof \Illuminate\Database\Eloquent\Model) {
                try {
                    $order->update([
                        'meta_capi_sent' => $fbResult['success'] ?? false,
                        'meta_capi_sent_at' => now(),
                        'meta_capi_event_id' => $eventId,
                        'meta_capi_response' => json_encode($fbResult),
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to update order with CAPI status', [
                        'order_id' => $orderId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        if ($this->ttCapiEnabled && $this->ttAccessToken && $this->ttPixelId) {
            $ttData = $this->buildTikTokPurchaseData($order);
            $results['tiktok'] = $this->sendTikTokEventsAPI('Purchase', $ttData, $ctx);
        }

        return $results + ['event_id' => $eventId];
    }

    public function trackLead(array $leadData, ?array $userData = null, ?array $options = []): array
    {
        if (!$this->shouldDispatchEvent('Lead')) {
            return ['success' => false, 'reason' => $this->isOptedOut() ? 'gdpr_optout' : 'tracking_disabled'];
        }
        $eventData = $this->buildLeadData($leadData, $options);
        $eventId = $this->generateEventId('Lead');
        $results = [];

        $dedupCheck = $this->dedup ? $this->dedup->checkAndMark('Lead', $eventId) : true;
        if (!$dedupCheck) {
            return ['success' => false, 'reason' => 'duplicate', 'event_id' => $eventId];
        }

        $ctx = [
            'event_id' => $eventId,
            'event_time' => time(),
            'event_source_url' => $this->getEventSourceUrl(),
            'action_source' => $options['action_source'] ?? 'website',
        ];

        if ($this->fbCapiEnabled && $this->fbAccessToken) {
            $results['facebook'] = $this->sendFacebookCAPI('Lead', $eventData, $userData, $ctx);
        }

        return $results + ['event_id' => $eventId];
    }

    public function trackSubscribe(?array $userData = null, ?array $options = []): array
    {
        if (!$this->shouldDispatchEvent('Subscribe')) {
            return ['success' => false, 'reason' => $this->isOptedOut() ? 'gdpr_optout' : 'tracking_disabled'];
        }
        $eventData = $this->buildSubscribeData($options);
        $eventId = $this->generateEventId('Subscribe');
        $results = [];

        $ctx = [
            'event_id' => $eventId,
            'event_time' => time(),
            'event_source_url' => $this->getEventSourceUrl(),
            'action_source' => $options['action_source'] ?? 'website',
        ];

        if ($this->fbCapiEnabled && $this->fbAccessToken) {
            $results['facebook'] = $this->sendFacebookCAPI('Subscribe', $eventData, $userData, $ctx);
        }

        return $results + ['event_id' => $eventId];
    }

    public function trackSearch(string $query, array $results_data = [], ?array $options = []): array
    {
        if (!$this->shouldDispatchEvent('Search')) {
            return ['success' => false, 'reason' => $this->isOptedOut() ? 'gdpr_optout' : 'tracking_disabled'];
        }
        $eventData = $this->buildSearchData($query, $results_data, $options);
        $eventId = $this->generateEventId('Search', md5($query));
        $results = [];

        $ctx = [
            'event_id' => $eventId,
            'event_time' => time(),
            'event_source_url' => $this->getEventSourceUrl(),
            'action_source' => $options['action_source'] ?? 'website',
        ];

        if ($this->fbCapiEnabled && $this->fbAccessToken) {
            $results['facebook'] = $this->sendFacebookCAPI('Search', $eventData, null, $ctx);
        }

        return $results + ['event_id' => $eventId];
    }

    public function trackContact(?array $userData = null, ?array $options = []): array
    {
        if (!$this->shouldDispatchEvent('Contact')) {
            return ['success' => false, 'reason' => $this->isOptedOut() ? 'gdpr_optout' : 'tracking_disabled'];
        }
        $eventData = $this->buildContactData($options);
        $eventId = $this->generateEventId('Contact');
        $results = [];

        $ctx = [
            'event_id' => $eventId,
            'event_time' => time(),
            'event_source_url' => $this->getEventSourceUrl(),
            'action_source' => $options['action_source'] ?? 'website',
        ];

        if ($this->fbCapiEnabled && $this->fbAccessToken) {
            $results['facebook'] = $this->sendFacebookCAPI('Contact', $eventData, $userData, $ctx);
        }

        return $results + ['event_id' => $eventId];
    }

    public function trackCustomEvent(string $customEventName, array $eventData = [], ?array $userData = null, ?array $options = []): array
    {
        if (!$this->shouldDispatchEvent($customEventName)) {
            return ['success' => false, 'reason' => $this->isOptedOut() ? 'gdpr_optout' : 'tracking_disabled'];
        }
        $eventId = $this->generateEventId($customEventName);
        $results = [];

        $ctx = [
            'event_id' => $eventId,
            'event_time' => time(),
            'event_source_url' => $this->getEventSourceUrl(),
            'action_source' => $options['action_source'] ?? 'website',
        ];

        if ($this->fbCapiEnabled && $this->fbAccessToken) {
            $results['facebook'] = $this->sendFacebookCAPI($customEventName, $eventData, $userData, $ctx);
        }

        return $results + ['event_id' => $eventId];
    }

    private function sendFacebookCAPI(string $eventName, array $eventData, ?array $userData = null, array $context = []): array
    {
        try {
            $eventId = $context['event_id'] ?? $this->generateEventId($eventName);

            $payload = [
                'data' => [[
                    'event_name' => $eventName,
                    'event_time' => $context['event_time'] ?? time(),
                    'event_id' => $eventId,
                    'event_source_url' => $context['event_source_url'] ?? $this->getEventSourceUrl(),
                    'action_source' => $context['action_source'] ?? 'website',
                    'user_data' => $this->buildFacebookUserData($userData),
                    'custom_data' => $eventData,
                ]],
            ];

            if ($this->fbTestCode) {
                $payload['test_event_code'] = $this->fbTestCode;
                $payload['data'][0]['test_event_code'] = $this->fbTestCode;
            }

            $url = sprintf(
                'https://graph.facebook.com/%s/%s/events?access_token=%s',
                self::FB_API_VERSION,
                $this->fbPixelId,
                $this->fbAccessToken
            );

            $response = Http::timeout(10)
                ->retry(3, 1000, function ($exception) {
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException;
                })
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            $success = $response->successful();
            $body = $response->json();
            $statusCode = $response->status();

            if (!$success) {
                $errorMsg = $body['error']['message'] ?? 'Unknown error';
                $errorCode = $body['error']['code'] ?? null;
                $errorType = $body['error']['type'] ?? null;
                $fbtraceId = $body['error']['fbtrace_id'] ?? null;

                Log::warning('Facebook CAPI failed', [
                    'event' => $eventName,
                    'event_id' => $eventId,
                    'status' => $statusCode,
                    'error_code' => $errorCode,
                    'error_type' => $errorType,
                    'error' => $errorMsg,
                    'fbtrace_id' => $fbtraceId,
                    'body' => $body,
                ]);
            } else {
                $receivedEventIds = $body['events_received'] ?? 0;
                $messages = $body['messages'] ?? [];

                if ($receivedEventIds === 0) {
                    Log::warning('Facebook CAPI: event not received', [
                        'event' => $eventName,
                        'event_id' => $eventId,
                        'messages' => $messages,
                    ]);
                }
            }

            $this->logCapiEvent('facebook', $eventName, $eventId, $success, $statusCode, $body);

            return [
                'success' => $success,
                'event_id' => $eventId,
                'status_code' => $statusCode,
                'response' => $body,
            ];
        } catch (\Exception $e) {
            Log::error('Facebook CAPI exception', [
                'event' => $eventName,
                'event_id' => $context['event_id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'event_id' => $context['event_id'] ?? null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function sendTikTokEventsAPI(string $eventName, array $eventData, array $context = []): array
    {
        try {
            $eventId = $context['event_id'] ?? $this->generateEventId($eventName);

            $payload = [
                'pixel_code' => $this->ttPixelId,
                'event' => $eventName,
                'event_id' => $eventId,
                'timestamp' => now()->toIso8601String(),
                'context' => [
                    'page' => ['url' => $context['event_source_url'] ?? request()->fullUrl()],
                    'user_agent' => request()->userAgent(),
                    'ip' => request()->ip(),
                    'source' => 'server_side',
                ],
                'properties' => $eventData,
            ];

            $response = Http::timeout(10)
                ->retry(3, 1000, function ($exception) {
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException;
                })
                ->withHeaders([
                    'Access-Token' => $this->ttAccessToken,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://analytics.tiktok.com/api/v2/offline/events', $payload);

            $success = $response->successful();
            $body = $response->json();
            $statusCode = $response->status();

            if (!$success) {
                Log::warning('TikTok Events API failed', [
                    'event' => $eventName,
                    'event_id' => $eventId,
                    'status' => $statusCode,
                    'body' => $body,
                ]);
            }

            $this->logCapiEvent('tiktok', $eventName, $eventId, $success, $statusCode, $body);

            return [
                'success' => $success,
                'event_id' => $eventId,
                'status_code' => $statusCode,
                'response' => $body,
            ];
        } catch (\Exception $e) {
            Log::error('TikTok Events API exception', [
                'event' => $eventName,
                'event_id' => $context['event_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'event_id' => $context['event_id'] ?? null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function logCapiEvent(string $platform, string $eventName, string $eventId, bool $success, int $statusCode, ?array $response): void
    {
        try {
            \App\Models\CapiEventLog::create([
                'platform' => $platform,
                'event_name' => $eventName,
                'event_id' => $eventId,
                'success' => $success,
                'status_code' => $statusCode,
                'response' => $response,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Exception $e) {
            Log::debug('Failed to log CAPI event', ['error' => $e->getMessage()]);
        }
    }

    private function buildFacebookUserData(?array $userData = null): array
    {
        $data = [];

        if ($userData) {
            if (!empty($userData['email'])) {
                $data['em'] = [hash('sha256', strtolower(trim($userData['email'])))];
            }
            if (!empty($userData['phone'])) {
                $phone = preg_replace('/[^0-9]/', '', $userData['phone']);
                $phone = ltrim($phone, '0');
                if (!str_starts_with($phone, '972') && !str_starts_with($phone, '97')) {
                    if (strlen($phone) === 9) $phone = '972' . $phone;
                }
                $data['ph'] = [hash('sha256', $phone)];
            }
            if (!empty($userData['firstName'])) {
                $data['fn'] = [hash('sha256', strtolower(trim($userData['firstName'])))];
            }
            if (!empty($userData['lastName'])) {
                $data['ln'] = [hash('sha256', strtolower(trim($userData['lastName'])))];
            }
            if (!empty($userData['name'])) {
                $parts = explode(' ', trim($userData['name']), 2);
                $data['fn'] = [hash('sha256', strtolower($parts[0]))];
                if (!empty($parts[1])) {
                    $data['ln'] = [hash('sha256', strtolower($parts[1]))];
                }
            }
            if (!empty($userData['city'])) {
                $data['ct'] = [hash('sha256', strtolower(trim($userData['city'])))];
            }
            if (!empty($userData['country'])) {
                $data['country'] = [hash('sha256', strtolower(trim($userData['country'])))];
            }
            if (!empty($userData['zip'])) {
                $data['zp'] = [hash('sha256', trim($userData['zip']))];
            }
            if (!empty($userData['gender'])) {
                $data['ge'] = [hash('sha256', strtolower(trim($userData['gender'])))];
            }
            if (!empty($userData['birthday'])) {
                $data['db'] = [hash('sha256', trim($userData['birthday']))];
            }
            if (!empty($userData['external_id'])) {
                $data['external_id'] = [hash('sha256', (string) $userData['external_id'])];
            }
        }

        $data['client_ip_address'] = request()->ip();
        $data['client_user_agent'] = request()->userAgent();

        $fbp = $this->extractFbp();
        if ($fbp) {
            $data['_fbp'] = $fbp;
        }

        $fbc = $this->extractFbc();
        if ($fbc) {
            $data['_fbc'] = $fbc;
        }

        $fbclid = $this->getFbclidFromUrl();
        if ($fbclid && !$fbc) {
            $data['_fbc'] = 'fb.1.' . time() . '.' . $fbclid;
        }

        return $data;
    }

    private function extractFbp(): ?string
    {
        $cookie = request()->cookie('_fbp');
        if ($cookie) return $cookie;

        $header = request()->header('X-Fbp');
        if ($header) return $header;

        return null;
    }

    private function extractFbc(): ?string
    {
        $cookie = request()->cookie('_fbc');
        if ($cookie) return $cookie;

        $header = request()->header('X-Fbc');
        if ($header) return $header;

        $fbclid = $this->getFbclidFromUrl();
        if ($fbclid) {
            return 'fb.1.' . time() . '.' . $fbclid;
        }

        return null;
    }

    private function buildViewContentData(array $product, array $options = []): array
    {
        return [
            'content_ids' => [$product['sku'] ?? $product['id'] ?? ''],
            'content_name' => $product['name'] ?? '',
            'content_category' => $product['category'] ?? $product['main_category'] ?? '',
            'content_type' => $product['content_type'] ?? 'product',
            'contents' => [[
                'id' => $product['sku'] ?? $product['id'] ?? '',
                'quantity' => 1,
                'item_price' => $product['price'] ?? 0,
            ]],
            'value' => $product['price'] ?? 0,
            'currency' => $options['currency'] ?? 'ILS',
            'product_catalog_id' => $options['product_catalog_id'] ?? null,
        ];
    }

    private function buildAddToCartData(array $product, int $quantity, array $options = []): array
    {
        return [
            'content_ids' => [$product['sku'] ?? $product['id'] ?? ''],
            'content_name' => $product['name'] ?? '',
            'content_category' => $product['category'] ?? '',
            'content_type' => 'product',
            'contents' => [[
                'id' => $product['sku'] ?? $product['id'] ?? '',
                'quantity' => $quantity,
                'item_price' => $product['price'] ?? 0,
            ]],
            'value' => ($product['price'] ?? 0) * $quantity,
            'currency' => $options['currency'] ?? 'ILS',
        ];
    }

    private function buildCheckoutData(array $cartData, array $options = []): array
    {
        $contents = [];
        foreach ($cartData['items'] ?? [] as $item) {
            $contents[] = [
                'id' => $item['sku'] ?? $item['id'] ?? '',
                'quantity' => $item['quantity'] ?? 1,
                'item_price' => $item['price'] ?? 0,
            ];
        }

        return [
            'contents' => $contents,
            'num_items' => count($contents),
            'value' => $cartData['total'] ?? 0,
            'currency' => $options['currency'] ?? $cartData['currency'] ?? 'ILS',
        ];
    }

    private function buildPurchaseData($order, array $options = []): array
    {
        $contents = [];
        foreach ($order->items ?? [] as $item) {
            $contents[] = [
                'id' => $item->product_sku ?? $item->sku ?? '',
                'quantity' => $item->quantity ?? 1,
                'item_price' => (float) ($item->unit_price ?? $item->price ?? 0),
            ];
        }

        return [
            'contents' => $contents,
            'content_type' => 'product',
            'num_items' => $order->items->sum('quantity') ?? count($contents),
            'value' => (float) ($order->total_amount ?? $order->total ?? 0),
            'currency' => $options['currency'] ?? $order->currency ?? 'ILS',
            'order_id' => $order->order_number ?? $order->id,
        ];
    }

    private function buildLeadData(array $leadData, array $options = []): array
    {
        return [
            'lead_id' => $leadData['id'] ?? $leadData['lead_id'] ?? '',
            'lead_source' => $leadData['source'] ?? $leadData['lead_source'] ?? $options['lead_source'] ?? '',
            'value' => $leadData['value'] ?? $options['value'] ?? 0,
            'currency' => $options['currency'] ?? 'ILS',
        ];
    }

    private function buildSubscribeData(array $options = []): array
    {
        return [
            'subscription_id' => $options['subscription_id'] ?? '',
            'value' => $options['value'] ?? 0,
            'currency' => $options['currency'] ?? 'ILS',
            'predicted_ltv' => $options['predicted_ltv'] ?? null,
        ];
    }

    private function buildSearchData(string $query, array $results_data = [], array $options = []): array
    {
        return [
            'search_string' => mb_substr($query, 0, 128),
            'num_results' => count($results_data),
            'category' => $options['category'] ?? '',
        ];
    }

    private function buildContactData(array $options = []): array
    {
        return [
            'contact_type' => $options['contact_type'] ?? 'form',
            'department' => $options['department'] ?? '',
        ];
    }

    private function buildTikTokPurchaseData($order): array
    {
        $contents = [];
        foreach ($order->items ?? [] as $item) {
            $contents[] = [
                'content_id' => $item->product_sku ?? $item->sku ?? '',
                'content_name' => $item->product_name ?? $item->name ?? '',
                'quantity' => $item->quantity ?? 1,
                'price' => (float) ($item->unit_price ?? $item->price ?? 0),
            ];
        }

        return [
            'contents' => $contents,
            'value' => (float) ($order->total_amount ?? $order->total ?? 0),
            'currency' => $order->currency ?? 'ILS',
            'order_id' => $order->order_number ?? $order->id,
        ];
    }

    public function isEnabled(): bool { return $this->trackingEnabled; }
    public function isFbPixelEnabled(): bool { return $this->fbPixelEnabled && $this->fbPixelId; }
    public function isTtPixelEnabled(): bool { return $this->ttPixelEnabled && $this->ttPixelId; }
    public function isAsyncMode(): bool { return $this->asyncMode; }
    public function isTestMode(): bool { return $this->testMode; }
    public function getFbPixelId(): ?string { return $this->fbPixelId; }
    public function getTtPixelId(): ?string { return $this->ttPixelId; }
}
