@extends('admin.layouts.app')
@section('title', 'التحليلات المتقدمة')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-chart-line" style="color:var(--pink-600);margin-left:8px;"></i> التحليلات المتقدمة</h1>
        <p class="text-muted small mb-0">قمع التحليلات، نماذج الإسناد، ومقارنة الفترات الزمنية</p>
    </div>
    <a href="{{ route('admin.meta-advanced.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-right"></i> العودة
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label small">الفترة الزمنية</label>
        <select class="form-select form-select-sm" id="days-select" onchange="updateAnalytics()">
            <option value="7" {{ $days == 7 ? 'selected' : '' }}>آخر 7 أيام</option>
            <option value="14" {{ $days == 14 ? 'selected' : '' }}>آخر 14 يوم</option>
            <option value="30" {{ $days == 30 ? 'selected' : '' }}>آخر 30 يوم</option>
            <option value="90" {{ $days == 90 ? 'selected' : '' }}>آخر 90 يوم</option>
        </select>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach($comparisons as $metric => $data)
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">{{ $metric === 'purchases' ? 'المشتريات' : ($metric === 'revenue' ? 'الإيرادات' : 'النقرات') }}</span>
                    <span class="badge bg-{{ $data['trend'] === 'up' ? 'success' : ($data['trend'] === 'down' ? 'danger' : 'secondary') }}">
                        <i class="fas fa-arrow-{{ $data['trend'] === 'up' ? 'up' : ($data['trend'] === 'down' ? 'down' : 'right') }}"></i>
                        {{ abs($data['change']) }}%
                    </span>
                </div>
                <div class="h4 mb-1">{{ number_format($data['current'], $metric === 'revenue' ? 0 : 0) }}</div>
                <div class="text-muted small">السابق: {{ number_format($data['previous'], $metric === 'revenue' ? 0 : 0) }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card mb-4">
    <div class="card-header bg-light fw-bold">
        <i class="fas fa-filter" style="color:var(--pink-600);margin-left:6px;"></i> قمع التحليلات
    </div>
    <div class="card-body">
        <div class="row text-center">
            @php
                $stages = [
                    'impressions' => 'الظهور',
                    'clicks' => 'النقرات',
                    'landing_pages' => 'صفحات الهبوط',
                    'add_to_cart' => 'إضافة للسلة',
                    'checkouts' => 'الدفع',
                    'purchases' => 'المشتريات'
                ];
                $maxValue = max($funnel['funnel']);
            @endphp
            @foreach($stages as $key => $label)
            <div class="col-md-2 col-4 mb-3">
                <div class="funnel-stage">
                    <div class="funnel-bar" style="height: {{ $maxValue > 0 ? ($funnel['funnel'][$key] / $maxValue) * 100 : 0 }}%;"></div>
                    <div class="funnel-value">{{ number_format($funnel['funnel'][$key]) }}</div>
                    <div class="funnel-label">{{ $label }}</div>
                    @if(isset($funnel['conversion_rates'][$key]))
                    <div class="funnel-rate">{{ $funnel['conversion_rates'][$key] }}%</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-3">
            <span class="badge bg-primary fs-6">معدل التحويل الإجمالي: {{ $funnel['total_conversion_rate'] }}%</span>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light fw-bold">
                <i class="fas fa-trophy" style="color:var(--pink-600);margin-left:6px;"></i> أفضل الحملات أداءً
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>الحملة</th>
                                <th>المشتريات</th>
                                <th>الإيرادات</th>
                                <th>معدل التحويل</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCampaigns as $campaign)
                            <tr>
                                <td class="small">{{ $campaign['campaign_name'] }}</td>
                                <td>{{ $campaign['purchases'] }}</td>
                                <td>{{ number_format($campaign['revenue'], 0) }} ₪</td>
                                <td>
                                    <span class="badge bg-{{ $campaign['conversion_rate'] > 5 ? 'success' : ($campaign['conversion_rate'] > 2 ? 'warning' : 'secondary') }}">
                                        {{ $campaign['conversion_rate'] }}%
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">لا توجد بيانات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light fw-bold">
                <i class="fas fa-chart-pie" style="color:var(--pink-600);margin-left:6px;"></i> الإسناد حسب الحملة
            </div>
            <div class="card-body">
                <div id="attributionChart"></div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.funnel-stage { position: relative; padding: 20px 10px; }
.funnel-bar { 
    position: absolute; 
    bottom: 0; 
    left: 50%; 
    transform: translateX(-50%); 
    width: 60%; 
    background: linear-gradient(to top, var(--pink-600), #ff6b9d); 
    border-radius: 4px 4px 0 0;
    min-height: 10px;
    transition: height 0.5s ease;
}
.funnel-value { 
    position: relative; 
    font-size: 1.5rem; 
    font-weight: bold; 
    color: #333;
    margin-bottom: 5px;
}
.funnel-label { 
    position: relative; 
    font-size: 0.85rem; 
    color: #666;
    margin-top: 10px;
}
.funnel-rate { 
    position: relative; 
    font-size: 0.75rem; 
    color: var(--pink-600);
    margin-top: 5px;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
function updateAnalytics() {
    const days = document.getElementById('days-select').value;
    window.location.href = '{{ route("admin.meta-advanced.analytics") }}?days=' + days;
}

var attributionData = @json($attribution);
if (attributionData.length > 0) {
    var options = {
        chart: { type: 'donut', height: 300, fontFamily: 'Tajawal, sans-serif' },
        series: attributionData.map(a => a.revenue),
        labels: attributionData.map(a => a.campaign_name),
        colors: ['#1877F2', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'],
        legend: { position: 'bottom', labels: { fontFamily: 'Tajawal' } },
        plotOptions: { pie: { donut: { size: '60%' } } }
    };
    new ApexCharts(document.getElementById('attributionChart'), options).render();
}
</script>
@endpush
@endsection
