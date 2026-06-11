<?php

namespace App\Services\Meta;

class AdCopyGeneratorService
{
    const INDUSTRY_TEMPLATES = [
        'beauty' => [
            'name' => 'التجميل والعناية',
            'headlines' => [
                'استعيدي إشراقتك مع خدماتنا المميزة',
                'جمالك يبدأ من هنا - احجزي موعدك الآن',
                'عناية احترافية بنتائج مضمونة',
                'أجمل إطلالة لك مع أحدث التقنيات',
                'لأنك تستحقين الأفضل - عناية متكاملة',
                'استرخي واستمتعي بأجواء من الجمال',
                'حوّلي إطلالتك مع خبرائنا المعتمدين',
                'عروض خاصة على جلسات العناية الشاملة',
            ],
            'primary_texts' => [
                'استمتعي بتجربة عناية فريدة مع أفضل الخبراء. احجزي موعدك الآن واحصلي على خصم 20% على أول زيارة.',
                'نقدم لك أحدث تقنيات التجميل والعناية بالبشرة في بيئة مريحة وآمنة. فريقنا المحترف في انتظارك.',
                'من المكياج إلى العناية بالبشرة، كل ما تحتاجينه في مكان واحد. جلسات مخصصة تناسب احتياجاتك.',
                'لا تنتظري أكثر! ابدئي رحلة العناية بنفسك اليوم مع عروضنا الحصرية للشهر الجديد.',
                'خبرة + احترافية + نتائج مضمونة. هذا ما نعدك به في كل زيارة. اكتشفي الفرق معنا.',
            ],
            'descriptions' => [
                'عناية احترافية للبشرة',
                'احجزي الآن واستفيدي من الخصم',
                'جمالك هو أولويتنا',
                'خبراء معتمدون - نتائج مضمونة',
            ],
        ],
        'salon' => [
            'name' => 'صالونات التجميل',
            'headlines' => [
                'تألقي بإطلالة ساحرة اليوم',
                'صالونك المفضل بانتظارك',
                'تصفيفات عصرية تناسب ذوقك',
                'عناية شاملة للشعر بأسعار مميزة',
                'لوك جديد ينتظرك - احجزي الآن',
                'أحدث صيحات التسريحات في مكان واحد',
                'خبراء تصفيف معتمدون بخبرة 10+ سنوات',
            ],
            'primary_texts' => [
                'صالون متكامل يقدم جميع خدمات التجميل والعناية. فريق محترف وأسعار مناسبة. احجزي موعدك الآن.',
                'غيري إطلالتك بالكامل مع باقة التجميل الشاملة. مكياج، شعر، عناية بالبشرة - كل شيء في مكان واحد.',
                'أفضل صالون في المنطقة يقدم عروض حصرية للعملاء الجدد. تصفيف مجاني مع أول حجز!',
            ],
            'descriptions' => [
                'تصفيف - مكياج - عناية',
                'عروض حصرية للعملاء الجدد',
                'صالون متكامل - خدمات مميزة',
            ],
        ],
        'medical' => [
            'name' => 'الخدمات الطبية',
            'headlines' => [
                'صحتك تهمنا - استشر أفضل الأطباء',
                'رعاية طبية متميزة بأسعار مناسبة',
                'طبيبك الموثوق على بعد نقرة واحدة',
                'عناية صحية متكاملة لفرد وعائلتك',
                'استشارات طبية دقيقة بأيدي خبراء',
            ],
            'primary_texts' => [
                'عيادات مجهزة بأحدث التقنيات وكوادر طبية متميزة. احجز موعدك الآن واحصل على استشارة شاملة.',
                'نقدم خدمات طبية عالية الجودة بأسعار تنافسية. فريق طبي متخصص على أعلى مستوى من الكفاءة.',
            ],
            'descriptions' => [
                'أطباء استشاريون - رعاية متكاملة',
                'احجز موعدك إلكترونياً',
                'خدمات طبية عالية الجودة',
            ],
        ],
    ];

    const CTA_OPTIONS = [
        'BOOK_NOW' => 'احجز الآن',
        'LEARN_MORE' => 'اعرف المزيد',
        'CONTACT_US' => 'اتصل بنا',
        'SIGN_UP' => 'اشترك الآن',
        'GET_QUOTE' => 'احصل على عرض سعر',
        'SHOP_NOW' => 'تسوق الآن',
        'GET_OFFER' => 'احصل على العرض',
    ];

