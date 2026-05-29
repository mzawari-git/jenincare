<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlamaProvider implements LLMProviderInterface
{
    private string $baseUrl;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.llama.base_url', env('LLAMA_BASE_URL', 'http://localhost:11434'));
        $this->model = config('services.llama.model', 'llama3');
        $this->timeout = (int) config('services.llama.timeout', 10);
    }

    public function getName(): string
    {
        return 'Local LLaMA (Ollama)';
    }

    public function isAvailable(): bool
    {
        if (empty($this->baseUrl)) {
            return false;
        }
        try {
            $response = Http::timeout(2)->get("{$this->baseUrl}/api/tags");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function sanitize(string $text, array $context = []): string
    {
        if (!$this->isAvailable()) {
            return $text;
        }

        $productName = $context['product_name'] ?? 'Unknown';
        $prompt = "Review this product description for ad policy violations. Remove or replace any problematic words with safer alternatives in [brackets]. Return ONLY the sanitized text:\n\nProduct: {$productName}\nDescription: {$text}";

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/api/generate", [
                    'model' => $this->model,
                    'prompt' => $prompt,
                    'stream' => false,
                    'options' => ['temperature' => 0.1],
                ]);

            if ($response->successful()) {
                $result = $response->json('response');
                return trim($result ?? $text);
            }

            Log::warning('LLaMA sanitize failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('LLaMA sanitize exception', ['error' => $e->getMessage()]);
        }

        return $text;
    }
}
