<?php

namespace App\Services\AI;

interface LLMProviderInterface
{
    public function sanitize(string $text, array $context = []): string;
    public function isAvailable(): bool;
    public function getName(): string;
}
