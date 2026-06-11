<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuspendedCart extends Model
{
    protected $fillable = [
        'user_id',
        'cart_data',
        'customer_name',
        'customer_phone',
        'customer_email',
        'payment_method',
        'notes',
        'item_count',
        'total',
    ];

    protected $casts = [
        'cart_data' => 'array',
        'total' => 'decimal:2',
        'item_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
