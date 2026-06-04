@extends('admin.layouts.app')

@section('title', 'سجل النشاطات')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">سجل النشاطات</h1>
        <p class="text-muted small mb-0">جميع الإجراءات التي تمت في النظام</p>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="small text-muted">النوع</label>
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">الكل</option>
                    @foreach($types as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="small text-muted">بحث</label>
                <input type="text" name="action" class="form-control form-control-sm" placeholder="إجراء..." value="{{ request('action') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-pink"><i class="fas fa-search"></i></button>
                @if(request()->anyFilled(['type','action']))
                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-sm btn-outline-secondary">إلغاء</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>النوع</th>
                        <th>الإجراء</th>
                        <th>النموذج</th>
                        <th>IP</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar" style="width:28px;height:28px;border-radius:50%;background:var(--pink-100);color:var(--pink-600);display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;">
                                    {{ substr($log->user->name ?? 'S', 0, 1) }}
                                </div>
                                <span class="small">{{ $log->user->name ?? 'النظام' }}</span>
                            </div>
                        </td>
                        <td><span class="badge bg-info">{{ $log->type }}</span></td>
                        <td class="small">{{ $log->action }}</td>
                        <td class="small text-muted">
                            @if($log->model_type)
                                {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="small text-muted font-monospace">{{ $log->ip_address ?? '—' }}</td>
                        <td class="small text-muted">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">لا توجد نشاطات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">{{ $logs->links() }}</div>
@endsection
