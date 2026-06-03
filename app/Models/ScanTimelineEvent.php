<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanTimelineEvent extends Model
{
    use HasFactory, HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'scan_id', 'status', 'description', 'description_ar', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(SkinScan::class, 'scan_id');
    }
}
