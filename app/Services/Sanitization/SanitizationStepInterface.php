<?php

namespace App\Services\Sanitization;

interface SanitizationStepInterface
{
    public function process(array $payload, array $context = []): array;
    public function getName(): string;
}
