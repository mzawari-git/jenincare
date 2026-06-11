<?php

namespace App\Models\Meta;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaLeadConversion extends Model
{
    protected $fillable = [
        'lead_id',
        'campaign_id',
        'booking_id',
        'order_id',
        'conversion_type',
        'value',
        'currency',
        'days_to_convert',
        'touchpoints',
        'attribution_model',
    ];

    protected $casts = [
        'touchpoints' => 'array',
        'value' => 'decimal:2',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(MetaLead::class, 'lead_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MetaCampaign::class, 'campaign_id');
    }
}
