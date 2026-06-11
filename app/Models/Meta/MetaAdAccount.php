<?php

namespace App\Models\Meta;

use Illuminate\Database\Eloquent\Model;

class MetaAdAccount extends Model
{
    protected $table = 'meta_ad_accounts';

    protected $fillable = [
        'ad_account_id', 'name', 'currency', 'timezone', 'access_token',
        'business_id', 'spend_cap', 'amount_spent', 'account_status',
        'is_active', 'last_synced_at',
    ];

    protected $casts = [
        'spend_cap' => 'decimal:2',
        'amount_spent' => 'decimal:2',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function campaigns()
    {
        return $this->hasMany(MetaCampaign::class, 'ad_account_id');
    }

    public function adSets()
    {
        return $this->hasMany(MetaAdSet::class, 'ad_account_id');
    }

    public function creatives()
    {
        return $this->hasMany(MetaAdCreative::class, 'ad_account_id');
    }

    public function ads()
    {
        return $this->hasMany(MetaAd::class, 'ad_account_id');
    }
}
