<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RfqItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rfq_items';

    protected $fillable = [
        'rfq_id', 'product_id', 'product_name', 'product_description',
        'quantity', 'specifications', 'quoted_unit_price', 'quoted_total_price'
    ];

    protected $casts = [
        'specifications' => 'json',
    ];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
