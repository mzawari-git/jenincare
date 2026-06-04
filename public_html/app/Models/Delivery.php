<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'order_id', 'delivery_number',
        'status', 'driver_name', 'driver_phone', 'driver_vehicle',
        'courier_service', 'tracking_number', 'tracking_url',
        'delivery_address', 'delivery_city', 'delivery_region',
        'delivery_latitude', 'delivery_longitude', 'delivery_cost', 'delivery_zone',
        'estimated_delivery_days', 'assigned_at', 'picked_up_at',
        'in_transit_at', 'delivered_at', 'estimated_delivery_at',
        'delivery_attempted_at', 'delivery_attempts', 'failure_reason',
        'recipient_name', 'recipient_signature', 'recipient_relation',
        'cod_amount', 'cod_status', 'cod_collected_at',
        'delivery_notes', 'internal_notes', 'status_history',
        'assigned_by', 'cancelled_at',
    ];

    protected $casts = [
        'status_history' => 'json',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'in_transit_at' => 'datetime',
        'delivered_at' => 'datetime',
        'estimated_delivery_at' => 'datetime',
        'delivery_attempted_at' => 'datetime',
        'cod_collected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'delivery_cost' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'delivery_latitude' => 'decimal:8',
        'delivery_longitude' => 'decimal:8',
        'delivery_attempts' => 'integer',
        'estimated_delivery_days' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public static function generateDeliveryNumber(): string
    {
        return 'DLV-' . date('Ymd') . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'قيد الانتظار',
            'assigned' => 'تم التعيين',
            'picked_up' => 'تم الاستلام',
            'in_transit' => 'قيد النقل',
            'out_for_delivery' => 'قيد التوصيل',
            'delivered' => 'تم التوصيل',
            'attempted' => 'محاولة توصيل',
            'failed' => 'فشل التوصيل',
            'returned' => 'مرتجع',
            'cancelled' => 'ملغي',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'secondary',
            'assigned' => 'info',
            'picked_up', 'in_transit' => 'primary',
            'out_for_delivery' => 'warning',
            'delivered' => 'success',
            'attempted' => 'orange',
            'failed', 'cancelled' => 'danger',
            'returned' => 'dark',
            default => 'secondary',
        };
    }
}
