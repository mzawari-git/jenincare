<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'name', 'email', 'password', 'phone', 'avatar',
        'role', 'is_active', 'phone_verified_at', 'last_login_at', 'last_login_ip', 'settings',
        'security_question', 'security_answer', 'identity_uuid'
    ];

    protected $hidden = ['password', 'remember_token', 'security_answer'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'settings' => 'json',
        'is_active' => 'boolean',
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class)->where('is_active', true);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function skinScans(): HasMany
    {
        return $this->hasMany(SkinScan::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function hasProvider(string $provider): bool
    {
        return $this->socialAccounts()->where('provider', $provider)->exists();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isB2B(): bool
    {
        return $this->role === 'b2b';
    }
}
