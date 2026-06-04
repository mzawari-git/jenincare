<?php

namespace App\Services\Sanitization;

use App\Services\AISanitizerService;
use Illuminate\Support\Facades\Log;

class LLMFilter implements SanitizationStepInterface
{
    public function __construct(
        private AISanitizerService $aiSanitizer,
    ) {}

    public function getName(): string
    {
        return 'LLM Sanitizer';
    }

    public function process(array $payload, array $context = []): array
    {
        if (!config('tracking.ai.enabled', true)) {
            return $payload;
        }

        $fieldsToCheck = ['product_name', 'description', 'product_category', 'search_string'];

        foreach ($fieldsToCheck as $field) {
            if (empty($payload['data'][$field])) continue;

            $original = $payload['data'][$field];
            $sanitized = $this->aiSanitizer->sanitize($original, [
                'product_name' => $payload['data']['product_name'] ?? '',
                'category' => $payload['data']['product_category'] ?? '',
                'platform' => $context['platform'] ?? '',
            ]);

            if ($sanitized !== $original) {
                $payload['data'][$field] = $sanitized;
                $payload['_sanitized'] = true;
                $payload['_sanitization_log'][] = [
                    'step' => $this->getName(),
                    'field' => $field,
                    'before' => $original,
                    'after' => $sanitized,
                ];

                Log::info('LLM sanitized field', [
                    'field' => $field,
                    'platform' => $context['platform'] ?? 'unknown',
                ]);
            }
        }

        return $payload;
    }
}
