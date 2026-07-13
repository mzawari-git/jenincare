@extends('admin.layouts.app')

@section('title', 'إدارة عناصر الدولاب (Wheel of Fortune)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">عناصر الدولاب <span style="color:var(--pink-600);">(Wheel of Fortune)</span></h1>
        <p class="text-muted small mb-0">أضف جوائز (منتجات) أو نسب خصم للدولاب — حد أقصى 8 عناصر.</p>
    </div>
    <a href="{{ route('admin.wheel-prizes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> إضافة عنصر
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show py-2 small">{{ session('success') }}
    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
</div>
@endif

@if($prizes->isNotEmpty())
<div class="alert alert-info py-2 small mb-3">
    <i class="fas fa-info-circle"></i> يوجد <strong>{{ $prizes->count() }}</strong> عنصر في الدولاب.
    <a href="{{ url('/spin-wheel') }}" target="_blank" class="alert-link">
        <i class="fas fa-external-link-alt"></i> معاينة الدولاب
    </a>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="prizesTable">
            <thead>
                <tr>
                    <th style="width:40px;"><i class="fas fa-grip-vertical text-muted"></i></th>
                    <th style="width:50px;">#</th>
                    <th>العنوان</th>
                    <th>النوع</th>
                    <th>اللون</th>
                    <th>الوزن</th>
                    <th>الحالة</th>
                    <th style="width:140px;"></th>
                </tr>
            </thead>
            <tbody id="sortable">
                @forelse($prizes as $prize)
                <tr data-id="{{ $prize->id }}">
                    <td class="handle" style="cursor:grab;color:var(--gray-400);"><i class="fas fa-grip-vertical"></i></td>
                    <td class="fw-bold text-muted small">{{ $prize->sort_order }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($prize->type === 'discount')
                            <div style="width:36px;height:36px;border-radius:50%;background:{{ $prize->color }};display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:.85rem;">
                                {{ $prize->discount_percent }}%
                            </div>
                            <div>
                                <div class="fw-bold small">خصم {{ $prize->discount_percent }}%</div>
                            </div>
                            @else
                            @if($prize->image_url)
                            <img src="{{ $prize->image_url }}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                            @else
                            <div style="width:36px;height:36px;border-radius:50%;background:{{ $prize->color }}20;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-gift" style="color:{{ $prize->color }};font-size:.85rem;"></i>
                            </div>
                            @endif
                            <div>
                                <div class="fw-bold small">{{ $prize->name }}</div>
                            </div>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($prize->type === 'discount')
                        <span class="badge bg-warning text-dark">نسبة خصم</span>
                        @else
                        <span class="badge bg-info">منتج / جائزة</span>
                        @endif
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:9999px;font-size:.75rem;border:1px solid {{ $prize->color }}40;background:{{ $prize->color }}15;color:{{ $prize->color }};font-weight:600;">
                            <span style="width:10px;height:10px;border-radius:50%;display:inline-block;background:{{ $prize->color }};"></span>
                            {{ $prize->color }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-secondary">{{ $prize->weight ?? 1 }}</span>
                    </td>
                    <td>
                        <form action="{{ route('admin.wheel-prizes.toggle', $prize) }}" method="POST">
                            @csrf
                            @if($prize->is_active)
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:9999px;font-size:.75rem;font-weight:600;background:#DCFCE7;color:#16A34A;">
                                <i class="fas fa-check-circle"></i> مفعّل
                            </span>
                            @else
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:9999px;font-size:.75rem;font-weight:600;background:var(--gray-100);color:var(--gray-500);">
                                <i class="fas fa-times-circle"></i> معطّل
                            </span>
                            @endif
                        </form>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.wheel-prizes.edit', $prize) }}" class="btn btn-sm btn-outline-primary" style="padding:4px 10px;font-size:.75rem;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.wheel-prizes.toggle', $prize) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $prize->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" style="padding:4px 10px;font-size:.75rem;">
                                    <i class="fas {{ $prize->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.wheel-prizes.destroy', $prize) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف هذا العنصر؟')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" style="padding:4px 10px;font-size:.75rem;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">
                    <i class="fas fa-gift mb-2" style="font-size:2.5rem;display:block;opacity:.2;"></i>
                    لا توجد عناصر. <a href="{{ route('admin.wheel-prizes.create') }}">أضف أول عنصر</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('sortable');
    if (!el) return;
    new Sortable(el, {
        handle: '.handle',
        animation: 150,
        onEnd: function () {
            const ids = Array.from(el.querySelectorAll('tr')).map(tr => tr.dataset.id);
            fetch('{{ route('admin.wheel-prizes.reorder') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ ids }),
            }).then(r => r.json()).then(d => {
                if (d.success) location.reload();
            });
        }
    });
});
</script>
@endpush
