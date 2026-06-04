@extends('admin.layouts.app')
@section('title', 'إنشاء توصيل جديد')

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
.btn-pink { background: #d97a8c; color: #fff; border: none; }
.btn-pink:hover { background: #c7687a; color: #fff; }
.btn-outline-pink { border-color: #d97a8c; color: #d97a8c; }
.btn-outline-pink:hover { background: #d97a8c; color: #fff; }
.text-pink { color: #d97a8c !important; }
.info-row {
    display: flex; justify-content: space-between;
    padding: .4rem 0; border-bottom: 1px solid #f8fafc;
    font-size: .875rem;
}
.info-row:last-child { border-bottom: none; }
.info-label { color: var(--gray-500); font-weight: 500; }
.info-value { color: var(--gray-800); font-weight: 600; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.deliveries.index') }}" class="btn btn-sm btn-light rounded-pill">
            <i class="fas fa-arrow-right"></i>
        </a>
        <h3 class="fw-bold mb-0"><i class="fas fa-plus-circle text-pink"></i> إنشاء توصيل جديد</h3>
    </div>

    <form action="{{ route('admin.deliveries.store') }}" method="POST">
        @csrf

        {{-- Select Order --}}
        <div class="detail-card">
            <div class="card-header"><i class="fas fa-shopping-bag text-pink"></i> اختر الطلب</div>
            <div class="card-body">
                @if($order)
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                    <div class="alert bg-light rounded-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <strong>{{ $order->order_number }}</strong>
                                <span class="mx-2 text-muted">|</span>
                                <span>{{ $order->customer_name ?? $order->user->name ?? '—' }}</span>
                                <span class="mx-2 text-muted">|</span>
                                <span>{{ number_format($order->total_amount, 2) }} ₪</span>
                                <span class="mx-2 text-muted">|</span>
                                <span class="badge bg-info">{{ $order->status }}</span>
                            </div>
                            <a href="{{ route('admin.deliveries.create') }}" class="text-danger small">تغيير</a>
                        </div>
                    </div>
                @else
                    <div class="mb-3">
                        <label class="form-label fw-bold">رقم الطلب</label>
                        <select name="order_id" class="form-select rounded-pill @error('order_id') is-invalid @enderror" required>
                            <option value="">اختر الطلب...</option>
                            @foreach($pendingOrders as $pendOrder)
                                <option value="{{ $pendOrder->id }}" {{ old('order_id') == $pendOrder->id ? 'selected' : '' }}>
                                    {{ $pendOrder->order_number }} — {{ $pendOrder->customer_name ?? $pendOrder->user->name ?? '—' }} — {{ number_format($pendOrder->total_amount, 2) }} ₪
                                </option>
                            @endforeach
                        </select>
                        @error('order_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @endif
            </div>
        </div>

        {{-- Delivery Details --}}
        <div class="detail-card">
            <div class="card-header"><i class="fas fa-truck text-pink"></i> تفاصيل التوصيل</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">الحالة الأولية</label>
                        <select name="status" class="form-select rounded-pill">
                            <option value="pending">قيد الانتظار</option>
                            <option value="assigned">تم التعيين</option>
                            <option value="picked_up">تم الاستلام</option>
                            <option value="in_transit">قيد النقل</option>
                            <option value="out_for_delivery">قيد التوصيل</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">السائق</label>
                        <input type="text" name="driver_name" class="form-control rounded-pill" value="{{ old('driver_name') }}" placeholder="اسم السائق">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">هاتف السائق</label>
                        <input type="text" name="driver_phone" class="form-control rounded-pill" value="{{ old('driver_phone') }}" placeholder="رقم الهاتف">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">المركبة</label>
                        <input type="text" name="driver_vehicle" class="form-control rounded-pill" value="{{ old('driver_vehicle') }}" placeholder="نوع ورقم المركبة">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">شركة الشحن</label>
                        <input type="text" name="courier_service" class="form-control rounded-pill" value="{{ old('courier_service') }}" placeholder="اسم شركة الشحن">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">رقم التتبع</label>
                        <input type="text" name="tracking_number" class="form-control rounded-pill" value="{{ old('tracking_number') }}" placeholder="رقم التتبع">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">رابط التتبع</label>
                        <input type="url" name="tracking_url" class="form-control rounded-pill" value="{{ old('tracking_url') }}" placeholder="https://...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">تكلفة التوصيل</label>
                        <input type="number" step="0.01" name="delivery_cost" class="form-control rounded-pill" value="{{ old('delivery_cost', $order->shipping_cost ?? 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">أيام التوصيل</label>
                        <input type="number" name="estimated_delivery_days" class="form-control rounded-pill" value="{{ old('estimated_delivery_days', 3) }}" min="1">
                    </div>
                </div>
            </div>
        </div>

        {{-- Shipping Info --}}
        <div class="detail-card">
            <div class="card-header"><i class="fas fa-map-marker-alt text-pink"></i> عنوان التوصيل</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold">العنوان الكامل <span class="text-danger">*</span></label>
                        <input type="text" name="delivery_address" class="form-control rounded-pill @error('delivery_address') is-invalid @enderror"
                               value="{{ old('delivery_address', $order->shipping_address ?? '') }}" required>
                        @error('delivery_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">المدينة</label>
                        <input type="text" name="delivery_city" class="form-control rounded-pill" value="{{ old('delivery_city', $order->shipping_city ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">المنطقة</label>
                        <input type="text" name="delivery_region" class="form-control rounded-pill" value="{{ old('delivery_region', $order->shipping_region ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">منطقة التوصيل</label>
                        <input type="text" name="delivery_zone" class="form-control rounded-pill" value="{{ old('delivery_zone') }}" placeholder="شمال / جنوب / وسط">
                    </div>
                </div>
            </div>
        </div>

        {{-- More Info --}}
        <div class="detail-card">
            <div class="card-header"><i class="fas fa-info-circle text-pink"></i> معلومات إضافية</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">اسم المستلم</label>
                        <input type="text" name="recipient_name" class="form-control rounded-pill" value="{{ old('recipient_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">الدفع عند الاستلام</label>
                        <input type="number" step="0.01" name="cod_amount" class="form-control rounded-pill" value="{{ old('cod_amount', 0) }}" placeholder="0.00">
                    </div>
                    <div class="col-md-4">&nbsp;</div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">ملاحظات التوصيل</label>
                        <textarea name="delivery_notes" class="form-control rounded-3" rows="2" placeholder="ملاحظات خاصة بالتوصيل...">{{ old('delivery_notes') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">ملاحظات داخلية</label>
                        <textarea name="internal_notes" class="form-control rounded-3" rows="2" placeholder="ملاحظات داخلية للفريق...">{{ old('internal_notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-pink rounded-pill px-4">
                <i class="fas fa-save"></i> إنشاء عملية التوصيل
            </button>
            <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                إلغاء
            </a>
        </div>
    </form>
</div>
@endsection
