@extends('admin.layouts.app')
@section('title', 'الأتمتة')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-robot" style="color:var(--pink-600);margin-left:8px;"></i> الأتمتة</h1>
        <p class="text-muted small mb-0">قواعد الإيقاف التلقائي، تحجيم الميزانية، وجدولة الإجراءات</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRuleModal">
            <i class="fas fa-plus"></i> قاعدة جديدة
        </button>
        <button class="btn btn-outline-success btn-sm" onclick="executeRules()">
            <i class="fas fa-play"></i> تنفيذ القواعد الآن
        </button>
        <a href="{{ route('admin.meta-advanced.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right"></i> العودة
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list" style="color:var(--pink-600);margin-left:6px;"></i> قواعد الأتمتة ({{ $rules->count() }})</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الاسم</th>
                        <th>النوع</th>
                        <th>الحساب</th>
                        <th>النطاق</th>
                        <th>الحالة</th>
                        <th>آخر تنفيذ</th>
                        <th>عدد التنفيذات</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                    <tr>
                        <td class="fw-bold">{{ $rule->name }}</td>
                        <td>
                            <span class="badge bg-info">{{ $rule->type }}</span>
                        </td>
                        <td class="small">{{ $rule->adAccount->name ?? '-' }}</td>
                        <td class="small">{{ $rule->scope === 'all_campaigns' ? 'جميع الحملات' : 'حملات محددة' }}</td>
                        <td>
                            <span class="badge bg-{{ $rule->status === 'active' ? 'success' : 'secondary' }}">
                                {{ $rule->status === 'active' ? 'نشط' : 'متوقف' }}
                            </span>
                        </td>
                        <td class="small text-muted">
                            {{ $rule->last_executed_at ? $rule->last_executed_at->diffForHumans() : 'لم ينفذ بعد' }}
                        </td>
                        <td>{{ $rule->execution_count }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-{{ $rule->status === 'active' ? 'warning' : 'success' }}" 
                                        onclick="toggleRule({{ $rule->id }}, '{{ $rule->status === 'active' ? 'paused' : 'active' }}')">
                                    <i class="fas fa-{{ $rule->status === 'active' ? 'pause' : 'play' }}"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteRule({{ $rule->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">لا توجد قواعد أتمتة بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light fw-bold">
        <i class="fas fa-clock" style="color:var(--pink-600);margin-left:6px;"></i> الإجراءات المجدولة ({{ $scheduled->count() }})
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الحملة</th>
                        <th>الإجراء</th>
                        <th>الوقت المحدد</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scheduled as $item)
                    <tr>
                        <td class="small">{{ $item->campaign->name ?? '-' }}</td>
                        <td><span class="badge bg-info">{{ $item->action }}</span></td>
                        <td class="small">{{ $item->scheduled_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <span class="badge bg-{{ $item->status === 'pending' ? 'warning' : ($item->status === 'executed' ? 'success' : 'secondary') }}">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td>
                            @if($item->status === 'pending')
                            <button class="btn btn-sm btn-outline-danger" onclick="cancelScheduled({{ $item->id }})">
                                <i class="fas fa-times"></i> إلغاء
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">لا توجد إجراءات مجدولة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createRuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> إنشاء قاعدة أتمتة</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createRuleForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">اسم القاعدة</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الحساب الإعلاني</label>
                            <select class="form-select" name="ad_account_id" required>
                                <option value="">اختر حساب</option>
                                @foreach(\App\Models\Meta\MetaAdAccount::where('is_active', true)->get() as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">النوع</label>
                            <select class="form-select" name="type" required>
                                <option value="auto_pause">إيقاف تلقائي</option>
                                <option value="budget_scale">تحجيم الميزانية</option>
                                <option value="bid_adjust">تعديل التسعير</option>
                                <option value="alert">تنبيه فقط</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">النطاق</label>
                            <select class="form-select" name="scope" required>
                                <option value="all_campaigns">جميع الحملات</option>
                                <option value="specific_campaigns">حملات محددة</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">الشروط (JSON)</label>
                            <textarea class="form-control font-monospace" name="conditions" rows="3" required
                                placeholder='[{"metric":"roas","operator":"less_than","value":1}]'></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">الإجراءات (JSON)</label>
                            <textarea class="form-control font-monospace" name="actions" rows="3" required
                                placeholder='[{"type":"pause"}]'></textarea>
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

document.getElementById('createRuleForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = {
        name: formData.get('name'),
        ad_account_id: formData.get('ad_account_id'),
        type: formData.get('type'),
        scope: formData.get('scope'),
        conditions: JSON.parse(formData.get('conditions')),
        actions: JSON.parse(formData.get('actions'))
    };
    
    try {
        const res = await fetch(BASE + '/admin/meta-advanced/automation/rules', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message || 'فشل');
        }
    } catch (err) {
        alert('حدث خطأ');
    }
});

async function toggleRule(id, status) {
    try {
        const res = await fetch(BASE + '/admin/meta-advanced/automation/rules/' + id, {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ status: status })
        });
        const result = await res.json();
        if (result.success) location.reload();
    } catch (err) {
        alert('حدث خطأ');
    }
}

async function deleteRule(id) {
    if (!confirm('هل أنت متأكد من حذف هذه القاعدة؟')) return;
    try {
        const res = await fetch(BASE + '/admin/meta-advanced/automation/rules/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const result = await res.json();
        if (result.success) location.reload();
    } catch (err) {
        alert('حدث خطأ');
    }
}

async function executeRules() {
    if (!confirm('هل تريد تنفيذ جميع القواعد النشطة الآن؟')) return;
    try {
        const res = await fetch(BASE + '/admin/meta-advanced/automation/execute', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        const result = await res.json();
        alert(result.message);
        location.reload();
    } catch (err) {
        alert('حدث خطأ');
    }
}

async function cancelScheduled(id) {
    if (!confirm('هل تريد إلغاء هذا الإجراء المجدول؟')) return;
    try {
        const res = await fetch(BASE + '/admin/meta-advanced/automation/scheduled/' + id + '/cancel', {
            method: 'POST',
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
