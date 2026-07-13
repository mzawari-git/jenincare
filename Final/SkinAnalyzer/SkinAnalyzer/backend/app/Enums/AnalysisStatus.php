<?php

namespace App\Enums;

enum AnalysisStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'قيد المراجعة',
            self::APPROVED => 'تمت الموافقة',
            self::REJECTED => 'مرفوض',
        };
    }
}
