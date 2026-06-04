<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkinAnalysisPin extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'skin_analysis_pins';

    protected $fillable = [
        'scan_id',
        'user_id',
        'pin_type',
        'admin_note',
        'admin_note_ar',
        'pinned_at',
    ];

    protected $casts = [
        'pinned_at' => 'datetime',
    ];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(SkinScan::class, 'scan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFeatured($query)
    {
        return $query->where('pin_type', 'featured');
    }

    public function scopeShowcase($query)
    {
        return $query->where('pin_type', 'showcase');
    }
}
