@extends('admin.layouts.app')
@section('title', 'سجل الإيقاف التلقائي')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-pause-circle" style="color:var(--pink-600);margin-left:8px;"></i> سجل الإيقاف التلقائي</h1>
        <p class="text-muted small mb-0">سجل عمليات الإيقاف التلقائي للحملات الإعلانية</p>
    </div>
    <a href="{{ route('admin.ad-alerts.index') }}" class="btn btn-outline-pink btn-sm"><i class="fas fa-bell"></i> التنبيهات</a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="stat-card-new"><div class="stat-meta-icon" style="background:var(--pink-600);color:#fff"><i class="fas fa-pause-circle"></i></div><div class="stat-value-new">{{ $stats['total'] ?? 0 }}</div><div class="stat-label-new">إجمالي المحاولات</div></div></div>
    <div class="col-md-3"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#10B981;color:#fff"><i class="fas fa-check-circle"></i></div><div class="stat-value-new">{{ $stats['successful'] ?? 0 }}</div><div class="stat-label-new">ناجحة</div></div></div>
    <div class="col-md-3"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#EF4444;color:#fff"><i class="fas fa-times-circle"></i></div><div class="stat-value-new">{{ $stats['failed'] ?? 0 }}</div><div class="stat-label-new">فاشلة</div></div></div>
    <div class="col-md-3"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#F59E0B;color:#fff"><i class="fas fa-clock"></i></div><div class="stat-value-new">{{ $stats['paused_today'] ?? 0 }}</div><div class="stat-label-new">إيقاف اليوم</div></div></div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">المنصة</label>
                <select name="platform" class="form-select form-select-sm">
                    <option value="">الكل</option>
                    @foreach($platforms as $p)
                        <option value="{{ $p }}" {{ request('platform') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">الإجراء</label>
                <select name="action" class="form-select form-select-sm">
                    <option value="">الكل</option>
                    <option value="paused" {{ request('action') === 'paused' ? 'selected' : '' }}>إيقاف</option>
                    <option value="resumed" {{ request('action') === 'resumed' ? 'selected' : '' }}>تشغيل</option>
                    <option value="attempted" {{ request('action') === 'attempted' ? 'selected' : '' }}>محاولة</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="failed" value="1" id="failed" {{ request('failed') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="failed">فاشلة فقط</label>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-pink btn-sm w-100"><i class="fas fa-filter"></i> تصفية</button>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>المنصة</th>
                        <th>الحملة</th>
                        <th>السبب</th>
                        <th>الإجراء</th>
                        <th>القيمة</th>
                        <th>الحد</th>
                        <th>النتيجة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="{{ !$log->success ? 'table-danger' : ($log->action === 'paused' ? 'table-warning' : ($log->action === 'resumed' ? 'table-success' : '')) }}">
                        <td>{{ $log->id }}</td>
                        <td><span class="badge bg-secondary">{{ $log->platform }}</span></td>
                        <td>
                            <strong>{{ $log->campaign_name ?? '—' }}</strong>
                            @if($log->campaign_id)
                                <br><small class="text-muted">{{ $log->campaign_id }}</small>
                            @endif
                        </td>
                        <td><code>{{ $log->trigger_type }}</code></td>
                        <td>
                            @if($log->action === 'paused')
                                <span class="badge bg-warning text-dark"><i class="fas fa-pause"></i> إيقاف</span>
                            @elseif($log->action === 'resumed')
                                <span class="badge bg-success"><i class="fas fa-play"></i> تشغيل</span>
                            @else
                                <span class="badge bg-secondary">محاولة</span>
                            @endif
                        </td>
                        <td>{{ $log->trigger_value }}</td>
                        <td>{{ $log->threshold }}</td>
                        <td>
                            @if($log->success)
                                <span class="badge bg-success">ناجح</span>
                            @else
                                <span class="badge bg-danger" title="{{ $log->error_message }}">فاشل</span>
                            @endif
                        </td>
                        <td><small class="text-muted" title="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</small></td>
                    </tr>
                    @if($log->error_message)
                    <tr class="table-danger"><td colspan="9" class="py-1"><small class="text-danger"><i class="fas fa-info-circle"></i> {{ $log->error_message }}</small></td></tr>
                    @endif
                    @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted"><i class="fas fa-check-circle fa-2x d-block mb-2"></i> لا توجد سجلات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer">
        {{ $logs->links('bootstrap-5') }}
    </div>
    @endif
</div>

@endsection
