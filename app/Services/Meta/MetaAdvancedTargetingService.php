<?php

namespace App\Services\Meta;

use App\Models\Meta\MetaAdAccount;
use App\Models\Meta\MetaCampaign;
use App\Models\CustomAudience;
use App\Services\Meta\FacebookGraphService;
use Illuminate\Support\Facades\Log;

class MetaAdvancedTargetingService
{
    protected $graphService;

    public function __construct(FacebookGraphService $graphService)
    {
        $this->graphService = $graphService;
    }

    public function createLookalikeAudience($accountId, $sourceAudienceId, $country = 'PS', $percentage = 1)
    {
        $account = MetaAdAccount::findOrFail($accountId);
        
        $this->graphService->setAccessToken($account->access_token);

        try {
            $response = $this->graphService->createLookalikeAudience([
                'name' => 'Lookalike (' . $percentage . '%) - ' . now()->format('Y-m-d'),
                'origin_audience_id' => $sourceAudienceId,
            ]);

            Log::info("Created lookalike audience", ['audience_id' => $response['id'] ?? null]);

            return $response;
        } catch (\Exception $e) {
            Log::error("Failed to create lookalike audience: " . $e->getMessage());
            throw $e;
        }
    }

    public function buildRetargetingAudience($accountId, $rules)
    {
        $account = MetaAdAccount::findOrFail($accountId);
        
        $this->graphService->setAccessToken($account->access_token);

        $audienceData = [
            'name' => $rules['name'] ?? 'Retargeting Audience - ' . now()->format('Y-m-d'),
            'subtype' => 'WEBSITE',
            'rule' => json_encode($this->buildRetargetingRules($rules)),
            'retention_days' => $rules['retention_days'] ?? 30,
        ];

        try {
            $response = $this->graphService->createCustomAudience($audienceData);

            CustomAudience::create([
                'platform' => 'facebook',
                'platform_audience_id' => $response['id'] ?? null,
                'name' => $audienceData['name'],
                'source_type' => 'website',
                'audience_size' => 0,
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error("Failed to create retargeting audience: " . $e->getMessage());
            throw $e;
        }
    }

    protected function buildRetargetingRules($rules)
    {
        $retargetingRules = [];

        if (!empty($rules['page_visitors'])) {
            $retargetingRules[] = [
                'event' => ['url' => ['contains' => $rules['page_visitors']]],
            ];
        }

        if (!empty($rules['cart_abandoners'])) {
            $retargetingRules[] = [
                'event' => ['name' => ['eq' => 'AddToCart']],
                'event' => ['name' => ['neq' => 'Purchase']],
            ];
        }

        if (!empty($rules['time_on_site'])) {
            $retargetingRules[] = [
                'event' => ['time_spent' => ['gte' => $rules['time_on_site']]],
            ];
        }

        return [
            'inclusions' => [
                'operator' => 'or',
                'rules' => $retargetingRules,
            ],
        ];
    }

    public function createDynamicProductAudience($accountId, $catalogId, $productSetId = null)
    {
        $account = MetaAdAccount::findOrFail($accountId);
        
        $this->graphService->setAccessToken($account->access_token);

        $audienceData = [
            'name' => 'Dynamic Product Audience - ' . now()->format('Y-m-d'),
            'subtype' => 'WEBSITE',
            'rule' => json_encode([
                'inclusions' => [
                    'operator' => 'or',
                    'rules' => [
                        [
                            'event' => ['name' => ['eq' => 'ViewContent']],
                        ],
                        [
                            'event' => ['name' => ['eq' => 'AddToCart']],
                        ],
                    ],
                ],
            ]),
            'retention_days' => 30,
        ];

        try {
            return $this->graphService->createCustomAudience($audienceData);
        } catch (\Exception $e) {
            Log::error("Failed to create dynamic product audience: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAudienceInsights($accountId, $audienceId)
    {
        $account = MetaAdAccount::findOrFail($accountId);
        
        $this->graphService->setAccessToken($account->access_token);

        try {
            return $this->graphService->getAudienceInsights($audienceId);
        } catch (\Exception $e) {
            Log::error("Failed to get audience insights: " . $e->getMessage());
            return null;
        }
    }

    public function analyzeAudienceOverlap($accountId, $audienceIds)
    {
        $account = MetaAdAccount::findOrFail($accountId);
        
        $this->graphService->setAccessToken($account->access_token);

        $overlaps = [];
        
        for ($i = 0; $i < count($audienceIds); $i++) {
            for ($j = $i + 1; $j < count($audienceIds); $j++) {
                try {
                    $overlap = $this->graphService->getAudienceOverlap(
                        $audienceIds[$i],
                        $audienceIds[$j]
                    );
                    
                    $overlaps[] = [
                        'audience_1' => $audienceIds[$i],
                        'audience_2' => $audienceIds[$j],
                        'overlap_percentage' => $overlap['overlap'] ?? 0,
                    ];
                } catch (\Exception $e) {
                    Log::error("Failed to analyze audience overlap: " . $e->getMessage());
                }
            }
        }

        return $overlaps;
    }

    public function suggestAudienceExpansions($accountId, $campaignId)
    {
        $campaign = MetaCampaign::with('adSets')->findOrFail($campaignId);
        
        $suggestions = [];

        foreach ($campaign->adSets as $adSet) {
            $targeting = $adSet->targeting ?? [];
            
            if (empty($targeting['geo_locations'])) {
                $suggestions[] = [
                    'type' => 'geo_expansion',
                    'ad_set_id' => $adSet->id,
                    'suggestion' => 'وسع الموقع الجغرافي لتشمل مدن إضافية',
                ];
            }

            if (!empty($targeting['age_min']) && !empty($targeting['age_max'])) {
                $ageRange = $targeting['age_max'] - $targeting['age_min'];
                if ($ageRange < 20) {
                    $suggestions[] = [
                        'type' => 'age_expansion',
                        'ad_set_id' => $adSet->id,
                        'suggestion' => 'وسع الفئة العمرية للوصول لمزيد من العملاء',
                    ];
                }
            }

            if (empty($targeting['flexible_spec'])) {
                $suggestions[] = [
                    'type' => 'interest_expansion',
                    'ad_set_id' => $adSet->id,
                    'suggestion' => 'أضف اهتمامات إضافية للوصول لجمهور أوسع',
                ];
            }
        }

        return $suggestions;
    }

    public function createSimilarAudiences($accountId, $sourceCampaignId)
    {
        $campaign = MetaCampaign::with('adSets')->findOrFail($sourceCampaignId);
        
        $createdAudiences = [];

        foreach ($campaign->adSets as $adSet) {
            $targeting = $adSet->targeting ?? [];
            
            if (!empty($targeting['custom_audiences'])) {
                foreach ($targeting['custom_audiences'] as $customAudience) {
                    try {
                        $lookalike = $this->createLookalikeAudience(
                            $accountId,
                            $customAudience['id'],
                            'PS',
                            1
                        );
                        
                        $createdAudiences[] = $lookalike;
                    } catch (\Exception $e) {
                        Log::error("Failed to create similar audience: " . $e->getMessage());
                    }
                }
            }
        }

        return $createdAudiences;
    }
}
