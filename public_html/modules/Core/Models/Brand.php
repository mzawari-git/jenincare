<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name', 'name_en', 'logo', 'website', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}