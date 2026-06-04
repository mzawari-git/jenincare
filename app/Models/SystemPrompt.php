<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemPrompt extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'system_prompts';

    protected $fillable = [
        'name',
        'provider_key',
        'system_instruction',
        'tone',
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
        $query->where('provider_key', $key);
    }

    public function scopeByEngine(Builder $query, string $engineType): void
    {
        $query->where('tone', $engineType);
    }
}
