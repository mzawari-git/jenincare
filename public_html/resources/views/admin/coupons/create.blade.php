@extends('admin.layouts.app')

@section('title', 'إضافة كوبون')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.coupons.index') }}" class="text-muted text-decoration-none small"><i class="fas fa-arrow-right"></i> العودة</a>
    <h1 class="h4 mt-2">إضافة كوبون خصم جديد</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.coupons.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">كود الخصم <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control font-monospace" dir="ltr" value="{{ old('code') }}" required placeholder="SUMMER20">
                </div>
                <div class="col-md-4">
                    <label class="form-label">النوع <span class="text-danger">*</span></label>
                    <select name="type" class="form-select">
                        <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>نسبة مئوية (%)</option>
                        <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>قيمة ثابتة (₪)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">القيمة <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="value" class="form-control" value="{{ old('value') }}" required placeholder="20">
                </div>
                <div class="col-md-4">
                    <label class="form-label">الحد الأدنى للطلب</label>
                    <input type="number" step="0.01" name="min_order_amount" class="form-control" value="{{ old('min_order_amount') }}" placeholder="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">الحد الأقصى للاستخدام</label>
                    <input type="number" name="max_uses" class="form-control" value="{{ old('max_uses') }}" placeholder="100">
                </div>
                <div class="col-md-4">
                    <label class="form-label">تاريخ الانتهاء</label>
                    <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" id="is_active" checked>
                        <label class="form-check-label" for="is_active">مفعل</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-pink"><i class="fas fa-save"></i> حفظ</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
