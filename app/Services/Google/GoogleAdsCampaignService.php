<?php

namespace App\Services\Google;

use App\Models\MarketingSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleAdsCampaignService
{
    private ?string $customerId;
    private ?string $developerToken;
    private ?string $refreshToken;
    private ?string $clientId;
    private ?string $clientSecret;
    private ?string $loginCustomerId;

    private const API_VERSION = 'v17';

    public function __construct()
    {
        $this->loadSettings();
    }

    private function loadSettings(): void
    {
        $this->customerId = MarketingSetting::get('google_ads_cid');
        $this->developerToken = MarketingSetting::get('google_ads_developer_token');
        $this->refreshToken = MarketingSetting::get('google_ads_refresh_token');
        $this->clientId = config('services.google.client_id');
        $this->clientSecret = config('services.google.client_secret');
        $this->loginCustomerId = MarketingSetting::get('google_ads_login_customer_id');
    }

    public function isEnabled(): bool
    {
        return !empty($this->customerId) && !empty($this->developerToken) && !empty($this->refreshToken);
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
                return $response->json('access_token');
            }
        } catch (\Exception $e) {
            Log::error('Google OAuth token refresh failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    private function request(string $method, string $endpoint, ?array $data = null): ?array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::error('Google Ads: No access token available');
            return null;
        }

        $customerId = str_replace('-', '', $this->customerId);
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'developer-token' => $this->developerToken,
            'Content-Type' => 'application/json',
        ];

        if ($this->loginCustomerId) {
            $headers['login-customer-id'] = str_replace('-', '', $this->loginCustomerId);
        }

        $url = "https://googleads.googleapis.com/" . self::API_VERSION . "/customers/{$customerId}/{$endpoint}";

        try {
            $start = microtime(true);
            $response = match ($method) {
                'GET' => Http::timeout(15)->withHeaders($headers)->get($url, $data ?? []),
                'POST' => Http::timeout(15)->withHeaders($headers)->post($url, $data),
                'PUT' => Http::timeout(15)->withHeaders($headers)->put($url, $data),
                'DELETE' => Http::timeout(15)->withHeaders($headers)->delete($url),
                default => Http::timeout(15)->withHeaders($headers)->get($url),
            };
            $duration = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                return $response->json();
            }

            $body = $response->json();
            Log::warning('Google Ads API error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'error' => $body['error']['message'] ?? 'Unknown',
                'duration_ms' => $duration,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Google Ads API exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function getCampaigns(): array
    {
        $query = "SELECT campaign.id, campaign.name, campaign.status, campaign.advertising_channel_type, campaign.bidding_strategy_type, campaign.start_date, campaign.end_date, campaign_budget.amount_micros, campaign_budget.currency_code, campaign_budget.period FROM campaign WHERE campaign.status != 'REMOVED' ORDER BY campaign.id DESC";

        $result = $this->request('googleAds:searchStream', [
            'query' => $query,
        ]);

        $campaigns = [];
        if (!empty($result[0]['results'])) {
            foreach ($result[0]['results'] as $row) {
                $campaign = $row['campaign'] ?? [];
                $budget = $row['campaignBudget'] ?? [];
                $campaigns[] = [
                    'campaign_id' => $campaign['resourceName'] ?? '',
                    'name' => $campaign['name'] ?? '',
                    'status' => $campaign['status'] ?? 'PAUSED',
                    'channel_type' => $campaign['advertisingChannelType'] ?? 'SEARCH',
                    'bidding_strategy' => $campaign['biddingStrategyType'] ?? '',
                    'start_date' => $campaign['startDate'] ?? null,
                    'end_date' => $campaign['endDate'] ?? null,
                    'budget_amount' => isset($budget['amountMicros']) ? ($budget['amountMicros'] / 1000000) : 0,
                    'budget_currency' => $budget['currencyCode'] ?? 'ILS',
                    'budget_period' => $budget['period'] ?? 'DAILY',
                ];
            }
        }

        return $campaigns;
    }

    public function getCampaign(string $campaignId): ?array
    {
        $query = "SELECT campaign.id, campaign.name, campaign.status, campaign.advertising_channel_type, campaign.bidding_strategy_type, campaign.start_date, campaign.end_date, campaign_budget.amount_micros, campaign_budget.currency_code, campaign_budget.period, campaign.target_cpa.target_cpa_micros, campaign.target_roas.target_roas FROM campaign WHERE campaign.resource_name = '{$campaignId}'";

        $result = $this->request('googleAds:searchStream', [
            'query' => $query,
        ]);

        if (!empty($result[0]['results'][0])) {
            $row = $result[0]['results'][0];
            $campaign = $row['campaign'] ?? [];
            $budget = $row['campaignBudget'] ?? [];

            return [
                'campaign_id' => $campaign['resourceName'] ?? '',
                'name' => $campaign['name'] ?? '',
                'status' => $campaign['status'] ?? 'PAUSED',
                'channel_type' => $campaign['advertisingChannelType'] ?? 'SEARCH',
                'bidding_strategy' => $campaign['biddingStrategyType'] ?? '',
                'start_date' => $campaign['startDate'] ?? null,
                'end_date' => $campaign['endDate'] ?? null,
                'budget_amount' => isset($budget['amountMicros']) ? ($budget['amountMicros'] / 1000000) : 0,
                'budget_currency' => $budget['currencyCode'] ?? 'ILS',
                'budget_period' => $budget['period'] ?? 'DAILY',
                'target_cpa' => isset($campaign['targetCpa']['targetCpaMicros']) ? ($campaign['targetCpa']['targetCpaMicros'] / 1000000) : null,
                'target_roas' => $campaign['targetRoas']['targetRoas'] ?? null,
            ];
        }

        return null;
    }

    public function createCampaign(array $data): ?string
    {
        $budgetResourceName = $this->createBudget([
            'amount_micros' => ($data['budget_amount'] ?? 10) * 1000000,
            'currency' => $data['budget_currency'] ?? 'ILS',
            'period' => $data['budget_period'] ?? 'DAILY',
        ]);

        if (!$budgetResourceName) {
            return null;
        }

        $campaign = [
            'name' => $data['name'],
            'advertisingChannelType' => $data['channel_type'] ?? 'SEARCH',
            'status' => $data['status'] ?? 'PAUSED',
            'campaignBudget' => $budgetResourceName,
            'biddingStrategyType' => $data['bidding_strategy'] ?? 'MAXIMIZE_CONVERSIONS',
        ];

        if (!empty($data['start_date'])) {
            $campaign['startDate'] = $data['start_date'];
        }

        if (!empty($data['end_date'])) {
            $campaign['endDate'] = $data['end_date'];
        }

        if (!empty($data['target_cpa']) && $data['bidding_strategy'] === 'TARGET_CPA') {
            $campaign['targetCpa'] = [
                'targetCpaMicros' => $data['target_cpa'] * 1000000,
            ];
        }

        $result = $this->request('campaigns:mutate', [
            'operations' => [[
                'create' => $campaign,
            ]],
        ]);

        if (!empty($result['results'][0]['resourceName'])) {
            return $result['results'][0]['resourceName'];
        }

        return null;
    }

    private function createBudget(array $data): ?string
    {
        $result = $this->request('campaignBudgets:mutate', [
            'operations' => [[
                'create' => [
                    'name' => 'Budget - ' . now()->format('Y-m-d H:i:s'),
                    'amountMicros' => $data['amount_micros'],
                    'deliveryMethod' => 'STANDARD',
                ],
            ]],
        ]);

        if (!empty($result['results'][0]['resourceName'])) {
            return $result['results'][0]['resourceName'];
        }

        return null;
    }

    public function updateCampaign(string $campaignId, array $data): bool
    {
        $updateMask = [];
        $campaign = ['resourceName' => $campaignId];

        if (isset($data['name'])) {
            $campaign['name'] = $data['name'];
            $updateMask[] = 'name';
        }

        if (isset($data['status'])) {
            $campaign['status'] = $data['status'];
            $updateMask[] = 'status';
        }

        if (isset($data['budget_amount'])) {
            $campaign['budgetAmountMicros'] = $data['budget_amount'] * 1000000;
            $updateMask[] = 'budget_amount_micros';
        }

        if (empty($updateMask)) {
            return false;
        }

        $result = $this->request('campaigns:mutate', [
            'operations' => [[
                'update' => $campaign,
                'updateMask' => implode(',', $updateMask),
            ]],
        ]);

        return !empty($result['results'][0]['resourceName']);
    }

    public function pauseCampaign(string $campaignId): bool
    {
        return $this->updateCampaign($campaignId, ['status' => 'PAUSED']);
    }

    public function resumeCampaign(string $campaignId): bool
    {
        return $this->updateCampaign($campaignId, ['status' => 'ENABLED']);
    }

    public function removeCampaign(string $campaignId): bool
    {
        $result = $this->request('campaigns:mutate', [
            'operations' => [[
                'remove' => $campaignId,
            ]],
        ]);

        return !empty($result['results'][0]['resourceName']);
    }

    public function getCampaignMetrics(string $campaignId, string $dateRange = 'last_30d'): ?array
    {
        $query = "SELECT campaign.id, campaign.name, metrics.impressions, metrics.clicks, metrics.cost_micros, metrics.conversions, metrics.conversions_value, metrics.ctr, metrics.average_cpc, metrics.cost_per_conversion, metrics.search_impression_share FROM campaign WHERE campaign.resource_name = '{$campaignId}' AND segments.date DURING {$dateRange}";

        $result = $this->request('googleAds:searchStream', [
            'query' => $query,
        ]);

        if (!empty($result[0]['results'][0])) {
            $metrics = $result[0]['results'][0]['metrics'] ?? [];
            return [
                'impressions' => $metrics['impressions'] ?? 0,
                'clicks' => $metrics['clicks'] ?? 0,
                'cost' => isset($metrics['costMicros']) ? ($metrics['costMicros'] / 1000000) : 0,
                'conversions' => $metrics['conversions'] ?? 0,
                'conversion_value' => $metrics['conversionsValue'] ?? 0,
                'ctr' => $metrics['ctr'] ?? 0,
                'average_cpc' => isset($metrics['averageCpc']) ? ($metrics['averageCpc'] / 1000000) : 0,
                'cost_per_conversion' => $metrics['costPerConversion'] ?? 0,
                'search_impression_share' => $metrics['searchImpressionShare'] ?? 0,
                'roas' => ($metrics['conversions'] ?? 0) > 0
                    ? ($metrics['conversionsValue'] ?? 0) / ($metrics['conversions'] ?? 1)
                    : 0,
            ];
        }

        return null;
    }

    public function getAdGroups(string $campaignId): array
    {
        $query = "SELECT ad_group.id, ad_group.name, ad_group.status, ad_group.type, ad_group.cpc_bid_micros FROM ad_group WHERE ad_group.campaign = '{$campaignId}' ORDER BY ad_group.id DESC";

        $result = $this->request('googleAds:searchStream', [
            'query' => $query,
        ]);

        $adGroups = [];
        if (!empty($result[0]['results'])) {
            foreach ($result[0]['results'] as $row) {
                $ag = $row['adGroup'] ?? [];
                $adGroups[] = [
                    'ad_group_id' => $ag['resourceName'] ?? '',
                    'name' => $ag['name'] ?? '',
                    'status' => $ag['status'] ?? 'PAUSED',
                    'type' => $ag['type'] ?? 'SEARCH_STANDARD',
                    'cpc_bid' => isset($ag['cpcBidMicros']) ? ($ag['cpcBidMicros'] / 1000000) : 0,
                ];
            }
        }

        return $adGroups;
    }

    public function createAdGroup(string $campaignId, array $data): ?string
    {
        $result = $this->request('adGroups:mutate', [
            'operations' => [[
                'create' => [
                    'campaign' => $campaignId,
                    'name' => $data['name'],
                    'status' => $data['status'] ?? 'PAUSED',
                    'type' => $data['type'] ?? 'SEARCH_STANDARD',
                    'cpcBidMicros' => isset($data['cpc_bid']) ? ($data['cpc_bid'] * 1000000) : null,
                ],
            ]],
        ]);

        if (!empty($result['results'][0]['resourceName'])) {
            return $result['results'][0]['resourceName'];
        }

        return null;
    }

    public function getKeywords(string $adGroupId): array
    {
        $query = "SELECT ad_group_criterion.keyword.text, ad_group_criterion.keyword.match_type, ad_group_criterion.status, ad_group_criterion.quality_info.quality_score FROM ad_group_criterion WHERE ad_group_criterion.ad_group = '{$adGroupId}' AND ad_group_criterion.type = 'KEYWORD'";

        $result = $this->request('googleAds:searchStream', [
            'query' => $query,
        ]);

        $keywords = [];
        if (!empty($result[0]['results'])) {
            foreach ($result[0]['results'] as $row) {
                $kw = $row['adGroupCriterion'] ?? [];
                $keywords[] = [
                    'text' => $kw['keyword']['text'] ?? '',
                    'match_type' => $kw['keyword']['matchType'] ?? 'BROAD',
                    'status' => $kw['status'] ?? 'ENABLED',
                    'quality_score' => $kw['qualityInfo']['qualityScore'] ?? null,
                ];
            }
        }

        return $keywords;
    }

    public function addKeyword(string $adGroupId, array $data): bool
    {
        $result = $this->request('adGroupCriteria:mutate', [
            'operations' => [[
                'create' => [
                    'adGroup' => $adGroupId,
                    'status' => $data['status'] ?? 'ENABLED',
                    'keyword' => [
                        'text' => $data['text'],
                        'matchType' => $data['match_type'] ?? 'BROAD',
                    ],
                ],
            ]],
        ]);

        return !empty($result['results'][0]['resourceName']);
    }

    public function createResponsiveSearchAd(string $adGroupId, array $data): ?string
    {
        $headlines = [];
        foreach (array_slice($data['headlines'] ?? [], 0, 15) as $i => $headline) {
            $headlines[] = [
                'text' => $headline,
                'pinnedField' => $i < 3 ? 'HEADLINE_' . ($i + 1) : 'UNSPECIFIED',
            ];
        }

        $descriptions = [];
        foreach (array_slice($data['descriptions'] ?? [], 0, 4) as $i => $desc) {
            $descriptions[] = [
                'text' => $desc,
                'pinnedField' => $i < 2 ? 'DESCRIPTION_' . ($i + 1) : 'UNSPECIFIED',
            ];
        }

        $result = $this->request('adGroupAds:mutate', [
            'operations' => [[
                'create' => [
                    'adGroup' => $adGroupId,
                    'status' => $data['status'] ?? 'PAUSED',
                    'ad' => [
                        'responsiveSearchAd' => [
                            'headlines' => $headlines,
                            'descriptions' => $descriptions,
                        ],
                        'finalUrls' => [$data['final_url'] ?? url('/')],
                    ],
                ],
            ]],
        ]);

        if (!empty($result['results'][0]['resourceName'])) {
            return $result['results'][0]['resourceName'];
        }

        return null;
    }

    public function testConnection(): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'Google Ads غير مكون، يرجى إدخال بيانات الاتصال'];
        }

        try {
            $query = "SELECT customer.id, customer.descriptive_name, customer.currency_code, customer.time_zone FROM customer LIMIT 1";
            $result = $this->request('googleAds:searchStream', [
                'query' => $query,
            ]);

            if (!empty($result[0]['results'][0])) {
                $customer = $result[0]['results'][0]['customer'] ?? [];
                return [
                    'success' => true,
                    'message' => 'تم الاتصال بـ Google Ads بنجاح',
                    'account' => $customer,
                ];
            }

            return ['success' => false, 'message' => 'لم يتم العثور على حساب Google Ads'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'خطأ في الاتصال: ' . $e->getMessage()];
        }
    }
}