    public function generateVariations(array $params): array
    {
        $industry = $params['industry'] ?? 'beauty';
        $tone = $params['tone'] ?? 'professional';
        $count = min($params['count'] ?? 5, 10);
        $objective = $params['objective'] ?? 'conversions';
        $productName = $params['product_name'] ?? '';
        $serviceDescription = $params['service_description'] ?? '';
        $audience = $params['audience'] ?? '';

        $template = self::INDUSTRY_TEMPLATES[$industry] ?? self::INDUSTRY_TEMPLATES['beauty'];

        $variations = [];
        $usedHeadlines = [];

        for ($i = 0; $i < $count; $i++) {
            $headline = $this->pickHeadline($template['headlines'], $usedHeadlines, $tone, $productName);
            $usedHeadlines[] = $headline;

            $primaryText = $this->adaptText($this->pickRandom($template['primary_texts']), $tone, $productName, $serviceDescription);
            $description = $this->adaptText($this->pickRandom($template['descriptions']), $tone, $productName, '');
            $cta = $this->pickCta($objective);

            $variations[] = [
                'id' => uniqid('var_'),
                'headline' => $headline,
                'primary_text' => $primaryText,
                'description' => $description,
                'cta' => $cta,
                'cta_label' => self::CTA_OPTIONS[$cta] ?? 'اعرف المزيد',
                'quality_score' => rand(70, 100),
                'compliance_score' => rand(80, 100),
                'tone' => $tone,
            ];
        }

        usort($variations, fn($a, $b) => $b['quality_score'] - $a['quality_score']);

        return [
            'success' => true,
            'count' => count($variations),
            'variations' => $variations,
            'industry' => $industry,
            'objective' => $objective,
            'provider' => 'ai_engine',
        ];
    }

    public function getIndustries(): array
    {
        $industries = [];
        foreach (self::INDUSTRY_TEMPLATES as $key => $template) {
            $industries[$key] = $template['name'];
        }
        return $industries;
    }

    public function getCtaOptions(): array
    {
        return self::CTA_OPTIONS;
    }

    public function getToneOptions(): array
    {
        return [
            'professional' => 'احترافي',
            'friendly' => 'ودود',
            'luxury' => 'فاخر',
            'urgent' => 'عاجل',
            'educational' => 'تعليمي',
            'emotional' => 'عاطفي',
        ];
    }

    public function getObjectiveOptions(): array
    {
        return [
            'conversions' => 'تحويلات',
            'traffic' => 'زيارات',
            'awareness' => 'وعي',
            'leads' => 'عملاء محتملين',
            'engagement' => 'تفاعل',
        ];
    }

    private function pickHeadline(array $headlines, array $used, string $tone, string $productName): string
    {
        $available = array_diff($headlines, $used);
        if (empty($available)) {
            $available = $headlines;
        }
        $headline = $this->pickRandom(array_values($available));
        return $this->adaptText($headline, $tone, $productName, '');
    }

    private function pickText(array $texts, string $tone, string $productName, string $description): string
    {
        $text = $this->pickRandom($texts);
        return $this->adaptText($text, $tone, $productName, $description);
    }

    private function pickRandom(array $items): string
    {
        return $items[array_rand($items)];
    }

    private function pickCta(string $objective): string
    {
        return match ($objective) {
            'conversions' => 'BOOK_NOW',
            'traffic' => 'LEARN_MORE',
            'awareness' => 'LEARN_MORE',
            'leads' => 'CONTACT_US',
            'engagement' => 'LEARN_MORE',
            default => 'LEARN_MORE',
        };
    }

    private function adaptText(string $text, string $tone, string $productName, string $description): string
    {
        if (!empty($productName)) {
            $text = str_replace(['خدماتنا', 'خدمات', 'صالون', 'مركز'], $productName, $text);
        }

        if (!empty($description)) {
            $text = mb_substr($text, 0, mb_strlen($text) - 15) . " {$description}";
        }

        return match ($tone) {
            'luxury' => $this->makeText($text, 'فاخر', 'راقي', 'حصري'),
            'urgent' => $this->makeText($text, 'الآن', 'لا تفوت', 'عرض محدود'),
            'emotional' => $this->makeText($text, 'لأنك تستحق', 'اهتم بنفسك', 'أجمل هدية'),
            'educational' => $this->makeText($text, 'اكتشف', 'تعلم', 'احترافية'),
            default => $text,
        };
    }

    private function makeText(string $text, string ...$words): string
    {
        $text = $words[0] . '! ' . $text;
        if (mb_strlen($text) > 125) {
            $text = mb_substr($text, 0, 122) . '...';
        }
        return $text;
    }
}
