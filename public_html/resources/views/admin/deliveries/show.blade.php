@extends('admin.layouts.app')
@section('title', 'تفاصيل التوصيل - ' . $delivery->delivery_number)

@push('styles')
<style>
.detail-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    margin-bottom: 1.5rem;
}
.detail-card .card-header {
    background: transparent;
    border-bottom: 1px solid #f1f5f9;
    padding: 1rem 1.25rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.detail-card .card-body { padding: 1.25rem; }
.info-row {
    display: flex;
    justify-content: space-between;
    padding: .5rem 0;
    border-bottom: 1px solid #f8fafc;
    font-size: .875rem;
}
.info-row:last-child { border-bottom: none; }
.info-label { color: var(--gray-500); font-weight: 500; }
.info-value { color: var(--gray-800); font-weight: 600; text-align: left; }
.timeline { position: relative; padding-right: 2rem; }
.timeline::before {
    content: '';
    position: absolute;
    right: 8px; top: 0; bottom: 0;
    width: 2px;
    background: #e2e8f0;
}
.timeline-item {
    position: relative;
    padding-bottom: 1.25rem;
}
.timeline-item:last-child { padding-bottom: 0; }
.timeline-item::before {
    content: '';
    position: absolute;
    right: -25px; top: 4px;
    width: 12px; height: 12px;
    border-radius: 50%;
    background: #d97a8c;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #d97a8c;
}
.timeline-item .time { font-size: .75rem; color: var(--gray-400); }
.timeline-item .status-text { font-size: .85rem; font-weight: 600; color: var(--gray-700); }
.timeline-item .status-note { font-size: .78rem; color: var(--gray-500); }
.btn-pink { background: #d97a8c; color: #fff; border: none; }
.btn-pink:hover { background: #c7687a; color: #fff; }
.btn-outline-pink { border-color: #d97a8c; color: #d97a8c; }
.btn-outline-pink:hover { background: #d97a8c; color: #fff; }
.text-pink { color: #d97a8c !important; }
.status-badge-lg {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: .9rem;
    font-weight: 700;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.deliveries.index') }}" class="btn btn-sm btn-light rounded-pill">
                    <i class="fas fa-arrow-right"></i>
                </a>
                <h3 class="fw-bold mb-0">{{ $delivery->delivery_number }}</h3>
                <span class="status-badge-lg bg-{{ $delivery->status_color }} text-white">
                    {{ $delivery->status_label }}
                </span>
            </div>
            <p class="text-muted mb-0 mt-1">تاريخ الإنشاء: {{ $delivery->created_at->format('Y-m-d H:i') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.show', $delivery->order) }}" class="btn btn-outline-secondary rounded-pill">
                <i class="fas fa-shopping-bag"></i> عرض الطلب
            </a>
            <a href="{{ route('admin.deliveries.edit', $delivery) }}" class="btn btn-outline-pink rounded-pill">
                <i class="fas fa-edit"></i> تعديل
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Left Column --}}
        <div class="col-lg-8">
            {{-- Status Update --}}
            <div class="detail-card">
                <div class="card-header">
                    <i class="fas fa-sync-alt text-pink"></i> تحديث الحالة
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.deliveries.update-status', $delivery) }}" method="POST">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">الحالة الجديدة</label>
                                <select name="status" class="form-select rounded-pill">
                                    <option value="pending" {{ $delivery->status == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                    <option value="assigned" {{ $delivery->status == 'assigned' ? 'selected' : '' }}>تم التعيين</option>
                                    <option value="picked_up" {{ $delivery->status == 'picked_up' ? 'selected' : '' }}>تم الاستلام</option>
                                    <option value="in_transit" {{ $delivery->status == 'in_transit' ? 'selected' : '' }}>قيد النقل</option>
                                    <option value="out_for_delivery" {{ $delivery->status == 'out_for_delivery' ? 'selected' : '' }}>قيد التوصيل</option>
                                    <option value="delivered" {{ $delivery->status == 'delivered' ? 'selected' : '' }}>تم التوصيل</option>
                                    <option value="attempted" {{ $delivery->status == 'attempted' ? 'selected' : '' }}>محاولة توصيل</option>
                                    <option value="failed" {{ $delivery->status == 'failed' ? 'selected' : '' }}>فشل التوصيل</option>
                                    <option value="returned" {{ $delivery->status == 'returned' ? 'selected' : '' }}>مرتجع</option>
                                    <option value="cancelled" {{ $delivery->status == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                </select>
                            </div>
                            @if($delivery->status == 'delivered')
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">اسم المستلم</label>
                                <input type="text" name="recipient_name" class="form-control rounded-pill" value="{{ $delivery->recipient_name }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">صلة القرابة</label>
                                <input type="text" name="recipient_relation" class="form-control rounded-pill" placeholder="مثال: الزوجة" value="{{ $delivery->recipient_relation }}">
                            </div>
                            @endif
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">ملاحظات</label>
                                <input type="text" name="notes" class="form-control rounded-pill" placeholder="ملاحظات على تغيير الحالة...">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-pink rounded-pill w-100">
                                    <i class="fas fa-check"></i> تحديث
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Order Items --}}
            <div class="detail-card">
                <div class="card-header">
                    <i class="fas fa-box text-pink"></i> عناصر الطلب
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">المنتج</th>
                                    <th>الكمية</th>
                                    <th>السعر</th>
                                    <th class="pe-3">المجموع</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($delivery->order->items as $item)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($item->product_image)
                                                <img src="{{ url('files/' . $item->product_image) }}" width="36" height="36" class="rounded-2" style="object-fit:cover">
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $item->product_name }}</div>
                                                <small class="text-muted">{{ $item->product_sku }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->unit_price, 2) }} ₪</td>
                                    <td class="pe-3 fw-bold">{{ number_format($item->total, 2) }} ₪</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-3">لا توجد عناصر</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th class="ps-3" colspan="3">الإجمالي</th>
                                    <th class="pe-3">{{ number_format($delivery->order->total_amount ?? 0, 2) }} ₪</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Status History --}}
            @if(!empty($delivery->status_history))
            <div class="detail-card">
                <div class="card-header">
                    <i class="fas fa-history text-pink"></i> سجل الحالات
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach(array_reverse(is_array($delivery->status_history) ? $delivery->status_history : json_decode($delivery->status_history, true) ?? []) as $entry)
                        <div class="timeline-item">
                            <div class="time">{{ $entry['timestamp'] ?? '' }}</div>
                            <div class="status-text">{{ $entry['status'] ?? '' }}</div>
                            @if(!empty($entry['notes']))
                                <div class="status-note">{{ $entry['notes'] }}</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column --}}
        <div class="col-lg-4">
            {{-- Order Info --}}
            <div class="detail-card">
                <div class="card-header">
                    <i class="fas fa-receipt text-pink"></i> معلومات الطلب
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="info-label">رقم الطلب</span>
                        <a href="{{ route('admin.orders.show', $delivery->order) }}" class="info-value text-pink text-decoration-none">
                            {{ $delivery->order->order_number ?? '—' }}
                        </a>
                    </div>
                    <div class="info-row">
                        <span class="info-label">حالة الطلب</span>
                        <span class="info-value">{{ $delivery->order->status ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">طريقة الدفع</span>
                        <span class="info-value">{{ $delivery->order->payment_method ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">حالة الدفع</span>
                        <span class="info-value">{{ $delivery->order->payment_status ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">إجمالي الطلب</span>
                        <span class="info-value">{{ number_format($delivery->order->total_amount ?? 0, 2) }} ₪</span>
                    </div>
                </div>
            </div>

            {{-- Customer Info --}}
            <div class="detail-card">
                <div class="card-header">
                    <i class="fas fa-user text-pink"></i> معلومات العميل
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="info-label">الاسم</span>
                        <span class="info-value">{{ $delivery->order->customer_name ?? $delivery->order->user->name ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">الهاتف</span>
                        <span class="info-value" dir="ltr">{{ $delivery->order->customer_phone ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">البريد الإلكتروني</span>
                        <span class="info-value" dir="ltr">{{ $delivery->order->customer_email ?? '—' }}</span>
                    </div>
                    @if($delivery->order->customer_phone_secondary)
                    <div class="info-row">
                        <span class="info-label">هاتف إضافي</span>
                        <span class="info-value" dir="ltr">{{ $delivery->order->customer_phone_secondary }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Delivery Details --}}
            <div class="detail-card">
                <div class="card-header">
                    <i class="fas fa-truck text-pink"></i> تفاصيل التوصيل
                </div>
                <div class="card-body">
                    @if($delivery->driver_name)
                    <div class="info-row">
                        <span class="info-label">السائق</span>
                        <span class="info-value">{{ $delivery->driver_name }}</span>
                    </div>
                    @endif
                    @if($delivery->driver_phone)
                    <div class="info-row">
                        <span class="info-label">هاتف السائق</span>
                        <span class="info-value" dir="ltr">{{ $delivery->driver_phone }}</span>
                    </div>
                    @endif
                    @if($delivery->driver_vehicle)
                    <div class="info-row">
                        <span class="info-label">المركبة</span>
                        <span class="info-value">{{ $delivery->driver_vehicle }}</span>
                    </div>
                    @endif
                    @if($delivery->courier_service)
                    <div class="info-row">
                        <span class="info-label">شركة الشحن</span>
                        <span class="info-value">{{ $delivery->courier_service }}</span>
                    </div>
                    @endif
                    @if($delivery->tracking_number)
                    <div class="info-row">
                        <span class="info-label">رقم التتبع</span>
                        <span class="info-value" dir="ltr">
                            @if($delivery->tracking_url)
                                <a href="{{ $delivery->tracking_url }}" target="_blank" class="text-pink">{{ $delivery->tracking_number }}</a>
                            @else
                                {{ $delivery->tracking_number }}
                            @endif
                        </span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">العنوان</span>
                        <span class="info-value">{{ $delivery->delivery_address ?: $delivery->order->shipping_address ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">المدينة</span>
                        <span class="info-value">{{ $delivery->delivery_city ?: $delivery->order->shipping_city ?? '—' }}</span>
                    </div>
                    @if($delivery->delivery_zone)
                    <div class="info-row">
                        <span class="info-label">المنطقة</span>
                        <span class="info-value">{{ $delivery->delivery_zone }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">تكلفة التوصيل</span>
                        <span class="info-value">{{ number_format($delivery->delivery_cost, 2) }} ₪</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">أيام التوصيل المقدرة</span>
                        <span class="info-value">{{ $delivery->estimated_delivery_days }} يوم</span>
                    </div>
                    @if($delivery->cod_amount > 0)
                    <div class="info-row">
                        <span class="info-label">الدفع عند الاستلام</span>
                        <span class="info-value">{{ number_format($delivery->cod_amount, 2) }} ₪</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">حالة التحصيل</span>
                        <span class="info-value">{{ $delivery->cod_status ?? '—' }}</span>
                    </div>
                    @endif
                    @if($delivery->recipient_name)
                    <div class="info-row">
                        <span class="info-label">المستلم</span>
                        <span class="info-value">{{ $delivery->recipient_name }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Timestamps --}}
            <div class="detail-card">
                <div class="card-header">
                    <i class="fas fa-clock text-pink"></i> التواريخ
                </div>
                <div class="card-body">
                    @if($delivery->assigned_at)
                    <div class="info-row">
                        <span class="info-label">تاريخ التعيين</span>
                        <span class="info-value">{{ $delivery->assigned_at->format('Y-m-d H:i') }}</span>
                    </div>
                    @endif
                    @if($delivery->picked_up_at)
                    <div class="info-row">
                        <span class="info-label">تاريخ الاستلام</span>
                        <span class="info-value">{{ $delivery->picked_up_at->format('Y-m-d H:i') }}</span>
                    </div>
                    @endif
                    @if($delivery->in_transit_at)
                    <div class="info-row">
                        <span class="info-label">تاريخ النقل</span>
                        <span class="info-value">{{ $delivery->in_transit_at->format('Y-m-d H:i') }}</span>
                    </div>
                    @endif
                    @if($delivery->delivery_attempted_at)
                    <div class="info-row">
                        <span class="info-label">آخر محاولة</span>
                        <span class="info-value">{{ $delivery->delivery_attempted_at->format('Y-m-d H:i') }}</span>
                    </div>
                    @endif
                    @if($delivery->delivered_at)
                    <div class="info-row">
                        <span class="info-label">تاريخ التوصيل</span>
                        <span class="info-value">{{ $delivery->delivered_at->format('Y-m-d H:i') }}</span>
                    </div>
                    @endif
                    @if($delivery->estimated_delivery_at)
                    <div class="info-row">
                        <span class="info-label">التوصيل المتوقع</span>
                        <span class="info-value">{{ $delivery->estimated_delivery_at->format('Y-m-d') }}</span>
                    </div>
                    @endif
                    @if($delivery->notes)
                    <div class="info-row">
                        <span class="info-label">ملاحظات</span>
                        <span class="info-value">{{ $delivery->notes }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Quick Assign Driver --}}
            @if(in_array($delivery->status, ['pending', 'assigned']))
            <div class="detail-card">
                <div class="card-header">
                    <i class="fas fa-user-plus text-pink"></i> تعيين سائق
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.deliveries.update-driver', $delivery) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">اسم السائق</label>
                            <input type="text" name="driver_name" class="form-control rounded-pill" required value="{{ $delivery->driver_name }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">رقم الهاتف</label>
                            <input type="text" name="driver_phone" class="form-control rounded-pill" value="{{ $delivery->driver_phone }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">المركبة</label>
                            <input type="text" name="driver_vehicle" class="form-control rounded-pill" placeholder="نوع ورقم المركبة" value="{{ $delivery->driver_vehicle }}">
                        </div>
                        <button type="submit" class="btn btn-pink rounded-pill w-100">
                            <i class="fas fa-check"></i> تعيين السائق
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Danger Zone --}}
            <div class="detail-card border-danger">
                <div class="card-header text-danger">
                    <i class="fas fa-trash"></i> منطقة الخطر
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.deliveries.destroy', $delivery) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف عملية التوصيل هذه؟')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger rounded-pill w-100">
                            <i class="fas fa-trash"></i> حذف عملية التوصيل
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
