@extends('admin.layouts.app')

@section('title', 'تعديل SEO')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.seo.index') }}" class="text-muted text-decoration-none small"><i class="fas fa-arrow-right"></i> العودة لـ SEO</a>
    <h1 class="h4 mt-2">تعديل SEO: {{ $product->name_ar }}</h1>
</div>

<div class="row g-4">
    {{-- SEO Form --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-search" style="color:var(--pink-600);margin-left:6px;"></i> إعدادات SEO</div>
            <div class="card-body">
                <form action="{{ route('admin.seo.update', $product->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Title <small class="text-muted">(يظهر في نتائج البحث)</small></label>
                            <input type="text" name="meta_title" class="form-control font-monospace" dir="ltr" value="{{ old('meta_title', $product->meta_title) }}" maxlength="160" placeholder="Product Name - Category | JeninCare">
                            <small class="text-muted">الأفضل 50-60 حرفاً. الحالي: <span id="mtCount">{{ strlen($product->meta_title ?? '') }}</span></small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Title (بالعربية)</label>
                            <input type="text" name="meta_title_ar" class="form-control" value="{{ old('meta_title_ar', $product->meta_title) }}" maxlength="160">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Description <small class="text-muted">(وصف مختصر للبحث)</small></label>
                            <textarea name="meta_description" class="form-control font-monospace" dir="ltr" rows="3" maxlength="320" placeholder="وصف مختصر للمنتج...">{{ old('meta_description', $product->meta_description) }}</textarea>
                            <small class="text-muted">الأفضل 150-160 حرفاً. الحالي: <span id="mdCount">{{ strlen($product->meta_description ?? '') }}</span></small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Description (بالعربية)</label>
                            <textarea name="meta_description_ar" class="form-control" rows="2" maxlength="320">{{ old('meta_description_ar', $product->meta_description) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">الكلمات المفتاحية <small class="text-muted">(Meta Keywords - افصل بينها بفاصلة)</small></label>
                            @php
                                $kws = $product->meta_keywords;
                                if (is_string($kws)) {
                                    $decoded = json_decode($kws, true);
                                    if (is_array($decoded)) $kws = implode(', ', $decoded);
                                }
                            @endphp
                            <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', is_array($kws) ? implode(', ', $kws) : $kws) }}" placeholder="كلمة1, كلمة2, كلمة3...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">رابط صورة OG <small class="text-muted">(Open Graph - تظهر عند مشاركة الرابط)</small></label>
                            <input type="text" name="og_image" class="form-control" dir="ltr" value="{{ old('og_image', $product->og_image) }}" placeholder="https://...">
                            @if($product->og_image)
                            <div class="mt-2">
                                <img src="{{ $product->og_image }}" style="max-width:200px;max-height:120px;border-radius:8px;object-fit:contain;border:1px solid var(--gray-200);">
                            </div>
                            @endif
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-pink"><i class="fas fa-save"></i> حفظ SEO</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Preview --}}
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><i class="fab fa-google" style="color:var(--pink-600);margin-left:6px;"></i> معاينة Google</div>
            <div class="card-body">
                <div style="font-size:1.1rem;color:#1a0dab;line-height:1.3;margin-bottom:3px;word-break:break-word;">
                    {{ $product->meta_title ?: ($product->name_ar . ' - ' . ($product->category->name_ar ?? '') . ' | JeninCare') }}
                </div>
                <div style="font-size:.8rem;color:#006621;line-height:1.4;margin-bottom:2px;">
                    {{ url('/product/' . $product->slug) }}
                </div>
                <div style="font-size:.8rem;color:#545454;line-height:1.4;word-break:break-word;">
                    {{ \Str::limit($product->meta_description ?: strip_tags($product->description_ar ?? ''), 160) }}
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">معلومات المنتج</div>
            <div class="card-body">
                <div class="mb-2"><strong>الاسم:</strong> {{ $product->name_ar }}</div>
                <div class="mb-2"><strong>Slug:</strong> <code>{{ $product->slug }}</code></div>
                <div class="mb-2"><strong>التصنيف:</strong> {{ $product->category->name_ar ?? '—' }}</div>
                <div><strong>السعر:</strong> {{ number_format($product->b2c_price, 2) }} ₪</div>
            </div>
        </div>

        <form action="{{ route('admin.seo.auto', $product->id) }}" method="POST">
            @csrf
            <button class="btn btn-outline-info w-100"><i class="fas fa-magic"></i> توليد SEO تلقائياً</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelector('[name="meta_title"]').addEventListener('input', function() {
    document.getElementById('mtCount').textContent = this.value.length;
});
document.querySelector('[name="meta_description"]').addEventListener('input', function() {
    document.getElementById('mdCount').textContent = this.value.length;
});
</script>
@endpush
