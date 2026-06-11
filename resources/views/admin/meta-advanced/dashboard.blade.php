@extends('admin.layouts.app')
@section('title', 'Meta Advanced Tools')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-cogs" style="color:var(--pink-600);margin-left:8px;"></i> أدوات Meta المتقدمة</h1>
        <p class="text-muted small mb-0">تحليلات متقدمة، أتمتة، تحسين إبداعي، وإدارة الامتثال</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card-new">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted small">قواعد الأتمتة</span>
                <i class="fas fa-robot text-primary"></i>
            </div>
            <div class="stat-value-new">{{ $summary['automation_rules'] }}</div>
            <div class="small text-muted mt-1">قاعدة نشطة</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card-new">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted small">إجراءات مجدولة</span>
                <i class="fas fa-clock text-info"></i>
            </div>
            <div class="stat-value-new">{{ $summary['scheduled_actions'] }}</div>
            <div class="small text-muted mt-1">إجراء قيد الانتظار</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card-new">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted small">مشاكل الامتثال</span>
                <i class="fas fa-exclamation-triangle text-warning"></i>
            </div>
            <div class="stat-value-new">{{ $summary['compliance_issues'] }}</div>
            <div class="small text-muted mt-1">مشكلة مفتوحة</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card-new">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted small">تقارير آلية</span>
                <i class="fas fa-file-alt text-success"></i>
            </div>
            <div class="stat-value-new">{{ $summary['active_reports'] }}</div>
            <div class="small text-muted mt-1">تقرير نشط</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('admin.meta-advanced.analytics') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm hover-shadow">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-chart-line fa-3x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-2">التحليلات المتقدمة</h5>
                    <p class="text-muted small mb-0">قمع التحليلات، الإسناد، ومقارنة الفترات</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('admin.meta-advanced.automation') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm hover-shadow">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-robot fa-3x text-info"></i>
                    </div>
                    <h5 class="fw-bold mb-2">الأتمتة</h5>
                    <p class="text-muted small mb-0">قواعد الإيقاف التلقائي، تحجيم الميزانية، والجدولة</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('admin.meta-advanced.creative') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm hover-shadow">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-palette fa-3x text-success"></i>
                    </div>
                    <h5 class="fw-bold mb-2">تحسين التصميمات</h5>
                    <p class="text-muted small mb-0">كشف الإرهاق، اقتراحات AI، واختبار الفيديو</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('admin.meta-advanced.compliance') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm hover-shadow">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-shield-alt fa-3x text-warning"></i>
                    </div>
                    <h5 class="fw-bold mb-2">الامتثال والمراقبة</h5>
                    <p class="text-muted small mb-0">تنبيهات السياسات، صحة الحساب، وحدود الإنفاق</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('admin.meta-advanced.leads') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm hover-shadow">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-danger"></i>
                    </div>
                    <h5 class="fw-bold mb-2">إدارة العملاء المحتملين</h5>
                    <p class="text-muted small mb-0">تتبع التحويلات، النقاط التلقائية، والإشعارات الفورية</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('admin.meta-advanced.targeting') }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm hover-shadow">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-bullseye fa-3x text-purple"></i>
                    </div>
                    <h5 class="fw-bold mb-2">الاستهداف المتقدم</h5>
                    <p class="text-muted small mb-0">جماهير مشابهة، إعلانات ديناميكية، وإعادة الاستهداف</p>
                </div>
            </div>
        </a>
    </div>
</div>

@push('styles')
<style>
.hover-shadow { transition: all 0.3s ease; }
.hover-shadow:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
</style>
@endpush
@endsection
