<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'company_code', 'company_name_ar', 'company_name_en', 'company_type',
        'primary_contact_name', 'primary_contact_phone', 'primary_contact_email',
        'secondary_contact_phone', 'secondary_contact_email', 'whatsapp_number',
        'address', 'city', 'region', 'postal_code', 'country', 'latitude', 'longitude',
        'tax_id', 'commercial_registration', 'license_number', 'license_expiry_date',
        'credit_limit', 'current_balance', 'payment_terms_days', 'credit_approved',
        'credit_approved_by', 'credit_approved_at', 'default_discount_percentage',
        'tier_discounts', 'has_free_shipping', 'minimum_order_quantity', 'minimum_order_amount',
        'status', 'trust_score', 'average_order_value', 'total_orders', 'lifetime_value', 'last_order_date',
        'team_size', 'team_members', 'notes', 'documents', 'certifications', 'user_id', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'tier_discounts' => 'json',
        'team_members' => 'json',
        'documents' => 'json',
        'certifications' => 'json',
        'credit_approved_at' => 'datetime',
        'last_order_date' => 'datetime',
        'license_expiry_date' => 'date',
        'has_free_shipping' => 'boolean',
        'credit_approved' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function rfqs(): HasMany
    {
        return $this->hasMany(Rfq::class);
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
