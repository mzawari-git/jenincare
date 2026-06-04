@extends("admin.layouts.app")

@section("title", "تعديل شريحة")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1 class="h4 mb-1">تعديل شريحة</h1><p class="text-muted small mb-0">{{ $heroSlide->title_ar }}</p></div>
    <a href="{{ route("admin.hero-slides.index") }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right"></i> العودة</a>
</div>

<div class="card"><div class="card-body">
<form action="{{ route("admin.hero-slides.update", $heroSlide) }}" method="POST" enctype="multipart/form-data">@csrf @method("PUT")

<h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-pen" style="color:var(--pink-600);margin-left:8px;"></i> المحتوى النصي</h5>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label">العنوان (عربي) *</label>
        <input type="text" name="title_ar" class="form-control @error("title_ar") is-invalid @enderror" value="{{ old("title_ar", $heroSlide->title_ar) }}" required>
        @error("title_ar")<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">العنوان (إنجليزي)</label>
        <input type="text" name="title_en" class="form-control" value="{{ old("title_en", $heroSlide->title_en) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">العنوان الفرعي (عربي)</label>
        <input type="text" name="subtitle_ar" class="form-control" value="{{ old("subtitle_ar", $heroSlide->subtitle_ar) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">العنوان الفرعي (إنجليزي)</label>
        <input type="text" name="subtitle_en" class="form-control" value="{{ old("subtitle_en", $heroSlide->subtitle_en) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">الوصف (عربي)</label>
        <textarea name="description_ar" class="form-control" rows="2">{{ old("description_ar", $heroSlide->description_ar) }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">الوصف (إنجليزي)</label>
        <textarea name="description_en" class="form-control" rows="2">{{ old("description_en", $heroSlide->description_en) }}</textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label">شارة (عربي)</label>
        <input type="text" name="badge_text_ar" class="form-control" value="{{ old("badge_text_ar", $heroSlide->badge_text_ar) }}" placeholder="جديد · خصم 30%">
    </div>
    <div class="col-md-4">
        <label class="form-label">لون النص</label>
        <input type="color" name="text_color" class="form-control form-control-color" value="{{ old("text_color", $heroSlide->text_color ?: "#262626") }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">محاذاة النص</label>
        <select name="text_align" class="form-select">
            <option value="right" {{ ($heroSlide->text_align ?? "right") == "right" ? "selected" : "" }}>يمين</option>
            <option value="center" {{ ($heroSlide->text_align ?? "right") == "center" ? "selected" : "" }}>وسط</option>
            <option value="left" {{ ($heroSlide->text_align ?? "right") == "left" ? "selected" : "" }}>يسار</option>
        </select>
    </div>
</div>

<h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-image" style="color:var(--pink-600);margin-left:8px;"></i> الوسائط</h5>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label">رفع صورة جديدة</label>
        <input type="file" name="image_file" class="form-control" accept="image/*">
        @if($heroSlide->image)
        <div class="form-text mt-1">الصورة الحالية: <a href="{{ $heroSlide->image_url }}" target="_blank">عرض</a></div>
        @endif
    </div>
    <div class="col-md-4">
        <label class="form-label">أو رابط الصورة</label>
        <input type="text" name="image" class="form-control" value="{{ old("image", $heroSlide->image) }}" placeholder="https://...">
    </div>
    <div class="col-md-3">
        <label class="form-label">مكان الصورة</label>
        <select name="image_position" class="form-select">
            <option value="right" {{ ($heroSlide->image_position ?? "right") == "right" ? "selected" : "" }}>يمين النص</option>
            <option value="left" {{ ($heroSlide->image_position ?? "right") == "left" ? "selected" : "" }}>يسار النص</option>
            <option value="background" {{ ($heroSlide->image_position ?? "right") == "background" ? "selected" : "" }}>خلفية كاملة</option>
            <option value="none" {{ ($heroSlide->image_position ?? "right") == "none" ? "selected" : "" }}>بدون صورة</option>
        </select>
    </div>
    <div class="col-md-5">
        <label class="form-label">رابط فيديو (YouTube/Vimeo)</label>
        <input type="text" name="video_url" class="form-control" value="{{ old("video_url", $heroSlide->video_url) }}" placeholder="https://www.youtube.com/watch?v=...">
    </div>
    <div class="col-md-6">
        <label class="form-label">شفافية الغطاء ({{ old("overlay_opacity", $heroSlide->overlay_opacity ?? 0.3) }})</label>
        <input type="range" name="overlay_opacity" class="form-range" min="0" max="1" step="0.05" value="{{ old("overlay_opacity", $heroSlide->overlay_opacity ?? 0.3) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">نوع الحركة</label>
        <select name="animation_type" class="form-select">
            <option value="fade" {{ ($heroSlide->animation_type ?? "fade") == "fade" ? "selected" : "" }}>تلاشي Fade</option>
            <option value="slide" {{ ($heroSlide->animation_type ?? "fade") == "slide" ? "selected" : "" }}>انزلاق Slide</option>
            <option value="zoom" {{ ($heroSlide->animation_type ?? "fade") == "zoom" ? "selected" : "" }}>تكبير Zoom</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">&nbsp;</label>
        <div class="form-check mt-2">
            <input type="checkbox" name="parallax" class="form-check-input" value="1" id="parallax" {{ $heroSlide->parallax ? "checked" : "" }}>
            <label class="form-check-label" for="parallax">تأثير Parallax</label>
        </div>
        <div class="form-check">
            <input type="checkbox" name="full_width_image" class="form-check-input" value="1" id="fw" {{ $heroSlide->full_width_image ? "checked" : "" }}>
            <label class="form-check-label" for="fw">صورة بعرض كامل</label>
        </div>
    </div>
</div>

<h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-code" style="color:var(--pink-600);margin-left:8px;"></i> كود HTML مخصص</h5>
<div class="mb-4">
    <textarea name="html_content" class="form-control font-monospace" rows="6" style="font-size:.85rem;" placeholder="<div class="my-slide">{{ old("html_content", $heroSlide->html_content) }}">{{ old("html_content", $heroSlide->html_content) }}</textarea>
    <div class="form-text">أدخل كود HTML مخصص. إذا تم ملء هذا الحقل، سيتم عرضه بدلاً من النص والصورة.</div>
</div>

<h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-link" style="color:var(--pink-600);margin-left:8px;"></i> الأزرار والروابط</h5>
<div class="row g-3 mb-4">
    <div class="col-md-5">
        <label class="form-label">نص الزر الأول (عربي)</label>
        <input type="text" name="button_text_ar" class="form-control" value="{{ old("button_text_ar", $heroSlide->button_text_ar) }}" placeholder="تسوقي الآن">
    </div>
    <div class="col-md-5">
        <label class="form-label">رابط الزر الأول</label>
        <input type="text" name="button_url" class="form-control" value="{{ old("button_url", $heroSlide->button_url) }}" placeholder="/shop">
    </div>
    <div class="col-md-5">
        <label class="form-label">نص الزر الثاني (عربي)</label>
        <input type="text" name="second_button_text_ar" class="form-control" value="{{ old("second_button_text_ar", $heroSlide->second_button_text_ar) }}" placeholder="طلب جملة">
    </div>
    <div class="col-md-5">
        <label class="form-label">رابط الزر الثاني</label>
        <input type="text" name="second_button_url" class="form-control" value="{{ old("second_button_url", $heroSlide->second_button_url) }}" placeholder="/b2b">
    </div>
</div>

<h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-palette" style="color:var(--pink-600);margin-left:8px;"></i> التصميم</h5>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label">لون البداية</label>
        <input type="color" name="gradient_from" class="form-control form-control-color" value="{{ old("gradient_from", $heroSlide->gradient_from ?: "#FDF2F8") }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">لون النهاية</label>
        <input type="color" name="gradient_to" class="form-control form-control-color" value="{{ old("gradient_to", $heroSlide->gradient_to ?: "#FFF1F2") }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">الترتيب</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old("sort_order", $heroSlide->sort_order) }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">المنتج</label>
        <select name="product_id" class="form-select">
            <option value="">بدون</option>
            @foreach($products as $product)
            <option value="{{ $product->id }}" {{ old("product_id", $heroSlide->product_id) == $product->id ? "selected" : "" }}>{{ $product->name_ar }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">عرض المحتوى</label>
        <select name="content_width" class="form-select">
            <option value="container" {{ ($heroSlide->content_width ?? "container") == "container" ? "selected" : "" }}>محدد</option>
            <option value="container-fluid" {{ ($heroSlide->content_width ?? "container") == "container-fluid" ? "selected" : "" }}>كامل</option>
        </select>
    </div>
    <div class="col-12">
        <div class="form-check">
            <input type="checkbox" name="is_active" class="form-check-input" value="1" id="is_active" {{ $heroSlide->is_active ? "checked" : "" }}>
            <label class="form-check-label" for="is_active">نشط</label>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-pink btn-lg"><i class="fas fa-save"></i> تحديث الشريحة</button>
<a href="{{ route("admin.hero-slides.index") }}" class="btn btn-secondary">إلغاء</a>
</form>
</div></div>
@endsection
