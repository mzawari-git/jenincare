<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdReviewerIp extends Model
{
    protected $fillable = [
        'ip_address', 'user_agent', 'isp', 'source', 'notes', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
