<?php

namespace Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'name_en', 'description', 'base_price', 'sku', 'status', 'category_id'
    ];
    protected $casts = ['base_price' => 'float'];
}