<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'code', 'name', 'name_en', 'address', 'city', 'country',
        'latitude', 'longitude', 'phone', 'email', 'manager_name',
        'type', 'is_active', 'capacity', 'operating_hours', 'settings'
    ];
    protected $casts = [
        'operating_hours' => 'array',
        'settings' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'is_active' => 'boolean',
        'capacity' => 'integer'
    ];
}