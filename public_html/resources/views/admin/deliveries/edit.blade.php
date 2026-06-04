@extends('admin.layouts.app')
@section('title', 'تعديل التوصيل - ' . $delivery->delivery_number)

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
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.deliveries.show', $delivery) }}" class="btn btn-sm btn-light rounded-pill">
            <i class="fas fa-arrow-right"></i>
        </a>
        <h3 class="fw-bold mb-0"><i class="fas fa-edit text-pink"></i> تعديل التوصيل: {{ $delivery->delivery_number }}</h3>
    </div>

    <form action="{{ route('admin.deliveries.update', $delivery) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Delivery Details --}}
        <div class="detail-card">
            <div class="card-header"><i class="fas fa-truck text-pink"></i> تفاصيل التوصيل</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">السائق</label>
                        <input type="text" name="driver_name" class="form-control rounded-pill" value="{{ old('driver_name', $delivery->driver_name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">هاتف السائق</label>
                        <input type="text" name="driver_phone" class="form-control rounded-pill" value="{{ old('driver_phone', $delivery->driver_phone) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">المركبة</label>
                        <input type="text" name="driver_vehicle" class="form-control rounded-pill" value="{{ old('driver_vehicle', $delivery->driver_vehicle) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">شركة الشحن</label>
                        <input type="text" name="courier_service" class="form-control rounded-pill" value="{{ old('courier_service', $delivery->courier_service) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">رقم التتبع</label>
                        <input type="text" name="tracking_number" class="form-control rounded-pill" value="{{ old('tracking_number', $delivery->tracking_number) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">رابط التتبع</label>
                        <input type="url" name="tracking_url" class="form-control rounded-pill" value="{{ old('tracking_url', $delivery->tracking_url) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">تكلفة التوصيل</label>
                        <input type="number" step="0.01" name="delivery_cost" class="form-control rounded-pill" value="{{ old('delivery_cost', $delivery->delivery_cost) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">أيام التوصيل</label>
                        <input type="number" name="estimated_delivery_days" class="form-control rounded-pill" value="{{ old('estimated_delivery_days', $delivery->estimated_delivery_days) }}" min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">المستلم</label>
                        <input type="text" name="recipient_name" class="form-control rounded-pill" value="{{ old('recipient_name', $delivery->recipient_name) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">الدفع عند الاستلام</label>
                        <input type="number" step="0.01" name="cod_amount" class="form-control rounded-pill" value="{{ old('cod_amount', $delivery->cod_amount) }}">
                    </div>
                    @if($delivery->cod_amount > 0)
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">حالة تحصيل COD</label>
                        <select name="cod_status" class="form-select rounded-pill">
                            <option value="">—</option>
                            <option value="pending" {{ $delivery->cod_status == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                            <option value="collected" {{ $delivery->cod_status == 'collected' ? 'selected' : '' }}>تم التحصيل</option>
                            <option value="settled" {{ $delivery->cod_status == 'settled' ? 'selected' : '' }}>تم التسوية</option>
                            <option value="failed" {{ $delivery->cod_status == 'failed' ? 'selected' : '' }}>فشل التحصيل</option>
                        </select>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="detail-card">
            <div class="card-header"><i class="fas fa-map-marker-alt text-pink"></i> عنوان التوصيل</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold">العنوان الكامل</label>
                        <input type="text" name="delivery_address" class="form-control rounded-pill" value="{{ old('delivery_address', $delivery->delivery_address) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">المدينة</label>
                        <input type="text" name="delivery_city" class="form-control rounded-pill" value="{{ old('delivery_city', $delivery->delivery_city) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">المنطقة</label>
                        <input type="text" name="delivery_region" class="form-control rounded-pill" value="{{ old('delivery_region', $delivery->delivery_region) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">منطقة التوصيل</label>
                        <input type="text" name="delivery_zone" class="form-control rounded-pill" value="{{ old('delivery_zone', $delivery->delivery_zone) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="detail-card">
            <div class="card-header"><i class="fas fa-sticky-note text-pink"></i> ملاحظات</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold">ملاحظات التوصيل</label>
                        <textarea name="delivery_notes" class="form-control rounded-3" rows="2">{{ old('delivery_notes', $delivery->delivery_notes) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">ملاحظات داخلية</label>
                        <textarea name="internal_notes" class="form-control rounded-3" rows="2">{{ old('internal_notes', $delivery->internal_notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-pink rounded-pill px-4">
                <i class="fas fa-save"></i> حفظ التعديلات
            </button>
            <a href="{{ route('admin.deliveries.show', $delivery) }}" class="btn btn-outline-secondary rounded-pill px-4">
                إلغاء
            </a>
        </div>
    </form>
</div>
@endsection
