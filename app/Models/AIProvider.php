<?php

namespace App\Models;

use App\Enums\EngineType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIProvider extends Model
{
    use HasFactory;

    protected $table = 'ai_providers';

    protected $fillable = [
        'name',
        'name_ar',
        'driver_key',
        'engine_type',
        'api_credentials',
        'is_active',
        'quota_limit',
        'quota_used',
        'config',
        'priority',
        'last_check_at',
    ];

    protected $casts = [
        'api_credentials' => 'array',
        'config' => 'array',
        'is_active' => 'boolean',
        'quota_limit' => 'integer',
        'quota_used' => 'integer',
        'priority' => 'integer',
        'last_check_at' => 'datetime',
    ];

    protected $hidden = [
        'api_credentials',
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeByType(Builder $query, EngineType $type): void
    {
        $query->where('engine_type', $type->value);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('priority')->orderBy('name');
    }

    public function hasQuotaAvailable(): bool
    {
        if ($this->quota_limit === 0) {
            return true;
        }

        return $this->quota_used < $this->quota_limit;
    }

    public function incrementQuota(): void
    {
        $this->increment('quota_used');
    }

    public function canFailover(): bool
    {
        return $this->is_active
            && $this->hasQuotaAvailable()
            && !empty($this->api_credentials);
    }
}
