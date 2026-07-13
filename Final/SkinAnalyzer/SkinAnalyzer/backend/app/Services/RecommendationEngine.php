<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SkinAnalysis;
use Illuminate\Support\Collection;

class RecommendationEngine
{
    private const DEFECT_CATEGORY_MAP = [
        'acne' => ['cleanser', 'treatment', 'toner'],
        'pigmentation' => ['serum', 'sunscreen', 'treatment'],
        'dark_circles' => ['eye_care', 'treatment'],
        'dryness' => ['moisturizer', 'mask', 'serum'],
        'oiliness' => ['cleanser', 'toner', 'moisturizer'],
        'pores' => ['toner', 'cleanser', 'exfoliator', 'mask'],
        'wrinkles' => ['treatment', 'serum', 'moisturizer'],
        'redness' => ['treatment', 'moisturizer', 'serum'],
        'texture' => ['exfoliator', 'serum', 'treatment'],
        'elasticity' => ['treatment', 'serum', 'supplement'],
    ];

    private const DEFECT_ARABIC_LABELS = [
        'acne' => 'حب الشباب',
        'pigmentation' => 'التصبغات',
        'dark_circles' => 'الهالات السوداء',
        'dryness' => 'الجفاف',
        'oiliness' => 'الدهون الزائدة',
        'pores' => 'المسام الواسعة',
        'wrinkles' => 'التجاعيد',
        'redness' => 'احمرار البشرة',
        'texture' => 'ملمس البشرة غير المنتظم',
        'elasticity' => 'ضعف مرونة البشرة',
    ];

    public function analyzeScanDefects(SkinAnalysis $scan): array
    {
        $defects = [];

        if ($scan->radar_metrics) {
            $metrics = is_array($scan->radar_metrics)
                ? $scan->radar_metrics
                : $scan->radar_metrics->toArray();

            if (($metrics['hydration'] ?? 100) < 50) {
                $defects[] = 'dryness';
            }

            if (($metrics['sebum'] ?? 0) > 60) {
                $defects[] = 'oiliness';
            }

            if (($metrics['pigmentation'] ?? 0) > 50) {
                $defects[] = 'pigmentation';
            }

            if (($metrics['pores'] ?? 0) > 55) {
                $defects[] = 'pores';
            }

            if (($metrics['elasticity'] ?? 100) < 55) {
                $defects[] = 'elasticity';
            }
        }

        if ($scan->heatmap_coordinates) {
            $heatmap = is_array($scan->heatmap_coordinates)
                ? $scan->heatmap_coordinates
                : $scan->heatmap_coordinates->toArray();

            foreach ($heatmap as $region) {
                $defectType = $region['type'] ?? $region['defect_type'] ?? null;

                if ($defectType && ! in_array($defectType, $defects)) {
                    $defects[] = $defectType;
                }
            }
        }

        if ($scan->raw_vendor_response) {
            $raw = is_array($scan->raw_vendor_response)
                ? $scan->raw_vendor_response
                : $scan->raw_vendor_response->toArray();

            $detectedDefects = $raw['detected_issues']
                ?? $raw['defects']
                ?? $raw['skin_concerns']
                ?? $raw['conditions']
                ?? [];

            foreach ($detectedDefects as $defect) {
                $normalized = $this->normalizeDefectName(
                    is_string($defect) ? $defect : ($defect['name'] ?? $defect['type'] ?? '')
                );

                if ($normalized && ! in_array($normalized, $defects)) {
                    $defects[] = $normalized;
                }
            }
        }

        $defects = array_unique($defects);

        if (empty($defects)) {
            $defects = ['dryness', 'oiliness', 'pores'];
        }

        return array_values($defects);
    }

    public function recommendProducts(array $defects, int $limit = 2): Collection
    {
        $recommendations = collect();

        foreach ($defects as $defectType) {
            $categories = self::DEFECT_CATEGORY_MAP[$defectType]
                ?? ['treatment', 'serum', 'moisturizer'];

            $products = Product::available()
                ->whereIn('category', $categories)
                ->inRandomOrder()
                ->take($limit)
                ->get();

            foreach ($products as $product) {
                if (! $recommendations->contains('id', $product->id)) {
                    $product->setAttribute('_matched_defect', $defectType);
                    $product->setAttribute(
                        '_matching_reason',
                        $this->generateMatchingReason($product, $defectType)
                    );
                    $recommendations->push($product);
                }
            }
        }

        return $recommendations->values();
    }

