<?php

namespace App\Services\AI;

use App\Enums\EngineType;

interface AIProviderInterface
{
    public function analyze(array $imageData): array;

    public function getProviderName(): string;

    public function getEngineType(): EngineType;

    public function isAvailable(): bool;

    public function getQuotaStatus(): array;
}
