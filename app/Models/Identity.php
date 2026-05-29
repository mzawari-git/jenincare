<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Identity extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'ip_address', 'user_agent',
        'fingerprint_hash', 'fingerprint_data',
        'email_hash', 'phone_hash',
        'first_seen_at', 'last_seen_at',
    ];

    protected $casts = [
        'fingerprint_data' => 'json',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
