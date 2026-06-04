<?php

namespace App\Models\Meta;

use Illuminate\Database\Eloquent\Model;

class MetaAdCreative extends Model
{
    protected $table = 'meta_ad_creatives';

    protected $fillable = [
        'ad_account_id', 'creative_id', 'name', 'title', 'body',
        'description', 'image_hash', 'image_url', 'video_id',
        'link_url', 'display_link', 'call_to_action', 'page_id',
        'instagram_actor_id', 'product_set_id', 'status',
    ];

    public function adAccount()
    {
        return $this->belongsTo(MetaAdAccount::class, 'ad_account_id');
    }

    public function ads()
    {
        return $this->hasMany(MetaAd::class, 'creative_id');
    }
}
