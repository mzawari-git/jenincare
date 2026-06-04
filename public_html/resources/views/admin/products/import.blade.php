@extends('admin.layouts.app')

@section('title', 'استيراد منتجات من Excel')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">استيراد منتجات</h1>
        <p class="text-muted small mb-0">رفع ملف Excel لاستيراد المنتجات دفعة واحدة</p>
    </div>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right"></i> العودة للمنتجات</a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="fas fa-file-excel" style="color:var(--pink-600);margin-left:8px;"></i> رفع ملف Excel</div>
            <div class="card-body">
                <form action="{{ route('admin.products.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2">اختر ملف Excel (xlsx, xls, csv)</label>
                        <input type="file" name="file" class="form-control form-control-lg" accept=".xlsx,.xls,.csv" required style="padding:12px;">
                        <div class="form-text mt-2">الحد الأقصى لحجم الملف: 10 ميجابايت</div>
                    </div>

                    <div class="alert alert-info rounded-3 d-flex align-items-start gap-2 mb-4">
                        <i class="fas fa-info-circle mt-1" style="color:#0D6EFD;"></i>
                        <div>
                            <strong>تعليمات:</strong>
                            <ul class="mb-0 mt-1 small">
                                <li>يجب أن يحتوي الملف على صف عناوين (Header Row)</li>
                                <li>العمود الإلزامي الوحيد هو <code>name_ar</code> (اسم المنتج بالعربية)</li>
                                <li>باقي الأعمدة اختيارية وسيتم تجاهل الصفوف الفارغة</li>
                                <li>يمكن استخدام أسماء عربية أو إنجليزية للأعمدة</li>
                                <li>يمكّن النظام التعرف على الفئة والعلامة التجارية بالاسم</li>
                            </ul>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-pink btn-lg w-100">
                        <i class="fas fa-upload"></i> بدء الاستيراد
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-table" style="color:var(--pink-600);margin-left:8px;"></i> الأعمدة المدعومة (أي اسم من هذه)</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>البيان</th>
                                <th>أسماء الأعمدة المقبولة</th>
                                <th>مطلوب</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>اسم المنتج (عربي)</td><td><code>product_name_ar</code> · <code>product_name</code> · <code>name_ar</code> · <code>الاسم</code></td><td><span class="badge bg-danger">نعم</span></td></tr>
                            <tr><td>اسم المنتج (إنجليزي)</td><td><code>product_name_en</code> · <code>name_en</code></td><td><span class="badge bg-secondary">لا</span></td></tr>
                            <tr><td>الباركود / SKU</td><td><code>product_barcode</code> · <code>product_sku</code> · <code>sku</code> · <code>barcode</code></td><td><span class="badge bg-secondary">لا</span></td></tr>
                            <tr><td>الفئة</td><td><code>category</code> · <code>category_name</code> · <code>الفئة</code> · <code>التصنيف</code></td><td><span class="badge bg-secondary">لا</span></td></tr>
                            <tr><td>سعر التجزئة</td><td><code>product_price_1</code> · <code>b2c_price</code> · <code>السعر</code></td><td><span class="badge bg-secondary">لا</span></td></tr>
                            <tr><td>سعر الجملة</td><td><code>product_price_2</code> · <code>b2b_price</code></td><td><span class="badge bg-secondary">لا</span></td></tr>
                            <tr><td>سعر التكلفة</td><td><code>product_cost</code> · <code>cost_price</code></td><td><span class="badge bg-secondary">لا</span></td></tr>
                            <tr><td>الكمية</td><td><code>product_stock</code> · <code>stock_quantity</code> · <code>المخزون</code></td><td><span class="badge bg-secondary">لا</span></td></tr>
                            <tr><td>الوصف</td><td><code>product_description</code> · <code>description_ar</code> · <code>الوصف</code></td><td><span class="badge bg-secondary">لا</span></td></tr>
                            <tr><td>الصورة الرئيسية</td><td><code>product_image_url_1</code> · <code>main_image</code> · <code>الصورة</code></td><td><span class="badge bg-secondary">لا</span></td></tr>
                            <tr><td>صور إضافية</td><td><code>product_image_url_2</code> إلى <code>product_image_url_5</code></td><td><span class="badge bg-secondary">لا</span></td></tr>
                            <tr><td>الحالة</td><td><code>product_is_active</code> (1=نشط, 0=غير نشط) · <code>status</code></td><td><span class="badge bg-secondary">لا</span></td></tr>
                            <tr><td>العلامة التجارية</td><td><code>brand</code> · <code>brand_name</code> · <code>الماركة</code></td><td><span class="badge bg-secondary">لا</span></td></tr>
                            <tr><td>نسبة الخصم</td><td><code>discount_percentage</code> · <code>discount</code></td><td><span class="badge bg-secondary">لا</span></td></tr>
                            <tr><td>مميز</td><td><code>is_featured</code> · <code>featured</code> (1=نعم)</td><td><span class="badge bg-secondary">لا</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <a href="{{ route('admin.products.import.template') }}" class="btn btn-outline-pink w-100">
            <i class="fas fa-download"></i> تحميل قالب Excel جاهز
        </a>
    </div>
</div>
@endsection
