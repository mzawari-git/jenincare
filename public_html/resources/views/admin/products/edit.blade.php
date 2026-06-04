@extends('admin.layouts.app')

@section('title', 'تعديل المنتج')

@section('content')
<h1 class="h4 mb-4">تعديل المنتج</h1>
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.products.update', $product) }}" method="POST">
            @csrf @method('PUT')
            <ul class="nav nav-tabs mb-3" id="productTabs">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#basic">معلومات أساسية</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#pricing">التسعير</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#inventory">المخزون</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#seo"><i class="fas fa-search"></i> SEO</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="basic">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">الاسم (بالعربية) *</label>
                            <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror" value="{{ old('name_ar', $product->name_ar) }}" required>
                            @error('name_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الاسم (بالإنجليزية)</label>
                            <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror" value="{{ old('name_en', $product->name_en) }}">
                            @error('name_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الرابط (Slug)</label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $product->slug) }}">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">رمز المنتج (SKU)</label>
                            <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}">
                            @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">التصنيف</label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">اختر تصنيفاً</option>
                                @foreach(\App\Models\Category::all() as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">العلامة التجارية</label>
                            <select name="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
                                <option value="">اختر علامة تجارية</option>
                                @foreach(\Modules\Core\Models\Brand::all() as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            @error('brand_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">الوصف (بالعربية)</label>
                            <textarea name="description_ar" class="form-control @error('description_ar') is-invalid @enderror" rows="4">{{ old('description_ar', $product->description_ar) }}</textarea>
                            @error('description_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">الوصف (بالإنجليزية)</label>
                            <textarea name="description_en" class="form-control @error('description_en') is-invalid @enderror" rows="4">{{ old('description_en', $product->description_en) }}</textarea>
                            @error('description_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">رابط الصورة الرئيسية</label>
                            <input type="text" name="main_image" class="form-control @error('main_image') is-invalid @enderror" value="{{ old('main_image', $product->main_image) }}">
                            @error('main_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="pricing">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">سعر التجزئة (B2C) *</label>
                            <input type="number" step="0.01" name="b2c_price" class="form-control @error('b2c_price') is-invalid @enderror" value="{{ old('b2c_price', $product->b2c_price) }}">
                            @error('b2c_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">سعر الجملة (B2B)</label>
                            <input type="number" step="0.01" name="b2b_price" class="form-control @error('b2b_price') is-invalid @enderror" value="{{ old('b2b_price', $product->b2b_price) }}">
                            @error('b2b_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">سعر التكلفة</label>
                            <input type="number" step="0.01" name="cost_price" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price', $product->cost_price) }}">
                            @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">نسبة الخصم %</label>
                            <input type="number" step="0.01" name="discount_percentage" class="form-control" value="{{ old('discount_percentage', $product->discount_percentage ?? 0) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">قيمة الخصم</label>
                            <input type="number" step="0.01" name="discount_amount" class="form-control" value="{{ old('discount_amount', $product->discount_amount ?? 0) }}">
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="inventory">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">كمية المخزون</label>
                            <input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">تنبيه المخزون المنخفض</label>
                            <input type="number" name="low_stock_alert" class="form-control" value="{{ old('low_stock_alert', $product->low_stock_alert ?? 10) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">حالة المخزون</label>
                            <select name="stock_status" class="form-select">
                                <option value="in_stock" {{ $product->stock_status === 'in_stock' ? 'selected' : '' }}>متوفر</option>
                                <option value="low_stock" {{ $product->stock_status === 'low_stock' ? 'selected' : '' }}>مخزون منخفض</option>
                                <option value="out_of_stock" {{ $product->stock_status === 'out_of_stock' ? 'selected' : '' }}>غير متوفر</option>
                                <option value="pre_order" {{ $product->stock_status === 'pre_order' ? 'selected' : '' }}>طلب مسبق</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch d-inline-block me-3">
                                <input type="checkbox" name="track_inventory" class="form-check-input" value="1" id="track_inv" {{ $product->track_inventory ? 'checked' : '' }}>
                                <label class="form-check-label" for="track_inv">تتبع المخزون</label>
                            </div>
                            <div class="form-check form-switch d-inline-block">
                                <input type="checkbox" name="allow_backorder" class="form-check-input" value="1" id="allow_bo" {{ $product->allow_backorder ? 'checked' : '' }}>
                                <label class="form-check-label" for="allow_bo">السماح بالطلب المسبق</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="seo">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Title</label>
                            <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $product->meta_title) }}" maxlength="160" placeholder="اسم المنتج - التصنيف | JeninCare">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="2" maxlength="320" placeholder="وصف مختصر للمنتج...">{{ old('meta_description', $product->meta_description) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">الكلمات المفتاحية</label>
                            @php
                                $editKws = $product->meta_keywords;
                                if (is_string($editKws)) {
                                    $decoded = json_decode($editKws, true);
                                    if (is_array($decoded)) $editKws = implode(', ', $decoded);
                                }
                            @endphp
                            <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $editKws) }}" placeholder="كلمة1, كلمة2, كلمة3...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">رابط صورة OG</label>
                            <input type="text" name="og_image" class="form-control" value="{{ old('og_image', $product->og_image) }}" placeholder="https://...">
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <div class="form-check form-switch d-inline-block me-3">
                        <input type="checkbox" name="status" class="form-check-input" value="active" id="status" {{ $product->status === 'active' ? 'checked' : '' }}>
                        <label class="form-check-label" for="status">نشط</label>
                    </div>
                    <div class="form-check form-switch d-inline-block me-3">
                        <input type="checkbox" name="is_featured" class="form-check-input" value="1" id="is_featured" {{ $product->is_featured ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_featured">مميز</label>
                    </div>
                    <div class="form-check form-switch d-inline-block me-3">
                        <input type="checkbox" name="is_new" class="form-check-input" value="1" id="is_new" {{ $product->is_new ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_new">جديد</label>
                    </div>
                    <div class="form-check form-switch d-inline-block">
                        <input type="checkbox" name="is_bestseller" class="form-check-input" value="1" id="is_bestseller" {{ $product->is_bestseller ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_bestseller">الأكثر مبيعاً</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-pink">تحديث</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">إلغاء</a>
        </form>
    </div>
</div>
@endsection
