<?php

namespace App\Services\AI\Providers;

use App\Enums\EngineType;
use App\Services\AI\BaseAIProvider;
use App\Services\AI\AIProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeProvider extends BaseAIProvider implements AIProviderInterface
{
    private const BASE_URL = 'https://api.anthropic.com/v1';

    private const MESSAGES_ENDPOINT = '/messages';

    private const ANTHROPIC_VERSION = '2023-06-01';

    private const MAX_RETRIES = 2;

    private const RETRY_DELAY_MS = 2000;

    private const TIMEOUT_SECONDS = 45;

    public function analyze(array $imageData): array
    {
        $this->validateImage($imageData);

        $base64Image = $this->extractBase64($imageData);
        $mediaType = $this->detectMediaType($imageData);

        $model = $this->config('model', 'claude-3-5-sonnet-20241022');

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->retry(self::MAX_RETRIES, self::RETRY_DELAY_MS, function ($exception) {
                    return $exception instanceof ConnectionException
                        || ($exception instanceof RequestException && $exception->response->serverError());
                })
                ->withHeaders([
                    'x-api-key' => $this->credentials('api_key'),
                    'anthropic-version' => self::ANTHROPIC_VERSION,
                    'Content-Type' => 'application/json',
                ])
                ->post(self::BASE_URL . self::MESSAGES_ENDPOINT, [
                    'model' => $model,
                    'max_tokens' => 4096,
                    'system' => $this->buildSystemPrompt(),
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'image',
                                    'source' => [
                                        'type' => 'base64',
                                        'media_type' => $mediaType,
                                        'data' => $base64Image,
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $this->buildUserPrompt(),
                                ],
                            ],
                        ],
                    ],
                ]);

            if ($response->failed()) {
                throw new RequestException($response);
            }

            $body = $response->json();

            $textContent = '';

            foreach ($body['content'] ?? [] as $block) {
                if (($block['type'] ?? '') === 'text') {
                    $textContent .= $block['text'];
                }
            }

            $jsonMatch = null;

            if (preg_match('/\{[\s\S]*\}/', $textContent, $matches)) {
                $jsonMatch = $matches[0];
            }

            $parsed = json_decode($jsonMatch ?? $textContent, true);

            if (! is_array($parsed)) {
                throw new \RuntimeException('Claude returned malformed or non-JSON response.');
            }

            $rawResponse = [
                'engine' => 'claude',
                'model' => $model,
                'usage' => $body['usage'] ?? [],
                'stop_reason' => $body['stop_reason'] ?? null,
                'overall_health_score' => $parsed['overall_health_score'] ?? 50,
                'radar_metrics' => $parsed['radar_metrics'] ?? [],
                'heatmap_coordinates' => $parsed['heatmap_coordinates'] ?? [],
                'defects' => $parsed['defects'] ?? [],
                'custom_arabic_analysis' => $parsed['arabic_analysis'] ?? [],
                'custom_arabic_analysis_text' => $parsed['arabic_analysis_text'] ?? $parsed['custom_arabic_analysis_text'] ?? '',
                'expert_free_tips' => $parsed['expert_free_tips'] ?? [],
            ];

            $this->logRequest($rawResponse);

            return $rawResponse;
        } catch (\Exception $e) {
            Log::error('Claude API request failed', [
                'provider' => $this->aiProvider->driver_key,
                'model' => $model,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException("Claude analysis failed: {$e->getMessage()}", 0, $e);
        }
    }

    public function getEngineType(): EngineType
    {
        return EngineType::GENERATIVE;
    }

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
أنت طبيب جلدية خبير ومحلل بشرة متخصص. قم بتحليل صورة الوجه المقدمة وأعد تقييماً شاملاً بصيغة JSON.

القواعد:
- قيّم صحة البشرة على مقياس 0-100 لكل من الدرجة الإجمالية وكل مقياس.
- حدد العيوب المرئية (حب الشباب، البقع الداكنة، التجاعيد، الاحمرار، الجفاف، إلخ) مع شدة 1-10.
- أنشئ إحداثيات خريطة حرارية (heatmap) لمناطق الوجه المتأثرة (الجبهة، الخدين، الأنف، الذقن).
- قدم تحليلاً مفصلاً باللغة العربية مع نصائح عملية للعناية بالبشرة.
- كن صادقاً ولكن مشجعاً في تحليلك.
- ركز فقط على الملاحظات الجلدية ذات الصلة.

You MUST respond with valid JSON only. No other text outside the JSON object.
PROMPT;
    }

    private function buildUserPrompt(): string
    {
        return <<<'PROMPT'
Analyze this facial image for skin health assessment. Return ONLY valid JSON with the following structure:

{
  "overall_health_score": (integer 0-100),
  "radar_metrics": {
    "hydration": (integer 0-100),
    "sebum": (integer 0-100),
    "pigmentation": (integer 0-100),
    "pores": (integer 0-100),
    "elasticity": (integer 0-100)
  },
  "heatmap_coordinates": [
    {"x": (number 0-100), "y": (number 0-100), "label": (string), "severity": (integer 1-10)}
  ],
  "defects": [
    {"type": (string), "severity": (integer 1-10), "description": (Arabic string)}
  ],
  "arabic_analysis_text": (comprehensive Arabic consultation),
  "arabic_analysis": {
    "summary": (Arabic summary string),
    "hydration_analysis": (Arabic string),
    "sebum_analysis": (Arabic string),
    "pigmentation_analysis": (Arabic string),
    "pores_analysis": (Arabic string),
    "elasticity_analysis": (Arabic string)
  },
  "expert_free_tips": [5 actionable Arabic skincare tips]
}
PROMPT;
    }
}
