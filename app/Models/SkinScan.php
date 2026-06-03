<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkinScan extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'skin_scans';

    protected $fillable = [
        'user_id', 'status', 'image_url', 'overall_health_score',
        'hydration', 'sebum', 'pigmentation', 'pores', 'elasticity',
        'custom_arabic_analysis', 'expert_free_tips', 'is_locked', 'pin_code',
        'pin_attempts', 'locked_until', 'lighting_quality', 'face_confidence',
        'image_width', 'image_height', 'reviewed_at',
    ];

    protected $casts = [
        'expert_free_tips' => 'array',
        'is_locked' => 'boolean',
        'pin_attempts' => 'integer',
        'locked_until' => 'datetime',
        'overall_health_score' => 'float',
        'hydration' => 'float',
        'sebum' => 'float',
        'pigmentation' => 'float',
        'pores' => 'float',
        'elasticity' => 'float',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function heatmapPoints(): HasMany
    {
        return $this->hasMany(ScanHeatmapPoint::class, 'scan_id');
    }

    public function defects(): HasMany
    {
        return $this->hasMany(ScanDefect::class, 'scan_id');
    }

    public function timelineEvents(): HasMany
    {
        return $this->hasMany(ScanTimelineEvent::class, 'scan_id');
    }

    public function generalTips(): HasMany
    {
        return $this->hasMany(ScanGeneralTip::class, 'scan_id');
    }
}
