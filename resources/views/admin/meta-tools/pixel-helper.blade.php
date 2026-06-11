@extends('admin.layouts.app')
@section('title', 'Pixel Helper')
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold"><i class="fas fa-plug text-primary me-2"></i>Pixel Helper</h4><p class="text-muted mb-0 small">فحص وتأكد من عمل Facebook Pixel و CAPI بشكل صحيح</p></div>
        <button class="btn btn-outline-primary btn-sm" onclick="location.reload()"><i class="fas fa-sync me-1"></i>تحديث</button>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2 {{ ($verification['success'] ?? false) ? 'bg-success' : 'bg-danger' }}" style="width:56px;height:56px;">
                <i class="fas fa-{{ ($verification['success'] ?? false) ? 'check' : 'times' }} text-white fs-4"></i>
            </div>
            <h6 class="fw-bold">Facebook Pixel</h6>
            <div class="text-muted small">Pixel ID: {{ $verification['pixel']['id'] ?? 'غير موجود' }}</div>
            <div class="text-muted small">Status: <span class="badge bg-{{ ($verification['pixel']['status'] ?? '') === 'active' ? 'success' : 'secondary' }}">{{ $verification['pixel']['status'] ?? 'N/A' }}</span></div>
        </div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2 {{ ($browserVsCapi['server_capi']['enabled'] ?? false) ? 'bg-success' : 'bg-warning' }}" style="width:56px;height:56px;">
                <i class="fas fa-server text-white fs-4"></i>
            </div>
            <h6 class="fw-bold">Server CAPI</h6>
            <div class="text-muted small">Events: {{ $browserVsCapi['server_capi']['events_count'] ?? 0 }}</div>
            <div class="text-muted small">Coverage: {{ $browserVsCapi['coverage']['browser'] ?? 0 }}%</div>
        </div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body text-center">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2 bg-{{ ($health['status'] ?? '') === 'healthy' ? 'success' : (($health['status'] ?? '') === 'warning' ? 'warning' : 'secondary') }}" style="width:56px;height:56px;">
                <i class="fas fa-heartbeat text-white fs-4"></i>
            </div>
            <h6 class="fw-bold">Health (24h)</h6>
            <div class="text-muted small">Events: {{ $health['total_events_24h'] ?? 0 }}</div>
            <div class="text-muted small">Success: {{ $health['success_rate'] ?? 0 }}%</div>
        </div></div></div>
    </div>
    @if(!empty($health['missing_events']))
    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i><strong>أحداث مفقودة:</strong> {{ implode(', ', $health['missing_events']) }}</div>
    @endif
    @if(!empty($browserVsCapi['recommendations']))
    <div class="card border-0 shadow-sm mb-4"><div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-bold"><i class="fas fa-lightbulb me-2"></i>التوصيات</h6></div><div class="card-body">
        @foreach($browserVsCapi['recommendations'] as $rec)
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fas fa-{{ $rec['type'] === 'error' ? 'times-circle text-danger' : ($rec['type'] === 'warning' ? 'exclamation-triangle text-warning' : 'info-circle text-info') }}"></i>
                <span>{{ $rec['message'] }}</span>
            </div>
        @endforeach
        @foreach($health['recommendations'] ?? [] as $rec)
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fas fa-{{ $rec['type'] === 'error' ? 'times-circle text-danger' : ($rec['type'] === 'warning' ? 'exclamation-triangle text-warning' : 'info-circle text-info') }}"></i>
                <span>{{ $rec['message'] }}</span>
            </div>
        @endforeach
    </div></div>
    @endif
</div>
@endsection