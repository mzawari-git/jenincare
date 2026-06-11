@extends('admin.layouts.app')
@section('title', 'تحسين التصميمات')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-palette" style="color:var(--pink-600);margin-left:8px;"></i> تحسين التصميمات</h1>
        <p class="text-muted small mb-0">كشف إرهاق التصميمات، اقتراحات AI، والتحسين المستمر</p>
    </div>
    <a href="{{ route('admin.meta-advanced.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-right"></i> العودة
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label small">مستوى الإرهاق</label>
        <select class="form-select form-select-sm" id="threshold-select" onchange="updateCreatives()">
            <option value="healthy" {{ $threshold == 'healthy' ? 'selected' : '' }}>الكل</option>
            <option value="warning" {{ $threshold == 'warning' ? 'selected' : '' }}>تحذير فما فوق</option>
            <option value="fatigued" {{ $threshold == 'fatigued' ? 'selected' : '' }}>مرهق فما فوق</option>
            <option value="critical" {{ $threshold == 'critical' ? 'selected' : '' }}>حرج فقط</option>
        </select>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <div class="h4 mb-0">{{ $fatiguedCreatives->where('fatigue_level', 'healthy')->count() }}</div>
                <div class="text-muted small">صحي</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
            <div class="card-body text-center">
                <i class="fas fa-exclamation-circle fa-2x text-warning mb-2"></i>
                <div class="h4 mb-0">{{ $fatiguedCreatives->where('fatigue_level', 'warning')->count() }}</div>
                <div class="text-muted small">تحذير</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
            <div class="card-body text-center">
                <i class="fas fa-tired fa-2x text-danger mb-2"></i>
                <div class="h4 mb-0">{{ $fatiguedCreatives->where('fatigue_level', 'fatigued')->count() }}</div>
                <div class="text-muted small">مرهق</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-dark bg-opacity-10">
            <div class="card-body text-center">
                <i class="fas fa-skull-crossbones fa-2x text-dark mb-2"></i>
                <div class="h4 mb-0">{{ $fatiguedCreatives->where('fatigue_level', 'critical')->count() }}</div>
                <div class="text-muted small">حرج</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light fw-bold">
        <i class="fas fa-images" style="color:var(--pink-600);margin-left:6px;"></i> التصميمات المرهقة
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>التصميم</th>
                        <th>مستوى الإرهاق</th>
                        <th>نقاط الإرهاق</th>
                        <th>تغير CTR</th>
                        <th>التردد</th>
                        <th>التوصيات</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fatiguedCreatives as $item)
                    <tr>
                        <td>
                            <div class="fw-bold small">{{ $item['creative']->name ?? '-' }}</div>
                            <div class="text-muted" style="font-size:0.7rem;">{{ $item['creative']->title ?? '' }}</div>
                        </td>
                        <td>
                            @php
                                $levelColors = [
                                    'healthy' => 'success',
                                    'warning' => 'warning',
                                    'fatigued' => 'danger',
                                    'critical' => 'dark'
                                ];
                                $levelLabels = [
                                    'healthy' => 'صحي',
                                    'warning' => 'تحذير',
                                    'fatigued' => 'مرهق',
                                    'critical' => 'حرج'
                                ];
                            @endphp
                            <span class="badge bg-{{ $levelColors[$item['fatigue_level']] ?? 'secondary' }}">
                                {{ $levelLabels[$item['fatigue_level']] ?? $item['fatigue_level'] }}
                            </span>
                        </td>
                        <td>
                            <div class="progress" style="height: 8px; width: 80px;">
                                <div class="progress-bar bg-{{ $item['fatigue_score'] > 70 ? 'success' : ($item['fatigue_score'] > 40 ? 'warning' : 'danger') }}" 
                                     style="width: {{ $item['fatigue_score'] }}%"></div>
                            </div>
                            <small class="text-muted">{{ $item['fatigue_score'] }}/100</small>
                        </td>
                        <td>
                            <span class="{{ $item['ctr_change'] < 0 ? 'text-danger' : 'text-success' }}">
                                <i class="fas fa-arrow-{{ $item['ctr_change'] < 0 ? 'down' : 'up' }}"></i>
                                {{ abs($item['ctr_change']) }}%
                            </span>
                        </td>
                        <td>{{ number_format($item['frequency'], 2) }}</td>
                        <td>
                            @if(!empty($item['recommendations']))
                            <button class="btn btn-sm btn-outline-info" onclick="showRecommendations({{ $item['creative']->id }})">
                                <i class="fas fa-lightbulb"></i> {{ count($item['recommendations']) }} توصيات
                            </button>
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="analyzeCreative({{ $item['creative']->id }})">
                                <i class="fas fa-chart-line"></i> تحليل
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">لا توجد تصميمات مرهقة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="recommendationsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-lightbulb"></i> التوصيات</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="recommendationsContent">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="analysisModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-chart-line"></i> تحليل التصميم</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="analysisContent">
                <div class="text-center py-4">
                    <span class="spinner-border"></span> جاري التحليل...
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const BASE = '{{ url("/") }}';

function updateCreatives() {
    const threshold = document.getElementById('threshold-select').value;
    window.location.href = '{{ route("admin.meta-advanced.creative") }}?threshold=' + threshold;
}

async function showRecommendations(creativeId) {
    try {
        const res = await fetch(BASE + '/admin/meta-advanced/creative/' + creativeId + '/suggestions');
        const result = await res.json();
        
        let html = '<div class="mb-3"><h6>اقتراحات العناوين:</h6><ul>';
        (result.data.headline_variations || []).forEach(v => html += '<li>' + v + '</li>');
        html += '</ul></div>';
        
        html += '<div class="mb-3"><h6>اقتراحات النص:</h6><ul>';
        (result.data.body_variations || []).forEach(v => html += '<li>' + v + '</li>');
        html += '</ul></div>';
        
        html += '<div class="mb-3"><h6>نصائح بصرية:</h6><ul>';
        (result.data.visual_tips || []).forEach(v => html += '<li>' + v + '</li>');
        html += '</ul></div>';
        
        document.getElementById('recommendationsContent').innerHTML = html;
        new bootstrap.Modal(document.getElementById('recommendationsModal')).show();
    } catch (err) {
        alert('حدث خطأ');
    }
}

async function analyzeCreative(creativeId) {
    new bootstrap.Modal(document.getElementById('analysisModal')).show();
    
    try {
        const res = await fetch(BASE + '/admin/meta-advanced/creative/' + creativeId + '/analyze');
        const result = await res.json();
        
        let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr>';
        html += '<th>التاريخ</th><th>CTR</th><th>التغير</th><th>التردد</th><th>المستوى</th>';
        html += '</tr></thead><tbody>';
        
        (result.data || []).forEach(day => {
            html += '<tr>';
            html += '<td>' + day.date + '</td>';
            html += '<td>' + day.ctr + '%</td>';
            html += '<td class="' + (day.ctr_change < 0 ? 'text-danger' : 'text-success') + '">' + day.ctr_change + '%</td>';
            html += '<td>' + day.frequency + '</td>';
            html += '<td><span class="badge bg-' + (day.fatigue_level === 'healthy' ? 'success' : (day.fatigue_level === 'warning' ? 'warning' : 'danger')) + '">' + day.fatigue_level + '</span></td>';
            html += '</tr>';
        });
        
        html += '</tbody></table></div>';
        document.getElementById('analysisContent').innerHTML = html;
    } catch (err) {
        document.getElementById('analysisContent').innerHTML = '<div class="alert alert-danger">حدث خطأ في التحليل</div>';
    }
}
</script>
@endpush
@endsection
