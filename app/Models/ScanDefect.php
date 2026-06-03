<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ScanDefect extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'scan_id', 'name_ar', 'name_en', 'severity',
        'tip_ar', 'tip_en', 'icon_name',
    ];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(SkinScan::class, 'scan_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'scan_defect_products', 'defect_id', 'product_id')
            ->withPivot('matching_reason', 'matching_reason_ar')
            ->withTimestamps();
    }
}
