<?php

namespace App\Services\Meta;

class PreFlightComplianceService
{
    const RESTRICTED_WORDS = [
        'مجاني', 'Free', 'خصم', 'discount', 'عرض', 'offer',
        'ضمان', 'guarantee', 'أفضل', 'best', 'الأول', 'first',
        'الرقم واحد', 'number one', 'احجز الآن', 'اشتري الآن',
        'لوقت محدود', 'limited time', 'عاجل', 'urgent',
        'كنز', 'secret', 'سر', 'معجزة', 'miracle', 'علاج',
        'cure', 'خسارة', 'weight loss', 'تخسيس',
    ];

    const RESTRICTED_SYMBOLS = ['!!!', '???', '***', '$$$'];

    const MAX_TITLE_LENGTH = 40;
    const MAX_PRIMARY_TEXT_LENGTH = 125;
    const MAX_DESCRIPTION_LENGTH = 30;

    public function checkAdContent(array $content): array
    {
        $issues = [];
        $warnings = [];
        $passed = true;

        $title = $content['title'] ?? '';
        $body = $content['body'] ?? '';
        $description = $content['description'] ?? '';
        $linkUrl = $content['link_url'] ?? '';
        $imageText = $content['image_text'] ?? '';

        if (empty($title)) {
            $issues[] = ['field' => 'title', 'message' => 'العنوان مطلوب', 'severity' => 'error'];
            $passed = false;
        } else {
            if (mb_strlen($title) > self::MAX_TITLE_LENGTH) {
                $issues[] = ['field' => 'title', 'message' => "العنوان طويل جداً ({$title} حرف)، الحد الأقصى " . self::MAX_TITLE_LENGTH . ' حرف', 'severity' => 'error'];
                $passed = false;
            }

            foreach (self::RESTRICTED_SYMBOLS as $symbol) {
                if (mb_strpos($title, $symbol) !== false) {
                    $issues[] = ['field' => 'title', 'message' => "العنوان يحتوي على رمز محظور: {$symbol}", 'severity' => 'warning'];
                }
            }

            $titleWords = $this->findRestrictedWords($title);
            foreach ($titleWords as $word) {
                $warnings[] = ['field' => 'title', 'message' => "العنوان يحتوي على كلمة قد تسبب حظر الإعلان: {$word}", 'severity' => 'warning'];
            }
        }

        if (empty($body)) {
            $issues[] = ['field' => 'body', 'message' => 'النص الأساسي مطلوب', 'severity' => 'error'];
            $passed = false;
        } else {
            if (mb_strlen($body) > self::MAX_PRIMARY_TEXT_LENGTH) {
                $issues[] = ['field' => 'body', 'message' => "النص الأساسي طويل جداً ({$body} حرف)، الحد الأقصى " . self::MAX_PRIMARY_TEXT_LENGTH . ' حرف', 'severity' => 'error'];
                $passed = false;
            }

            $bodyWords = $this->findRestrictedWords($body);
            foreach ($bodyWords as $word) {
                $warnings[] = ['field' => 'body', 'message' => "النص يحتوي على كلمة قد تسبب حظر الإعلان: {$word}", 'severity' => 'warning'];
            }
        }

        if (!empty($description) && mb_strlen($description) > self::MAX_DESCRIPTION_LENGTH) {
            $issues[] = ['field' => 'description', 'message' => "الوصف طويل جداً ({$description} حرف)، الحد الأقصى " . self::MAX_DESCRIPTION_LENGTH . ' حرف', 'severity' => 'error'];
            $passed = false;
        }

        if (empty($linkUrl)) {
            $issues[] = ['field' => 'link_url', 'message' => 'رابط الوجهة مطلوب', 'severity' => 'error'];
            $passed = false;
        } elseif (!filter_var($linkUrl, FILTER_VALIDATE_URL)) {
            $issues[] = ['field' => 'link_url', 'message' => 'رابط الوجهة غير صالح', 'severity' => 'error'];
            $passed = false;
        }

        $uppercaseRatio = $this->getUppercaseRatio($title . ' ' . $body);
        if ($uppercaseRatio > 0.5) {
            $warnings[] = ['field' => 'general', 'message' => 'نسبة الأحرف الكبيرة مرتفعة جداً - قد يعتبر إعلاناً مزعجاً', 'severity' => 'warning'];
        }

        if ($imageText && mb_strlen($imageText) > 20) {
            $warnings[] = ['field' => 'image', 'message' => 'نسبة النص في الصورة قد تتجاوز حد 20% المسموح به من فيسبوك', 'severity' => 'warning'];
        }

        $finalIssues = array_merge($issues, $warnings);

        return [
            'passed' => $passed,
            'score' => $this->calculateScore(count($issues), count($warnings)),
            'issues' => $issues,
            'warnings' => $warnings,
            'all_issues' => $finalIssues,
            'has_errors' => !empty($issues),
            'has_warnings' => !empty($warnings),
            'summary' => $this->getSummary($passed, count($issues), count($warnings)),
            'checks' => [
                'title_length' => mb_strlen($title) <= self::MAX_TITLE_LENGTH,
                'body_length' => mb_strlen($body) <= self::MAX_PRIMARY_TEXT_LENGTH,
                'has_title' => !empty($title),
                'has_body' => !empty($body),
                'has_link' => !empty($linkUrl) && filter_var($linkUrl, FILTER_VALIDATE_URL),
                'no_restricted_words' => empty($this->findRestrictedWords($title . ' ' . $body)),
            ],
        ];
    }

