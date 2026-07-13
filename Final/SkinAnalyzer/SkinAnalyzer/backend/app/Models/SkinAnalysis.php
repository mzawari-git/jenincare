<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\AnalysisStatus;

class SkinAnalysis extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'skin_analyses';

    protected $fillable = [
        'user_id',
        'ai_provider_id',
        'image_path',
        'status',
        'overall_health_score',
        'radar_metrics',
        'heatmap_coordinates',
        'custom_arabic_analysis',
        'expert_free_tips',
        'raw_vendor_response',
        'approved_at',
    ];

    protected $casts = [
        'radar_metrics' => 'array',
        'heatmap_coordinates' => 'array',
        'expert_free_tips' => 'array',
        'raw_vendor_response' => 'array',
        'approved_at' => 'datetime',
        'overall_health_score' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiProvider(): BelongsTo
    {
        return $this->belongsTo(AIProvider::class);
    }

    public function accessPin(): HasOne
    {
        return $this->hasOne(SkinAnalysisPin::class);
    }

    public function recommendedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'skin_analysis_products')
            ->withPivot('matching_reason')
            ->withTimestamps();
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', AnalysisStatus::PENDING->value);
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', AnalysisStatus::APPROVED->value);
    }

    public function scopeForUser(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    public function getIsLockedAttribute(): bool
    {
        return $this->status === AnalysisStatus::PENDING->value
            && $this->accessPin !== null
            && ! $this->accessPin->is_used;
    }

    public function getFormattedScoreAttribute(): string
    {
        if ($this->overall_health_score === null) {
            return 'N/A';
        }

        $score = $this->overall_health_score;

        return match (true) {
            $score >= 80 => "{$score}% — ممتاز",
            $score >= 60 => "{$score}% — جيد",
            $score >= 40 => "{$score}% — متوسط",
            default => "{$score}% — يحتاج عناية",
        };
    }
}
