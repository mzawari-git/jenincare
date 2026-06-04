<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'order_number', 'order_type', 'user_id', 'company_id',
        'customer_name', 'customer_email', 'customer_phone', 'customer_phone_secondary',
        'shipping_address', 'shipping_city', 'shipping_region', 'shipping_postal_code', 'shipping_country', 'shipping_notes',
        'shipping_latitude', 'shipping_longitude', 'billing_same_as_shipping', 'billing_address', 'billing_city', 'billing_region',
        'subtotal', 'discount_amount', 'discount_code', 'shipping_cost', 'tax_amount', 'total_amount', 'currency',
        'status', 'payment_status', 'payment_method', 'transaction_id', 'paid_at', 'paid_amount',
        'courier_service', 'tracking_number', 'tracking_url', 'shipped_at', 'delivered_at', 'estimated_delivery_days',
        'customer_notes', 'internal_notes', 'cancellation_reason', 'status_history',
        'created_by', 'confirmed_by', 'confirmed_at', 'source', 'utm_source', 'utm_medium', 'utm_campaign',
        'referrer', 'user_agent', 'ip_address', 'meta_capi_sent', 'meta_capi_sent_at', 'meta_capi_response',
        'customer_rating', 'customer_review', 'reviewed_at'
    ];

    protected $casts = [
        'status_history' => 'json',
        'meta_capi_response' => 'json',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'meta_capi_sent_at' => 'datetime',
        'billing_same_as_shipping' => 'boolean',
        'meta_capi_sent' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function delivery(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
    }
}
