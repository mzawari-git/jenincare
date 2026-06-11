@extends('admin.layouts.app')
@section('title', 'تنبيهات الإعلانات')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-bell" style="color:var(--pink-600);margin-left:8px;"></i> تنبيهات الإعلانات</h1>
        <p class="text-muted small mb-0">مراقبة صحة الحسابات الإعلانية واكتشاف الحالات الشاذة</p>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="stat-card-new"><div class="stat-meta-icon" style="background:var(--pink-600);color:#fff"><i class="fas fa-bell"></i></div><div class="stat-value-new">{{ $stats['unresolved'] ?? 0 }}</div><div class="stat-label-new">غير محلولة</div></div></div>
    <div class="col-md-3"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#EF4444;color:#fff"><i class="fas fa-exclamation-triangle"></i></div><div class="stat-value-new">{{ $stats['critical'] ?? 0 }}</div><div class="stat-label-new">حرجة</div></div></div>
    <div class="col-md-3"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#F59E0B;color:#fff"><i class="fas fa-exclamation-circle"></i></div><div class="stat-value-new">{{ $stats['warning'] ?? 0 }}</div><div class="stat-label-new">تحذيرية</div></div></div>
    <div class="col-md-3"><div class="stat-card-new"><div class="stat-meta-icon" style="background:#6366F1;color:#fff"><i class="fas fa-history"></i></div><div class="stat-value-new">{{ $stats['total'] ?? 0 }}</div><div class="stat-label-new">إجمالي</div></div></div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">المنصة</label>
                <select name="platform" class="form-select form-select-sm">
                    <option value="">الكل</option>
                    @foreach($platforms as $p)
                        <option value="{{ $p }}" {{ request('platform') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">نوع التنبيه</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">الكل</option>
                    @foreach($types as $t)
                        <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">الخطورة</label>
                <select name="severity" class="form-select form-select-sm">
                    <option value="">الكل</option>
                    <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>حرجة</option>
                    <option value="warning" {{ request('severity') === 'warning' ? 'selected' : '' }}>تحذير</option>
                    <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>معلومات</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="unresolved" value="1" id="unresolved" {{ request('unresolved') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="unresolved">غير محلولة فقط</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="unacknowledged" value="1" id="unacknowledged" {{ request('unacknowledged') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="unacknowledged">غير مؤكدة فقط</label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-pink btn-sm w-100"><i class="fas fa-filter"></i> تصفية</button>
            </div>
        </form>
    </div>
</div>

{{-- Alerts Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الخطورة</th>
                        <th>النوع</th>
                        <th>المنصة</th>
                        <th>العنوان</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                    <tr class="{{ $alert->severity === 'critical' ? 'table-danger' : ($alert->severity === 'warning' ? 'table-warning' : '') }}">
                        <td>{{ $alert->id }}</td>
                        <td>
                            @if($alert->severity === 'critical')
                                <span class="badge bg-danger">حرجة</span>
                            @elseif($alert->severity === 'warning')
                                <span class="badge bg-warning text-dark">تحذير</span>
                            @else
                                <span class="badge bg-info">معلومات</span>
                            @endif
                        </td>
                        <td><code>{{ $alert->type }}</code></td>
                        <td><span class="badge bg-secondary">{{ $alert->platform }}</span></td>
                        <td>
                            <strong>{{ $alert->title }}</strong>
                            @if($alert->body)
                                <br><small class="text-muted">{{ Str::limit($alert->body, 80) }}</small>
                            @endif
                        </td>
                        <td><small class="text-muted" title="{{ $alert->created_at }}">{{ $alert->created_at->diffForHumans() }}</small></td>
                        <td>
                            @if($alert->resolved_at)
                                <span class="badge bg-success">تم الحل</span>
                            @elseif($alert->acknowledged)
                                <span class="badge bg-primary">تم التأكيد</span>
                            @else
                                <span class="badge bg-secondary">جديد</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if(!$alert->acknowledged)
                                <button class="btn btn-sm btn-outline-primary acknowledge-btn" data-id="{{ $alert->id }}" title="تأكيد"><i class="fas fa-check"></i></button>
                                @endif
                                @if(!$alert->resolved_at)
                                <button class="btn btn-sm btn-outline-success resolve-btn" data-id="{{ $alert->id }}" title="حل"><i class="fas fa-check-double"></i></button>
                                @endif
                                <form action="{{ route('admin.ad-alerts.destroy', $alert) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف التنبيه؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="حذف"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted"><i class="fas fa-check-circle fa-2x d-block mb-2"></i> لا توجد تنبيهات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($alerts->hasPages())
    <div class="card-footer">
        {{ $alerts->links('bootstrap-5') }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.acknowledge-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        fetch('{{ url('admin/ad-alerts') }}/' + id + '/acknowledge', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json()).then(d => { if(d.success) this.closest('tr').querySelector('td:last-child').innerHTML = '<span class="badge bg-primary">تم التأكيد</span>'; });
    });
});
document.querySelectorAll('.resolve-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        fetch('{{ url('admin/ad-alerts') }}/' + id + '/resolve', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(r => r.json()).then(d => { if(d.success) { let tr = this.closest('tr'); if(tr) tr.remove(); } });
    });
});
</script>
@endpush
