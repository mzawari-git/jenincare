<?php

namespace App\Services\AI\Providers;

use App\Enums\EngineType;
use App\Services\AI\BaseAIProvider;
use App\Services\AI\AIProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider extends BaseAIProvider implements AIProviderInterface
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    private const GENERATE_ENDPOINT = '/models/{model}:generateContent';

    private const MAX_RETRIES = 2;

    private const RETRY_DELAY_MS = 2000;

    private const TIMEOUT_SECONDS = 45;

    public function analyze(array $imageData): array
    {
        $this->validateImage($imageData);

        $base64Image = $this->extractBase64($imageData);
        $mimeType = $this->detectMediaType($imageData);

        $model = $this->config('model', 'gemini-1.5-pro');

        $endpoint = str_replace('{model}', $model, self::GENERATE_ENDPOINT);
        $url = self::BASE_URL . $endpoint . '?key=' . urlencode($this->credentials('api_key', ''));

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->retry(self::MAX_RETRIES, self::RETRY_DELAY_MS, function ($exception) {
                    return $exception instanceof ConnectionException
                        || ($exception instanceof RequestException && $exception->response->serverError());
                })
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $this->buildSystemInstruction()],
                        ],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                [
                                    'inlineData' => [
                                        'mimeType' => $mimeType,
                                        'data' => $base64Image,
                                    ],
                                ],
                                [
                                    'text' => $this->buildUserPrompt(),
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'responseSchema' => $this->getOutputSchema(),
                        'temperature' => 0.2,
                        'maxOutputTokens' => 4096,
                    ],
                ]);

            if ($response->failed()) {
                throw new RequestException($response);
            }

            $body = $response->json();
            $content = $body['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            $parsed = json_decode($content, true);

            if (! is_array($parsed)) {
                throw new \RuntimeException('Gemini returned malformed JSON response.');
            }

            $rawResponse = [
                'engine' => 'gemini',
                'model' => $model,
                'usage' => $body['usageMetadata'] ?? [],
                'finish_reason' => $body['candidates'][0]['finishReason'] ?? null,
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
            Log::error('Gemini API request failed', [
                'provider' => $this->aiProvider->driver_key,
                'model' => $model,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException("Gemini analysis failed: {$e->getMessage()}", 0, $e);
        }
    }

    public function getEngineType(): EngineType
    {
        return EngineType::GENERATIVE;
    }

    private function extractBase64(array $imageData): string
    {
        if (! empty($imageData['base64'])) {
            $base64 = $imageData['base64'];

            if (str_contains($base64, 'base64,')) {
                $parts = explode('base64,', $base64, 2);
                return $parts[1];
            }

            return $base64;
        }

        if (! empty($imageData['path'])) {
            $content = $this->disk->get($imageData['path']);
            return base64_encode($content);
        }

        throw new \InvalidArgumentException('No valid image data provided.');
    }

    private function detectMediaType(array $imageData): string
    {
        if (! empty($imageData['base64']) && str_contains($imageData['base64'], 'data:')) {
            if (preg_match('/data:(image\/\w+);base64,/', $imageData['base64'], $matches)) {
                return $matches[1];
            }
        }

        if (! empty($imageData['path'])) {
            $mimeType = $this->disk->mimeType($imageData['path']);
            if ($mimeType) {
                return $mimeType;
            }
        }

        return 'image/jpeg';
    }

    private function buildSystemInstruction(): string
    {
        return <<<'PROMPT'
You are an expert dermatologist and skin analysis AI specializing in Arabic-language patient consultation. Analyze facial images and provide comprehensive skin assessments.

Guidelines:
- Evaluate overall skin health on a 0-100 scale.
- Assess five metrics: hydration, sebum production, pigmentation, pore visibility, elasticity — each 0-100.
- Identify visible skin concerns and defects with severity ratings.
- Map affected areas to facial region coordinates for heatmap visualization.
- Provide compassionate, professional Arabic-language analysis and actionable skincare advice.
- Be honest but encouraging. Focus on dermatologically relevant observations only.
- Output must be strict JSON matching the exact schema provided.
PROMPT;
    }

    private function buildUserPrompt(): string
    {
        return 'Analyze this facial image for skin health assessment. Return a structured JSON response with overall_health_score, radar_metrics, heatmap_coordinates, defects, arabic_analysis_text, arabic_analysis, and expert_free_tips.';
    }

    private function getOutputSchema(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'overall_health_score' => [
                    'type' => 'INTEGER',
                    'description' => 'Overall skin health score from 0 to 100',
                ],
                'radar_metrics' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'hydration' => ['type' => 'INTEGER'],
                        'sebum' => ['type' => 'INTEGER'],
                        'pigmentation' => ['type' => 'INTEGER'],
                        'pores' => ['type' => 'INTEGER'],
                        'elasticity' => ['type' => 'INTEGER'],
                    ],
                    'required' => ['hydration', 'sebum', 'pigmentation', 'pores', 'elasticity'],
                ],
                'heatmap_coordinates' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'x' => ['type' => 'NUMBER'],
                            'y' => ['type' => 'NUMBER'],
                            'label' => ['type' => 'STRING'],
                            'severity' => ['type' => 'INTEGER'],
                        ],
                        'required' => ['x', 'y', 'label', 'severity'],
                    ],
                ],
                'defects' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'type' => ['type' => 'STRING'],
                            'severity' => ['type' => 'INTEGER'],
                            'description' => ['type' => 'STRING'],
                        ],
                        'required' => ['type', 'severity', 'description'],
                    ],
                ],
                'arabic_analysis_text' => [
                    'type' => 'STRING',
                    'description' => 'Full Arabic consultation text',
                ],
                'arabic_analysis' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'summary' => ['type' => 'STRING'],
                        'hydration_analysis' => ['type' => 'STRING'],
                        'sebum_analysis' => ['type' => 'STRING'],
                        'pigmentation_analysis' => ['type' => 'STRING'],
                        'pores_analysis' => ['type' => 'STRING'],
                        'elasticity_analysis' => ['type' => 'STRING'],
                    ],
                    'required' => ['summary'],
                ],
                'expert_free_tips' => [
                    'type' => 'ARRAY',
                    'items' => ['type' => 'STRING'],
                ],
            ],
            'required' => [
                'overall_health_score',
                'radar_metrics',
                'heatmap_coordinates',
                'defects',
                'arabic_analysis_text',
                'expert_free_tips',
            ],
        ];
    }
}
