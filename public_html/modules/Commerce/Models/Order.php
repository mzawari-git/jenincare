<?php

namespace Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'customer_id', 'status', 'total', 'subtotal',
        'tax', 'shipping_cost', 'discount', 'payment_method',
        'shipping_address', 'billing_address', 'notes'
    ];
    protected $casts = ['total' => 'float', 'subtotal' => 'float', 'tax' => 'float', 'shipping_cost' => 'float', 'discount' => 'float'];
}