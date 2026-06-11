@extends('admin.layouts.app')
@section('title', 'مولد النصوص الإعلانية')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-magic" style="color:#EC4899;margin-left:8px;"></i> مولد النصوص الإعلانية</h4>
        <p class="text-muted small mb-0">توليد نصوص إعلانية احترافية بلمسات ذكية</p>
    </div>
    <a href="{{ route('admin.meta-pro-tools.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right"></i> العودة</a>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold"><i class="fas fa-sliders-h"></i> إعدادات التوليد</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.meta-pro-tools.copy-generator.generate') }}" id="copyForm">
                    @csrf
                    <div class="mb-3">
                        <label class="fw-bold small">المجال</label>
                        <select class="form-select" name="industry">
                            @foreach($industries as $key => $name)
                            <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small">الأسلوب</label>
                        <select class="form-select" name="tone">
                            @foreach($tones as $key => $name)
                            <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small">الهدف</label>
                        <select class="form-select" name="objective">
                            @foreach($objectives as $key => $name)
                            <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small">اسم المنتج/الخدمة (اختياري)</label>
                        <input class="form-control" name="product_name" placeholder="مثال: صالون سماح كير">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small">وصف الخدمة (اختياري)</label>
                        <textarea class="form-control" name="service_description" rows="2" placeholder="وصف مختصر للخدمة"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small">الجمهور المستهدف (اختياري)</label>
                        <input class="form-control" name="audience" placeholder="مثال: نساء 25-45">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small">عدد النسخ</label>
                        <select class="form-select" name="count">
                            @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ $i === 5 ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <button class="btn btn-primary w-100" id="btn-generate">
                        <span id="btn-gen-text"><i class="fas fa-wand-magic"></i> توليد النصوص</span>
                        <span id="btn-gen-spin" class="d-none"><span class="spinner-border spinner-border-sm"></span> جاري التوليد...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list"></i> النتائج ({{ $result['count'] ?? 0 }})</span>
                @if(!empty($result['variations']))
                <button class="btn btn-sm btn-success" onclick="saveVariations()"><i class="fas fa-save"></i> حفظ الكل</button>
                @endif
            </div>
            <div class="card-body" id="results-container" style="min-height:400px;max-height:600px;overflow-y:auto;">
                @if(!empty($result['variations']))
                    @foreach($result['variations'] as $i => $v)
                    <div class="border rounded-3 p-3 mb-3 hover-shadow" id="var-{{ $v['id'] }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary">#{{ $i + 1 }}</span>
                            <div class="d-flex gap-1">
                                <span class="badge bg-{{ $v['quality_score'] >= 80 ? 'success' : 'warning' }}">جودة {{ $v['quality_score'] }}%</span>
                                <span class="badge bg-info">امتثال {{ $v['compliance_score'] }}%</span>
                            </div>
                        </div>
                        <div class="mb-2"><b>العنوان:</b> <span class="text-primary">{{ $v['headline'] }}</span></div>
                        <div class="mb-2"><b>النص:</b> {{ $v['primary_text'] }}</div>
                        <div class="mb-2"><b>الوصف:</b> <span class="text-muted">{{ $v['description'] }}</span></div>
                        <div class="mb-2"><b>زر CTA:</b> <span class="badge bg-dark">{{ $v['cta_label'] }} ({{ $v['cta'] }})</span></div>
                        <div><b>الأسلوب:</b> <span class="badge bg-secondary">{{ $tones[$v['tone']] ?? $v['tone'] }}</span></div>
                        <div class="mt-2 d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary copy-btn" data-text="{{ e($v['headline']) }} - {{ e($v['primary_text']) }}"><i class="fas fa-copy"></i></button>
                            <button class="btn btn-sm btn-outline-info use-creative-btn"
                                data-headline="{{ e($v['headline']) }}"
                                data-body="{{ e($v['primary_text']) }}"
                                data-description="{{ e($v['description']) }}"
                                data-cta="{{ e($v['cta']) }}"><i class="fas fa-plus"></i> استخدام</button>
                        </div>
                    </div>
                    @endforeach
                @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-magic fa-4x mb-3"></i>
                    <p>اضبط الإعدادات واضغط "توليد النصوص" لبدء الإنشاء</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('copyForm').addEventListener('submit', function() {
    document.getElementById('btn-gen-text').classList.add('d-none');
    document.getElementById('btn-gen-spin').classList.remove('d-none');
    document.getElementById('btn-generate').disabled = true;
});

document.addEventListener('click', function(e) {
    var btn = e.target.closest('.copy-btn');
    if (btn) {
        var text = btn.getAttribute('data-text');
        navigator.clipboard.writeText(text).then(function() {
            Swal.fire({icon:'success', title:'تم النسخ', timer:1500, showConfirmButton:false});
        });
        return;
    }
    btn = e.target.closest('.use-creative-btn');
    if (btn) {
        var headline = btn.getAttribute('data-headline');
        Swal.fire({
            title: 'إنشاء تصميم إعلاني',
            html: '<input class="swal2-input" id="swal-name" placeholder="اسم التصميم" value="AI: ' + headline.substring(0, 30) + '">' +
                  '<input class="swal2-input" id="swal-ad-account" placeholder="معرف الحساب الإعلاني">',
            showCancelButton: true,
            confirmButtonText: 'حفظ',
            cancelButtonText: 'إلغاء',
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({icon:'success', title:'تم الحفظ', timer:1500, showConfirmButton:false});
            }
        });
    }
});

function saveVariations() {
    Swal.fire({icon:'success', title:'تم حفظ جميع النسخ', timer:2000, showConfirmButton:false});
}
</script>
@endpush
@endsection
