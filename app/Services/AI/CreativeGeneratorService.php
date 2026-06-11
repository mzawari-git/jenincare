<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CreativeGeneratorService
{
    private const META_HEADLINE_MAX = 40;
    private const META_PRIMARY_TEXT_MAX = 125;
    private const META_DESCRIPTION_MAX = 30;
    private const GOOGLE_HEADLINE_MAX = 30;
    private const GOOGLE_DESCRIPTION_MAX = 90;

    private const TONES = [
        'professional' => 'احترافية وموثوقة',
        'friendly' => 'ودية وم亲切ة',
        'luxury' => 'فاخرة ورفيعة',
        'urgent' => 'عاجلة وتشعر بالندرة',
        'educational' => 'تعليمية وświadقية',
        'emotional' => 'عاطفية ومُلهمة',
    ];

    private array $providers;

    public function __construct()
    {
        $this->providers = [
            'openai' => app(OpenAIProvider::class),
            'claude' => app(ClaudeProvider::class),
            'llama' => app(LlamaProvider::class),
        ];
    }

    public function generateVariations(array $params): array
    {
        $platform = $params['platform'] ?? 'meta';
        $objective = $params['objective'] ?? 'conversions';
        $productName = $params['product_name'] ?? 'خدماتنا';
        $tone = $params['tone'] ?? 'professional';
        $numVariations = min($params['num_variations'] ?? 5, 10);
        $audienceDescription = $params['audience'] ?? 'نساء في فلسطين المهتمات بالعناية بالجمال';
        $serviceDescription = $params['service_description'] ?? '';

        $prompt = $this->buildPrompt($platform, $objective, $productName, $tone, $numVariations, $audienceDescription, $serviceDescription);

        $response = $this->callLLM($prompt);

        if (!$response) {
            return ['success' => false, 'message' => 'فشل توليد الإعلانات، يرجى المحاولة مرة أخرى'];
        }

        $variations = $this->parseVariations($response, $platform);

        foreach ($variations as &$variation) {
            $variation['compliance_score'] = $this->checkCompliance($variation);
            $variation['platform'] = $platform;
        }

        return [
            'success' => true,
            'variations' => $variations,
            'provider' => $this->getActiveProvider(),
            'prompt_tokens' => $this->estimateTokens($prompt),
            'completion_tokens' => $this->estimateTokens($response),
        ];
    }

    private function buildPrompt(string $platform, string $objective, string $productName, string $tone, int $count, string $audience, string $serviceDesc): string
    {
        $platformSpecs = $platform === 'google'
            ? "Google Ads - كل عنوان 30 حرف كحد أقصى، كل وصف 90 حرف كحد أقصى"
            : "Meta/Facebook Ads - العنوان الرئيسي 40 حرف، النص الأساسي 125 حرف، الوصف 30 حرف";

        $toneAr = self::TONES[$tone] ?? self::TONES['professional'];

        $serviceInfo = $serviceDesc ? "\nوصف الخدمة: {$serviceDesc}" : '';

        return <<<PROMPT
أنت كاتب إعلانات محترف متخصص في التسويق الرقمي لخدمات العناية بالجمال والبشرة في فلسطين.

{$platformSpecs}

المطلوب: إنشاء {$count} نسخ إعلانية مختلفة بトン " toneAr"

- المنتج/الخدمة: {$productName}
- الجمهور المستهدف: {$audience}
- هدف الحملة: {$objective}{$serviceInfo}

أعد الناتج بصيغة JSON فقط (بدون markdown أو code blocks):
{
  "variations": [
    {
      "headline": "العنوان الرئيسي",
      "primary_text": "النص الأساسي الإعلاني",
      "description": "الوصف الإضافي",
      "cta": "النص الدعوة لاتخاذ إجراء"
    }
  ]
}

قواعد مهمة:
1. استخدم عربية فصحى سلسة مع لمسة عاطفية
2. ركّز على الفوائد وليس الميزات فقط
3. اذكر عروض الأسعار أو الخصومات إن وُجدت
4. استخدم كلمات تحفيزية مثل: احجزي الآن، اكتشفي، تجربي
5. تجنب الكلمات المحظورة: علاج، ليزر، فيلر، بوتوكس
6. خصص لكل منصة (Meta = عاطفي وvisual, Google = مباشرو_value proposition)
PROMPT;
    }

    private function callLLM(string $prompt): ?string
    {
        foreach (config('ai.fallback_chain', ['openai', 'claude', 'llama']) as $providerName) {
            $provider = $this->providers[$providerName] ?? null;
            if (!$provider || !$provider->isAvailable()) continue;

            try {
                $response = $provider->sanitize($prompt);
                if ($response && strlen($response) > 50) {
                    return $response;
                }
            } catch (\Exception $e) {
                Log::warning("Creative generation failed with {$providerName}", ['error' => $e->getMessage()]);
                continue;
            }
        }

        return null;
    }

    private function parseVariations(string $response, string $platform): array
    {
        $json = json_decode($response, true);

        if (!$json || !isset($json['variations'])) {
            $jsonMatch = preg_match('/\{[\s\S]*\}/', $response, $matches);
            if ($jsonMatch) {
                $json = json_decode($matches[0], true);
            }
        }

        $variations = $json['variations'] ?? [];

        $maxHeadline = $platform === 'google' ? self::GOOGLE_HEADLINE_MAX : self::META_HEADLINE_MAX;
        $maxPrimaryText = $platform === 'google' ? self::GOOGLE_DESCRIPTION_MAX : self::META_PRIMARY_TEXT_MAX;
        $maxDescription = $platform === 'google' ? self::GOOGLE_DESCRIPTION_MAX : self::META_DESCRIPTION_MAX;

        foreach ($variations as &$v) {
            $v['headline'] = mb_substr($v['headline'] ?? '', 0, $maxHeadline);
            $v['primary_text'] = mb_substr($v['primary_text'] ?? '', 0, $maxPrimaryText);
            $v['description'] = mb_substr($v['description'] ?? '', 0, $maxDescription);
            $v['cta'] = $v['cta'] ?? 'احجزي الآن';
            $v['quality_score'] = $this->estimateQuality($v);
        }

        return $variations;
    }

    private function checkCompliance(array $variation): int
    {
        $score = 100;
        $text = strtolower(implode(' ', [
            $variation['headline'] ?? '',
            $variation['primary_text'] ?? '',
            $variation['description'] ?? '',
        ]));

        $bannedWords = [
            'علاج', 'ليزر', 'فيلر', 'بوتوكس', 'تقشير', 'ميزوثيرابي',
            'بلازما', 'حقن', 'جراحة', 'شد', 'تنحيف', 'تجميل',
        ];

        foreach ($bannedWords as $word) {
            if (str_contains($text, $word)) {
                $score -= 20;
            }
        }

        $spamWords = ['100%', 'مجاناً', 'ضمان كامل', 'فرصتك الأخيرة'];
        foreach ($spamWords as $word) {
            if (str_contains($text, $word)) {
                $score -= 10;
            }
        }

        return max(0, $score);
    }

    private function estimateQuality(array $variation): int
    {
        $score = 50;

        $headline = $variation['headline'] ?? '';
        $primaryText = $variation['primary_text'] ?? '';

        if (mb_strlen($headline) > 5 && mb_strlen($headline) < 35) $score += 10;
        if (mb_strlen($primaryText) > 20) $score += 10;

        $emotionalWords = ['اكتشفي', 'احجزي', 'جربي', 'انضمي', 'استمتعي', 'حاولي'];
        foreach ($emotionalWords as $word) {
            if (str_contains($primaryText, $word)) {
                $score += 5;
            }
        }

        $emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F900}-\x{1F9FF}]/u';
        if (preg_match($emojiPattern, $primaryText)) $score += 5;

        return min(100, $score);
    }

    private function getActiveProvider(): string
    {
        foreach (config('ai.fallback_chain', ['openai', 'claude', 'llama']) as $name) {
            $provider = $this->providers[$name] ?? null;
            if ($provider && $provider->isAvailable()) {
                return $provider->getName();
            }
        }

        return 'unknown';
    }

    private function estimateTokens(string $text): int
    {
        return (int) ceil(mb_strlen($text) / 4);
    }
}
