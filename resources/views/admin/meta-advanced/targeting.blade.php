@extends('admin.layouts.app')
@section('title', 'الاستهداف المتقدم')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-bullseye" style="color:var(--pink-600);margin-left:8px;"></i> الاستهداف المتقدم</h1>
        <p class="text-muted small mb-0">جماهير مشابهة، إعلانات ديناميكية، وإعادة الاستهداف</p>
    </div>
    <a href="{{ route('admin.meta-advanced.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-right"></i> العودة
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light fw-bold">
                <i class="fas fa-users" style="color:var(--pink-600);margin-left:6px;"></i> إنشاء جمهور مشابه (Lookalike)
            </div>
            <div class="card-body">
                <form id="lookalikeForm">
                    <div class="mb-3">
                        <label class="form-label">الحساب الإعلاني</label>
                        <select class="form-select" name="account_id" required>
                            <option value="">اختر حساب</option>
                            @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">معرف الجمهور المصدر</label>
                        <input type="text" class="form-control" name="source_audience_id" required 
                               placeholder="أدخل معرف الجمهور المخصص">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">الدولة</label>
                            <input type="text" class="form-control" name="country" value="PS" maxlength="2">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">النسبة المئوية (1-10%)</label>
                            <input type="number" class="form-control" name="percentage" value="1" min="1" max="10">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-3">
                        <i class="fas fa-plus"></i> إنشاء جمهور مشابه
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light fw-bold">
                <i class="fas fa-redo" style="color:var(--pink-600);margin-left:6px;"></i> إنشاء جمهور إعادة استهداف
            </div>
            <div class="card-body">
                <form id="retargetingForm">
                    <div class="mb-3">
                        <label class="form-label">الحساب الإعلاني</label>
                        <select class="form-select" name="account_id" required>
                            <option value="">اختر حساب</option>
                            @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">اسم الجمهور</label>
                        <input type="text" class="form-control" name="name" required 
                               placeholder="مثال: زوار الموقع - آخر 30 يوم">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">زوار الصفحة (URL يحتوي على)</label>
                        <input type="text" class="form-control" name="page_visitors" 
                               placeholder="مثال: /products">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">أيام الاحتفاظ</label>
                            <input type="number" class="form-control" name="retention_days" value="30" min="1" max="180">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الوقت على الموقع (ثانية)</label>
                            <input type="number" class="form-control" name="time_on_site" min="10" 
                                   placeholder="اختياري">
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="cart_abandoners" value="1" id="cartCheck">
                        <label class="form-check-label" for="cartCheck">
                            المتسوقين الذين لم يكملوا الشراء
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-3">
                        <i class="fas fa-plus"></i> إنشاء جمهور إعادة استهداف
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light fw-bold">
        <i class="fas fa-lightbulb" style="color:var(--pink-600);margin-left:6px;"></i> اقتراحات توسيع الجمهور
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="border rounded p-3 text-center">
                    <i class="fas fa-map-marker-alt fa-2x text-primary mb-2"></i>
                    <h6>توسيع جغرافي</h6>
                    <p class="text-muted small">أضف مدن ومناطق إضافية للوصول لجمهور أوسع</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 text-center">
                    <i class="fas fa-birthday-cake fa-2x text-success mb-2"></i>
                    <h6>توسيع عمري</h6>
                    <p class="text-muted small">وسع الفئة العمرية لتشمل شرائح جديدة</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 text-center">
                    <i class="fas fa-heart fa-2x text-danger mb-2"></i>
                    <h6>اهتمامات إضافية</h6>
                    <p class="text-muted small">أضف اهتمامات وسلوكيات جديدة للاستهداف</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const BASE = '{{ url("/") }}';

document.getElementById('lookalikeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data.percentage = parseInt(data.percentage);
    
    try {
        const res = await fetch(BASE + '/admin/meta-advanced/targeting/lookalike', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        alert(result.message);
        if (result.success) this.reset();
    } catch (err) {
        alert('حدث خطأ');
    }
});

document.getElementById('retargetingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data.retention_days = parseInt(data.retention_days);
    data.cart_abandoners = formData.get('cart_abandoners') === '1';
    
    try {
        const res = await fetch(BASE + '/admin/meta-advanced/targeting/retargeting', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        alert(result.message);
        if (result.success) this.reset();
    } catch (err) {
        alert('حدث خطأ');
    }
});
</script>
@endpush
@endsection
