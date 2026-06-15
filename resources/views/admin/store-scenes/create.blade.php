@extends('admin.layouts.app')

@section('title', 'إضافة مشهد جديد')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">إضافة مشهد بانورامي</h1>
        <p class="text-muted small mb-0">أضف مشهد 360° جديد للجولة الافتراضية</p>
    </div>
    <a href="{{ route('admin.store-scenes.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-right ms-1"></i> العودة
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.store-scenes.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">الاسم (عربي) *</label>
                    <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">الاسم (إنجليزي)</label>
                    <input type="text" name="name_en" class="form-control" value="{{ old('name_en') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">القسم</label>
                    <input type="text" name="section" class="form-control" value="{{ old('section') }}" placeholder="مثلاً: العناية بالبشرة">
                </div>
                <div class="col-md-6">
                    <label class="form-label">الممر / الرف</label>
                    <input type="text" name="aisle" class="form-control" value="{{ old('aisle') }}" placeholder="مثلاً: الممر 3 - الرف ب">
                </div>
                <div class="col-md-8">
                    <label class="form-label">رابط الصورة البانورامية *</label>
                    <input type="text" name="image_path" class="form-control @error('image_path') is-invalid @enderror" value="{{ old('image_path') }}" required placeholder="/storage/scenes/aisle1.jpg">
                </div>
                <div class="col-md-4">
                    <label class="form-label">الصورة المصغرة</label>
                    <input type="text" name="thumbnail" class="form-control" value="{{ old('thumbnail') }}" placeholder="/storage/scenes/thumbs/aisle1.jpg">
                </div>
                <div class="col-md-4">
                    <label class="form-label">رابط فيديو المعاينة</label>
                    <input type="text" name="video_path" class="form-control" value="{{ old('video_path') }}" placeholder="store-videos/walkthrough-1.mp4">
                    <small class="text-muted">فيديو قصير يعرض القسم (اختياري)</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">X على الخريطة</label>
                    <input type="number" name="map_x" class="form-control" value="{{ old('map_x') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Y على الخريطة</label>
                    <input type="number" name="map_y" class="form-control" value="{{ old('map_y') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">ترتيب الظهور</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" checked id="isActive">
                        <label class="form-check-label" for="isActive">نشط</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">الوصف (عربي)</label>
                    <textarea name="description_ar" class="form-control" rows="2">{{ old('description_ar') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">الوصف (إنجليزي)</label>
                    <textarea name="description_en" class="form-control" rows="2">{{ old('description_en') }}</textarea>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-pink px-4"><i class="fas fa-save ms-1"></i> حفظ</button>
            </div>
        </form>
    </div>
</div>
@endsection
