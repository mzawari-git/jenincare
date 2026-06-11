<?php

namespace App\Models\Meta;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaScheduledCampaign extends Model
{
    protected $fillable = [
        'campaign_id',
        'action',
        'scheduled_at',
        'parameters',
        'status',
        'executed_at',
        'error_message',
        'created_by',
    ];

    protected $casts = [
        'parameters' => 'array',
        'scheduled_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MetaCampaign::class, 'campaign_id');
    }
}
