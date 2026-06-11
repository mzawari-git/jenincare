<?php

namespace App\Services\Meta;

class AdPlacementRecommendationService
{
    const PLACEMENTS = [
        'feed' => [
            'name' => 'فيسبوك Feed',
            'best_for' => ['OUTCOME_AWARENESS', 'OUTCOME_TRAFFIC', 'OUTCOME_ENGAGEMENT', 'OUTCOME_SALES'],
            'cost_factor' => 'متوسط',
            'reach' => 'واسع',
            'engagement' => 'جيد',
            'pros' => ['وصول واسع', 'مناسب لجميع الأهداف', 'تكلفة متوسطة'],
            'cons' => ['منافسة عالية', 'قد لا يظهر دائماً'],
        ],
        'story' => [
            'name' => 'فيسبوك Stories',
            'best_for' => ['OUTCOME_AWARENESS', 'OUTCOME_TRAFFIC', 'OUTCOME_ENGAGEMENT'],
            'cost_factor' => 'منخفض',
            'reach' => 'متوسط',
            'engagement' => 'ممتاز',
            'pros' => ['تكلفة منخفضة', 'تفاعل عالي', 'تجربة غامرة'],
            'cons' => ['مساحة إعلانية محدودة', 'ينتهي سريعاً'],
        ],
        'instagram_feed' => [
            'name' => 'Instagram Feed',
            'best_for' => ['OUTCOME_AWARENESS', 'OUTCOME_ENGAGEMENT', 'OUTCOME_SALES', 'OUTCOME_LEADS'],
            'cost_factor' => 'مرتفع',
            'reach' => 'واسع',
            'engagement' => 'ممتاز',
            'pros' => ['جمهور متفاعل', 'مناسب للعلامات التجارية', 'نسبة تفاعل عالية'],
            'cons' => ['تكلفة أعلى', 'يتطلب محتوى بصري جذاب'],
        ],
        'instagram_story' => [
            'name' => 'Instagram Stories',
            'best_for' => ['OUTCOME_AWARENESS', 'OUTCOME_TRAFFIC', 'OUTCOME_ENGAGEMENT'],
            'cost_factor' => 'منخفض',
            'reach' => 'متوسط',
            'engagement' => 'ممتاز',
            'pros' => ['تكلفة منخفضة', 'شعبية عالية', 'محتوى إبداعي'],
            'cons' => ['مدة عرض قصيرة', 'حجم إعلان عمودي'],
        ],
        'marketplace' => [
            'name' => 'Marketplace',
            'best_for' => ['OUTCOME_SALES', 'OUTCOME_TRAFFIC'],
            'cost_factor' => 'منخفض',
            'reach' => 'متوسط',
            'engagement' => 'جيد',
            'pros' => ['نية شراء عالية', 'تكلفة منخفضة', 'مستهدف'],
            'cons' => ['متاح فقط في فيسبوك', 'مجال محدود'],
        ],
        'video_feeds' => [
            'name' => 'Video Feeds',
            'best_for' => ['OUTCOME_AWARENESS', 'OUTCOME_ENGAGEMENT', 'OUTCOME_SALES'],
            'cost_factor' => 'متوسط',
            'reach' => 'واسع',
            'engagement' => 'جيد جداً',
            'pros' => ['محتوى فيديو جذاب', 'مشاهدات عالية'],
            'cons' => ['يتطلب فيديو احترافي', 'قد يتجاوزه المستخدم'],
        ],
        'search' => [
            'name' => 'نتائج البحث',
            'best_for' => ['OUTCOME_TRAFFIC', 'OUTCOME_SALES', 'OUTCOME_LEADS'],
            'cost_factor' => 'منخفض',
            'reach' => 'مستهدف',
            'engagement' => 'ممتاز',
            'pros' => ['استهداف عالي الدقة', 'نية بحث عالية'],
            'cons' => ['حجم جمهور محدود', 'حيز إعلاني صغير'],
        ],
        'messenger_inbox' => [
            'name' => 'Messenger',
            'best_for' => ['OUTCOME_ENGAGEMENT', 'OUTCOME_LEADS'],
            'cost_factor' => 'منخفض',
            'reach' => 'متوسط',
            'engagement' => 'ممتاز',
            'pros' => ['تفاعل شخصي', 'تكلفة منخفضة'],
            'cons' => ['مساحة محدودة', 'يتطلب محتوى مختصر'],
        ],
    ];

    public function getRecommendationsForObjective(string $objective): array
    {
        $recommended = [];
        foreach (self::PLACEMENTS as $key => $placement) {
            if (in_array($objective, $placement['best_for'])) {
                $recommended[$key] = $placement;
            }
        }
        return $recommended;
    }

    public function getObjectivePlacementMatrix(): array
    {
        $objectives = [
            'OUTCOME_AWARENESS' => 'الوعي',
            'OUTCOME_TRAFFIC' => 'الزيارات',
            'OUTCOME_ENGAGEMENT' => 'التفاعل',
            'OUTCOME_LEADS' => 'العملاء المحتملين',
            'OUTCOME_SALES' => 'المبيعات',
            'OUTCOME_APP_PROMOTION' => 'ترقية التطبيق',
        ];

        $matrix = [];
        foreach ($objectives as $key => $name) {
            $placements = $this->getRecommendationsForObjective($key);
            $top = array_slice(array_keys($placements), 0, 3);
            $matrix[] = [
                'objective' => $key,
                'objective_name' => $name,
                'recommended_placements' => $top,
                'placement_details' => array_map(fn($p) => self::PLACEMENTS[$p] ?? null, $top),
            ];
        }

        return $matrix;
    }

    public function getCostComparison(): array
    {
        $comparison = [];
        foreach (self::PLACEMENTS as $key => $placement) {
            $comparison[$key] = [
                'name' => $placement['name'],
                'cost_factor' => $placement['cost_factor'],
                'reach' => $placement['reach'],
                'engagement' => $placement['engagement'],
            ];
        }
        return $comparison;
    }

    public function getAllPlacements(): array
    {
        return self::PLACEMENTS;
    }

    public function getBestPlacementForIndustry(string $industry): array
    {
        return match ($industry) {
            'beauty' => [
                'primary' => ['instagram_feed', 'instagram_story'],
                'secondary' => ['feed', 'story'],
                'reason' => 'صناعة التجميل تستفيد كثيراً من المحتوى البصري على إنستغرام',
            ],
            'salon' => [
                'primary' => ['instagram_feed', 'feed'],
                'secondary' => ['story', 'marketplace'],
                'reason' => 'الصالونات تحتاج لعرض أعمالها عبر الصور والفيديو',
            ],
            'medical' => [
                'primary' => ['feed', 'search'],
                'secondary' => ['messenger_inbox', 'video_feeds'],
                'reason' => 'الخدمات الطبية تحتاج لبناء ثقة عبر محتوى تعليمي',
            ],
            'education' => [
                'primary' => ['feed', 'video_feeds'],
                'secondary' => ['instagram_feed', 'messenger_inbox'],
                'reason' => 'المحتوى التعليمي يناسب الفيديو والمنشورات الطويلة',
            ],
            default => [
                'primary' => ['feed', 'instagram_feed'],
                'secondary' => ['story', 'search'],
                'reason' => 'مزيج متوازن من الوصول والتفاعل',
            ],
        };
    }
}
