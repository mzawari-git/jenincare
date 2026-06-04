<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSale extends Model
{
    protected $fillable = [
        'pos_sale_id', 'uuid', 'user_id', 'store_id',
        'customer_name', 'customer_email', 'customer_phone',
        'order_total', 'subtotal', 'currency', 'items',
        'payment_method', 'matched_to_online', 'sale_at',
    ];

    protected $casts = [
        'items' => 'json',
        'matched_to_online' => 'boolean',
        'sale_at' => 'datetime',
        'order_total' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
