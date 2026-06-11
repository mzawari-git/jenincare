<?php

namespace App\Models\Meta;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaAutomationRule extends Model
{
    protected $fillable = [
        'ad_account_id',
        'name',
        'type',
        'conditions',
        'actions',
        'status',
        'scope',
        'campaign_ids',
        'last_executed_at',
        'execution_count',
    ];

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
        'campaign_ids' => 'array',
        'last_executed_at' => 'datetime',
    ];

    public function adAccount(): BelongsTo
    {
        return $this->belongsTo(MetaAdAccount::class, 'ad_account_id');
    }
}
