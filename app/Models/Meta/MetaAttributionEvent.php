<?php

namespace App\Models\Meta;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaAttributionEvent extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'fbp',
        'fbc',
        'external_id',
        'campaign_id',
        'ad_set_id',
        'ad_id',
        'event_type',
        'value',
        'currency',
        'url',
        'referrer',
        'utm_params',
        'device_type',
        'browser',
        'ip_address',
    ];

    protected $casts = [
        'utm_params' => 'array',
        'value' => 'decimal:2',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MetaCampaign::class, 'campaign_id');
    }

    public function adSet(): BelongsTo
    {
        return $this->belongsTo(MetaAdSet::class, 'ad_set_id');
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(MetaAd::class, 'ad_id');
    }
}
