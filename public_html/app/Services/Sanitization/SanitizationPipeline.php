<?php

namespace App\Services\Sanitization;

use Illuminate\Support\Facades\Log;

class SanitizationPipeline
{
    private array $steps = [];

    public function addStep(SanitizationStepInterface $step): self
    {
        $this->steps[] = $step;
        return $this;
    }

    public function process(array $payload, array $context = []): array
    {
        $payload['_sanitization_log'] = [];
        $payload['_sanitized'] = false;
        $payload['_blocked'] = false;

        foreach ($this->steps as $step) {
            try {
                $payload = $step->process($payload, $context);
                if (!empty($payload['_blocked'])) {
                    Log::info('Event blocked by sanitization pipeline', [
                        'step' => $step->getName(),
                        'reason' => $payload['_block_reason'] ?? 'unknown',
                        'platform' => $context['platform'] ?? 'unknown',
                    ]);
                    return $payload;
                }
            } catch (\Exception $e) {
                Log::error("Sanitization step {$step->getName()} failed", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $payload;
    }

    public function getSteps(): array
    {
        return array_map(fn($s) => $s->getName(), $this->steps);
    }
}
