<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateClick extends Model
{
    protected $fillable = [
        'affiliate_id', 'session_id', 'ip_address', 'user_agent',
        'device_hash', 'referral_code', 'utm_source', 'utm_medium',
        'utm_campaign', 'landing_page', 'converted', 'converted_at',
    ];

    protected $casts = [
        'converted' => 'boolean',
        'converted_at' => 'datetime',
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}
