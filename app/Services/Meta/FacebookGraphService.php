<?php

namespace App\Services\Meta;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FacebookGraphService
{
    private string $apiVersion;
    private string $appId;
    private string $appSecret;
    private ?string $userAccessToken = null;
    private array $rateLimitQueue = [];

    public function __construct()
    {
        $this->apiVersion = config('meta.api_version', 'v22.0');
        $this->appId = config('meta.app_id', '');
        $this->appSecret = config('meta.app_secret', '');
    }

    public function setUserAccessToken(string $token): self
    {
        $this->userAccessToken = $token;
        return $this;
    }

    public function getGraphUrl(): string
    {
        return "https://graph.facebook.com/{$this->apiVersion}";
    }

    public function exchangeToken(string $shortLivedToken): ?array
    {
        $response = Http::get("{$this->getGraphUrl()}/oauth/access_token", [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'fb_exchange_token' => $shortLivedToken,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Facebook token exchange failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    public function debugToken(string $token): ?array
    {
        $response = Http::get("{$this->getGraphUrl()}/debug_token", [
            'input_token' => $token,
            'access_token' => "{$this->appId}|{$this->appSecret}",
        ]);

        if ($response->successful()) {
            return $response->json('data');
        }

        return null;
    }

    public function get(string $endpoint, array $params = []): array
    {
        return $this->request('GET', $endpoint, $params);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, $data);
    }

    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    private function request(string $method, string $endpoint, array $params = []): array
    {
        $token = $this->resolveToken();
        $url = "{$this->getGraphUrl()}/{$endpoint}";

        $this->enforceRateLimit();

        $start = microtime(true);

        $response = match ($method) {
            'GET' => Http::withToken($token)->get($url, $params),
            'POST' => Http::withToken($token)->post($url, $params),
            'DELETE' => Http::withToken($token)->delete($url),
            default => Http::withToken($token)->get($url, $params),
        };

        $duration = round((microtime(true) - $start) * 1000);

        $this->recordApiCall($duration);

        if ($response->successful()) {
            return $response->json();
        }

        $body = $response->json();
        $error = $body['error'] ?? [];

        if (isset($error['code'])) {
            match ($error['code']) {
                4, 17, 32, 613 => $this->handleRateLimit(),
                190 => $this->handleInvalidToken($error),
                100 => Log::warning('Facebook API: invalid parameter', ['error' => $error]),
                200 => Log::warning('Facebook API: permission error', ['error' => $error]),
                default => Log::warning('Facebook API error', ['code' => $error['code'], 'message' => $error['message'] ?? '']),
            };
        }

        Log::error('Facebook API request failed', [
            'method' => $method,
            'endpoint' => $endpoint,
            'status' => $response->status(),
            'error' => $error,
            'duration_ms' => $duration,
        ]);

        return $body ?: ['error' => $error];
    }

    public function getAdAccounts(?string $token = null): array
    {
        if ($token) {
            $this->setUserAccessToken($token);
        }

        $result = $this->get('me/adaccounts', [
            'fields' => 'id,name,account_id,currency,timezone_name,business_id,spend_cap,amount_spent,account_status',
        ]);

        return $result['data'] ?? [];
    }

    public function getCampaigns(string $adAccountId, array $fields = []): array
    {
        $defaultFields = [
            'id', 'name', 'objective', 'status', 'buying_type',
            'daily_budget', 'lifetime_budget', 'bid_strategy',
            'special_ad_categories', 'start_time', 'stop_time',
            'created_time', 'updated_time',
        ];

        $result = $this->get("act_{$adAccountId}/campaigns", [
            'fields' => implode(',', $fields ?: $defaultFields),
            'limit' => 100,
        ]);

        return $result['data'] ?? [];
    }

    public function createCampaign(string $adAccountId, array $data): array
    {
        return $this->post("act_{$adAccountId}/campaigns", [
            'name' => $data['name'],
            'objective' => $data['objective'],
            'status' => $data['status'] ?? 'PAUSED',
            'buying_type' => $data['buying_type'] ?? 'AUCTION',
            'daily_budget' => isset($data['daily_budget']) ? (int) ($data['daily_budget'] * 100) : null,
            'lifetime_budget' => isset($data['lifetime_budget']) ? (int) ($data['lifetime_budget'] * 100) : null,
            'bid_strategy' => $data['bid_strategy'] ?? 'LOWEST_COST_WITHOUT_CAP',
            'special_ad_categories' => $data['special_ad_categories'] ?? null,
            'start_time' => $data['start_time'] ?? null,
        ]);
    }

    public function updateCampaignStatus(string $campaignId, string $status): array
    {
        return $this->post($campaignId, ['status' => $status]);
    }

    public function deleteCampaign(string $campaignId): array
    {
        return $this->delete($campaignId);
    }

    public function getAdSets(string $campaignId, array $fields = []): array
    {
        $defaultFields = [
            'id', 'name', 'campaign_id', 'status', 'optimization_goal',
            'billing_event', 'daily_budget', 'lifetime_budget', 'bid_amount',
            'targeting', 'start_time', 'end_time', 'promoted_object',
            'created_time', 'updated_time',
        ];

        $result = $this->get("{$campaignId}/adsets", [
            'fields' => implode(',', $fields ?: $defaultFields),
            'limit' => 100,
        ]);

        return $result['data'] ?? [];
    }

    public function createAdSet(string $adAccountId, array $data): array
    {
        return $this->post("act_{$adAccountId}/adsets", [
            'name' => $data['name'],
            'campaign_id' => $data['campaign_id'],
            'status' => $data['status'] ?? 'PAUSED',
            'optimization_goal' => $data['optimization_goal'],
            'billing_event' => $data['billing_event'] ?? 'IMPRESSIONS',
            'daily_budget' => isset($data['daily_budget']) ? (int) ($data['daily_budget'] * 100) : null,
            'lifetime_budget' => isset($data['lifetime_budget']) ? (int) ($data['lifetime_budget'] * 100) : null,
            'bid_amount' => isset($data['bid_amount']) ? (int) ($data['bid_amount'] * 100) : null,
            'targeting' => $data['targeting'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'promoted_object' => $data['promoted_object'] ?? null,
        ]);
    }

    public function updateAdSetStatus(string $adSetId, string $status): array
    {
        return $this->post($adSetId, ['status' => $status]);
    }

    public function getAds(string $adSetId, array $fields = []): array
    {
        $defaultFields = [
            'id', 'name', 'adset_id', 'campaign_id', 'status',
            'creative', 'tracking_specs', 'created_time', 'updated_time',
        ];

        $result = $this->get("{$adSetId}/ads", [
            'fields' => implode(',', $fields ?: $defaultFields),
            'limit' => 100,
        ]);

        return $result['data'] ?? [];
    }

    public function createAd(string $adAccountId, array $data): array
    {
        return $this->post("act_{$adAccountId}/ads", [
            'name' => $data['name'],
            'adset_id' => $data['adset_id'],
            'creative' => $data['creative'],
            'status' => $data['status'] ?? 'PAUSED',
        ]);
    }

    public function updateAdStatus(string $adId, string $status): array
    {
        return $this->post($adId, ['status' => $status]);
    }

    public function uploadImage(string $adAccountId, string $filePath, ?string $fileName = null): ?array
    {
        $token = $this->resolveToken();
        $url = "{$this->getGraphUrl()}/act_{$adAccountId}/adimages";

        $this->enforceRateLimit();
        $start = microtime(true);

        $response = Http::withToken($token)
            ->attach('filename', file_get_contents($filePath), $fileName ?? basename($filePath))
            ->post($url);

        $this->recordApiCall(round((microtime(true) - $start) * 1000));

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Facebook image upload failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    public function createCreative(string $adAccountId, array $data): array
    {
        return $this->post("act_{$adAccountId}/adcreatives", $data);
    }

    public function getInsights(string $objectId, string $level = 'campaign', array $params = []): array
    {
        $defaultParams = [
            'fields' => implode(',', [
                'campaign_name', 'campaign_id', 'account_id',
                'impressions', 'clicks', 'spend', 'ctr', 'cpc', 'cpm',
                'reach', 'frequency', 'actions', 'cost_per_action_type',
                'conversions', 'cost_per_conversion', 'conversion_values',
            ]),
            'date_preset' => 'last_30d',
            'level' => $level,
            'limit' => 100,
        ];

        $result = $this->get("{$objectId}/insights", array_merge($defaultParams, $params));
        return $result['data'] ?? [];
    }

    public function batchRequest(array $requests): array
    {
        $token = $this->resolveToken();
        $url = "{$this->getGraphUrl()}/";

        $batch = [];
        foreach ($requests as $i => $req) {
            $batch[] = [
                'method' => $req['method'] ?? 'GET',
                'relative_url' => $req['relative_url'],
                'body' => $req['body'] ?? null,
            ];
        }

        $this->enforceRateLimit();
        $start = microtime(true);

        $response = Http::withToken($token)->post($url, [
            'batch' => json_encode($batch),
            'include_headers' => false,
        ]);

        $this->recordApiCall(round((microtime(true) - $start) * 1000));

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    public function getPageAccessToken(string $pageId, string $userToken): ?string
    {
        $result = $this->get("{$pageId}", [
            'fields' => 'access_token',
            'access_token' => $userToken,
        ]);

        return $result['access_token'] ?? null;
    }

    public function subscribeToWebhook(string $pageId, string $pageToken, array $fields = ['messages', 'messaging_postbacks', 'feed']): bool
    {
        $response = Http::withToken($pageToken)->post("{$this->getGraphUrl()}/{$pageId}/subscribed_apps", [
            'subscribed_fields' => $fields,
        ]);

        return $response->successful();
    }

    private function resolveToken(): string
    {
        if ($this->userAccessToken) {
            return $this->userAccessToken;
        }

        $account = \App\Models\Meta\MetaAdAccount::where('is_active', true)
            ->whereNotNull('access_token')
            ->first();

        return $account?->access_token ?? '';
    }

    private function enforceRateLimit(): void
    {
        $maxCalls = config('meta.rate_limit.max_calls', 200);
        $period = config('meta.rate_limit.period_seconds', 3600);

        $now = time();
        $this->rateLimitQueue = array_filter($this->rateLimitQueue, fn($t) => $t > $now - $period);

        if (count($this->rateLimitQueue) >= $maxCalls) {
            $oldest = min($this->rateLimitQueue);
            $sleep = ($oldest + $period) - $now;
            if ($sleep > 0) {
                Log::warning("Facebook rate limit reached, sleeping {$sleep}s");
                usleep($sleep * 1_000_000);
            }
        }
    }

    private function recordApiCall(int $durationMs): void
    {
        $this->rateLimitQueue[] = time();
    }

    private function handleRateLimit(): void
    {
        Log::warning('Facebook API rate limit hit, backing off');
        sleep(10);
    }

    private function handleInvalidToken(array $error): void
    {
        Log::error('Facebook API token invalid/expired', ['error' => $error]);
    }
}
