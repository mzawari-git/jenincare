<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WheelPrize extends Model
{
    protected $fillable = [
        'name', 'image', 'color', 'sort_order', 'is_active',
        'type', 'discount_percent', 'value', 'weight',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'discount_percent' => 'integer',
        'weight' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image && $this->type === 'product') {
            return Storage::url('wheel-prizes/' . $this->image);
        }
        return null;
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'discount') {
            return "{$this->discount_percent}%";
        }
        return $this->name;
    }

    public function getPrizeValueAttribute(): string
    {
        if ($this->type === 'discount') {
            return "خصم {$this->discount_percent}%";
        }
        return $this->value ?: $this->name;
    }
}
