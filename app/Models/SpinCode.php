<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpinCode extends Model
{
    protected $fillable = [
        'order_id',
        'customer_email',
        'code',
        'gift',
        'is_used',
        'used_at',
        'expires_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public static function generateUniqueCode(): string
    {
        $prefix = 'JEN';
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = $prefix . '-';
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
