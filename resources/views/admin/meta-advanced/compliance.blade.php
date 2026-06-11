@extends('admin.layouts.app')
@section('title', 'الامتثال والمراقبة')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-shield-alt" style="color:var(--pink-600);margin-left:8px;"></i> الامتثال والمراقبة</h1>
        <p class="text-muted small mb-0">تنبيهات السياسات، صحة الحساب، وحدود الإنفاق</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createLimitModal">
            <i class="fas fa-plus"></i> حد إنفاق جديد
        </button>
        <a href="{{ route('admin.meta-advanced.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right"></i> العودة
        </a>
    </div>
</div>

@if($summary)
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="h3 mb-0 text-danger">{{ $summary['open_issues'] }}</div>
                <div class="text-muted small">مشاكل مفتوحة</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="h3 mb-0 text-dark">{{ $summary['critical_issues'] }}</div>
                <div class="text-muted small">مشاكل حرجة</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="h3 mb-0 text-success">{{ $summary['resolved_this_week'] }}</div>
                <div class="text-muted small">تم حلها هذا الأسبوع</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="h3 mb-0 text-warning">{{ $summary['triggered_limits'] }}</div>
                <div class="text-muted small">حدود مفعلة</div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card mb-4">
    <div class="card-header bg-light fw-bold">
        <i class="fas fa-exclamation-triangle" style="color:var(--pink-600);margin-left:6px;"></i> مشاكل الامتثال
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>النوع</th>
                        <th>الخطورة</th>
                        <th>الوصف</th>
                        <th>الحملة</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($issues as $issue)
                    <tr>
                        <td>
                            @php
                                $typeLabels = [
                                    'policy_violation' => 'مخالفة سياسة',
                                    'rejection' => 'رفض إعلان',
                                    'warning' => 'تحذير',
                                    'account_issue' => 'مشكلة حساب',
                                    'delivery_issue' => 'مشكلة توصيل'
                                ];
                            @endphp
                            <span class="badge bg-info">{{ $typeLabels[$issue->type] ?? $issue->type }}</span>
                        </td>
                        <td>
                            @php
                                $severityColors = [
                                    'low' => 'secondary',
                                    'medium' => 'warning',
                                    'high' => 'danger',
                                    'critical' => 'dark'
                                ];
                            @endphp
                            <span class="badge bg-{{ $severityColors[$issue->severity] ?? 'secondary' }}">{{ $issue->severity }}</span>
                        </td>
                        <td class="small">{{ \Str::limit($issue->description, 50) }}</td>
                        <td class="small">{{ $issue->campaign->name ?? '-' }}</td>
                        <td class="small text-muted">{{ $issue->created_at->diffForHumans() }}</td>
                        <td>
                            <span class="badge bg-{{ $issue->status === 'open' ? 'danger' : ($issue->status === 'resolved' ? 'success' : 'warning') }}">
                                {{ $issue->status }}
                            </span>
                        </td>
                        <td>
                            @if($issue->status === 'open')
                            <button class="btn btn-sm btn-outline-success" onclick="resolveIssue({{ $issue->id }})">
                                <i class="fas fa-check"></i> حل
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">لا توجد مشاكل امتثال</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($issues->hasPages())
        <div class="card-footer">{{ $issues->links() }}</div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header bg-light fw-bold">
        <i class="fas fa-dollar-sign" style="color:var(--pink-600);margin-left:6px;"></i> حدود الإنفاق
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>النطاق</th>
                        <th>الحد اليومي</th>
                        <th>الإنفاق الحالي</th>
                        <th>النسبة</th>
                        <th>الإجراء عند الوصول</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($spendingLimits as $limit)
                    <tr>
                        <td>
                            <span class="badge bg-info">{{ $limit->scope }}</span>
                        </td>
                        <td>{{ number_format($limit->daily_limit, 2) }} ₪</td>
                        <td>{{ number_format($limit->current_spend, 2) }} ₪</td>
                        <td>
                            @php
                                $percentage = $limit->daily_limit > 0 ? ($limit->current_spend / $limit->daily_limit) * 100 : 0;
                            @endphp
                            <div class="progress" style="height: 8px; width: 100px;">
                                <div class="progress-bar bg-{{ $percentage >= 100 ? 'danger' : ($percentage >= 80 ? 'warning' : 'success') }}" 
                                     style="width: {{ min(100, $percentage) }}%"></div>
                            </div>
                            <small>{{ number_format($percentage, 1) }}%</small>
                        </td>
                        <td>
                            @php
                                $actionLabels = [
                                    'pause' => 'إيقاف',
                                    'alert_only' => 'تنبيه فقط',
                                    'reduce_budget' => 'تقليل الميزانية'
                                ];
                            @endphp
                            {{ $actionLabels[$limit->action_on_limit] ?? $limit->action_on_limit }}
                        </td>
                        <td>
                            <span class="badge bg-{{ $limit->status === 'active' ? 'success' : ($limit->status === 'triggered' ? 'danger' : 'secondary') }}">
                                {{ $limit->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">لا توجد حدود إنفاق</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createLimitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> إنشاء حد إنفاق</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createLimitForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الحساب الإعلاني</label>
                        <select class="form-select" name="ad_account_id" required>
                            <option value="">اختر حساب</option>
                            @foreach(\App\Models\Meta\MetaAdAccount::where('is_active', true)->get() as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">النطاق</label>
                        <select class="form-select" name="scope" required>
                            <option value="account">الحساب بالكامل</option>
                            <option value="campaign">حملة محددة</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الحد اليومي (₪)</label>
                        <input type="number" class="form-control" name="daily_limit" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نسبة التنبيه (%)</label>
                        <input type="number" class="form-control" name="alert_threshold" value="80" min="0" max="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الإجراء عند الوصول</label>
                        <select class="form-select" name="action_on_limit" required>
                            <option value="alert_only">تنبيه فقط</option>
                            <option value="pause">إيقاف الحملات</option>
                            <option value="reduce_budget">تقليل الميزانية</option>
                        </select>
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

document.getElementById('createLimitForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data.daily_limit = parseFloat(data.daily_limit);
    data.alert_threshold = parseFloat(data.alert_threshold);
    
    try {
        const res = await fetch(BASE + '/admin/meta-advanced/compliance/spending-limits', {
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

async function resolveIssue(id) {
    const notes = prompt('أضف ملاحظات الحل (اختياري):');
    try {
        const res = await fetch(BASE + '/admin/meta-advanced/compliance/issues/' + id + '/resolve', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ resolution_notes: notes })
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
