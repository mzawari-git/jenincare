<?php

namespace App\Models\Meta;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaAutomatedReport extends Model
{
    protected $fillable = [
        'name',
        'type',
        'metrics',
        'filters',
        'recipients',
        'format',
        'status',
        'send_time',
        'timezone',
        'last_sent_at',
        'next_send_at',
        'created_by',
    ];

    protected $casts = [
        'metrics' => 'array',
        'filters' => 'array',
        'recipients' => 'array',
        'last_sent_at' => 'datetime',
        'next_send_at' => 'datetime',
    ];
}
