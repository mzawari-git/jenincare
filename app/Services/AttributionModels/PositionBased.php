<?php

namespace App\Services\AttributionModels;

class PositionBased implements AttributionModelInterface
{
    public function getName(): string
    {
        return 'Position Based (40-20-40)';
    }

    public function getTouchWeights(int $totalTouches): array
    {
        if ($totalTouches === 0) return [];
        if ($totalTouches === 1) return [1];
        if ($totalTouches === 2) return [0.5, 0.5];

        $weights = array_fill(0, $totalTouches, 0);
        $weights[0] = 0.4;
        $weights[$totalTouches - 1] = 0.4;
        $remaining = 0.2 / ($totalTouches - 2);
        for ($i = 1; $i < $totalTouches - 1; $i++) {
            $weights[$i] = $remaining;
        }
        return $weights;
    }
}
