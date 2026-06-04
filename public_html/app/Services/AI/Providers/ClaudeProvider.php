<?php

namespace App\Services\AI\Providers;

use App\Enums\EngineType;
use App\Models\AIProvider;
use App\Services\AI\BaseAIProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeProvider extends BaseAIProvider
{
    protected string $apiBase;

    public function __construct(AIProvider $aiProvider)
    {
        parent::__construct($aiProvider);
        $this->apiBase = $this->credentials('base_url', 'https://api.anthropic.com/v1');
    }

    public function analyze(array $imageData): array
    {
        $this->validateImage($imageData);

        try {
            $apiKey = $this->credentials('api_key');
            if (empty($apiKey)) {
                throw new \RuntimeException('Claude API key not configured.');
            }

            $imageBase64 = $this->getImagePayload($imageData);
            $mediaType = $this->getImageMediaType($imageData);

            $systemPrompt = $this->buildSystemPrompt();
            $userPrompt = $this->buildUserPrompt($imageData);

            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
            ])->timeout(120)
                ->post("{$this->apiBase}/messages", [
                    'model' => $this->config('model', 'claude-3-5-sonnet-20241022'),
                    'max_tokens' => (int) ($this->config('max_tokens', 4096)),
                    'system' => $systemPrompt,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'image',
                                    'source' => [
                                        'type' => 'base64',
                                        'media_type' => $mediaType,
                                        'data' => $imageBase64,
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $userPrompt,
                                ],
                            ],
                        ],
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Claude API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException('Claude API returned error: ' . $response->body());
            }

            $body = $response->json();
            $content = '';

            foreach ($body['content'] ?? [] as $block) {
                if (($block['type'] ?? '') === 'text') {
                    $content .= $block['text'] ?? '';
                }
            }

            $cleaned = $this->extractJson($content);
            $parsed = json_decode($cleaned, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to parse Claude response as JSON.');
            }

            $parsed['provider'] = 'claude';
            $this->incrementQuota();

            $this->logRequest($parsed);

            return $this->normalizeResponse($parsed);
        } catch (\Throwable $e) {
            Log::error('ClaudeProvider analysis failed: ' . $e->getMessage(), [
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

    protected function getImageMediaType(array $imageData): string
    {
        $path = $imageData['path'] ?? '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }

    protected function extractJson(string $text): string
    {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if ($start !== false && $end !== false && $end > $start) {
            return substr($text, $start, $end - $start + 1);
        }

        return $text;
    }

    protected function buildSystemPrompt(): string
    {
        return <<<PROMPT
You are a professional dermatological analysis AI. Analyze facial skin images and return structured JSON.

Return valid JSON only (no markdown, no code fences) with this structure:
{
  "overall_health_score": 0-100,
  "radar_metrics": {"hydration": number, "sebum": number, "pigmentation": number, "pores": number, "elasticity": number},
  "advanced_metrics": {"brightness": number, "texture": number, "redness": number, "sensitivity": number, "oiliness": number},
  "defects": [{"type": "...", "severity": 0-100, "description": "...", "description_ar": "...", "confidence": 0-1, "category": "...", "requires_medical": true/false, "recommended_ingredients": ["..."]}],
  "heatmap_coordinates": [{"x": number, "y": number, "label": "...", "label_ar": "...", "severity": 0-100, "type": "T-zone|U-zone"}],
  "facial_zone_analysis": [{"zone": "...", "name": "...", "name_ar": "...", "severity": 0-100, "issues": ["..."], "note": "...", "note_ar": "..."}],
  "spectral_analysis": [{"mode": "rgb|cross|parallel|uv", "score": 0-100, "findings": "...", "findings_ar": "..."}],
  "custom_arabic_analysis_text": "...",
  "expert_free_tips": [{"en": "...", "ar": "..."}],
  "confidence": 0-1
}
PROMPT;
    }

    protected function buildUserPrompt(array $imageData): string
    {
        $prompt = 'Analyze this facial skin image comprehensively. Include overall health score, radar and advanced metrics, all detected defects with severity, heatmap coordinates, per-zone analysis, and Arabic text.';

        if (!empty($imageData['spectral_modes'])) {
            $modes = implode(', ', array_keys($imageData['spectral_modes']));
            $prompt .= " Available spectral modes: {$modes}. Analyze each mode separately.";
        }

        if (!empty($imageData['features'])) {
            $prompt .= " Features: " . json_encode($imageData['features'], JSON_UNESCAPED_UNICODE);
        }

        return $prompt;
    }

    protected function incrementQuota(): void
    {
        $this->aiProvider->increment('quota_used');
    }
}
