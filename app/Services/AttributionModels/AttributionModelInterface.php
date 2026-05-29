<?php

namespace App\Services\AttributionModels;

interface AttributionModelInterface
{
    public function getName(): string;
    public function getTouchWeights(int $totalTouches): array;
}
