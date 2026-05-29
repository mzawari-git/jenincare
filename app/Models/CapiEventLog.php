<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapiEventLog extends Model
{
    protected $table = 'capi_event_logs';

    protected $fillable = [
        'platform',
        'event_name',
        'event_id',
        'success',
        'status_code',
        'response',
        'ip_address',
        'duration_ms',
        'error_message',
    ];

    protected $casts = [
        'success' => 'boolean',
        'response' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeByEvent($query, string $eventName)
    {
        return $query->where('event_name', $eventName);
    }

    public function scopeRecent($query, int $minutes = 60)
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }
}
