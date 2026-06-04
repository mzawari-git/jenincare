<?php

namespace App\Services;

use App\Services\AI\LLMProviderInterface;
use App\Services\AI\OpenAIProvider;
use App\Services\AI\ClaudeProvider;
use App\Services\AI\LlamaProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AISanitizerService
{
    private array $providers = [];
    private array $fallbackChain;

    public function __construct(
        private OpenAIProvider $openAI,
        private ClaudeProvider $claude,
        private LlamaProvider $llama,
    ) {
        $this->providers = [
            'openai' => $this->openAI,
            'claude' => $this->claude,
            'llama' => $this->llama,
        ];

        $this->fallbackChain = config('tracking.ai.fallback_chain', ['openai', 'claude', 'llama']);
    }

    public function sanitize(string $text, array $context = []): string
    {
        $cacheKey = 'ai_sanitize_' . md5($text);

        return Cache::remember($cacheKey, 3600, function () use ($text, $context) {
            foreach ($this->fallbackChain as $providerName) {
                $provider = $this->providers[$providerName] ?? null;
                if (!$provider || !$provider->isAvailable()) {
                    continue;
                }

                try {
                    $result = $provider->sanitize($text, $context);
                    if ($result !== $text) {
                        Log::info('AI sanitization applied', [
                            'provider' => $provider->getName(),
                            'has_changes' => $result !== $text,
                            'context' => $context,
                        ]);
                    }
                    return $result;
                } catch (\Exception $e) {
                    Log::warning("AI provider {$provider->getName()} failed, trying next", [
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }
            }

            return $text;
        });
    }

    public function getAvailableProviders(): array
    {
        $available = [];
        foreach ($this->providers as $name => $provider) {
            $available[$name] = [
                'name' => $provider->getName(),
                'available' => $provider->isAvailable(),
            ];
        }
        return $available;
    }

    public function getSanitizationStats(): array
    {
        return [
            'total_sanitized' => Cache::get('ai_sanitize_total', 0),
            'last_run' => Cache::get('ai_sanitize_last_run'),
        ];
    }
}
