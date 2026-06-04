<?php

namespace App\Enums;

enum AnalysisStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'قيد المراجعة',
            self::PROCESSING => 'قيد التحليل',
            self::COMPLETED => 'اكتمل التحليل',
            self::APPROVED => 'تمت الموافقة',
            self::REJECTED => 'مرفوض',
            self::FAILED => 'فشل التحليل',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING, self::PROCESSING => 'warning',
            self::COMPLETED, self::APPROVED => 'success',
            self::REJECTED, self::FAILED => 'danger',
        };
    }
}
