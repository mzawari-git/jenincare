@extends('admin.layouts.app')
@section('title', 'محسن الجدول الزمني')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-calendar-alt" style="color:#1877F2;margin-left:8px;"></i> محسن الجدول الزمني</h4>
        <p class="text-muted small mb-0">أفضل أوقات وأيام عرض الإعلانات بناءً على تحليلات السوق</p>
    </div>
    <a href="{{ route('admin.meta-pro-tools.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right"></i> العودة</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light fw-bold"><i class="fas fa-calendar-week"></i> أفضل أيام الأسبوع</div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($bestTimes['days'] as $day => $info)
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3 text-center" style="background:{{ $info['score'] >= 80 ? '#ecfdf5' : ($info['score'] >= 70 ? '#fffbeb' : '#f8f9fa') }};">
                            <div class="fw-bold">{{ $info['name'] }}</div>
                            <div style="font-size:24px;font-weight:bold;color:{{ $info['score'] >= 80 ? '#10B981' : ($info['score'] >= 70 ? '#F59E0B' : '#6c757d') }};">{{ $info['score'] }}</div>
                            <small class="text-muted">درجة</small>
                            <div class="mt-1">
                                @foreach($info['best_hours'] as $h)
                                <span class="badge bg-light text-dark d-block mb-1">{{ $h }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light fw-bold"><i class="fas fa-clock"></i> التوصيات العامة</div>
            <div class="card-body">
                <ul class="mb-0">
                    @foreach($bestTimes['general_recommendations'] as $r)
                    <li class="py-1">{{ $r }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold"><i class="fas fa-table"></i> الجدول الأسبوعي المقترح</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>اليوم</th><th>الدرجة</th><th>الساعات النشطة</th><th>توزيع الميزانية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weeklySchedule['schedule'] as $day => $s)
                        <tr>
                            <td><b>{{ $s['name'] }}</b></td>
                            <td>
                                <div class="progress" style="height:8px;width:80px;">
                                    <div class="progress-bar bg-{{ $s['score'] >= 80 ? 'success' : ($s['score'] >= 70 ? 'warning' : 'secondary') }}" style="width:{{ $s['score'] }}%"></div>
                                </div>
                                <small>{{ $s['score'] }}/100</small>
                            </td>
                            <td>
                                @foreach($s['active_hours'] as $h)
                                <span class="badge bg-primary me-1">{{ $h }}</span>
                                @endforeach
                            </td>
                            <td><span class="badge bg-info">{{ $s['suggested_budget_distribution'] }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light small text-muted">
                <i class="fas fa-info-circle"></i> {{ $weeklySchedule['note'] }}
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light fw-bold"><i class="fas fa-bullhorn"></i> توصية لحملة محددة</div>
            <div class="card-body">
                <select class="form-select mb-3" onchange="if(this.value) getSchedule(this.value)">
                    <option value="">اختر حملة</option>
                    @foreach($campaigns as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                <div id="campaign-schedule-results" class="small">
                    <p class="text-muted">اختر حملة للحصول على توصيات مخصصة</p>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold"><i class="fas fa-lightbulb"></i> نصائح إضافية</div>
            <div class="card-body small">
                <ul class="mb-0">
                    <li class="py-1">جمعة وسبت هما الأفضل للإعلانات</li>
                    <li class="py-1">الـ 3 ساعات الأولى بعد النشر هي الأهم</li>
                    <li class="py-1">اختبر أوقاتاً مختلفة لمعرفة الأفضل لجمهورك</li>
                    <li class="py-1">استخدم ميزة الجدولة المسبقة للحملات</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
async function getSchedule(campaignId) {
    const el = document.getElementById('campaign-schedule-results');
    el.innerHTML = '<span class="spinner-border spinner-border-sm"></span> جاري التحميل...';
    try {
        const r = await fetch('{{ route('admin.meta-pro-tools.schedule-optimizer.campaign', '') }}/' + campaignId);
        const d = await r.json();
        let html = '<div class="border rounded p-3">';
        html += `<div class="mb-2"><b>الحملة:</b> ${d.campaign}</div>`;
        html += `<div class="mb-2"><b>الهدف:</b> ${d.objective}</div>`;
        if (d.specific_recommendations) {
            html += '<div class="mb-2"><b>التوصيات:</b><ul class="mb-0">';
            d.specific_recommendations.forEach(r => html += `<li class="small py-1">${r}</li>`);
            html += '</ul></div>';
        }
        html += '</div>';
        el.innerHTML = html;
    } catch (e) {
        el.innerHTML = '<span class="text-danger">فشل التحميل</span>';
    }
}
</script>
@endpush
@endsection
