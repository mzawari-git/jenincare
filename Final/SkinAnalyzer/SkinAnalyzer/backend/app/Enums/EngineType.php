<?php

namespace App\Enums;

enum EngineType: string
{
    case STRUCTURED = 'structured';
    case GENERATIVE = 'generative';
    case HYBRID = 'hybrid';

    public function label(): string
    {
        return match ($this) {
            self::STRUCTURED => 'تحليل منظم',
            self::GENERATIVE => 'تحليل توليدي',
            self::HYBRID => 'تحليل هجين',
        };
    }
}
