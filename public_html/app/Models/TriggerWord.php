<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TriggerWord extends Model
{
    protected $fillable = [
        'word', 'category', 'severity', 'platform',
        'action', 'replacement', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForPlatform($query, ?string $platform)
    {
        if ($platform) {
            return $query->where(function ($q) use ($platform) {
                $q->where('platform', $platform)->orWhereNull('platform');
            });
        }
        return $query;
    }
}
