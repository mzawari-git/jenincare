<?php

namespace App\Services\AttributionModels;

class TimeDecay implements AttributionModelInterface
{
    public function getName(): string
    {
        return 'Time Decay';
    }

    public function getTouchWeights(int $totalTouches): array
    {
        if ($totalTouches === 0) return [];

        $weights = [];
        for ($i = 0; $i < $totalTouches; $i++) {
            $weights[] = $i + 1;
        }
        $sum = array_sum($weights);
        return array_map(fn($w) => $w / $sum, $weights);
    }
}
