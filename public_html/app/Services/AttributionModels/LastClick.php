<?php

namespace App\Services\AttributionModels;

class LastClick implements AttributionModelInterface
{
    public function getName(): string
    {
        return 'Last Click';
    }

    public function getTouchWeights(int $totalTouches): array
    {
        $weights = array_fill(0, $totalTouches, 0);
        if ($totalTouches > 0) {
            $weights[$totalTouches - 1] = 1;
        }
        return $weights;
    }
}
