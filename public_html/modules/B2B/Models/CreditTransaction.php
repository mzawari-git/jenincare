<?php

namespace Modules\B2B\Models;

use Illuminate\Database\Eloquent\Model;

class CreditTransaction extends Model
{
    protected $fillable = ['company_id', 'type', 'amount', 'balance', 'reference', 'notes'];
    protected $casts = ['amount' => 'float', 'balance' => 'float'];
}