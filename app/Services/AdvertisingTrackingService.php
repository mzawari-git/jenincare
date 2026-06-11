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
    private bool $gdprOptOut = false;

    private bool $fbPixelEnabled;
    private bool $fbCapiEnabled;
    private ?string $fbPixelId;
    private ?string $fbAccessToken;
    private ?string $fbTestCode;

    private bool $ttPixelEnabled;
    private bool $ttCapiEnabled;
    private ?string $ttPixelId;
    private ?string $ttAccessToken;

    private bool $googleCapiEnabled;
    private ?string $googleConversionId;
    private ?string $googleConversionLabel;

    private bool $obfuscationEnabled;
    private bool $urlSanitizationEnabled;

    private ?DeduplicationService $dedup;

    private const OBFUSCATION_MAP = [
        'Purchase' => 'event_01',
        'Lead' => 'event_02',
        'Subscribe' => 'event_03',
        'AddToCart' => 'event_04',
        'InitiateCheckout' => 'event_05',
        'ViewContent' => 'event_06',
        'Search' => 'event_07',
        'Contact' => 'event_08',
        'CompleteRegistration' => 'event_09',
        'AddPaymentInfo' => 'event_10',
        'AddToWishlist' => 'event_11',
    ];

    private const MEDICAL_TERMS = [
        'علاج', 'بوتوكس', 'فيلر', 'ليزر', 'تقشير', 'ميزوثيرابي',
        'بلازما', 'Protein', 'Plasma', 'Botox', 'Filler', 'Laser',
        'Mesotherapy', 'Peeling', 'Hydra', 'Microneedling', 'PRP',
        'Carboxy', 'Cryo', 'Radiofrequency', 'RF', 'HIFU',
        'thread', 'خيط', 'شد', 'تنحيف', 'تجميل', 'حقن',
        'procedure', 'treatment', 'clinic', 'therapist',
        'dermal', 'injection', 'aesthetic', 'cosmetic',
        'dermatologist', 'surgeon', 'surgery', 'liposuction',
        'خلطة', 'وصفة', 'ماسك', 'كريم', 'دهان', 'مرهم',
    ];

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

    private const CURRENCY = 'ILS';

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

        $this->googleCapiEnabled = MarketingSetting::get('google_ads_capi_enabled', false);
        $this->googleConversionId = MarketingSetting::get('google_conversion_id');
        $this->googleConversionLabel = MarketingSetting::get('google_conversion_label');

        $this->obfuscationEnabled = MarketingSetting::get('event_obfuscation_enabled', false);
        $this->urlSanitizationEnabled = MarketingSetting::get('url_sanitization_enabled', false);
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
            Log::debug('Error loading platform scripts', ['error' => $e->getMessage()]);
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

    private function obfuscateEventName(string $eventName): string
    {
        if (!$this->obfuscationEnabled) {
            return $eventName;
        }

        return self::OBFUSCATION_MAP[$eventName] ?? $eventName;
    }

    private function sanitizeUrl(string $url): string
    {
        if (!$this->urlSanitizationEnabled) {
            return $url;
        }

        $pattern = '/' . implode('|', array_map('preg_quote', self::MEDICAL_TERMS)) . '/ui';

        return preg_replace($pattern, '[redacted]', $url);
    }

    private function getEventSourceUrl(): string
    {
        $url = request()->fullUrl();

        return $this->sanitizeUrl($url);
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

        if ($this->googleCapiEnabled && $this->googleConversionId) {
            $results['google'] = $this->sendGoogleCAPI($eventName, $eventData, $userData, $commonContext);
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

        if ($this->googleCapiEnabled && $this->googleConversionId) {
            $results['google'] = $this->sendGoogleCAPI('ViewContent', $eventData, $userData, $ctx);
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

        if ($this->googleCapiEnabled && $this->googleConversionId) {
            $results['google'] = $this->sendGoogleCAPI('AddToCart', $eventData, $userData, $ctx);
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

        if ($this->googleCapiEnabled && $this->googleConversionId) {
            $results['google'] = $this->sendGoogleCAPI('InitiateCheckout', $eventData, $userData, $ctx);
        }

        return $results + ['event_id' => $eventId];
    }

    public function trackPurchase($booking, ?array $userData = null, ?array $options = []): array
    {
        if (!$this->shouldDispatchEvent('Purchase')) {
            return ['success' => false, 'reason' => $this->isOptedOut() ? 'gdpr_optout' : 'tracking_disabled'];
        }

        $this->loadSettings();
        $bookingId = $booking->booking_number ?? $booking->id ?? null;
        $eventData = $this->buildPurchaseData($booking, $options);
        $eventId = $this->generateEventId('Purchase', (string) $bookingId);
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
        }

        if ($this->ttCapiEnabled && $this->ttAccessToken && $this->ttPixelId) {
            $ttData = $this->buildTikTokPurchaseData($booking);
            $results['tiktok'] = $this->sendTikTokEventsAPI('Purchase', $ttData, $ctx);
        }

        if ($this->googleCapiEnabled && $this->googleConversionId) {
            $results['google'] = $this->sendGoogleCAPI('Purchase', $eventData, $userData, $ctx);
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

        if ($this->googleCapiEnabled && $this->googleConversionId) {
            $results['google'] = $this->sendGoogleCAPI('Lead', $eventData, $userData, $ctx);
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

        if ($this->googleCapiEnabled && $this->googleConversionId) {
            $results['google'] = $this->sendGoogleCAPI('Subscribe', $eventData, $userData, $ctx);
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

        if ($this->googleCapiEnabled && $this->googleConversionId) {
            $results['google'] = $this->sendGoogleCAPI('Search', $eventData, null, $ctx);
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

        if ($this->googleCapiEnabled && $this->googleConversionId) {
            $results['google'] = $this->sendGoogleCAPI('Contact', $eventData, $userData, $ctx);
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

        if ($this->googleCapiEnabled && $this->googleConversionId) {
            $results['google'] = $this->sendGoogleCAPI($customEventName, $eventData, $userData, $ctx);
        }

        return $results + ['event_id' => $eventId];
    }

    private function sendFacebookCAPI(string $eventName, array $eventData, ?array $userData = null, array $context = []): array
    {
        try {
            $obfuscatedName = $this->obfuscateEventName($eventName);
            $eventId = $context['event_id'] ?? $this->generateEventId($obfuscatedName);

            $payload = [
                'data' => [[
                    'event_name' => $obfuscatedName,
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
            $obfuscatedName = $this->obfuscateEventName($eventName);
            $eventId = $context['event_id'] ?? $this->generateEventId($obfuscatedName);

            $payload = [
                'pixel_code' => $this->ttPixelId,
                'event' => $obfuscatedName,
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

    private function sendGoogleCAPI(string $eventName, array $eventData, ?array $userData = null, array $context = []): array
    {
        try {
            $mappedEvent = $this->mapGoogleEventName($eventName);
            $eventId = $context['event_id'] ?? $this->generateEventId($eventName);

            $conversionData = [
                'conversion_action' => $this->googleConversionId . '/' . $this->googleConversionLabel,
                'conversion_date_time' => now()->format('Y-m-d H:i:s'),
                'conversion_value' => $eventData['value'] ?? 0,
                'currency_code' => $eventData['currency'] ?? self::CURRENCY,
                'order_id' => $eventData['order_id'] ?? null,
            ];

            $userIdentifiers = [];
            if ($userData) {
                if (!empty($userData['email'])) {
                    $userIdentifiers[] = [
                        'hashed_email' => hash('sha256', strtolower(trim($userData['email']))),
                    ];
                }
                if (!empty($userData['phone'])) {
                    $phone = preg_replace('/[^0-9]/', '', $userData['phone']);
                    $userIdentifiers[] = [
                        'hashed_phone_number' => hash('sha256', $phone),
                    ];
                }
                if (!empty($userData['name'])) {
                    $parts = explode(' ', trim($userData['name']), 2);
                    $userIdentifiers[] = [
                        'hashed_first_name' => hash('sha256', strtolower($parts[0])),
                    ];
                    if (!empty($parts[1])) {
                        $userIdentifiers[] = [
                            'hashed_last_name' => hash('sha256', strtolower($parts[1])),
                        ];
                    }
                }
            }

            if (!empty($userIdentifiers)) {
                $conversionData['user_identifiers'] = $userIdentifiers;
            }

            $gclid = $this->getGclidFromUrl();
            if ($gclid) {
                $conversionData['gclid'] = $gclid;
            }

            $conversionData['custom_variables'] = [
                ['key' => 'event_id', 'value' => $eventId],
                ['key' => 'event_source_url', 'value' => $context['event_source_url'] ?? request()->fullUrl()],
            ];

            $job = \App\Jobs\SendGoogleConversion::dispatch($conversionData)->onQueue('capi-events');

            $this->logCapiEvent('google', $eventName, $eventId, true, 202, ['queued' => true]);

            return [
                'success' => true,
                'event_id' => $eventId,
                'queued' => true,
            ];
        } catch (\Exception $e) {
            Log::error('Google CAPI exception', [
                'event' => $eventName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'event_id' => $context['event_id'] ?? null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function mapGoogleEventName(string $eventName): string
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
            'currency' => $options['currency'] ?? self::CURRENCY,
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
            'currency' => $options['currency'] ?? self::CURRENCY,
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
            'currency' => $options['currency'] ?? $cartData['currency'] ?? self::CURRENCY,
        ];
    }

    private function buildPurchaseData($booking, array $options = []): array
    {
        $contents = [[
            'id' => (string) ($booking->service_id ?? ''),
            'content_name' => $booking->service_name ?? '',
            'quantity' => 1,
            'item_price' => (float) ($booking->service_price ?? 0),
        ]];

        return [
            'contents' => $contents,
            'content_type' => 'product',
            'num_items' => 1,
            'value' => (float) ($booking->total_amount ?? 0),
            'currency' => $options['currency'] ?? self::CURRENCY,
            'order_id' => $booking->booking_number ?? $booking->id,
        ];
    }

    private function buildLeadData(array $leadData, array $options = []): array
    {
        return [
            'lead_id' => $leadData['id'] ?? $leadData['lead_id'] ?? '',
            'lead_source' => $leadData['source'] ?? $leadData['lead_source'] ?? $options['lead_source'] ?? '',
            'value' => $leadData['value'] ?? $options['value'] ?? 0,
            'currency' => $options['currency'] ?? self::CURRENCY,
        ];
    }

    private function buildSubscribeData(array $options = []): array
    {
        return [
            'subscription_id' => $options['subscription_id'] ?? '',
            'value' => $options['value'] ?? 0,
            'currency' => $options['currency'] ?? self::CURRENCY,
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

    private function buildTikTokPurchaseData($booking): array
    {
        $contents = [[
            'content_id' => (string) ($booking->service_id ?? ''),
            'content_name' => $booking->service_name ?? '',
            'quantity' => 1,
            'price' => (float) ($booking->service_price ?? 0),
        ]];

        return [
            'contents' => $contents,
            'value' => (float) ($booking->total_amount ?? 0),
            'currency' => self::CURRENCY,
            'order_id' => $booking->booking_number ?? $booking->id,
        ];
    }

    public function isEnabled(): bool { return $this->trackingEnabled; }
    public function isFbPixelEnabled(): bool { return $this->fbPixelEnabled && $this->fbPixelId; }
    public function isTtPixelEnabled(): bool { return $this->ttPixelEnabled && $this->ttPixelId; }
    public function isAsyncMode(): bool { return $this->asyncMode; }
    public function isTestMode(): bool { return $this->testMode; }
    public function getFbPixelId(): ?string { return $this->fbPixelId; }
    public function getTtPixelId(): ?string { return $this->ttPixelId; }
    public function isGoogleCapiEnabled(): bool { return $this->googleCapiEnabled && $this->googleConversionId; }

    public function testFacebook(): array
    {
        if (!$this->fbCapiEnabled) {
            return ['success' => false, 'message' => 'CAPI غير مفعل، قم بتفعيل Facebook Conversions API أولاً'];
        }
        if (!$this->fbAccessToken) {
            return ['success' => false, 'message' => 'Access Token فارغ، يرجى إدخال رمز الوصول'];
        }
        if (!$this->fbPixelId) {
            return ['success' => false, 'message' => 'Pixel ID فارغ، يرجى إدخال معرف البيكسل'];
        }
        try {
            $result = $this->sendFacebookCAPI('Test', ['value' => 0, 'currency' => self::CURRENCY], null, [
                'event_id' => $this->generateEventId('Test'),
                'event_time' => time(),
                'event_source_url' => request()->fullUrl(),
                'action_source' => 'website',
            ]);
            if ($result['success'] ?? false) {
                return ['success' => true, 'message' => 'تم الاتصال بفيسبوك بنجاح'];
            }
            $errorMsg = $result['response']['error']['message'] ?? 'خطأ غير معروف من فيسبوك';
            return ['success' => false, 'message' => 'فشل الاتصال: ' . $errorMsg];
        } catch (\Exception $e) {
            Log::error('Facebook test failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطأ في الاتصال: ' . $e->getMessage()];
        }
    }

    public function testTikTok(): bool
    {
        if (!$this->ttCapiEnabled || !$this->ttAccessToken || !$this->ttPixelId) return false;
        try {
            $result = $this->sendTikTokEventsAPI('Test', ['value' => 0, 'currency' => self::CURRENCY], [
                'event_id' => $this->generateEventId('Test'),
                'event_time' => time(),
                'event_source_url' => request()->fullUrl(),
                'action_source' => 'website',
            ]);
            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('TikTok test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function testGoogle(): array
    {
        if (!$this->googleCapiEnabled) {
            return ['success' => false, 'message' => 'Google CAPI غير مفعل، قم بتفعيل Google Conversions API أولاً'];
        }
        if (!$this->googleConversionId) {
            return ['success' => false, 'message' => 'Conversion ID فارغ، يرجى إدخال معرف التحويل'];
        }
        if (!$this->googleConversionLabel) {
            return ['success' => false, 'message' => 'Conversion Label فارغ، يرجى إدخال تسمية التحويل'];
        }
        try {
            $result = $this->sendGoogleCAPI('Test', ['value' => 0, 'currency' => self::CURRENCY], null, [
                'event_id' => $this->generateEventId('Test'),
                'event_time' => time(),
                'event_source_url' => request()->fullUrl(),
                'action_source' => 'website',
            ]);
            if ($result['success'] ?? false) {
                return ['success' => true, 'message' => 'تم الاتصال بـ Google Ads بنجاح'];
            }
            return ['success' => false, 'message' => 'فشل الاتصال: ' . ($result['error'] ?? 'خطأ غير معروف')];
        } catch (\Exception $e) {
            Log::error('Google test failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطأ في الاتصال: ' . $e->getMessage()];
        }
    }
}
