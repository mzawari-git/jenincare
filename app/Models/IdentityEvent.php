<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdentityEvent extends Model
{
    protected $fillable = [
        'uuid', 'event_type', 'url', 'referer',
        'utm_source', 'utm_medium', 'utm_campaign',
        'utm_term', 'utm_content',
        'fbclid', 'gclid', 'ttclid', 'twclid',
    ];

    public function identity(): BelongsTo
    {
        return $this->belongsTo(Identity::class, 'uuid', 'uuid');
    }
}
