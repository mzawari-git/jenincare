<?php

namespace App\Services\AI\Providers;

use App\Enums\EngineType;
use App\Models\AIProvider;
use App\Services\AI\BaseAIProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider extends BaseAIProvider
{
    protected string $apiBase;

    public function __construct(AIProvider $aiProvider)
    {
        parent::__construct($aiProvider);
        $this->apiBase = $this->credentials('base_url', 'https://api.openai.com/v1');
    }

    public function analyze(array $imageData): array
    {
        $this->validateImage($imageData);

        try {
            $apiKey = $this->credentials('api_key');
            if (empty($apiKey)) {
                throw new \RuntimeException('OpenAI API key not configured.');
            }

            $systemPrompt = $this->buildSystemPrompt();
            $userPrompt = $this->buildUserPrompt($imageData);
            $content = $this->buildContent($userPrompt, $imageData);

            $response = Http::withToken($apiKey)
                ->timeout(120)
                ->post("{$this->apiBase}/chat/completions", [
                    'model' => $this->config('model', 'gpt-4o'),
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $content],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => (float) ($this->config('temperature', 0.3)),
                    'max_tokens' => (int) ($this->config('max_tokens', 4096)),
                ]);

            if ($response->failed()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException('OpenAI API returned error: ' . $response->body());
            }

            $body = $response->json();
            $content = $body['choices'][0]['message']['content'] ?? '{}';
            $parsed = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to parse OpenAI response as JSON.');
            }

            $parsed['provider'] = 'openai';
            $this->incrementQuota();

            $this->logRequest($parsed);

            return $this->normalizeResponse($parsed);
        } catch (\Throwable $e) {
            Log::error('OpenAIProvider analysis failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function getImagePayload(array $imageData): string
    {
        if (!empty($imageData['base64'])) {
            return $imageData['base64'];
        }

        $path = $imageData['path'] ?? '';
        $fullPath = storage_path("app/public/{$path}");

        if (!file_exists($fullPath)) {
            throw new \RuntimeException("Image not found at: {$fullPath}");
        }

        $raw = file_get_contents($fullPath);
        if ($raw === false) {
            throw new \RuntimeException("Failed to read image: {$fullPath}");
        }

        return base64_encode($raw);
    }

    protected function buildSystemPrompt(): string
    {
        $library = app(\App\Services\AI\SkinDefectLibrary::class);

        $defectList = [];
        foreach ($library->getAll() as $category => $defects) {
            foreach ($defects as $type => $info) {
                $defectList[] = "- {$type}: {$info['name']} ({$info['name_ar']})";
            }
        }

        $zoneList = [];
        foreach ($library->getFacialZones() as $key => $zone) {
            $zoneList[] = "- {$key}: {$zone['name']} / {$zone['name_ar']}";
        }

        return <<<PROMPT
You are a professional dermatological analysis AI. Your task is to analyze facial skin images and return structured JSON.

DEFECT LIBRARY (detect any of these):
" . implode("\n", $defectList) . "

FACIAL ZONES (34-zone map):
" . implode("\n", $zoneList) . "

SPECTRAL MODES:
- rgb: Standard visible light (skin surface, texture, pores, redness)
- cross: Cross-polarized (subsurface vessels, deep pigmentation, inflammation)
- parallel: Parallel-polarized (surface texture, fine lines, wrinkles)
- uv: Ultraviolet (pigmentation, sun damage, melanin distribution)

Return valid JSON only, no markdown:
{
  "overall_health_score": 0-100,
  "radar_metrics": {"hydration": 0-100, "sebum": 0-100, "pigmentation": 0-100, "pores": 0-100, "elasticity": 0-100},
  "advanced_metrics": {"brightness": 0-100, "texture": 0-100, "redness": 0-100, "sensitivity": 0-100, "oiliness": 0-100},
  "defects": [{"type": "...", "severity": 0-100, "description": "...", "description_ar": "...", "confidence": 0-1, "category": "...", "requires_medical": true/false, "recommended_ingredients": ["..."]}],
  "heatmap_coordinates": [{"x": 0-1000, "y": 0-1000, "label": "...", "label_ar": "...", "severity": 0-100, "type": "T-zone|U-zone"}],
  "facial_zone_analysis": [{"zone": "...", "name": "...", "name_ar": "...", "severity": 0-100, "issues": ["..."], "note": "...", "note_ar": "..."}],
  "spectral_analysis": [{"mode": "rgb|cross|parallel|uv", "score": 0-100, "findings": "...", "findings_ar": "..."}],
  "custom_arabic_analysis_text": "...",
  "expert_free_tips": [{"en": "...", "ar": "..."}],
  "confidence": 0-1,
  "cross_channel_consistency": 0-100
}
PROMPT;
    }

    protected function buildContent(string $userPrompt, array $imageData): array
    {
        $content = [['type' => 'text', 'text' => $userPrompt]];
        $spectralModes = $imageData['spectral_modes'] ?? [];

        if (count($spectralModes) > 1) {
            foreach ($spectralModes as $mode => $path) {
                $tempData = array_merge($imageData, ['path' => $path]);
                $base64 = $this->getImagePayload($tempData);
                $content[] = [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => "data:image/jpeg;base64,{$base64}",
                        'detail' => $this->config('image_detail', 'high'),
                    ],
                ];
            }
        } else {
            $imageBase64 = $this->getImagePayload($imageData);
            $content[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => "data:image/jpeg;base64,{$imageBase64}",
                    'detail' => $this->config('image_detail', 'high'),
                ],
            ];
        }

        return $content;
    }

    protected function buildUserPrompt(array $imageData): string
    {
        $spectralModes = $imageData['spectral_modes'] ?? [];
        $hasMultiImage = count($spectralModes) > 1;

        if ($hasMultiImage) {
            $modes = implode(', ', array_keys($spectralModes));
            $prompt = "MULTI-CHANNEL ANALYSIS: I am providing {$modes} spectral images of the same face.";
            $prompt .= " Cross-analyze across all channels. Match surface findings (RGB) with subsurface findings (Cross-Polarized).";
            $prompt .= " Overlay UV sebum/pigmentation data on RGB findings. Report cross_channel_consistency score.";
        } else {
            $prompt = 'Analyze this facial skin image and return a comprehensive skin analysis in JSON format.';
        }

        if (!empty($imageData['spectral_modes'])) {
            $modes = implode(', ', array_keys($imageData['spectral_modes']));
            $prompt .= " Available spectral modes: {$modes}. Provide per-mode spectral_analysis entries.";
        }

        if (!empty($imageData['features'])) {
            $prompt .= ' Additional features: ' . json_encode($imageData['features'], JSON_UNESCAPED_UNICODE) . '.';
        }

        if (!empty($imageData['metadata'])) {
            $meta = $imageData['metadata'];
            $prompt .= " Scan metadata: client_id={$meta['scan_id']}.";
        }

        return $prompt;
    }

    protected function incrementQuota(): void
    {
        $this->aiProvider->increment('quota_used');
    }
}
