<?php

namespace App\Models\Meta;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaSpendingLimit extends Model
{
    protected $fillable = [
        'ad_account_id',
        'scope',
        'entity_id',
        'daily_limit',
        'lifetime_limit',
        'current_spend',
        'alert_threshold',
        'action_on_limit',
        'status',
        'reset_at',
    ];

    protected $casts = [
        'daily_limit' => 'decimal:2',
        'lifetime_limit' => 'decimal:2',
        'current_spend' => 'decimal:2',
        'alert_threshold' => 'decimal:2',
        'reset_at' => 'datetime',
    ];

    public function adAccount(): BelongsTo
    {
        return $this->belongsTo(MetaAdAccount::class, 'ad_account_id');
    }
}
