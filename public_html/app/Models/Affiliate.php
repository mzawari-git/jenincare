<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Affiliate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'email', 'phone', 'referral_code', 'discount_code',
        'status', 'tier_level', 'commission_type', 'commission_value',
        'wallet_balance', 'total_earned', 'total_paid', 'fraud_score',
        'settings', 'notes',
    ];

    protected $casts = [
        'wallet_balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'commission_value' => 'decimal:2',
        'settings' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clicks()
    {
        return $this->hasMany(AffiliateClick::class);
    }

    public function commissions()
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    public function payouts()
    {
        return $this->hasMany(AffiliatePayout::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getReferralLinkAttribute(): string
    {
        return url('/?ref=' . $this->referral_code);
    }

    public function getAvailableBalanceAttribute(): float
    {
        return (float) $this->wallet_balance;
    }

    public function getPendingBalanceAttribute(): float
    {
        return (float) $this->commissions()
            ->where('status', 'pending')
            ->sum('commission_amount');
    }

    public function getTotalClicksAttribute(): int
    {
        return $this->clicks()->count();
    }

    public function getTotalConversionsAttribute(): int
    {
        return $this->clicks()->where('converted', true)->count();
    }

    public function getConversionRateAttribute(): float
    {
        $clicks = $this->total_clicks;
        return $clicks > 0 ? round(($this->total_conversions / $clicks) * 100, 2) : 0;
    }
}
