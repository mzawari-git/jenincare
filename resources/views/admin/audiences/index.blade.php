@extends('admin.layouts.app')

@section('title', 'بناء الجماهير')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-users text-primary me-2"></i>بناء الجماهير
            </h4>
            <p class="text-muted mb-0 small">إنشاء وإدارة جماهير مخصصة ومماثلة لـ Meta و Google</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="overlapAnalysis()">
                <i class="fas fa-chart-pie me-1"></i>تحليل التداخل
            </button>
            <a href="{{ route('admin.audiences.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>جمهور جديد
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold">{{ $audiences->total() }}</div>
                    <div class="small">إجمالي الجماهير</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold">{{ $audiences->where('status', 'ready')->count() }}</div>
                    <div class="small">جاهزة</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold">{{ $audiences->where('platform', 'meta')->count() }}</div>
                    <div class="small">Meta</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold">{{ $audiences->where('status', 'fatigued')->count() }}</div>
                    <div class="small">يحتاج تجديد</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audiences Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($audiences->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 px-3">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th class="border-0">الجمهور</th>
                                <th class="border-0">المنصة</th>
                                <th class="border-0">المصدر</th>
                                <th class="border-0">الحجم</th>
                                <th class="border-0">الحالة</th>
                                <th class="border-0">الإجهاد</th>
                                <th class="border-0 text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($audiences as $audience)
                            <tr>
                                <td class="px-3">
                                    <input type="checkbox" class="form-check-input audience-check" value="{{ $audience->id }}">
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $audience->name }}</div>
                                    <small class="text-muted">{{ $audience->platform_audience_id ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($audience->platform === 'meta')
                                        <span class="badge bg-primary"><i class="fab fa-facebook me-1"></i>Meta</span>
                                    @elseif($audience->platform === 'google')
                                        <span class="badge bg-danger"><i class="fab fa-google me-1"></i>Google</span>
                                    @endif
                                </td>
                                <td><small>{{ $audience->source_type }}</small></td>
                                <td>{{ number_format($audience->audience_size) }}</td>
                                <td>
                                    <span class="badge bg-{{ $audience->status_badge }}">{{ $audience->status }}</span>
                                </td>
                                <td>
                                    <div class="progress" style="width:60px;height:6px;">
                                        <div class="progress-bar bg-{{ $audience->fatigue_color }}" style="width:{{ $audience->fatigue_score }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $audience->fatigue_score }}%</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.audiences.show', $audience) }}" class="btn btn-outline-primary" title="تفاصيل">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-secondary" onclick="syncAudience({{ $audience->id }})" title="مزامنة">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                        <form action="{{ route('admin.audiences.destroy', $audience) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top">
                    {{ $audiences->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-3 opacity-25"></i>
                    <h5 class="text-muted">لا توجد جماهير مخصصة</h5>
                    <p class="text-muted">ابدأ بإنشاء جمهور مخصص لتحسين استهداف حملاتك</p>
                    <a href="{{ route('admin.audiences.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إنشاء جمهور
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function syncAudience(id) {
    fetch(`/admin/audiences/${id}/sync`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('❌ ' + (data.message || 'فشلت المزامنة'));
        }
    })
    .catch(err => alert('خطأ: ' + err.message));
}

function overlapAnalysis() {
    const checked = document.querySelectorAll('.audience-check:checked');
    if (checked.length < 2) {
        alert('يجب تحديد جمهورين على الأقل لتحليل التداخل');
        return;
    }

    const ids = Array.from(checked).map(c => c.value);

    fetch('{{ route("admin.audiences.overlap") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ audience_ids: ids }),
    })
    .then(r => r.json())
    .then(data => {
        console.log('Overlap analysis:', data);
        alert('تم تحليل التداخل - تحقق من وحدة التحكم للمزيد');
    });
}

document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.audience-check').forEach(c => c.checked = this.checked);
});
</script>
@endsection
