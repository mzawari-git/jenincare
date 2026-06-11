@extends('admin.layouts.app')
@section('title', 'محسن الميزانية الإعلانية')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-chart-pie" style="color:#10B981;margin-left:8px;"></i> محسن الميزانية الإعلانية</h4>
        <p class="text-muted small mb-0">تحليل الأداء وتحسين توزيع الميزانية للحملات</p>
    </div>
    <a href="{{ route('admin.meta-pro-tools.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right"></i> العودة</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="fw-bold small">اختر حساب إعلاني</label>
        <select class="form-select" id="account-select" onchange="analyzeAccount(this.value)">
            <option value="">اختر حساب</option>
            @foreach($accounts as $acc)
            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency }})</option>
            @endforeach
        </select>
    </div>
</div>

<div id="results-container">
    @if(isset($account))
        {{-- Performance Score Card --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm text-center">
                    <div class="card-body py-4">
                        <div style="font-size:48px;font-weight:bold;color:{{ $performance['score'] >= 70 ? '#10B981' : ($performance['score'] >= 40 ? '#F59E0B' : '#EF4444') }};">
                            {{ $performance['score'] }}
                        </div>
                        <div class="display-6 fw-bold">{{ $performance['grade'] }}</div>
                        <div class="text-muted">درجة الأداء</div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-light fw-bold"><i class="fas fa-chart-bar"></i> مؤشرات الأداء</div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-4"><div class="border rounded p-2 text-center"><div class="fw-bold">{{ $performance['metrics']['ctr'] }}%</div><small class="text-muted">CTR</small></div></div>
                            <div class="col-4"><div class="border rounded p-2 text-center"><div class="fw-bold">{{ $performance['metrics']['cpc'] }}</div><small class="text-muted">CPC</small></div></div>
                            <div class="col-4"><div class="border rounded p-2 text-center"><div class="fw-bold">{{ $performance['metrics']['frequency'] }}</div><small class="text-muted">Frequency</small></div></div>
                        </div>
                        @if(!empty($performance['recommendations']))
                        <div class="mt-3">
                            <b>التوصيات:</b>
                            <ul class="small mb-0">
                                @foreach($performance['recommendations'] as $r)
                                <li>{{ $r }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Overview --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light fw-bold"><i class="fas fa-chart-simple"></i> نظرة عامة</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><div class="border rounded p-3 text-center"><div class="fw-bold h5 mb-0">{{ number_format($recommendations['overview']['total_spend'], 2) }} {{ $account->currency }}</div><small class="text-muted">إجمالي الإنفاق</small></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 text-center"><div class="fw-bold h5 mb-0">{{ $recommendations['overview']['total_conversions'] }}</div><small class="text-muted">تحويلات</small></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 text-center"><div class="fw-bold h5 mb-0">{{ number_format($recommendations['overview']['total_revenue'], 2) }} {{ $account->currency }}</div><small class="text-muted">إيرادات</small></div></div>
                    <div class="col-md-3"><div class="border rounded p-3 text-center"><div class="fw-bold h5 mb-0" style="color:{{ $recommendations['overview']['overall_roas'] >= 2 ? '#10B981' : '#F59E0B' }}">{{ $recommendations['overview']['overall_roas'] }}x</div><small class="text-muted">ROAS</small></div></div>
                </div>
            </div>
        </div>

        {{-- Campaign Analysis --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light fw-bold"><i class="fas fa-bullhorn"></i> تحليل الحملات</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>الحملة</th><th>الهدف</th><th>الميزانية</th><th>الإنفاق</th>
                            <th>تحويلات</th><th>إيرادات</th><th>ROAS</th><th>CPA</th><th>التوصية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recommendations['campaigns'] as $c)
                        <tr>
                            <td><b>{{ $c['name'] }}</b></td>
                            <td><span class="badge bg-light text-dark">{{ $c['objective'] }}</span></td>
                            <td>{{ $c['daily_budget'] ? number_format($c['daily_budget'], 2) : '-' }}</td>
                            <td>{{ number_format($c['current_spend'], 2) }}</td>
                            <td>{{ $c['conversions'] }}</td>
                            <td>{{ number_format($c['revenue'], 2) }}</td>
                            <td style="color:{{ $c['roas'] >= 2 ? '#10B981' : ($c['roas'] >= 1 ? '#F59E0B' : '#EF4444') }}">{{ $c['roas'] }}x</td>
                            <td>{{ $c['cpa'] > 0 ? number_format($c['cpa'], 2) : '-' }}</td>
                            <td><small class="text-muted">{{ $c['recommendation'] }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Budget Distribution --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light fw-bold"><i class="fas fa-chart-pie"></i> توزيع الميزانية المقترح</div>
            <div class="card-body">
                @if(!empty($distribution['distribution']))
                <table class="table table-sm small">
                    <thead>
                        <tr><th>الحملة</th><th>الميزانية الحالية</th><th>الحصة الحالية</th><th>ROAS</th><th>الحصة المقترحة</th><th>الميزانية المقترحة</th></tr>
                    </thead>
                    <tbody>
                        @foreach($distribution['distribution'] as $d)
                        <tr>
                            <td>{{ $d['campaign_name'] }}</td>
                            <td>{{ $d['current_budget'] ? number_format($d['current_budget'], 2) : '—' }}</td>
                            <td>{{ $d['current_share'] }}%</td>
                            <td style="color:{{ $d['roas'] >= 2 ? '#10B981' : '#F59E0B' }}">{{ $d['roas'] }}x</td>
                            <td style="color:#6366f1;font-weight:bold;">{{ $d['recommended_share'] }}%</td>
                            <td style="color:#6366f1;font-weight:bold;">{{ number_format($d['recommended_budget'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

        {{-- Summary Recommendations --}}
        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold"><i class="fas fa-list-check"></i> التوصيات العامة</div>
            <div class="card-body">
                <ul class="mb-0">
                    @foreach($recommendations['summary_recommendation'] as $r)
                    <li class="py-1">{{ $r }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @else
    <div class="text-center py-5 text-muted">
        <i class="fas fa-chart-pie fa-4x mb-3"></i>
        <p>اختر حساباً إعلانياً لبدء تحليل الميزانية والأداء</p>
    </div>
    @endif
</div>

@push('scripts')
<script>
async function analyzeAccount(id) {
    if (!id) return;
    window.location.href = '{{ route('admin.meta-pro-tools.budget-optimizer.analyze', '') }}/' + id;
}
</script>
@endpush
@endsection
