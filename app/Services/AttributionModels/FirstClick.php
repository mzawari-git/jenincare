<?php

namespace App\Services\AttributionModels;

class FirstClick implements AttributionModelInterface
{
    public function getName(): string
    {
        return 'First Click';
    }

    public function getTouchWeights(int $totalTouches): array
    {
        $weights = array_fill(0, $totalTouches, 0);
        if ($totalTouches > 0) {
            $weights[0] = 1;
        }
        return $weights;
    }
}
