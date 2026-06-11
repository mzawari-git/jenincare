@extends('admin.layouts.app')
@section('title', 'التقارير الآلية')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-file-alt" style="color:var(--pink-600);margin-left:8px;"></i> التقارير الآلية</h1>
        <p class="text-muted small mb-0">إنشاء وإدارة التقارير التلقائية المرسلة بالبريد الإلكتروني</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createReportModal">
            <i class="fas fa-plus"></i> تقرير جديد
        </button>
        <a href="{{ route('admin.meta-advanced.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right"></i> العودة
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light fw-bold">
        <i class="fas fa-list" style="color:var(--pink-600);margin-left:6px;"></i> التقارير الآلية ({{ $reports->count() }})
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الاسم</th>
                        <th>النوع</th>
                        <th>المقاييس</th>
                        <th>المستلمون</th>
                        <th>الصيغة</th>
                        <th>الحالة</th>
                        <th>آخر إرسال</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                    <tr>
                        <td class="fw-bold">{{ $report->name }}</td>
                        <td>
                            @php
                                $typeLabels = [
                                    'daily' => 'يومي',
                                    'weekly' => 'أسبوعي',
                                    'monthly' => 'شهري',
                                    'custom' => 'مخصص'
                                ];
                            @endphp
                            <span class="badge bg-info">{{ $typeLabels[$report->type] ?? $report->type }}</span>
                        </td>
                        <td class="small">
                            @foreach(array_slice($report->metrics ?? [], 0, 3) as $metric)
                            <span class="badge bg-light text-dark me-1">{{ $metric }}</span>
                            @endforeach
                            @if(count($report->metrics ?? []) > 3)
                            <span class="text-muted">+{{ count($report->metrics) - 3 }}</span>
                            @endif
                        </td>
                        <td class="small">{{ count($report->recipients ?? []) }} مستلم</td>
                        <td><span class="badge bg-secondary">{{ $report->format }}</span></td>
                        <td>
                            <span class="badge bg-{{ $report->status === 'active' ? 'success' : 'secondary' }}">
                                {{ $report->status === 'active' ? 'نشط' : 'متوقف' }}
                            </span>
                        </td>
                        <td class="small text-muted">
                            {{ $report->last_sent_at ? $report->last_sent_at->diffForHumans() : 'لم يرسل بعد' }}
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-primary" onclick="generateReport({{ $report->id }})">
                                    <i class="fas fa-play"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteReport({{ $report->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">لا توجد تقارير آلية بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> إنشاء تقرير آلي</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createReportForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">اسم التقرير</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">النوع</label>
                            <select class="form-select" name="type" required>
                                <option value="daily">يومي</option>
                                <option value="weekly">أسبوعي</option>
                                <option value="monthly">شهري</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">المقاييس</label>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="metrics[]" value="total_purchases" id="m1">
                                        <label class="form-check-label" for="m1">المشتريات</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="metrics[]" value="total_revenue" id="m2">
                                        <label class="form-check-label" for="m2">الإيرادات</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="metrics[]" value="total_clicks" id="m3">
                                        <label class="form-check-label" for="m3">النقرات</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="metrics[]" value="total_impressions" id="m4">
                                        <label class="form-check-label" for="m4">الظهور</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="metrics[]" value="conversion_rate" id="m5">
                                        <label class="form-check-label" for="m5">معدل التحويل</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">المستلمون (emails)</label>
                            <input type="text" class="form-control" name="recipients_text" 
                                   placeholder="email1@example.com, email2@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الصيغة</label>
                            <select class="form-select" name="format" required>
                                <option value="email">بريد إلكتروني</option>
                                <option value="pdf">PDF</option>
                                <option value="csv">CSV</option>
                                <option value="excel">Excel</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">وقت الإرسال</label>
                            <input type="time" class="form-control" name="send_time" value="09:00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">إنشاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const BASE = '{{ url("/") }}';

document.getElementById('createReportForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    const metrics = [];
    document.querySelectorAll('input[name="metrics[]"]:checked').forEach(cb => metrics.push(cb.value));
    
    const recipients = formData.get('recipients_text').split(',').map(e => e.trim()).filter(e => e);
    
    const data = {
        name: formData.get('name'),
        type: formData.get('type'),
        metrics: metrics,
        recipients: recipients,
        format: formData.get('format'),
        send_time: formData.get('send_time')
    };
    
    try {
        const res = await fetch(BASE + '/admin/meta-advanced/reports', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            alert(result.message);
            location.reload();
        }
    } catch (err) {
        alert('حدث خطأ');
    }
});

async function generateReport(id) {
    try {
        const res = await fetch(BASE + '/admin/meta-advanced/reports/' + id + '/generate', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const result = await res.json();
        if (result.success) {
            alert('تم إنشاء التقرير بنجاح');
            location.reload();
        }
    } catch (err) {
        alert('حدث خطأ');
    }
}

async function deleteReport(id) {
    if (!confirm('هل أنت متأكد من حذف هذا التقرير؟')) return;
    try {
        const res = await fetch(BASE + '/admin/meta-advanced/reports/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const result = await res.json();
        if (result.success) location.reload();
    } catch (err) {
        alert('حدث خطأ');
    }
}
</script>
@endpush
@endsection
