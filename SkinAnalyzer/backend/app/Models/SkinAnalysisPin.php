<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkinAnalysisPin extends Model
{
    use HasFactory;

    protected $table = 'skin_analysis_pins';

    protected $fillable = [
        'skin_analysis_id',
        'pin_code',
        'is_used',
        'expires_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function skinAnalysis(): BelongsTo
    {
        return $this->belongsTo(SkinAnalysis::class);
    }

    public static function generatePin(): string
    {
        $maxAttempts = 10;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $pin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

            $exists = static::where('pin_code', $pin)->exists();

            if (! $exists) {
                return $pin;
            }
        }

        throw new \RuntimeException('Unable to generate a unique PIN after ' . $maxAttempts . ' attempts.');
    }

    public function isValid(): bool
    {
        if ($this->is_used) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function markUsed(): void
    {
        $this->update(['is_used' => true]);
    }
}
