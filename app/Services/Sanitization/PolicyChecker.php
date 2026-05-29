<?php

namespace App\Services\Sanitization;

use Illuminate\Support\Facades\Log;

class PolicyChecker implements SanitizationStepInterface
{
    private array $platformPolicies = [
        'facebook' => [
            'max_text_length' => 5000,
            'blocked_categories' => ['medical_claims', 'weight_loss', 'financial'],
            'require_disclaimer' => false,
        ],
        'tiktok' => [
            'max_text_length' => 3000,
            'blocked_categories' => ['scam', 'financial'],
            'require_disclaimer' => true,
        ],
        'google' => [
            'max_text_length' => 2000,
            'blocked_categories' => ['medical_claims'],
            'require_disclaimer' => false,
        ],
    ];

    public function getName(): string
    {
        return 'Policy Checker';
    }

    public function process(array $payload, array $context = []): array
    {
        $platform = $context['platform'] ?? 'facebook';
        $policy = $this->platformPolicies[$platform] ?? $this->platformPolicies['facebook'];

        $description = $payload['data']['description'] ?? $payload['data']['product_name'] ?? '';

        if (mb_strlen($description) > $policy['max_text_length']) {
            $payload['data']['description'] = mb_substr($description, 0, $policy['max_text_length']);
            $payload['_sanitized'] = true;
            $payload['_sanitization_log'][] = [
                'step' => $this->getName(),
                'action' => 'truncated',
                'reason' => "Exceeded {$platform} max text length of {$policy['max_text_length']}",
            ];
        }

        $payload['_policy_checked'] = true;
        $payload['_policy'] = $policy;

        return $payload;
    }
}
