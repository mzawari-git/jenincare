<?php

namespace App\Models;

use App\Enums\AnalysisStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class SkinScan extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'skin_scans';

    protected $attributes = [
        'expert_free_tips' => '[]',
    ];

    protected $fillable = [
        'user_id',
        'status',
        'analysis_status',
        'image_url',
        'image_path',
        'overall_health_score',
        'hydration',
        'sebum',
        'pigmentation',
        'pores',
        'elasticity',
        'custom_arabic_analysis',
        'expert_free_tips',
        'analysis_data',
        'radar_metrics',
        'advanced_metrics',
        'defects',
        'heatmap_coordinates',
        'recommended_products',
        'metadata',
        'analyzed_by_provider',
        'confidence_score',
        'analyzed_at',
        'is_locked',
        'pin_code',
        'pin_attempts',
        'locked_until',
        'lighting_quality',
        'face_confidence',
        'image_width',
        'image_height',
        'reviewed_at',
    ];

    protected $casts = [
        'expert_free_tips' => 'array',
        'analysis_data' => 'array',
        'radar_metrics' => 'array',
        'advanced_metrics' => 'array',
        'defects' => 'array',
        'heatmap_coordinates' => 'array',
        'recommended_products' => 'array',
        'metadata' => 'array',
        'is_locked' => 'boolean',
        'pin_attempts' => 'integer',
        'locked_until' => 'datetime',
        'overall_health_score' => 'float',
        'hydration' => 'float',
        'sebum' => 'float',
        'pigmentation' => 'float',
        'pores' => 'float',
        'elasticity' => 'float',
        'confidence_score' => 'float',
        'reviewed_at' => 'datetime',
        'analyzed_at' => 'datetime',
    ];

    protected $appends = [
        'analysis_status_label',
        'analysis_status_color',
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

    public function pins(): HasMany
    {
        return $this->hasMany(SkinAnalysisPin::class, 'scan_id');
    }

    public function analysisImages(): HasMany
    {
        return $this->hasMany(ScanAnalysisImage::class, 'scan_id');
    }

    public function getLocalizedImagePath(): ?string
    {
        if ($this->image_path) {
            return storage_path("app/public/{$this->image_path}");
        }

        if ($this->image_url) {
            $relative = ltrim(str_replace('/storage/', '', $this->image_url), '/');
            $full = storage_path("app/public/{$relative}");
            if (file_exists($full)) {
                return $full;
            }
        }

        return null;
    }

    public function getImageFullPath(): ?string
    {
        if ($this->image_path) {
            return Storage::disk('public')->path($this->image_path);
        }

        if ($this->image_url) {
            $relative = ltrim(str_replace('/storage/', '', $this->image_url), '/');
            $full = Storage::disk('public')->path($relative);
            if (file_exists($full)) {
                return $full;
            }
        }

        return null;
    }

    public function isCompleted(): bool
    {
        return $this->analysis_status === AnalysisStatus::COMPLETED->value
            || $this->analysis_status === AnalysisStatus::APPROVED->value;
    }

    public function isProcessing(): bool
    {
        return $this->analysis_status === AnalysisStatus::PROCESSING->value;
    }

    public function isPending(): bool
    {
        return $this->analysis_status === AnalysisStatus::PENDING->value;
    }

    public function isFailed(): bool
    {
        return $this->analysis_status === AnalysisStatus::FAILED->value;
    }

    public function scopeWhereAnalysisStatus($query, AnalysisStatus $status)
    {
        return $query->where('analysis_status', $status->value);
    }

    public function getAnalysisStatusLabelAttribute(): ?string
    {
        $value = $this->analysis_status instanceof AnalysisStatus
            ? $this->analysis_status->value
            : $this->analysis_status;
        $status = AnalysisStatus::tryFrom($value);
        return $status?->label();
    }

    public function getAnalysisStatusColorAttribute(): ?string
    {
        $value = $this->analysis_status instanceof AnalysisStatus
            ? $this->analysis_status->value
            : $this->analysis_status;
        $status = AnalysisStatus::tryFrom($value);
        return $status?->color();
    }

    public static function createWithAnalysis(array $data): self
    {
        $data['analysis_status'] = $data['analysis_status'] ?? AnalysisStatus::PENDING->value;
        return static::create($data);
    }
}
