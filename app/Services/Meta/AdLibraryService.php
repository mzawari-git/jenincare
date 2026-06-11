<?php

namespace App\Services\Meta;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdLibraryService
{
    private string $apiVersion;
    private string $accessToken;

    public function __construct()
    {
        $this->apiVersion = config('meta.api_version', 'v22.0');
        $this->accessToken = config('meta.app_id') . '|' . config('meta.app_secret');
    }

    public function searchCompetitorAds(string $query, array $params = []): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/ads_archive";

        $defaultParams = [
            'access_token' => $this->accessToken,
            'search_terms' => $query,
            'ad_type' => 'ALL',
            'ad_reactive_country' => 'PS',
            'limit' => min($params['limit'] ?? 20, 100),
            'fields' => implode(',', [
                'ad_creative_body', 'ad_creative_link_description', 'ad_creative_link_title',
                'ad_delivery_start_time', 'ad_delivery_stop_time', 'ad_snapshot_url',
                'bylines', 'currency', 'demographic_distribution', 'delivery_by_region',
                'funding_entity', 'impressions', 'page_id', 'page_name',
                'publisher_platforms', 'spend', 'target_ages', 'target_gender',
                'target_locations',
            ]),
        ];

        if (!empty($params['platform'])) {
            $defaultParams['publisher_platforms'] = $params['platform'];
        }
        if (!empty($params['country'])) {
            $defaultParams['ad_reactive_country'] = $params['country'];
        }
        if (!empty($params['start_date'])) {
            $defaultParams['ad_delivery_date_min'] = $params['start_date'];
        }
        if (!empty($params['end_date'])) {
            $defaultParams['ad_delivery_date_max'] = $params['end_date'];
        }

        try {
            $response = Http::get($url, $defaultParams);

            if ($response->successful()) {
                $data = $response->json();
                $ads = $data['data'] ?? [];

                return [
                    'success' => true,
                    'total_count' => $data['total_count'] ?? count($ads),
                    'ads' => array_map([$this, 'normalizeAd'], $ads),
                    'search_params' => [
                        'query' => $query,
                        'country' => $defaultParams['ad_reactive_country'],
                        'results' => count($ads),
                    ],
                ];
            }

            Log::warning('Ad Library API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'فشل الاتصال بمكتبة إعلانات فيسبوك',
                'error' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('Ad Library API exception', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'خطأ في الاتصال: ' . $e->getMessage(),
            ];
        }
    }

    public function searchByPage(string $pageName, int $limit = 20): array
    {
        return $this->searchCompetitorAds($pageName, [
            'limit' => $limit,
            'start_date' => now()->subMonths(6)->format('Y-m-d'),
        ]);
    }

    public function getPageAds(string $pageId, int $limit = 50): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$pageId}/ads";

        try {
            $response = Http::get($url, [
                'access_token' => $this->accessToken,
                'limit' => $limit,
                'fields' => 'id,ad_creative_body,ad_creative_link_title,ad_creative_link_description,ad_delivery_start_time,ad_snapshot_url,publisher_platforms,spend,impressions',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'ads' => array_map([$this, 'normalizeAd'], $data['data'] ?? []),
                    'total' => $data['total_count'] ?? 0,
                ];
            }

            return ['success' => false, 'message' => 'فشل في جلب الإعلانات'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getCommonAdsInIndustry(string $industry, int $limit = 30): array
    {
        $competitors = $this->getIndustryCompetitors($industry);
        $results = [];

        foreach ($competitors as $name) {
            $ads = $this->searchByPage($name, 5);
            if ($ads['success'] && !empty($ads['ads'])) {
                $results[$name] = $ads['ads'];
            }
        }

        return [
            'industry' => $industry,
            'competitors_analyzed' => count($results),
            'total_ads_found' => array_sum(array_map('count', $results)),
            'competitors' => $results,
        ];
    }

    public function analyzeAdSpend(array $ads): array
    {
        $spendRanges = [];
        $platforms = [];
        $totalImpressions = 0;

        foreach ($ads as $ad) {
            $spend = $ad['spend'] ?? '';
            if ($spend && !isset($spendRanges[$spend])) {
                $spendRanges[$spend] = ($spendRanges[$spend] ?? 0) + 1;
            }

            $adPlatforms = $ad['publisher_platforms'] ?? [];
            foreach ((array) $adPlatforms as $p) {
                $platforms[$p] = ($platforms[$p] ?? 0) + 1;
            }

            $totalImpressions += $ad['impressions'] ?? 0;
        }

        arsort($spendRanges);
        arsort($platforms);

        return [
            'spend_distribution' => $spendRanges,
            'platform_distribution' => $platforms,
            'total_impressions' => $totalImpressions,
            'top_spend_range' => array_key_first($spendRanges) ?? 'غير معروف',
        ];
    }

    private function normalizeAd(array $ad): array
    {
        return [
            'id' => $ad['id'] ?? '',
            'page_id' => $ad['page_id'] ?? '',
            'page_name' => $ad['page_name'] ?? '',
            'creative_body' => $ad['ad_creative_body'] ?? '',
            'creative_title' => $ad['ad_creative_link_title'] ?? '',
            'creative_description' => $ad['ad_creative_link_description'] ?? '',
            'start_time' => $ad['ad_delivery_start_time'] ?? '',
            'stop_time' => $ad['ad_delivery_stop_time'] ?? '',
            'snapshot_url' => $ad['ad_snapshot_url'] ?? '',
            'platforms' => $ad['publisher_platforms'] ?? [],
            'spend' => $ad['spend'] ?? '',
            'impressions' => $ad['impressions'] ?? '',
            'funding_entity' => $ad['funding_entity'] ?? '',
            'currency' => $ad['currency'] ?? 'ILS',
            'target_gender' => $ad['target_gender'] ?? '',
            'target_ages' => $ad['target_ages'] ?? [],
        ];
    }

    private function getIndustryCompetitors(string $industry): array
    {
        return match ($industry) {
            'beauty' => [
                'صالون جمال', 'مركز عناية', 'بيوتي سنتر',
                'متجر مكياج', 'عناية بالبشرة',
            ],
            'salon' => [
                'صالون تسريحات', 'كوافير نسائي', 'صالون حلاقة',
                'مركز تجميل',
            ],
            'medical' => [
                'عيادة طبية', 'مركز صحي', 'مستشفى',
                'دكتور أسنان',
            ],
            default => [
                'شركة محلية', 'متجر الكتروني', 'خدمة عملاء',
            ],
        };
    }
}
