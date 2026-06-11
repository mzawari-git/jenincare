<?php

namespace App\Services\Meta;

use App\Models\Meta\MetaCreativeFatigue;
use App\Models\Meta\MetaAdCreative;
use App\Services\Meta\FacebookGraphService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MetaCreativeOptimizationService
{
    protected $graphService;

    public function __construct(FacebookGraphService $graphService)
    {
        $this->graphService = $graphService;
    }

    public function analyzeCreativeFatigue($creativeId, $days = 14)
    {
        $creative = MetaAdCreative::findOrFail($creativeId);
        $account = $creative->adAccount;

        $this->graphService->setAccessToken($account->access_token);

        $insights = $this->graphService->getCreativeInsights($creative->creative_id, [
            'date_preset' => 'last_' . $days . 'd',
            'time_increment' => 1,
        ]);

        $dailyData = [];
        $previousCtr = null;

        foreach ($insights as $day) {
            $ctr = $day['ctr'] ?? 0;
            $ctrChange = $previousCtr !== null ? (($ctr - $previousCtr) / $previousCtr) * 100 : 0;
            
            $fatigueLevel = $this->calculateFatigueLevel($ctr, $ctrChange, $day['frequency'] ?? 0);
            $fatigueScore = $this->calculateFatigueScore($ctr, $ctrChange, $day['frequency'] ?? 0);

            $dailyData[] = [
                'date' => $day['date_start'],
                'ctr' => $ctr,
                'ctr_change' => round($ctrChange, 2),
                'frequency' => $day['frequency'] ?? 0,
                'impressions' => $day['impressions'] ?? 0,
                'clicks' => $day['clicks'] ?? 0,
                'fatigue_level' => $fatigueLevel,
                'fatigue_score' => $fatigueScore,
                'recommendations' => $this->generateRecommendations($fatigueLevel, $ctrChange, $day['frequency'] ?? 0),
            ];

            $previousCtr = $ctr;
        }

        $this->syncFatigueData($creativeId, $dailyData);

        return $dailyData;
    }

    protected function calculateFatigueLevel($ctr, $ctrChange, $frequency)
    {
        if ($ctrChange < -30 || $frequency > 5) {
            return 'critical';
        }
        if ($ctrChange < -20 || $frequency > 3) {
            return 'fatigued';
        }
        if ($ctrChange < -10 || $frequency > 2) {
            return 'warning';
        }
        return 'healthy';
    }

    protected function calculateFatigueScore($ctr, $ctrChange, $frequency)
    {
        $score = 100;
        
        $score -= abs($ctrChange) * 2;
        $score -= ($frequency - 1) * 15;
        
        return max(0, min(100, $score));
    }

    protected function generateRecommendations($fatigueLevel, $ctrChange, $frequency)
    {
        $recommendations = [];

        if ($fatigueLevel === 'critical') {
            $recommendations[] = 'استبدل هذا التصميم فوراً - الأداء متدهور بشدة';
            $recommendations[] = 'قم بإنشاء تصميم جديد بصور وفيديوهات مختلفة';
        } elseif ($fatigueLevel === 'fatigued') {
            $recommendations[] = 'فكر في تحديث التصميم قريباً';
            $recommendations[] = 'جرب تغيير النص أو الصورة الرئيسية';
        } elseif ($fatigueLevel === 'warning') {
            $recommendations[] = 'راقب الأداء عن كثب';
            $recommendations[] = 'جهز تصميمات بديلة';
        }

        if ($frequency > 3) {
            $recommendations[] = 'التردد عالي - وسع الجمهور المستهدف';
        }

        if ($ctrChange < -15) {
            $recommendations[] = 'انخفاض حاد في CTR - راجع جودة الإعلان';
        }

        return $recommendations;
    }

    protected function syncFatigueData($creativeId, $dailyData)
    {
        foreach ($dailyData as $data) {
            MetaCreativeFatigue::updateOrCreate(
                [
                    'creative_id' => $creativeId,
                    'date' => $data['date'],
                ],
                $data
            );
        }
    }

    public function getFatiguedCreatives($accountId = null, $threshold = 'warning')
    {
        $query = MetaCreativeFatigue::where('date', '>=', now()->subDays(3))
            ->whereIn('fatigue_level', $this->getThresholdLevels($threshold))
            ->with('creative');

        if ($accountId) {
            $query->whereHas('creative', function ($q) use ($accountId) {
                $q->where('ad_account_id', $accountId);
            });
        }

        return $query->get()->groupBy('creative_id')->map(function ($records) {
            $latest = $records->sortByDesc('date')->first();
            return [
                'creative' => $latest->creative,
                'fatigue_level' => $latest->fatigue_level,
                'fatigue_score' => $latest->fatigue_score,
                'ctr_change' => $latest->ctr_change,
                'frequency' => $latest->frequency,
                'recommendations' => $latest->recommendations,
            ];
        });
    }

    protected function getThresholdLevels($threshold)
    {
        switch ($threshold) {
            case 'healthy':
                return ['healthy', 'warning', 'fatigued', 'critical'];
            case 'warning':
                return ['warning', 'fatigued', 'critical'];
            case 'fatigued':
                return ['fatigued', 'critical'];
            case 'critical':
                return ['critical'];
            default:
                return ['warning', 'fatigued', 'critical'];
        }
    }

    public function getCreativeSuggestions($creativeId)
    {
        $creative = MetaAdCreative::findOrFail($creativeId);
        
        $suggestions = [
            'headline_variations' => $this->generateHeadlineVariations($creative->title),
            'body_variations' => $this->generateBodyVariations($creative->body),
            'cta_suggestions' => $this->getCTASuggestions($creative->call_to_action),
            'visual_tips' => $this->getVisualTips(),
        ];

        return $suggestions;
    }

    protected function generateHeadlineVariations($currentHeadline)
    {
        if (!$currentHeadline) {
            return ['أضف عنوان جذاب للإعلان'];
        }

        return [
            $currentHeadline . ' - عرض محدود',
            'اكتشف ' . $currentHeadline,
            $currentHeadline . ' | خصم خاص',
            'لماذا ' . $currentHeadline . '؟',
        ];
    }

    protected function generateBodyVariations($currentBody)
    {
        if (!$currentBody) {
            return ['أضف وصفاً تفصيلياً للمنتج أو الخدمة'];
        }

        return [
            'أضف شهادات العملاء',
            'أضف أرقام وإحصائيات',
            'أضف دعوة واضحة للإجراء',
            'اجعل النص أقصر وأكثر تأثيراً',
        ];
    }

    protected function getCTASuggestions($currentCTA)
    {
        $ctas = [
            'SHOP_NOW' => 'تسوق الآن',
            'LEARN_MORE' => 'اعرف المزيد',
            'SIGN_UP' => 'اشترك الآن',
            'BOOK_NOW' => 'احجز الآن',
            'GET_OFFER' => 'احصل على العرض',
        ];

        return array_diff_key($ctas, [$currentCTA => $ctas[$currentCTA] ?? '']);
    }

    protected function getVisualTips()
    {
        return [
            'استخدم صور عالية الجودة',
            'أضف نص على الصورة (أقل من 20%)',
            'جرب ألوان متباينة وجذابة',
            'استخدم وجوه أشخاص حقيقيين',
            'اختبر فيديوهات قصيرة (15-30 ثانية)',
        ];
    }

    public function compareCreatives($creativeIds)
    {
        $creatives = MetaAdCreative::whereIn('id', $creativeIds)
            ->with(['fatigueData' => function ($q) {
                $q->where('date', '>=', now()->subDays(7))->orderByDesc('date');
            }])
            ->get();

        return $creatives->map(function ($creative) {
            $latestFatigue = $creative->fatigueData->first();
            
            return [
                'id' => $creative->id,
                'name' => $creative->name,
                'title' => $creative->title,
                'status' => $creative->status,
                'fatigue_level' => $latestFatigue?->fatigue_level ?? 'unknown',
                'fatigue_score' => $latestFatigue?->fatigue_score ?? 0,
                'ctr' => $latestFatigue?->ctr ?? 0,
                'frequency' => $latestFatigue?->frequency ?? 0,
            ];
        });
    }
}
