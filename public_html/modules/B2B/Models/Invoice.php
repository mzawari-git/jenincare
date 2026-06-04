<?php

namespace Modules\B2B\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['company_id', 'invoice_number', 'amount', 'due_date', 'status', 'items'];
    protected $casts = ['amount' => 'float', 'items' => 'array'];
}