@extends("admin.layouts.app")

@section("title", "إضافة شريحة جديدة")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">إضافة شريحة جديدة</h1>
        <p class="text-muted small mb-0">إنشاء شريحة احترافية للسلايدشو</p>
    </div>
    <a href="{{ route("admin.hero-slides.index") }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right"></i> العودة</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route("admin.hero-slides.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-pen" style="color:var(--pink-600);margin-left:8px;"></i> المحتوى النصي</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">العنوان (عربي) *</label>
                    <input type="text" name="title_ar" class="form-control @error("title_ar") is-invalid @enderror" value="{{ old("title_ar") }}" required>
                    @error("title_ar")<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">العنوان (إنجليزي)</label>
                    <input type="text" name="title_en" class="form-control" value="{{ old("title_en") }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">العنوان الفرعي (عربي)</label>
                    <input type="text" name="subtitle_ar" class="form-control" value="{{ old("subtitle_ar") }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">العنوان الفرعي (إنجليزي)</label>
                    <input type="text" name="subtitle_en" class="form-control" value="{{ old("subtitle_en") }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">الوصف (عربي)</label>
                    <textarea name="description_ar" class="form-control" rows="2">{{ old("description_ar") }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">الوصف (إنجليزي)</label>
                    <textarea name="description_en" class="form-control" rows="2">{{ old("description_en") }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">شارة (عربي)</label>
                    <input type="text" name="badge_text_ar" class="form-control" value="{{ old("badge_text_ar") }}" placeholder="جديد · خصم 30%">
                </div>
                <div class="col-md-4">
                    <label class="form-label">لون النص</label>
                    <div class="input-group">
                        <input type="color" name="text_color" class="form-control form-control-color" value="{{ old("text_color", "#262626") }}" style="max-width:44px;">
                        <input type="text" class="form-control" value="{{ old("text_color", "#262626") }}" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">محاذاة النص</label>
                    <select name="text_align" class="form-select">
                        <option value="right" {{ old("text_align") == "right" ? "selected" : "" }}>يمين</option>
                        <option value="center" {{ old("text_align") == "center" ? "selected" : "" }}>وسط</option>
                        <option value="left" {{ old("text_align") == "left" ? "selected" : "" }}>يسار</option>
                    </select>
                </div>
            </div>

            <h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-image" style="color:var(--pink-600);margin-left:8px;"></i> الوسائط</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="form-label">رفع صورة</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                    <div class="form-text">سيتم رفع الصورة إلى الخادم</div>
                </div>
                <div class="col-md-5">
                    <label class="form-label">أو رابط الصورة</label>
                    <input type="text" name="image" class="form-control" value="{{ old("image") }}" placeholder="https://... أو /storage/...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">مكان الصورة</label>
                    <select name="image_position" class="form-select">
                        <option value="right" {{ old("image_position") == "right" ? "selected" : "" }}>يمين النص</option>
                        <option value="left" {{ old("image_position") == "left" ? "selected" : "" }}>يسار النص</option>
                        <option value="background" {{ old("image_position") == "background" ? "selected" : "" }}>خلفية كاملة</option>
                        <option value="none" {{ old("image_position") == "none" ? "selected" : "" }}>بدون صورة</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">رابط فيديو (YouTube/Vimeo)</label>
                    <input type="text" name="video_url" class="form-control" value="{{ old("video_url") }}" placeholder="https://www.youtube.com/watch?v=...">
                </div>
                <div class="col-md-6">
                    <label class="form-label">شفافية الغطاء</label>
                    <input type="range" name="overlay_opacity" class="form-range" min="0" max="1" step="0.05" value="{{ old("overlay_opacity", 0.3) }}">
                    <div class="form-text">قيمة الغطاء الداكن فوق الخلفية (0 = شفاف، 1 = معتم بالكامل)</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">نوع الحركة</label>
                    <select name="animation_type" class="form-select">
                        <option value="fade" {{ old("animation_type") == "fade" ? "selected" : "" }}>تلاشي Fade</option>
                        <option value="slide" {{ old("animation_type") == "slide" ? "selected" : "" }}>انزلاق Slide</option>
                        <option value="zoom" {{ old("animation_type") == "zoom" ? "selected" : "" }}>تكبير Zoom</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="parallax" class="form-check-input" value="1" id="parallax" {{ old("parallax") ? "checked" : "" }}>
                        <label class="form-check-label" for="parallax">تأثير Parallax</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="full_width_image" class="form-check-input" value="1" id="full_width_image" {{ old("full_width_image") ? "checked" : "" }}>
                        <label class="form-check-label" for="full_width_image">صورة بعرض كامل</label>
                    </div>
                </div>
            </div>

            <h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-code" style="color:var(--pink-600);margin-left:8px;"></i> كود HTML مخصص <small class="text-muted">(اختياري - يتجاوز المحتوى أعلاه)</small></h5>
            <div class="mb-4">
                <textarea name="html_content" class="form-control font-monospace" rows="6" style="font-size:.85rem;" placeholder="<div class="my-custom-slide">...">{{ old("html_content") }}</textarea>
                <div class="form-text">أدخل كود HTML مخصص للشريحة. إذا تم ملء هذا الحقل، سيتم عرضه بدلاً من النص والصورة.</div>
            </div>

            <h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-link" style="color:var(--pink-600);margin-left:8px;"></i> الأزرار والروابط</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="form-label">نص الزر الأول (عربي)</label>
                    <input type="text" name="button_text_ar" class="form-control" value="{{ old("button_text_ar") }}" placeholder="تسوقي الآن">
                </div>
                <div class="col-md-5">
                    <label class="form-label">رابط الزر الأول</label>
                    <input type="text" name="button_url" class="form-control" value="{{ old("button_url") }}" placeholder="/shop">
                </div>
                <div class="col-md-5">
                    <label class="form-label">نص الزر الثاني (عربي)</label>
                    <input type="text" name="second_button_text_ar" class="form-control" value="{{ old("second_button_text_ar") }}" placeholder="طلب جملة">
                </div>
                <div class="col-md-5">
                    <label class="form-label">رابط الزر الثاني</label>
                    <input type="text" name="second_button_url" class="form-control" value="{{ old("second_button_url") }}" placeholder="/b2b">
                </div>
            </div>

            <h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-palette" style="color:var(--pink-600);margin-left:8px;"></i> التصميم والإعدادات</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">لون البداية</label>
                    <input type="color" name="gradient_from" class="form-control form-control-color" value="{{ old("gradient_from", "#FDF2F8") }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">لون النهاية</label>
                    <input type="color" name="gradient_to" class="form-control form-control-color" value="{{ old("gradient_to", "#FFF1F2") }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">الترتيب</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old("sort_order", 0) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">المنتج</label>
                    <select name="product_id" class="form-select">
                        <option value="">بدون</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old("product_id") == $product->id ? "selected" : "" }}>{{ $product->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">عرض المحتوى</label>
                    <select name="content_width" class="form-select">
                        <option value="container" {{ old("content_width") == "container" ? "selected" : "" }}>محدد</option>
                        <option value="container-fluid" {{ old("content_width") == "container-fluid" ? "selected" : "" }}>كامل</option>
                    </select>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" id="is_active" checked>
                        <label class="form-check-label" for="is_active">نشط</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-pink btn-lg"><i class="fas fa-save"></i> حفظ الشريحة</button>
            <a href="{{ route("admin.hero-slides.index") }}" class="btn btn-secondary">إلغاء</a>
        </form>
    </div>
</div>
@endsection
