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
        'pin_attempts',
        'locked_until',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
        'locked_until' => 'datetime',
        'pin_attempts' => 'integer',
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

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function recordFailedAttempt(): void
    {
        $maxAttempts = config('skinanalyzer.pin.max_attempts', 5);
        $lockoutMinutes = config('skinanalyzer.pin.lockout_minutes', 15);

        $this->increment('pin_attempts');

        if ($this->pin_attempts >= $maxAttempts) {
            $this->update(['locked_until' => now()->addMinutes($lockoutMinutes)]);
        }
    }

    public function getRemainingAttempts(): int
    {
        $maxAttempts = config('skinanalyzer.pin.max_attempts', 5);

        return max(0, $maxAttempts - $this->pin_attempts);
    }
}
