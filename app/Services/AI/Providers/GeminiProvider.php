<?php

namespace App\Services\AI\Providers;

use App\Enums\EngineType;
use App\Models\AIProvider;
use App\Services\AI\BaseAIProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider extends BaseAIProvider
{
    protected string $apiBase;

    public function __construct(AIProvider $aiProvider)
    {
        parent::__construct($aiProvider);
        $this->apiBase = $this->credentials('base_url', 'https://generativelanguage.googleapis.com/v1beta');
    }

    public function analyze(array $imageData): array
    {
        $this->validateImage($imageData);

        try {
            $apiKey = $this->credentials('api_key');
            if (empty($apiKey)) {
                throw new \RuntimeException('Gemini API key not configured.');
            }

            $imageBase64 = $this->getImagePayload($imageData);
            $mimeType = $this->getImageMimeType($imageData);

            $prompt = $this->buildPrompt($imageData);

            $response = Http::withOptions(['timeout' => 120])
                ->post("{$this->apiBase}/models/{$this->getModel()}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data' => $imageBase64,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => (float) ($this->config('temperature', 0.3)),
                        'maxOutputTokens' => (int) ($this->config('max_tokens', 4096)),
                        'response_mime_type' => 'application/json',
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException('Gemini API returned error: ' . $response->body());
            }

            $body = $response->json();
            $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

            $cleaned = $this->extractJson($text);
            $parsed = json_decode($cleaned, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to parse Gemini response as JSON: ' . json_last_error_msg());
            }

            $parsed['provider'] = 'gemini';
            $this->incrementQuota();

            $this->logRequest($parsed);

            return $this->normalizeResponse($parsed);
        } catch (\Throwable $e) {
            Log::error('GeminiProvider analysis failed: ' . $e->getMessage(), [
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

    protected function getImageMimeType(array $imageData): string
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

    protected function getModel(): string
    {
        return $this->config('model', 'gemini-2.0-flash');
    }

    protected function extractJson(string $text): string
    {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if ($start !== false && $end !== false && $end > $start) {
            return substr($text, $start, $end - $start + 1);
        }

        $start = strpos($text, '[');
        $end = strrpos($text, ']');

        if ($start !== false && $end !== false && $end > $start) {
            return substr($text, $start, $end - $start + 1);
        }

        return $text;
    }

    protected function buildPrompt(array $imageData): string
    {
        $prompt = <<<PROMPT
You are a professional dermatological AI. Analyze this facial skin image and return a JSON object.

Required JSON structure:
{
  "overall_health_score": 0-100,
  "radar_metrics": {"hydration": 0-100, "sebum": 0-100, "pigmentation": 0-100, "pores": 0-100, "elasticity": 0-100},
  "advanced_metrics": {"brightness": 0-100, "texture": 0-100, "redness": 0-100, "sensitivity": 0-100, "oiliness": 0-100},
  "defects": [{"type": "...", "severity": 0-100, "description": "...", "description_ar": "...", "confidence": 0-1, "category": "...", "requires_medical": true/false, "recommended_ingredients": ["..."]}],
  "heatmap_coordinates": [{"x": 0-1000, "y": 0-1000, "label": "...", "label_ar": "...", "severity": 0-100, "type": "T-zone|U-zone"}],
  "facial_zone_analysis": [{"zone": "...", "name": "...", "name_ar": "...", "severity": 0-100, "issues": ["..."], "note": "...", "note_ar": "..."}],
  "custom_arabic_analysis_text": "Arabic summary of findings",
  "expert_free_tips": [{"en": "...", "ar": "..."}],
  "confidence": 0-1
}

Common defect types: acne, blackheads, whiteheads, papules, pustules, hyperpigmentation, melasma, sun_damage, freckles, wrinkles, fine_lines, sagging, dry_skin, oily_skin, dehydration, enlarged_pores, redness, rosacea, eczema, psoriasis, uneven_texture, dullness, dark_circles, puffiness.

Common facial zones (34-zone map): forehead, left_cheek, right_cheek, nose, chin, under_eye_left, under_eye_right, left_temple, right_temple, left_jawline, right_jawline, left_nasolabial, right_nasolabial, left_eyebrow, right_eyebrow, glabella, left_ear, right_ear, left_upper_lip, right_upper_lip, lower_lip, left_cheekbone, right_cheekbone, left_mandible, right_mandible, left_eye_corner, right_eye_corner, left_nose_wing, right_nose_wing, left_lower_cheek, right_lower_cheek, left_upper_cheek, right_upper_cheek, left_side_face, right_side_face.

Return ONLY valid JSON. No markdown, no code fences.
PROMPT;

        if (!empty($imageData['spectral_modes'])) {
            $prompt .= "\nSpectral modes available: " . implode(', ', array_keys($imageData['spectral_modes']));
            $prompt .= "\nIf multiple modes are present, provide per-mode spectral_analysis entries.";
        }

        return $prompt;
    }

    protected function incrementQuota(): void
    {
        $this->aiProvider->increment('quota_used');
    }
}
