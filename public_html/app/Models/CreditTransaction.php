<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'order_id', 'type', 'amount', 'balance_before', 'balance_after',
        'reference_number', 'description', 'notes', 'payment_method', 'processed_by', 'transaction_date'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
