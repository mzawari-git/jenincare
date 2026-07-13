<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'device_id',
        'is_active',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_admin' => 'boolean',
    ];

    public function skinAnalyses(): HasMany
    {
        return $this->hasMany(SkinAnalysis::class)->orderByDesc('created_at');
    }

    public function approvedAnalyses(): HasMany
    {
        return $this->hasMany(SkinAnalysis::class)
            ->where('status', 'approved')
            ->orderByDesc('created_at');
    }

    public function latestAnalysis(): ?SkinAnalysis
    {
        return $this->skinAnalyses()->first();
    }

    public function hasPendingAnalysis(): bool
    {
        return $this->skinAnalyses()->pending()->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
