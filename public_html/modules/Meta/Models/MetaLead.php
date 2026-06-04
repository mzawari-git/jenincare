<?php

namespace Modules\Meta\Models;

use Illuminate\Database\Eloquent\Model;

class MetaLead extends Model
{
    protected $table = 'meta_leads';

    protected $fillable = ['name', 'email', 'phone', 'source', 'campaign_id', 'ad_id', 'event_id', 'event_name', 'data'];
    protected $casts = ['data' => 'array'];
}