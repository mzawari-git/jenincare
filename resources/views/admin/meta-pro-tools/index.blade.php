@extends('admin.layouts.app')
@section('title', 'أدوات Meta المتطورة')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">
            <i class="fas fa-tools" style="color:var(--pink-600);margin-left:10px;"></i>
            أدوات Meta المتطورة
        </h1>
        <p class="text-muted small mb-0">مجموعة متكاملة من الأدوات الاحترافية لتحسين إعلاناتك</p>
    </div>
</div>

<div class="row g-3">
    {{-- Ad Preview --}}
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('admin.meta-pro-tools.ad-preview', optional($creatives)->first() ?? 1) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 hover-card-pro">
                <div class="card-body text-center py-4">
                    <div class="mb-3"><i class="fas fa-eye fa-3x" style="color:#8B5CF6;"></i></div>
                    <h6 class="fw-bold mb-2">معاينة الإعلان</h6>
                    <p class="text-muted small mb-0">شاهد كيف سيبدو إعلانك على مختلف منصات Meta</p>
                    <span class="badge bg-light text-dark mt-2">Ads Preview</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Copy Generator --}}
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('admin.meta-pro-tools.copy-generator') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 hover-card-pro">
                <div class="card-body text-center py-4">
                    <div class="mb-3"><i class="fas fa-magic fa-3x" style="color:#EC4899;"></i></div>
                    <h6 class="fw-bold mb-2">مولد النصوص الإعلانية</h6>
                    <p class="text-muted small mb-0">توليد نصوص إعلانية احترافية ومتنوعة</p>
                    <span class="badge bg-light text-dark mt-2">AI Copy Generator</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Budget Optimizer --}}
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('admin.meta-pro-tools.budget-optimizer') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 hover-card-pro">
                <div class="card-body text-center py-4">
                    <div class="mb-3"><i class="fas fa-chart-pie fa-3x" style="color:#10B981;"></i></div>
                    <h6 class="fw-bold mb-2">محسن الميزانية</h6>
                    <p class="text-muted small mb-0">تحليل توزيع الميزانية وتحسين العائد على الإنفاق</p>
                    <span class="badge bg-light text-dark mt-2">Budget Optimizer</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Performance Forecast --}}
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('admin.meta-pro-tools.performance-forecast') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 hover-card-pro">
                <div class="card-body text-center py-4">
                    <div class="mb-3"><i class="fas fa-chart-line fa-3x" style="color:#6366f1;"></i></div>
                    <h6 class="fw-bold mb-2">التنبؤ بالأداء</h6>
                    <p class="text-muted small mb-0">توقعات الأداء المستقبلي للحملات الإعلانية</p>
                    <span class="badge bg-light text-dark mt-2">Forecast</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Placement Recommendations --}}
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('admin.meta-pro-tools.placement-recommendations') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 hover-card-pro">
                <div class="card-body text-center py-4">
                    <div class="mb-3"><i class="fas fa-map-pin fa-3x" style="color:#F59E0B;"></i></div>
                    <h6 class="fw-bold mb-2">توصيات أماكن الظهور</h6>
                    <p class="text-muted small mb-0">أفضل أماكن عرض الإعلانات حسب الهدف</p>
                    <span class="badge bg-light text-dark mt-2">Placements</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Schedule Optimizer --}}
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('admin.meta-pro-tools.schedule-optimizer') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 hover-card-pro">
                <div class="card-body text-center py-4">
                    <div class="mb-3"><i class="fas fa-calendar-alt fa-3x" style="color:#1877F2;"></i></div>
                    <h6 class="fw-bold mb-2">محسن الجدول الزمني</h6>
                    <p class="text-muted small mb-0">أفضل أوقات عرض الإعلانات حسب تحليلات السوق</p>
                    <span class="badge bg-light text-dark mt-2">Schedule</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Ad Library --}}
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('admin.meta-pro-tools.ad-library') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 hover-card-pro">
                <div class="card-body text-center py-4">
                    <div class="mb-3"><i class="fas fa-search fa-3x" style="color:#EF4444;"></i></div>
                    <h6 class="fw-bold mb-2">مكتبة الإعلانات</h6>
                    <p class="text-muted small mb-0">تحليل إعلانات المنافسين في السوق</p>
                    <span class="badge bg-light text-dark mt-2">Ad Library</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Pre-Flight Check --}}
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('admin.meta-advanced.compliance') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 hover-card-pro">
                <div class="card-body text-center py-4">
                    <div class="mb-3"><i class="fas fa-check-circle fa-3x" style="color:#22C55E;"></i></div>
                    <h6 class="fw-bold mb-2">فحص الامتثال</h6>
                    <p class="text-muted small mb-0">التأكد من مطابقة الإعلان لسياسات فيسبوك</p>
                    <span class="badge bg-light text-dark mt-2">Compliance</span>
                </div>
            </div>
        </a>
    </div>
</div>

@push('styles')
<style>
.hover-card-pro {
    transition: all 0.3s ease;
    cursor: pointer;
    border: 1px solid transparent;
}
.hover-card-pro:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.12) !important;
    border-color: var(--pink-200);
}
</style>
@endpush
@endsection
