<?php

namespace Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseStock extends Model
{
    protected $fillable = [
        'warehouse_id', 'product_id', 'quantity', 'reserved_quantity',
        'low_stock_threshold', 'cost_price', 'location', 'metadata', 'last_updated_at'
    ];
    protected $casts = [
        'metadata' => 'array',
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'cost_price' => 'float'
    ];
}