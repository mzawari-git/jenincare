<?php

namespace App\Services\Compliance;

use Illuminate\Support\Facades\Log;

class PreFlightChecker
{
    private const PLATFORM_RULES = [
        'meta' => [
            'max_headline' => 40,
            'max_primary_text' => 125,
            'max_description' => 30,
            'prohibited_words' => [
                'علاج', 'ليزر', 'فيلر', 'بوتوكس', 'تقشير', 'ميزوثيرابي',
                'بلازما', 'حقن', 'جراحة', 'شد', 'تنحيف', 'تجميل',
                ' Procedures', 'Surgery', 'Botox', 'Filler', 'Laser Treatment',
            ],
            'banned_content' => ['before_after', 'medical_claims', 'body_shaming'],
            'required_disclaimers' => true,
        ],
        'google' => [
            'max_headline' => 30,
            'max_primary_text' => 90,
            'max_description' => 90,
            'prohibited_words' => [
                'علاج', 'ليزر', 'فيلر', 'بوتوكس', 'جراحة', 'شد',
                'Surgery', 'Botox', 'Filler', 'Medical', 'Clinic',
            ],
            'banned_content' => ['misleading_claims', 'exaggerated_results'],
            'required_disclaimers' => false,
        ],
    ];

    public function checkCreative(array $creative, string $platform): array
    {
        $rules = self::PLATFORM_RULES[$platform] ?? self::PLATFORM_RULES['meta'];
        $issues = [];
        $warnings = [];

        $headline = $creative['headline'] ?? '';
        $primaryText = $creative['primary_text'] ?? '';
        $description = $creative['description'] ?? '';

        if (mb_strlen($headline) > $rules['max_headline']) {
            $issues[] = [
                'field' => 'headline',
                'message' => "العنوان يتجاوز {$rules['max_headline']} حرف (الحالي: " . mb_strlen($headline) . ")",
                'severity' => 'error',
            ];
        }

        if (mb_strlen($primaryText) > $rules['max_primary_text']) {
            $issues[] = [
                'field' => 'primary_text',
                'message' => "النص الأساسي يتجاوز {$rules['max_primary_text']} حرف",
                'severity' => 'error',
            ];
        }

        if (mb_strlen($description) > $rules['max_description']) {
            $issues[] = [
                'field' => 'description',
                'message' => "الوصف يتجاوز {$rules['max_description']} حرف",
                'severity' => 'error',
            ];
        }

        $allText = strtolower("{$headline} {$primaryText} {$description}");

        foreach ($rules['prohibited_words'] as $word) {
            if (str_contains($allText, strtolower($word))) {
                $issues[] = [
                    'field' => 'content',
                    'message' => "يحتوي على كلمة محظورة: \"{$word}\"",
                    'severity' => 'error',
                    'suggestion' => $this->getReplacement($word),
                ];
            }
        }

        if (preg_match('/(\d{3,}%|100%|ضمان|مجاناً)/u', $allText)) {
            $warnings[] = [
                'field' => 'content',
                'message' => 'قد يحتوي على ادعاءات م夸大 أو ضمانات',
                'severity' => 'warning',
            ];
        }

        if (!preg_match('/(احجز|اكتشفي|جربي|تواصل|سجل|اشترك|command_verb)/u', $allText)) {
            $warnings[] = [
                'field' => 'cta',
                'message' => 'يُنصح بإضافة دعوة لاتخاذ إجراء واضحة',
                'severity' => 'info',
            ];
        }

        $score = 100;
        foreach ($issues as $issue) {
            $score -= $issue['severity'] === 'error' ? 25 : 10;
        }
        foreach ($warnings as $warning) {
            $score -= 5;
        }

        return [
            'score' => max(0, $score),
            'passed' => empty($issues),
            'issues' => $issues,
            'warnings' => $warnings,
            'platform' => $platform,
            'compliant' => empty($issues),
        ];
    }

    private function getReplacement(string $word): string
    {
        $replacements = [
            'علاج' => 'عناية',
            'ليزر' => 'أجهزة متقدمة',
            'فيلر' => 'خدمات التجميل',
            'بوتوكس' => 'تقنيات التجديد',
            'تقشير' => 'تقشير لطيف',
            'mezotherapy' => 'عناية مغذية',
            'PRP' => 'علاجات متقدمة',
            'Botox' => 'تقنيات التجديد',
            'Filler' => 'خدمات التجميل',
            'Laser' => 'أجهزة متقدمة',
        ];

        return $replacements[$word] ?? '██';
    }

    public function batchCheck(array $creatives, string $platform): array
    {
        $results = [];
        foreach ($creatives as $i => $creative) {
            $results[$i] = $this->checkCreative($creative, $platform);
        }

        $passed = count(array_filter($results, fn($r) => $r['passed']));
        $total = count($results);

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $total - $passed,
            'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0,
            'results' => $results,
        ];
    }
}
