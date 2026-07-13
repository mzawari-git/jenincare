<?php

namespace App\Services\AI\Providers;

use App\Enums\EngineType;
use App\Services\AI\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HuggingFaceProvider implements AIProviderInterface
{
    private const HF_INFERENCE_URL = 'https://api-inference.huggingface.co/models';

    public function __construct(
        private readonly array $config,
    ) {}

    public function analyze(array $imageData): array
    {
        $model = $this->config['config']['model'] ?? 'HotJellyBean/skin-disease-classifier';
        $endpoint = $this->config['api_credentials']['endpoint_url']
            ?? self::HF_INFERENCE_URL . '/' . $model;

        try {
            $imageBytes = $imageData['contents'] ?? file_get_contents($imageData['path']);

            $response = Http::timeout($this->getTimeout())
                ->withHeaders([
                    'Authorization' => 'Bearer ' . ($this->config['api_credentials']['api_key'] ?? ''),
                    'Content-Type' => 'application/octet-stream',
                ])
                ->withBody($imageBytes, 'application/octet-stream')
                ->post($endpoint);

            if ($response->failed()) {
                throw new \RuntimeException("HuggingFace API returned status {$response->status()}: {$response->body()}");
            }

            $result = $response->json();

            return $this->normalizeResponse($result);
        } catch (\Throwable $e) {
            Log::error('HuggingFaceProvider analysis failed.', [
                'error' => $e->getMessage(),
                'model' => $model,
            ]);
            throw $e;
        }
    }

    private function normalizeResponse(array $raw): array
    {
        $labels = [
            'Actinic Keratosis',
            'Basal Cell Carcinoma',
            'Benign Keratosis',
            'Dermatofibroma',
            'Melanocytic Nevus',
            'Melanoma',
            'Squamous Cell Carcinoma',
            'Vascular Lesion',
        ];

        $top = $raw[0] ?? [];
        $topLabel = $top['label'] ?? 'Unknown';
        $topScore = $top['score'] ?? 0;

        $healthScore = max(0, min(100, (int) round((1 - $topScore) * 100)));

        $defects = [];
        $severityMap = ['Low', 'Moderate', 'High'];
        foreach ($raw as $i => $item) {
            $score = $item['score'] ?? 0;
            if ($score > 0.05) {
                $severityIdx = $score > 0.7 ? 2 : ($score > 0.3 ? 1 : 0);
                $defects[] = [
                    'type' => strtolower(str_replace(' ', '_', $item['label'] ?? '')),
                    'label' => $item['label'] ?? '',
                    'confidence' => $score,
                    'severity' => $severityMap[$severityIdx],
                ];
            }
        }

        $arTips = [
            'يُنصح باستشارة طبيب الجلدية لتقييم دقيق للحالة.',
            'تجنب التعرض المباشر لأشعة الشمس واستخدام واقي شمس مناسب.',
            'الحفاظ على نظافة البشرة وترطيبها يومياً.',
        ];

        $arAnalysis = match (true) {
            str_contains($topLabel, 'Melanoma') || str_contains($topLabel, 'Carcinoma') =>
                'بناءً على تحليل الصورة، تم اكتشاف تغيرات جلدية تتطلب تقييماً طبياً عاجلاً. النتيجة الأولية تشير إلى احتمال وجود ' . $topLabel . ' بنسبة ' . round($topScore * 100) . '%. يجب مراجعة طبيب الجلدية فوراً.',
            str_contains($topLabel, 'Nevus') || str_contains($topLabel, 'Keratosis') =>
                'بناءً على تحليل الصورة، تظهر بعض التغيرات الجلدية التي قد تكون حميدة. النتيجة تشير إلى احتمال وجود ' . $topLabel . ' بنسبة ' . round($topScore * 100) . '%. يُنصح بالمتابعة الدورية.',
            default =>
                'تم تحليل الصورة باستخدام نموذج تصنيف الأمراض الجلدية. النتيجة المحتملة: ' . $topLabel . ' بنسبة ثقة ' . round($topScore * 100) . '%.',
        };

        return [
            'overall_health_score' => $healthScore,
            'formatted_score' => $healthScore . '% — ' . ($healthScore > 70 ? 'جيد' : ($healthScore > 40 ? 'متوسط' : 'بحاجة للعناية')),
            'radar_metrics' => [
                'health_risk' => round($topScore * 100),
                'confidence' => round($topScore * 100),
            ],
            'defects_detected' => $defects,
            'custom_arabic_analysis' => $arAnalysis,
            'expert_free_tips' => $arTips,
            'ai_provider' => 'HuggingFace (' . $model . ')',
            'raw_classification' => $raw,
        ];
    }

    public function getProviderName(): string
    {
        return $this->config['name'] ?? 'HuggingFace';
    }

    public function getEngineType(): EngineType
    {
        return EngineType::STRUCTURED;
    }

    public function isAvailable(): bool
    {
        return ! empty($this->config['api_credentials']['api_key'] ?? null);
    }

    public function getQuotaStatus(): array
    {
        return [
            'used' => $this->config['quota_used'] ?? 0,
            'limit' => $this->config['quota_limit'] ?? 0,
            'available' => true,
        ];
    }

    private function getTimeout(): int
    {
        return $this->config['config']['timeout'] ?? 30;
    }
}
