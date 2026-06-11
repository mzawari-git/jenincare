<?php

namespace App\Models\Meta;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaCreativeFatigue extends Model
{
    protected $table = 'meta_creative_fatigue';

    protected $fillable = [
        'creative_id',
        'date',
        'ctr',
        'ctr_change',
        'frequency',
        'impressions',
        'clicks',
        'fatigue_level',
        'fatigue_score',
        'recommendations',
    ];

    protected $casts = [
        'date' => 'date',
        'recommendations' => 'array',
    ];

    public function creative(): BelongsTo
    {
        return $this->belongsTo(MetaAdCreative::class, 'creative_id');
    }
}
