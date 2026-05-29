<?php

namespace App\Models\Meta;

use Illuminate\Database\Eloquent\Model;

class MetaAdSet extends Model
{
    protected $table = 'meta_ad_sets';

    protected $fillable = [
        'campaign_id', 'ad_account_id', 'ad_set_id', 'name', 'status',
        'optimization_goal', 'billing_event', 'daily_budget', 'lifetime_budget',
        'bid_amount', 'targeting', 'start_time', 'end_time', 'promoted_object',
        'insights', 'last_synced_at',
    ];

    protected $casts = [
        'daily_budget' => 'decimal:2',
        'lifetime_budget' => 'decimal:2',
        'bid_amount' => 'decimal:2',
        'targeting' => 'json',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'insights' => 'json',
        'last_synced_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(MetaCampaign::class, 'campaign_id');
    }

    public function adAccount()
    {
        return $this->belongsTo(MetaAdAccount::class, 'ad_account_id');
    }

    public function ads()
    {
        return $this->hasMany(MetaAd::class, 'ad_set_id');
    }
}
