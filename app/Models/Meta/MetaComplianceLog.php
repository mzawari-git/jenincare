<?php

namespace App\Models\Meta;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaComplianceLog extends Model
{
    protected $fillable = [
        'ad_account_id',
        'campaign_id',
        'ad_id',
        'type',
        'severity',
        'policy_name',
        'description',
        'meta_data',
        'status',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'meta_data' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function adAccount(): BelongsTo
    {
        return $this->belongsTo(MetaAdAccount::class, 'ad_account_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MetaCampaign::class, 'campaign_id');
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(MetaAd::class, 'ad_id');
    }
}
