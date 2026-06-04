<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanAuditLog extends Model
{
    protected $table = 'scan_audit_logs';

    protected $fillable = [
        'scan_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function scan()
    {
        return $this->belongsTo(SkinScan::class, 'scan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $scanId, string $action, ?array $metadata = null): self
    {
        return static::create([
            'scan_id' => $scanId,
            'user_id' => auth()->id(),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
