@extends('admin.layouts.app')
@section('title', 'إدارة العملاء المحتملين')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-users" style="color:var(--pink-600);margin-left:8px;"></i> إدارة العملاء المحتملين</h1>
        <p class="text-muted small mb-0">تتبع التحويلات، النقاط التلقائية، والإشعارات الفورية</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success btn-sm" onclick="autoScoreLeads()">
            <i class="fas fa-calculator"></i> تحديث النقاط
        </button>
        <a href="{{ route('admin.meta-advanced.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right"></i> العودة
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="h3 mb-0 text-primary">{{ $stats['total_conversions'] }}</div>
                <div class="text-muted small">إجمالي التحويلات</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="h3 mb-0 text-success">{{ number_format($stats['total_value'], 0) }} ₪</div>
                <div class="text-muted small">القيمة الإجمالية</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="h3 mb-0 text-info">{{ number_format($stats['avg_days_to_convert'], 1) }}</div>
                <div class="text-muted small">متوسط أيام التحويل</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="h3 mb-0 text-warning">{{ array_sum($stats['by_type'] ?? []) }}</div>
                <div class="text-muted small">حسب النوع</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light fw-bold">
                <i class="fas fa-filter" style="color:var(--pink-600);margin-left:6px;"></i> قمع العملاء المحتملين
            </div>
            <div class="card-body">
                <div id="leadFunnelChart"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light fw-bold">
                <i class="fas fa-chart-pie" style="color:var(--pink-600);margin-left:6px;"></i> التحويلات حسب النوع
            </div>
            <div class="card-body">
                <div id="conversionTypeChart"></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light fw-bold">
        <i class="fas fa-trophy" style="color:var(--pink-600);margin-left:6px;"></i> أفضل الحملات تحويلاً
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الحملة</th>
                        <th>التحويلات</th>
                        <th>القيمة الإجمالية</th>
                        <th>متوسط أيام التحويل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCampaigns as $campaign)
                    <tr>
                        <td class="fw-bold">{{ $campaign['campaign_name'] }}</td>
                        <td>{{ $campaign['conversions'] }}</td>
                        <td>{{ number_format($campaign['total_value'], 0) }} ₪</td>
                        <td>{{ $campaign['avg_days'] }} يوم</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">لا توجد بيانات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
const CSRF = '{{ csrf_token() }}';
const BASE = '{{ url("/") }}';

var funnelData = @json($funnel);
var funnelOptions = {
    chart: { type: 'bar', height: 300, fontFamily: 'Tajawal, sans-serif' },
    series: [{ 
        name: 'العملاء', 
        data: [funnelData.new, funnelData.engaged, funnelData.warm, funnelData.hot, funnelData.converted] 
    }],
    colors: ['#94A3B8', '#3B82F6', '#F59E0B', '#EF4444', '#10B981'],
    plotOptions: { bar: { borderRadius: 4, horizontal: true } },
    dataLabels: { enabled: true },
    xaxis: { categories: ['جديد', 'متفاعل', 'دافئ', 'ساخن', 'محول'] },
    legend: { show: false }
};
new ApexCharts(document.getElementById('leadFunnelChart'), funnelOptions).render();

var typeData = @json($stats['by_type'] ?? []);
var typeLabels = Object.keys(typeData);
var typeValues = Object.values(typeData);
var typeLabelsAr = {
    'booking': 'حجز',
    'purchase': 'شراء',
    'signup': 'تسجيل',
    'call': 'اتصال',
    'form_submit': 'نموذج'
};

if (typeLabels.length > 0) {
    var typeOptions = {
        chart: { type: 'donut', height: 300, fontFamily: 'Tajawal, sans-serif' },
        series: typeValues,
        labels: typeLabels.map(l => typeLabelsAr[l] || l),
        colors: ['#1877F2', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
        legend: { position: 'bottom', labels: { fontFamily: 'Tajawal' } }
    };
    new ApexCharts(document.getElementById('conversionTypeChart'), typeOptions).render();
}

async function autoScoreLeads() {
    if (!confirm('هل تريد تحديث نقاط جميع العملاء المحتملين؟')) return;
    try {
        const res = await fetch(BASE + '/admin/meta-advanced/leads/auto-score', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const result = await res.json();
        alert(result.message);
    } catch (err) {
        alert('حدث خطأ');
    }
}
</script>
@endpush
@endsection
