<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaPage extends Model
{
    protected $fillable = ['page_id', 'page_name', 'page_picture_url', 'access_token', 'webhook_subscribed'];
    protected $casts = ['webhook_subscribed' => 'boolean'];
}
