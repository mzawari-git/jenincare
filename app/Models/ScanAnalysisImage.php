<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanAnalysisImage extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'scan_analysis_images';

    protected $fillable = [
        'scan_id',
        'mode',
        'image_path',
        'analysis',
    ];

    protected $casts = [
        'analysis' => 'array',
    ];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(SkinScan::class, 'scan_id');
    }
}
