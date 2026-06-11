<?php

namespace App\Models\Meta;

use Illuminate\Database\Eloquent\Model;

class MetaAd extends Model
{
    protected $table = 'meta_ads';

    protected $fillable = [
        'ad_set_id', 'creative_id', 'ad_account_id', 'ad_id', 'name',
        'status', 'tracking_specs', 'insights', 'last_synced_at',
    ];

    protected $casts = [
        'tracking_specs' => 'json',
        'insights' => 'json',
        'last_synced_at' => 'datetime',
    ];

    public function adSet()
    {
        return $this->belongsTo(MetaAdSet::class, 'ad_set_id');
    }

    public function creative()
    {
        return $this->belongsTo(MetaAdCreative::class, 'creative_id');
    }

    public function adAccount()
    {
        return $this->belongsTo(MetaAdAccount::class, 'ad_account_id');
    }
}
