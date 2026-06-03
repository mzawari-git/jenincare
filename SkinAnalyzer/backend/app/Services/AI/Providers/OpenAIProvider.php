<?php

namespace App\Services\AI\Providers;

use App\Enums\EngineType;
use App\Services\AI\AIProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenaiProvider implements AIProviderInterface
{
    public function __construct(
        private readonly array $config,
    ) {}

    public function analyze(array $imageData): array
    {
        $endpoint = $this->config['api_credentials']['endpoint_url'] ?? 'https://api.openai.com/v1/chat/completions';
        $model = $this->config['config']['model'] ?? 'gpt-4o';

        try {
            $response = Http::timeout($this->getTimeout())
                ->withHeaders([
                    'Authorization' => 'Bearer ' . ($this->config['api_credentials']['api_key'] ?? ''),
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a dermatology AI that analyzes skin images. Respond with structured JSON containing radar_metrics, overall_health_score, custom_arabic_analysis, expert_free_tips, and heatmap_coordinates.',
                        ],
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => 'data:' . $imageData['mime_type'] . ';base64,' . base64_encode($imageData['contents']),
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'text' => 'Analyze this skin image and provide a detailed dermatological assessment. Include all radar metrics, health score, Arabic analysis text, and expert tips.',
                                ],
                            ],
                        ],
                    ],
                    'temperature' => $this->config['config']['temperature'] ?? 0.3,
                    'max_tokens' => $this->config['config']['max_tokens'] ?? 2000,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if ($response->failed()) {
                throw new \RuntimeException("OpenAI API returned status {$response->status()}: {$response->body()}");
            }

            $content = $response->json('choices.0.message.content');
            $parsed = json_decode($content, true);

            if (! is_array($parsed)) {
                throw new \RuntimeException('OpenAI returned invalid JSON response.');
            }

            return $parsed;
        } catch (\Throwable $e) {
            Log::error('OpenaiProvider analysis failed.', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getProviderName(): string
    {
        return $this->config['name'] ?? 'OpenAI';
    }

    public function getEngineType(): EngineType
    {
        return EngineType::GENERATIVE;
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
        return $this->config['config']['timeout'] ?? 60;
    }
}
