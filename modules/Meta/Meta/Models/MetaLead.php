<?php

namespace Modules\Meta\Models;

use Illuminate\Database\Eloquent\Model;

class MetaLead extends Model
{
    protected $table = 'meta_leads';

    protected $fillable = [
        'name', 'email', 'phone', 'sender_name', 'city', 'country',
        'source', 'source_campaign', 'campaign_id', 'ad_id',
        'event_id', 'event_name', 'lead_score', 'stage', 'intent',
        'total_interactions', 'last_activity_at', 'tags', 'meta_data', 'data'
    ];
    protected $casts = ['data' => 'array'];
}