<?php

namespace Modules\B2B\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'address', 'tax_number', 'contact_person', 'credit_limit', 'is_active'];
    protected $casts = ['credit_limit' => 'float', 'is_active' => 'boolean'];
}