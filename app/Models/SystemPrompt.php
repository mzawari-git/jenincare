<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemPrompt extends Model
{
    use HasFactory;

    protected $table = 'system_prompts';

    protected $fillable = [
        'key',
        'name',
        'name_ar',
        'content',
        'content_ar',
        'engine_type',
        'is_active',
        'version',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'integer',
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeByKey(Builder $query, string $key): void
    {
        $query->where('key', $key);
    }

    public function scopeByEngine(Builder $query, string $engineType): void
    {
        $query->where('engine_type', $engineType);
    }
}
