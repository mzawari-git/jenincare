@extends('admin.layouts.app')
@section('title', 'منصة Meta المتكاملة')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">
            <i class="fab fa-facebook" style="color:#1877F2;margin-left:10px;"></i>
            منصة Meta المتكاملة
        </h1>
        <p class="text-muted small mb-0">جميع أدوات Facebook و Instagram و WhatsApp و Messenger في مكان واحد</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.ads.dashboard') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> إنشاء إعلان جديد
        </a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- ═══ القسم 1: لوحة التحكم الرئيسية ═══ --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="mb-4">
    <h5 class="fw-bold mb-3">
        <i class="fas fa-tachometer-alt text-primary"></i>
        لوحة التحكم الرئيسية
    </h5>
    <div class="row g-3">
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.meta-marketing.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-rocket fa-3x" style="color:#EC4899;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">التسويق عبر ميتا</h6>
                        <p class="text-muted small mb-0">أداء الحملات، CAPI، والإيرادات</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.roas.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-chart-bar fa-3x text-success"></i>
                        </div>
                        <h6 class="fw-bold mb-2">True ROAS</h6>
                        <p class="text-muted small mb-0">العائد الحقيقي على الإنفاق</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.meta-advanced.analytics') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-chart-line fa-3x text-info"></i>
                        </div>
                        <h6 class="fw-bold mb-2">التحليلات المتقدمة</h6>
                        <p class="text-muted small mb-0">قمع التحليلات والإسناد</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.meta-advanced.reports') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-file-alt fa-3x text-warning"></i>
                        </div>
                        <h6 class="fw-bold mb-2">التقارير الآلية</h6>
                        <p class="text-muted small mb-0">تقارير مجدولة بالبريد</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- ═══ القسم 2: إدارة الإعلانات ═══ --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="mb-4">
    <h5 class="fw-bold mb-3">
        <i class="fas fa-bullhorn text-danger"></i>
        إدارة الإعلانات
    </h5>
    <div class="row g-3">
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.ads.dashboard') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-ad fa-3x" style="color:#1877F2;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">إنشاء وإدارة الإعلانات</h6>
                        <p class="text-muted small mb-0">حملات، مجموعات إعلانية، وإعلانات</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.ai-creative.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-magic fa-3x text-purple"></i>
                        </div>
                        <h6 class="fw-bold mb-2">AI Creative</h6>
                        <p class="text-muted small mb-0">تصميمات إبداعية بالذكاء الاصطناعي</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.ab-tests.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-flask fa-3x text-info"></i>
                        </div>
                        <h6 class="fw-bold mb-2">A/B Testing</h6>
                        <p class="text-muted small mb-0">اختبار المقارنة للإعلانات</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.meta-advanced.creative') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-palette fa-3x text-success"></i>
                        </div>
                        <h6 class="fw-bold mb-2">تحسين التصميمات</h6>
                        <p class="text-muted small mb-0">كشف الإرهاق واقتراحات AI</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- ═══ القسم 3: العملاء المحتملين ═══ --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="mb-4">
    <h5 class="fw-bold mb-3">
        <i class="fas fa-user-friends text-warning"></i>
        العملاء المحتملين
    </h5>
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('admin.leads-hub.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-user-friends fa-3x" style="color:#F59E0B;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">مركز العملاء المحتملين</h6>
                        <p class="text-muted small mb-0">إدارة وتتبع جميع العملاء</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('admin.meta-advanced.leads') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-users-cog fa-3x text-primary"></i>
                        </div>
                        <h6 class="fw-bold mb-2">إدارة العملاء المتقدمة</h6>
                        <p class="text-muted small mb-0">تتبع التحويلات والنقاط التلقائية</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('admin.meta-advanced.targeting') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-crosshairs fa-3x text-danger"></i>
                        </div>
                        <h6 class="fw-bold mb-2">الاستهداف المتقدم</h6>
                        <p class="text-muted small mb-0">جماهير مشابهة وإعادة استهداف</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- ═══ القسم 4: التواصل ═══ --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="mb-4">
    <h5 class="fw-bold mb-3">
        <i class="fas fa-comments text-info"></i>
        التواصل
    </h5>
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('admin.meta-tools.conversations') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fab fa-facebook-messenger fa-3x" style="color:#006AFF;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">Messenger المحادثات</h6>
                        <p class="text-muted small mb-0">الرد على رسائل العملاء</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('admin.meta-tools.whatsapp') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fab fa-whatsapp fa-3x" style="color:#25D366;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">WhatsApp</h6>
                        <p class="text-muted small mb-0">إرسال رسائل WhatsApp</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('admin.meta-tools.instagram') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fab fa-instagram fa-3x" style="color:#E4405F;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">Instagram</h6>
                        <p class="text-muted small mb-0">إدارة حساب Instagram</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- ═══ القسم 5: الجماهير والاستهداف ═══ --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="mb-4">
    <h5 class="fw-bold mb-3">
        <i class="fas fa-bullseye text-success"></i>
        الجماهير والاستهداف
    </h5>
    <div class="row g-3">
        <div class="col-md-6 col-lg-6">
            <a href="{{ route('admin.audiences.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-bullseye fa-3x" style="color:#10B981;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">بناء الجماهير</h6>
                        <p class="text-muted small mb-0">إنشاء جماهير مخصصة ومشابهة</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-6">
            <a href="{{ route('admin.meta-tools.audience-upload') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-upload fa-3x text-primary"></i>
                        </div>
                        <h6 class="fw-bold mb-2">رفع بيانات الجمهور</h6>
                        <p class="text-muted small mb-0">رفع CSV وأرقام هواتف وإيميلات</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- ═══ القسم 6: الأتمتة والأدوات المتقدمة ═══ --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="mb-4">
    <h5 class="fw-bold mb-3">
        <i class="fas fa-robot text-purple"></i>
        الأتمتة والأدوات المتقدمة
    </h5>
    <div class="row g-3">
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.meta-advanced.automation') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-robot fa-3x text-info"></i>
                        </div>
                        <h6 class="fw-bold mb-2">الأتمتة</h6>
                        <p class="text-muted small mb-0">إيقاف تلقائي وتحجيم ميزانية</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.meta-advanced.compliance') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-shield-alt fa-3x text-warning"></i>
                        </div>
                        <h6 class="fw-bold mb-2">الامتثال والمراقبة</h6>
                        <p class="text-muted small mb-0">سياسات وصحة الحساب</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.meta-tools.pixel-helper') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-plug fa-3x text-primary"></i>
                        </div>
                        <h6 class="fw-bold mb-2">Pixel Helper</h6>
                        <p class="text-muted small mb-0">فحص Pixel و CAPI</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.diagnostics.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-stethoscope fa-3x text-danger"></i>
                        </div>
                        <h6 class="fw-bold mb-2">تشخيص CAPI</h6>
                        <p class="text-muted small mb-0">فحص أحداث CAPI</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- ═══ القسم 7: أدوات متطورة (NEW) ═══ --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="mb-4">
    <h5 class="fw-bold mb-3">
        <i class="fas fa-tools text-purple"></i>
        أدوات متطورة
        <span class="badge bg-danger" style="font-size:10px;vertical-align:middle;">جديد</span>
    </h5>
    <div class="row g-3">
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.meta-pro-tools.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-tools fa-3x" style="color:#8B5CF6;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">مركز الأدوات المتطورة</h6>
                        <p class="text-muted small mb-0">معاينة، توليد نصوص، تحسين الميزانية</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.meta-pro-tools.ad-preview', 1) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-eye fa-3x" style="color:#EC4899;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">معاينة الإعلان</h6>
                        <p class="text-muted small mb-0">شاهد إعلانك في جميع الأماكن</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.meta-pro-tools.copy-generator') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-magic fa-3x" style="color:#10B981;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">مولد النصوص</h6>
                        <p class="text-muted small mb-0">توليد نصوص إعلانية بالذكاء الاصطناعي</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.meta-pro-tools.budget-optimizer') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-chart-pie fa-3x" style="color:#F59E0B;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">محسن الميزانية</h6>
                        <p class="text-muted small mb-0">تحليل وتحسين عائد الإنفاق</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- ═══ القسم 8: التنبيهات والمراقبة ═══ --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="mb-4">
    <h5 class="fw-bold mb-3">
        <i class="fas fa-bell text-danger"></i>
        التنبيهات والمراقبة
    </h5>
    <div class="row g-3">
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.ad-alerts.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-bell fa-3x text-danger"></i>
                        </div>
                        <h6 class="fw-bold mb-2">تنبيهات الإعلانات</h6>
                        <p class="text-muted small mb-0">تنبيهات فورية للحملات</p>
                        @php $alertCount = \App\Models\AdAlert::unresolved()->unacknowledged()->count(); @endphp
                        @if($alertCount > 0)
                            <span class="badge bg-danger mt-2">{{ $alertCount }} تنبيه جديد</span>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.ad-alerts.pause-log') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-pause-circle fa-3x text-warning"></i>
                        </div>
                        <h6 class="fw-bold mb-2">سجل الإيقاف التلقائي</h6>
                        <p class="text-muted small mb-0">تتبع الحملات الموقوفة</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.ai-compliance.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-shield-alt fa-3x text-success"></i>
                        </div>
                        <h6 class="fw-bold mb-2">الامتثال AI</h6>
                        <p class="text-muted small mb-0">فحص الامتثال بالذكاء الاصطناعي</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('admin.trigger-words.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-card">
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-ban fa-3x text-dark"></i>
                        </div>
                        <h6 class="fw-bold mb-2">الكلمات الممنوعة</h6>
                        <p class="text-muted small mb-0">إدارة الكلمات المحظورة</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- ═══ القسم 9: الإعدادات ═══ --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="mb-4">
    <h5 class="fw-bold mb-3">
        <i class="fas fa-cogs text-secondary"></i>
        الإعدادات
    </h5>
    <div class="row g-3">
        <div class="col-md-6 col-lg-12">
            <a href="{{ route('admin.account-configuration.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm hover-card">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fas fa-cogs fa-2x text-secondary"></i>
                            <div>
                                <h6 class="fw-bold mb-1">إعدادات الحساب</h6>
                                <p class="text-muted small mb-0">تهيئة Pixel، CAPI، OAuth، وإعدادات المنصات</p>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
.hover-card {
    transition: all 0.3s ease;
    cursor: pointer;
}
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.12) !important;
}
</style>
@endpush
@endsection
