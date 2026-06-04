<?php

namespace App\Services\AttributionModels;

class Linear implements AttributionModelInterface
{
    public function getName(): string
    {
        return 'Linear';
    }

    public function getTouchWeights(int $totalTouches): array
    {
        if ($totalTouches === 0) return [];
        $weight = 1 / $totalTouches;
        return array_fill(0, $totalTouches, $weight);
    }
}
