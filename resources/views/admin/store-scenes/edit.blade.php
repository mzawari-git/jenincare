@extends('admin.layouts.app')

@section('title', 'تعديل المشهد')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">تعديل: {{ $storeScene->name_ar }}</h1>
    </div>
    <a href="{{ route('admin.store-scenes.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-right ms-1"></i> العودة
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.store-scenes.update', $storeScene) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">الاسم (عربي) *</label>
                    <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $storeScene->name_ar) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">الاسم (إنجليزي)</label>
                    <input type="text" name="name_en" class="form-control" value="{{ old('name_en', $storeScene->name_en) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">القسم</label>
                    <input type="text" name="section" class="form-control" value="{{ old('section', $storeScene->section) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">الممر / الرف</label>
                    <input type="text" name="aisle" class="form-control" value="{{ old('aisle', $storeScene->aisle) }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">رابط الصورة البانورامية *</label>
                    <input type="text" name="image_path" class="form-control" value="{{ old('image_path', $storeScene->image_path) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">الصورة المصغرة</label>
                    <input type="text" name="thumbnail" class="form-control" value="{{ old('thumbnail', $storeScene->thumbnail) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">رابط فيديو المعاينة</label>
                    <input type="text" name="video_path" class="form-control" value="{{ old('video_path', $storeScene->video_path) }}" placeholder="store-videos/walkthrough-1.mp4">
                    <small class="text-muted">فيديو قصير يعرض القسم (اختياري)</small>
                    @if($storeScene->video_path)
                    <div class="mt-2">
                        <video src="{{ Storage::url($storeScene->video_path) }}" muted controls style="max-height:80px;border-radius:4px;"></video>
                    </div>
                    @endif
                </div>
                <div class="col-md-3">
                    <label class="form-label">X على الخريطة</label>
                    <input type="number" name="map_x" class="form-control" value="{{ old('map_x', $storeScene->map_x) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Y على الخريطة</label>
                    <input type="number" name="map_y" class="form-control" value="{{ old('map_y', $storeScene->map_y) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">ترتيب الظهور</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $storeScene->sort_order) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" id="isActive" {{ $storeScene->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">نشط</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">الوصف (عربي)</label>
                    <textarea name="description_ar" class="form-control" rows="2">{{ old('description_ar', $storeScene->description_ar) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">الوصف (إنجليزي)</label>
                    <textarea name="description_en" class="form-control" rows="2">{{ old('description_en', $storeScene->description_en) }}</textarea>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-pink px-4"><i class="fas fa-save ms-1"></i> حفظ التغييرات</button>
            </div>
        </form>
    </div>
</div>
@endsection
