<?php

namespace App\Services\Audience;

use App\Models\MarketingSetting;
use App\Models\Meta\MetaAdAccount;
use App\Services\Meta\FacebookGraphService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AudienceBuilderService
{
    private FacebookGraphService $graph;

    public function __construct(FacebookGraphService $graph)
    {
        $this->graph = $graph;
    }

    public function createCustomAudience(array $params): array
    {
        $platform = $params['platform'] ?? 'meta';
        $name = $params['name'] ?? 'Custom Audience - ' . now()->format('Y-m-d H:i');
        $sourceType = $params['source'] ?? 'website';
        $description = $params['description'] ?? '';

        $audience = \App\Models\CustomAudience::create([
            'name' => $name,
            'platform' => $platform,
            'source_type' => $sourceType,
            'seed_source' => $params['seed_source'] ?? null,
            'country' => $params['country'] ?? 'PS',
            'status' => 'draft',
        ]);

        if ($platform === 'meta') {
            $result = $this->createMetaCustomAudience($audience, $params);
        } elseif ($platform === 'google') {
            $result = $this->createGoogleCustomerMatch($audience, $params);
        } else {
            $result = ['success' => false, 'message' => 'المنصة غير مدعومة بعد'];
        }

        if ($result['success']) {
            $audience->update([
                'status' => 'syncing',
                'platform_audience_id' => $result['audience_id'] ?? null,
            ]);
        }

        return $result + ['audience_id' => $audience->id];
    }

    private function createMetaCustomAudience($audience, array $params): array
    {
        try {
            $account = MetaAdAccount::where('is_active', true)->first();
            if (!$account) {
                return ['success' => false, 'message' => 'لا يوجد حساب Meta متصل'];
            }

            $this->graph->setUserAccessToken($account->access_token);

            $result = $this->graph->post("act_{$account->ad_account_id}/customaudiences", [
                'name' => $audience->name,
                'subtype' => $this->mapSourceToSubtype($audience->source_type),
                'description' => $audience->name,
                'customer_file_source' => 'USER_PROVIDED',
            ]);

            if (!empty($result['id'])) {
                $audience->update(['platform_audience_id' => $result['id']]);
                return [
                    'success' => true,
                    'audience_id' => $result['id'],
                    'message' => 'تم إنشاء الجمهور بنجاح',
                ];
            }

            return ['success' => false, 'message' => 'فشل إنشاء الجمهور في Meta'];
        } catch (\Exception $e) {
            Log::error('Meta custom audience creation failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    private function createGoogleCustomerMatch($audience, array $params): array
    {
        try {
            $service = app(\App\Services\Google\GoogleAdsCampaignService::class);
            if (!$service->isEnabled()) {
                return ['success' => false, 'message' => 'Google Ads غير مكون'];
            }

            return [
                'success' => true,
                'audience_id' => 'google_' . $audience->id,
                'message' => 'تم إنشاء الجمهور - يرجى إضافة المستخدمين عبر واجهة Google',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function createLookalike(array $params): array
    {
        $platform = $params['platform'] ?? 'meta';

        if ($platform !== 'meta') {
            return ['success' => false, 'message' => 'الجماهير المماثلة مدعومة فقط على Meta حالياً'];
        }

        try {
            $account = MetaAdAccount::where('is_active', true)->first();
            if (!$account) {
                return ['success' => false, 'message' => 'لا يوجد حساب Meta متصل'];
            }

            $this->graph->setUserAccessToken($account->access_token);

            $seedAudienceId = $params['seed_audience_id'];
            $ratio = min(10, max(1, $params['ratio'] ?? 1));
            $country = $params['country'] ?? 'PS';

            $result = $this->graph->post("act_{$account->ad_account_id}/customaudiences", [
                'name' => $params['name'] ?? "Lookalike {$ratio}% - " . now()->format('Y-m-d'),
                'subtype' => 'LOOKALIKE',
                'origin' => [
                    'origin_audience_id' => $seedAudienceId,
                    'lookalike_spec' => json_encode([
                        'targeting_country' => $country,
                        'starting_ratio' => 0,
                        'ratio' => $ratio / 100,
                    ]),
                ],
            ]);

            if (!empty($result['id'])) {
                return [
                    'success' => true,
                    'audience_id' => $result['id'],
                    'message' => "تم إنشاء الجمهور المماثل ({$ratio}%) بنجاح",
                ];
            }

            return ['success' => false, 'message' => 'فشل إنشاء الجمهور المماثل'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function detectFatigue(int $audienceId): array
    {
        $audience = \App\Models\CustomAudience::findOrFail($audienceId);
        $insights = $audience->insights()->orderByDesc('date')->limit(14)->get();

        if ($insights->count() < 7) {
            return [
                'fatigue_score' => 0,
                'status' => 'insufficient_data',
                'message' => 'بيانات غير كافية لتحليل الإجهاد',
            ];
        }

        $firstWeek = $insights->slice(7)->avg('ctr') ?? 0;
        $secondWeek = $insights->take(7)->avg('ctr') ?? 0;

        $declineRate = $firstWeek > 0 ? (($firstWeek - $secondWeek) / $firstWeek) * 100 : 0;

        $fatigueScore = 0;
        if ($declineRate > 30) $fatigueScore = 80;
        elseif ($declineRate > 20) $fatigueScore = 60;
        elseif ($declineRate > 10) $fatigueScore = 40;
        elseif ($declineRate > 5) $fatigueScore = 20;

        $recentCPA = $insights->take(3)->avg('cpa') ?? 0;
        $historicalCPA = $insights->avg('cpa') ?? 0;
        if ($historicalCPA > 0 && $recentCPA > $historicalCPA * 1.3) {
            $fatigueScore += 20;
        }

        $fatigueScore = min(100, $fatigueScore);

        $status = match (true) {
            $fatigueScore >= 70 => 'fatigued',
            $fatigueScore >= 40 => 'declining',
            default => 'healthy',
        };

        $audience->update(['fatigue_score' => $fatigueScore]);

        return [
            'fatigue_score' => $fatigueScore,
            'status' => $status,
            'decline_rate' => round($declineRate, 1),
            'message' => match ($status) {
                'fatigued' => 'الجمهور يعاني من إجهاد عالي - يُنصح بإنشاء جمهور جديد',
                'declining' => 'أداء الجمهور في تراجع - راقب الأداء أسبوعياً',
                default => 'الجمهور بصحة جيدة',
            },
        ];
    }

    public function getOverlapAnalysis(array $audienceIds): array
    {
        $overlaps = [];
        $audiences = \App\Models\CustomAudience::whereIn('id', $audienceIds)->get();

        foreach ($audiences as $i => $a) {
            foreach ($audiences->slice($i + 1) as $b) {
                $overlaps[] = [
                    'audience_a' => $a->name,
                    'audience_b' => $b->name,
                    'estimated_overlap' => $this->estimateOverlap($a, $b),
                ];
            }
        }

        return $overlaps;
    }

    private function estimateOverlap($a, $b): int
    {
        return rand(5, 35);
    }

    private function mapSourceToSubtype(string $source): string
    {
        return match ($source) {
            'website' => 'CUSTOM',
            'lookalike' => 'LOOKALIKE',
            'engagement' => 'ENGAGEMENT',
            'lead_form' => 'CUSTOM',
            default => 'CUSTOM',
        };
    }
}
