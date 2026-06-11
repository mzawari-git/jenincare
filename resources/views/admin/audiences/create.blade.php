@extends('admin.layouts.app')
@section('title', 'إنشاء جمهور جديد')
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-users text-primary me-2"></i>إنشاء جمهور جديد
            </h4>
            <p class="text-muted mb-0 small">أنشئ جمهور مخصص أو مماثل لتحسين استهداف حملاتك</p>
        </div>
        <a href="{{ route('admin.audiences.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i>العودة
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.audiences.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="fw-bold small mb-1">اسم الجمهور</label>
                        <input class="form-control" name="name" required placeholder="مثال: عملاء مميزون - الرياض">
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold small mb-1">المنصة</label>
                        <select class="form-select" name="platform" required>
                            <option value="meta">Meta (فيسبوك/إنستغرام)</option>
                            <option value="google">Google Ads</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold small mb-1">المصدر</label>
                        <select class="form-select" name="source" required>
                            <option value="website">زيارات الموقع</option>
                            <option value="lookalike">مماثل (Lookalike)</option>
                            <option value="engagement">تفاعل</option>
                            <option value="lead_form">نماذج العملاء المحتملين</option>
                            <option value="capi">CAPI</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold small mb-1">الدولة</label>
                        <select class="form-select" name="country">
                            <option value="">الكل</option>
                            <option value="SA">السعودية</option>
                            <option value="AE">الإمارات</option>
                            <option value="QA">قطر</option>
                            <option value="KW">الكويت</option>
                            <option value="BH">البحرين</option>
                            <option value="OM">عمان</option>
                            <option value="JO">الأردن</option>
                            <option value="PS">فلسطين</option>
                            <option value="EG">مصر</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary"><i class="fas fa-save me-1"></i>إنشاء الجمهور</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
