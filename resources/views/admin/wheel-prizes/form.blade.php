@extends('admin.layouts.app')

@section('title', isset($wheelPrize) ? 'تعديل العنصر' : 'إضافة عنصر جديد')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.wheel-prizes.index') }}" class="text-muted small text-decoration-none">
        <i class="fas fa-arrow-right me-1"></i> العودة إلى قائمة العناصر
    </a>
</div>

<div class="card" style="max-width:650px;">
    <div class="card-body">
        <h1 class="h5 mb-3">{{ isset($wheelPrize) ? 'تعديل العنصر' : 'إضافة عنصر جديد' }}</h1>

        <form action="{{ isset($wheelPrize) ? route('admin.wheel-prizes.update', $wheelPrize) : route('admin.wheel-prizes.store') }}"
              method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($wheelPrize)) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label small fw-bold">نوع العنصر <span class="text-danger">*</span></label>
                <select name="type" class="form-select" id="prizeType" onchange="toggleTypeFields()">
                    <option value="product" {{ old('type', $wheelPrize->type ?? 'product') === 'product' ? 'selected' : '' }}>منتج / جائزة</option>
                    <option value="discount" {{ old('type', $wheelPrize->type ?? '') === 'discount' ? 'selected' : '' }}>نسبة خصم</option>
                </select>
            </div>

            <div class="mb-3" id="nameField">
                <label class="form-label small fw-bold">الاسم <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $wheelPrize->name ?? '') }}" placeholder="مثال: عطر مسك وايت">
            </div>

            <div class="mb-3" id="discountField" style="display:none;">
                <label class="form-label small fw-bold">نسبة الخصم (%) <span class="text-danger">*</span></label>
                <div class="d-flex gap-2 align-items-center">
                    <select name="discount_percent" class="form-select" style="width:150px;">
                        <option value="">اختر النسبة</option>
                        @foreach([5, 10, 15, 20, 25, 30, 40, 50] as $pct)
                        <option value="{{ $pct }}" {{ old('discount_percent', $wheelPrize->discount_percent ?? '') == $pct ? 'selected' : '' }}>{{ $pct }}%</option>
                        @endforeach
                    </select>
                    <span class="text-muted small">خصم على أي منتج من المتجر</span>
                </div>
            </div>

            <div class="mb-3" id="imageField">
                <label class="form-label small fw-bold">صورة العنصر</label>
                @if(isset($wheelPrize) && $wheelPrize->image_url)
                <div class="mb-2">
                    <img src="{{ $wheelPrize->image_url }}" style="width:80px;height:80px;border-radius:12px;object-fit:cover;border:2px solid var(--gray-200);">
                </div>
                @endif
                <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                <div class="form-text small">يُفضل صورة مربعة. الحد الأقصى 2MB.</div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">اللون <span class="text-danger">*</span></label>
                <div class="d-flex align-items-center gap-2">
                    <input type="color" name="color" class="form-control form-control-color p-0" style="width:60px;height:42px;cursor:pointer;"
                           value="{{ old('color', $wheelPrize->color ?? '#6366f1') }}">
                    <input type="text" class="form-control" style="width:120px;font-family:monospace;"
                           value="{{ old('color', $wheelPrize->color ?? '#6366f1') }}" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">نسبة الفوز (وزن)</label>
                <input type="number" name="weight" class="form-control" style="width:150px;"
                       value="{{ old('weight', $wheelPrize->weight ?? 1) }}" min="1" max="10000">
                <div class="form-text small">كلما زاد الرقم، زادت فرصة الفوز بهذه الجائزة</div>
            </div>

            <div class="mb-3 form-check form-switch">
                <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1"
                    {{ old('is_active', $wheelPrize->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label small" for="isActive">نشط (يظهر في الدولاب)</label>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> {{ isset($wheelPrize) ? 'حفظ التغييرات' : 'إضافة' }}
            </button>
        </form>
    </div>
</div>

<script>
function toggleTypeFields() {
    const type = document.getElementById('prizeType').value;
    document.getElementById('nameField').style.display = type === 'product' ? 'block' : 'none';
    document.getElementById('discountField').style.display = type === 'discount' ? 'block' : 'none';
    document.getElementById('imageField').style.display = type === 'product' ? 'block' : 'none';
}
toggleTypeFields();
</script>
@endsection