    public function generateMatchingReason(Product $product, string $defectType): string
    {
        $defectLabel = self::DEFECT_ARABIC_LABELS[$defectType] ?? $defectType;
        $productName = $product->name_ar ?: $product->name;

        $reasonTemplates = [
            'acne' => fn ($p) => "{$p} — مناسب للبشرة المعرضة لحب الشباب، يساعد في تنظيف المسام وتقليل الالتهابات",
            'pigmentation' => fn ($p) => "{$p} — يساعد في توحيد لون البشرة وتقليل التصبغات بفعالية",
            'dark_circles' => fn ($p) => "{$p} — تركيبته مخصصة لمنطقة محيط العين لتقليل الهالات السوداء",
            'dryness' => fn ($p) => "{$p} — يوفر ترطيباً عميقاً للبشرة الجافة ويعيد توازن الرطوبة",
            'oiliness' => fn ($p) => "{$p} — يتحكم في إفراز الدهون الزائدة ويمنح البشرة مظهراً مطفيًا",
            'pores' => fn ($p) => "{$p} — يساعد في تضييق المسام الواسعة وتنظيفها بعمق",
            'wrinkles' => fn ($p) => "{$p} — غني بمضادات الشيخوخة لتقليل التجاعيد والخطوط الدقيقة",
            'redness' => fn ($p) => "{$p} — يهدئ البشرة ويقلل الاحمرار والتهيج بتركيبة لطيفة",
            'texture' => fn ($p) => "{$p} — يعمل على تحسين ملمس البشرة وتنعيمها بشكل ملحوظ",
            'elasticity' => fn ($p) => "{$p} — يعزز إنتاج الكولاجين ويحسن مرونة البشرة",
        ];

        if (isset($reasonTemplates[$defectType])) {
            return $reasonTemplates[$defectType]($productName);
        }

        return "{$productName} — منتج موصى به للعناية بـ {$defectLabel}";
    }

    public function getDefectsArabicLabels(): array
    {
        return self::DEFECT_ARABIC_LABELS;
    }

    public function getDefectCategories(): array
    {
        return self::DEFECT_CATEGORY_MAP;
    }

    private function normalizeDefectName(string $rawName): ?string
    {
        $mapping = [
            'acne' => 'acne',
            'pimples' => 'acne',
            'acne vulgaris' => 'acne',
            'breakouts' => 'acne',
            'حب الشباب' => 'acne',
            'بثور' => 'acne',
            'hyperpigmentation' => 'pigmentation',
            'dark spots' => 'pigmentation',
            'uneven skin tone' => 'pigmentation',
            'melasma' => 'pigmentation',
            'تصبغات' => 'pigmentation',
            'بقع داكنة' => 'pigmentation',
            'dark circles' => 'dark_circles',
            'under eye darkness' => 'dark_circles',
            'periorbital hyperpigmentation' => 'dark_circles',
            'هالات سوداء' => 'dark_circles',
            'dry skin' => 'dryness',
            'dehydrated' => 'dryness',
            'flaky' => 'dryness',
            'xerosis' => 'dryness',
            'بشرة جافة' => 'dryness',
            'جفاف' => 'dryness',
            'oily skin' => 'oiliness',
            'excess sebum' => 'oiliness',
            'seborrhea' => 'oiliness',
            'بشرة دهنية' => 'oiliness',
            'دهون زائدة' => 'oiliness',
            'large pores' => 'pores',
            'enlarged pores' => 'pores',
            'visible pores' => 'pores',
            'مسام واسعة' => 'pores',
            'wrinkles' => 'wrinkles',
            'fine lines' => 'wrinkles',
            'crow\'s feet' => 'wrinkles',
            'rhytides' => 'wrinkles',
            'تجاعيد' => 'wrinkles',
            'خطوط دقيقة' => 'wrinkles',
            'redness' => 'redness',
            'erythema' => 'redness',
            'irritation' => 'redness',
            'inflammation' => 'redness',
            'احمرار' => 'redness',
            'تهيج' => 'redness',
            'rough texture' => 'texture',
            'uneven texture' => 'texture',
            'bumpy skin' => 'texture',
            'ملمس خشن' => 'texture',
            'elasticity loss' => 'elasticity',
            'sagging' => 'elasticity',
            'loss of firmness' => 'elasticity',
            'فقدان المرونة' => 'elasticity',
            'ترهل' => 'elasticity',
        ];

        $lower = mb_strtolower(trim($rawName));

        return $mapping[$lower] ?? null;
    }
}
