<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = [
        'login_enabled',
        'registration_enabled',
        'maintenance_mode',
        'maintenance_message_ar',
        'maintenance_message_en',
        'min_app_version',
        'latest_app_version',
    ];

    protected $casts = [
        'login_enabled' => 'boolean',
        'registration_enabled' => 'boolean',
        'maintenance_mode' => 'boolean',
    ];
}
