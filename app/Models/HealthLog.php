<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthLog extends Model
{
    protected $fillable = [
        'platform', 'score', 'signals', 'checked_at',
    ];

    protected $casts = [
        'signals' => 'json',
        'checked_at' => 'datetime',
    ];
}
