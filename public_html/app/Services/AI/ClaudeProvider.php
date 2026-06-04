<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeProvider implements LLMProviderInterface
{
    private string $apiKey;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key', env('CLAUDE_API_KEY', ''));
        $this->model = config('services.claude.model', 'claude-sonnet-4-20250514');
        $this->timeout = (int) config('services.claude.timeout', 5);
    }

    public function getName(): string
    {
        return 'Claude 4 Sonnet';
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    public function sanitize(string $text, array $context = []): string
    {
        if (!$this->isAvailable()) {
            return $text;
        }

        $systemPrompt = 'You are an ad compliance sanitizer. Review product/ad text and:
1. Remove or replace words that violate ad platform policies (medical claims, unrealistic promises, profanity, discrimination)
2. Replace flagged terms with safer alternatives in [brackets]
3. Return ONLY the sanitized text, no explanations
4. If clean, return unchanged';

        $productName = $context['product_name'] ?? 'Unknown';
        $category = $context['category'] ?? 'General';

        try {
            $response = Http::timeout($this->timeout)
                ->withHeader('x-api-key', $this->apiKey)
                ->withHeader('anthropic-version', '2023-06-01')
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => $this->model,
                    'max_tokens' => 500,
                    'system' => $systemPrompt,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => "Product: {$productName}\nCategory: {$category}\nDescription: {$text}",
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $result = $response->json('content.0.text');
                return trim($result ?? $text);
            }

            Log::warning('Claude sanitize failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Claude sanitize exception', ['error' => $e->getMessage()]);
        }

        return $text;
    }
}
