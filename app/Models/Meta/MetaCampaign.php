<?php

namespace App\Models\Meta;

use Illuminate\Database\Eloquent\Model;

class MetaCampaign extends Model
{
    protected $table = 'meta_campaigns';

    protected $fillable = [
        'ad_account_id', 'campaign_id', 'name', 'objective', 'status',
        'buying_type', 'daily_budget', 'lifetime_budget', 'bid_strategy',
        'special_ad_categories', 'start_time', 'stop_time', 'insights',
        'last_synced_at',
    ];

    protected $casts = [
        'daily_budget' => 'decimal:2',
        'lifetime_budget' => 'decimal:2',
        'start_time' => 'datetime',
        'stop_time' => 'datetime',
        'insights' => 'json',
        'last_synced_at' => 'datetime',
    ];

    public function adAccount()
    {
        return $this->belongsTo(MetaAdAccount::class, 'ad_account_id');
    }

    public function adSets()
    {
        return $this->hasMany(MetaAdSet::class, 'campaign_id');
    }
}
