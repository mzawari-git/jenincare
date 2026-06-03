<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanHeatmapPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'scan_id', 'x', 'y', 'severity', 'label', 'label_ar',
        'description', 'description_ar',
    ];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(SkinScan::class, 'scan_id');
    }
}
