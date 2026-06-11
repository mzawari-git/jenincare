@extends('admin.layouts.app')
@section('title', 'التنبؤ بأداء الحملات')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-chart-line" style="color:#6366f1;margin-left:8px;"></i> التنبؤ بأداء الحملات</h4>
        <p class="text-muted small mb-0">توقعات الأداء المستقبلي بناءً على البيانات الحالية</p>
    </div>
    <a href="{{ route('admin.meta-pro-tools.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right"></i> العودة</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="fw-bold small">اختر حملة</label>
        <select class="form-select" onchange="if(this.value) window.location.href='{{ route('admin.meta-pro-tools.performance-forecast.campaign', '') }}/'+this.value">
            <option value="">اختر حملة</option>
            @foreach($campaigns as $c)
            <option value="{{ $c->id }}" {{ isset($campaignId) && $campaignId == $c->id ? 'selected' : '' }}>
                {{ $c->name }} ({{ $c->adAccount->name ?? '—' }})
            </option>
            @endforeach
        </select>
    </div>
</div>

<div id="forecast-results">
    @if(isset($forecast) && ($forecast['has_data'] ?? false))
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-bold"><i class="fas fa-info-circle"></i> {{ $forecast['campaign_name'] }}</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-3"><div class="border rounded p-2 text-center"><b>{{ $forecast['current_metrics']['daily_spend'] }}</b><br><small>الإنفاق اليومي</small></div></div>
                        <div class="col-3"><div class="border rounded p-2 text-center"><b>{{ $forecast['current_metrics']['ctr'] }}</b><br><small>CTR</small></div></div>
                        <div class="col-3"><div class="border rounded p-2 text-center"><b>{{ $forecast['current_metrics']['cvr'] }}</b><br><small>CVR</small></div></div>
                        <div class="col-3"><div class="border rounded p-2 text-center"><b>{{ $forecast['current_metrics']['aov'] }}</b><br><small>متوسط قيمة الطلب</small></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach(['30_days' => '30 يوم', '60_days' => '60 يوم', '90_days' => '90 يوم'] as $key => $label)
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-bold text-center">{{ $label }}</div>
                <div class="card-body small text-center">
                    @php $f = $forecast['baseline_forecast'][$key]; @endphp
                    <div class="mb-1"><b>الإنفاق:</b> {{ number_format($f['estimated_spend'], 2) }}</div>
                    <div class="mb-1"><b>مرات الظهور:</b> {{ number_format($f['estimated_impressions']) }}</div>
                    <div class="mb-1"><b>النقرات:</b> {{ number_format($f['estimated_clicks']) }}</div>
                    <div class="mb-1"><b>التحويلات:</b> {{ number_format($f['estimated_conversions']) }}</div>
                    <div class="mb-1"><b>الإيرادات:</b> {{ number_format($f['estimated_revenue'], 2) }}</div>
                    <div><b>ROAS:</b> <span style="color:{{ $f['estimated_roas'] >= 2 ? '#10B981' : '#F59E0B' }}">{{ $f['estimated_roas'] }}x</span></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if(!empty($forecast['default_scenarios']))
    <div class="card shadow-sm">
        <div class="card-header bg-light fw-bold"><i class="fas fa-code-branch"></i> سيناريوهات الأداء (30 يوم)</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>السيناريو</th><th>الإنفاق</th><th>مرات الظهور</th><th>النقرات</th><th>التحويلات</th><th>الإيرادات</th><th>ROAS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($forecast['default_scenarios'] as $key => $s)
                    <tr>
                        <td><span class="badge bg-{{ $key === 'optimized' ? 'success' : ($key === 'aggressive' ? 'warning' : ($key === 'conservative' ? 'secondary' : 'info')) }}">{{ $key }}</span></td>
                        <td>{{ number_format($s['estimated_spend'], 2) }}</td>
                        <td>{{ number_format($s['estimated_impressions']) }}</td>
                        <td>{{ number_format($s['estimated_clicks']) }}</td>
                        <td>{{ number_format($s['estimated_conversions']) }}</td>
                        <td>{{ number_format($s['estimated_revenue'], 2) }}</td>
                        <td style="color:{{ $s['estimated_roas'] >= 2 ? '#10B981' : '#F59E0B' }}">{{ $s['estimated_roas'] }}x</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @elseif(isset($forecast) && !($forecast['has_data'] ?? false))
    <div class="alert alert-info"><i class="fas fa-info-circle"></i> {{ $forecast['message'] ?? 'لا توجد بيانات كافية' }}</div>
    @else
    <div class="text-center py-5 text-muted">
        <i class="fas fa-chart-line fa-4x mb-3"></i>
        <p>اختر حملة لعرض التوقعات والتحليلات</p>
    </div>
    @endif
</div>
@endsection
