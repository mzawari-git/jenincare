<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements LLMProviderInterface
{
    private string $apiKey;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY', ''));
        $this->model = config('services.openai.model', 'gpt-4o');
        $this->timeout = (int) config('services.openai.timeout', 5);
    }

    public function getName(): string
    {
        return 'OpenAI GPT-4o';
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

        $systemPrompt = 'You are an ad compliance sanitizer. Your task is to review the given product/ad text and:
1. Remove or replace any words that could trigger ad platform policy violations (medical claims, unrealistic promises, profanity, discriminatory language)
2. Replace flagged terms with safer alternatives in square brackets
3. Return ONLY the sanitized text, no explanations
4. If the text is clean, return it unchanged';

        $productName = $context['product_name'] ?? 'Unknown';
        $category = $context['category'] ?? 'General';

        try {
            $response = Http::timeout($this->timeout)
                ->withToken($this->apiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => "Product: {$productName}\nCategory: {$category}\nDescription: {$text}"],
                    ],
                    'max_tokens' => 500,
                    'temperature' => 0.1,
                ]);

            if ($response->successful()) {
                $result = $response->json('choices.0.message.content');
                return trim($result ?? $text);
            }

            Log::warning('OpenAI sanitize failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('OpenAI sanitize exception', ['error' => $e->getMessage()]);
        }

        return $text;
    }
}