    public function sanitizeContent(array $content): array
    {
        $sanitized = $content;

        foreach (['title', 'body', 'description'] as $field) {
            if (!empty($sanitized[$field])) {
                foreach (self::RESTRICTED_SYMBOLS as $symbol) {
                    $sanitized[$field] = str_replace($symbol, '', $sanitized[$field]);
                }
                $sanitized[$field] = trim($sanitized[$field]);
            }
        }

        if (!empty($sanitized['title']) && mb_strlen($sanitized['title']) > self::MAX_TITLE_LENGTH) {
            $sanitized['title'] = mb_substr($sanitized['title'], 0, self::MAX_TITLE_LENGTH - 3) . '...';
        }

        if (!empty($sanitized['body']) && mb_strlen($sanitized['body']) > self::MAX_PRIMARY_TEXT_LENGTH) {
            $sanitized['body'] = mb_substr($sanitized['body'], 0, self::MAX_PRIMARY_TEXT_LENGTH - 3) . '...';
        }

        return $sanitized;
    }

    public function getComplianceRules(): array
    {
        return [
            'text_rules' => [
                ['rule' => 'الحد الأقصى لطول العنوان: 40 حرفاً', 'importance' => 'عالية'],
                ['rule' => 'الحد الأقصى للنص الأساسي: 125 حرفاً', 'importance' => 'عالية'],
                ['rule' => 'الحد الأقصى للوصف: 30 حرفاً', 'importance' => 'عالية'],
                ['rule' => 'تجنب علامات الترقيم المتكررة (!!!, ???)', 'importance' => 'متوسطة'],
                ['rule' => 'نسبة الأحرف الكبيرة يجب أن لا تتجاوز 50%', 'importance' => 'متوسطة'],
            ],
            'image_rules' => [
                ['rule' => 'نسبة النص في الصورة أقل من 20%', 'importance' => 'عالية'],
                ['rule' => 'الصورة بدقة عالية (1200x628 للإعلانات العادية)', 'importance' => 'متوسطة'],
                ['rule' => 'لا تستخدم صوراً محمية بحقوق نشر', 'importance' => 'عالية'],
            ],
            'targeting_rules' => [
                ['rule' => 'لا تستهدف بناءً على العرق أو الدين أو التوجه السياسي', 'importance' => 'عالية'],
                ['rule' => 'للعقارات والتوظيف والائتمان، استخدم فئة الإعلانات الخاصة', 'importance' => 'عالية'],
                ['rule' => 'احترم سياسة الخصوصية وعدم التمييز', 'importance' => 'عالية'],
            ],
        ];
    }

    private function findRestrictedWords(string $text): array
    {
        $found = [];
        foreach (self::RESTRICTED_WORDS as $word) {
            if (mb_stripos($text, $word) !== false) {
                $found[] = $word;
            }
        }
        return $found;
    }

    private function getUppercaseRatio(string $text): float
    {
        if (empty($text)) {
            return 0;
        }
        $letters = preg_replace('/[^A-Za-z]/', '', $text);
        $uppercase = preg_replace('/[^A-Z]/', '', $text);
        if (strlen($letters) === 0) {
            return 0;
        }
        return strlen($uppercase) / strlen($letters);
    }

    private function calculateScore(int $errors, int $warnings): int
    {
        $score = 100;
        $score -= $errors * 20;
        $score -= $warnings * 10;
        return max(0, $score);
    }

    private function getSummary(bool $passed, int $errors, int $warnings): array
    {
        if ($passed && $errors === 0 && $warnings === 0) {
            return [
                'status' => 'perfect',
                'label' => 'ممتاز',
                'color' => '#10B981',
                'message' => 'الإعلان مطابق لجميع معايير فيسبوك',
            ];
        }
        if ($passed && $warnings > 0) {
            return [
                'status' => 'warning',
                'label' => 'تحذيرات',
                'color' => '#F59E0B',
                'message' => 'يوجد بعض التحذيرات لكن الإعلان قابل للنشر',
            ];
        }
        return [
            'status' => 'error',
            'label' => 'أخطاء',
            'color' => '#EF4444',
            'message' => 'يوجد أخطاء يجب تصحيحها قبل النشر',
        ];
    }
}
