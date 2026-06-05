<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaPage extends Model
{
    protected $fillable = [
        'page_id',
        'page_name',
        'page_username',
        'page_category',
        'page_picture_url',
        'page_followers',
        'page_access_token',
        'app_id',
        'app_secret',
        'is_active',
        'webhook_subscribed',
        'webhook_fields',
        'meta_data',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'webhook_subscribed' => 'boolean',
        'webhook_fields' => 'array',
        'meta_data' => 'array',
        'last_synced_at' => 'datetime',
    ];
}
