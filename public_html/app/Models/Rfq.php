<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rfq extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rfqs';

    protected $fillable = [
        'tenant_id', 'rfq_number', 'company_id', 'user_id', 'title', 'description',
        'required_by_date', 'estimated_quantity', 'delivery_address', 'status',
        'quoted_price', 'quoted_total', 'quote_notes', 'quoted_by', 'quoted_at',
        'quote_valid_until', 'converted_order_id', 'converted_at', 'attachments',
        'customer_notes', 'internal_notes'
    ];

    protected $casts = [
        'attachments' => 'json',
        'quoted_at' => 'datetime',
        'converted_at' => 'datetime',
        'quote_valid_until' => 'date',
        'required_by_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RfqItem::class);
    }

    public static function generateRfqNumber(): string
    {
        return 'RFQ-' . date('Ymd') . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
    }
}
