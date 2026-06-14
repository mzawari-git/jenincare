@extends('admin.layouts.app')
@section('title', 'معاينة الإعلان')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-eye" style="color:#8B5CF6;margin-left:8px;"></i> معاينة الإعلان</h4>
        <p class="text-muted small mb-0">{{ $creative->name }}</p>
    </div>
    <a href="{{ route('admin.meta-pro-tools.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right"></i> العودة</a>
</div>

<div class="row g-4">
    {{-- Preview Section --}}
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="fw-bold"><i class="fas fa-mobile-alt"></i> المعاينة المباشرة</span>
                <div class="d-flex gap-1 flex-wrap">
                    @foreach($allPlacements as $key => $pl)
                    <a href="{{ route('admin.meta-pro-tools.ad-preview', [$creative, 'placement' => $key]) }}"
                       class="btn btn-sm {{ $key === $placement ? 'btn-primary' : 'btn-outline-secondary' }}"
                       title="{{ $pl['description'] }}">
                        <i class="{{ $pl['icon'] }}"></i>
                        <span class="d-none d-md-inline">{{ $pl['name'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            <div class="card-body d-flex justify-content-center" style="min-height:400px;background:#f8f9fa;">
                @if(isset($preview['preview_html']))
                    {!! $preview['preview_html'] !!}
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                        <p>تعذر إنشاء المعاينة</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Content Validation --}}
        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold"><i class="fas fa-clipboard-check"></i> التحقق من المحتوى</div>
            <div class="card-body" id="validation-results">
                <button class="btn btn-primary btn-sm" onclick="validateContent()">
                    <i class="fas fa-sync-alt"></i> تحقق الآن
                </button>
                <div id="validation-output" class="mt-3"></div>
            </div>
        </div>
    </div>

    {{-- Info Section --}}
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light fw-bold"><i class="fas fa-info-circle"></i> معلومات الإعلان</div>
            <div class="card-body small">
                <div class="mb-2"><b>العنوان:</b><br>{{ $creative->title ?: '—' }}</div>
                <div class="mb-2"><b>النص:</b><br>{{ $creative->body ?: '—' }}</div>
                <div class="mb-2"><b>الوصف:</b><br>{{ $creative->description ?: '—' }}</div>
                <div class="mb-2"><b>الرابط:</b><br><small>{{ $creative->link_url ?: '—' }}</small></div>
                <div class="mb-2"><b>زر CTA:</b><br>{{ $creative->call_to_action ?: '—' }}</div>
                <div class="mb-2"><b>الحالة:</b> <span class="badge bg-{{ $creative->status === 'active' ? 'success' : 'secondary' }}">{{ $creative->status }}</span></div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light fw-bold"><i class="fas fa-image"></i> مواصفات الصورة</div>
            <div class="card-body small">
                @if(isset($preview['specs']))
                    @php $imgSpecs = $preview['specs']['recommended_image_size'] ?? []; @endphp
                    <div class="mb-1"><b>الحجم الموصى به:</b><br>{{ $imgSpecs['width'] ?? '—' }} ? {{ $imgSpecs['height'] ?? '—' }}</div>
                    <div class="mb-1"><b>النسبة:</b> {{ $imgSpecs['ratio'] ?? '—' }}</div>
                @endif
                <div class="mt-2 text-muted"><small>* قد تختلف المقاسات حسب موقع الظهور</small></div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold"><i class="fas fa-ruler"></i> حدود النص</div>
            <div class="card-body small">
                @if(isset($preview['specs']))
                <table class="table table-sm mb-0">
                    <tr><td>العنوان</td><td>حد أقصى {{ $preview['specs']['max_headline_length'] ?? 40 }} حرف</td></tr>
                    <tr><td>النص الأساسي</td><td>حد أقصى {{ $preview['specs']['max_body_length'] ?? 125 }} حرف</td></tr>
                    <tr><td>الوصف</td><td>حد أقصى {{ $preview['specs']['max_description_length'] ?? 30 }} حرف</td></tr>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
async function validateContent() {
    const out = document.getElementById('validation-output');
    out.innerHTML = '<span class="spinner-border spinner-border-sm"></span> جاري التحقق...';
    try {
        const r = await fetch('{{ route('admin.meta-pro-tools.validate-ad') }}', {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json'},
            body: JSON.stringify({
                title: @json($creative->title ?? ''),
                body: @json($creative->body ?? ''),
                description: @json($creative->description ?? ''),
                link_url: @json($creative->link_url ?? ''),
            })
        });
        const d = await r.json();
        const v = d.content_validation;
        const c = d.compliance_check;
        let html = '';
        html += `<div class="mb-2"><b>نتيجة التحقق:</b> ${v.valid ? '<span class="text-success">✅ مطابق</span>' : '<span class="text-danger">❌ يحتاج تعديل</span>'} <span class="badge bg-${v.score >= 70 ? 'success' : 'danger'}">${v.score}%</span></div>`;
        if (v.issues.length) {
            html += '<div class="mb-2"><b>مشاكل:</b><ul class="mb-0">';
            v.issues.forEach(i => html += `<li class="text-danger small">${i}</li>`);
            html += '</ul></div>';
        }
        if (c.all_issues && c.all_issues.length) {
            html += '<div class="mb-2"><b>فحص الامتثال:</b><ul class="mb-0">';
            c.all_issues.forEach(i => html += `<li class="text-${i.severity === 'error' ? 'danger' : 'warning'} small">${i.message}</li>`);
            html += '</ul></div>';
        }
        if (!v.issues.length && (!c.all_issues || !c.all_issues.length)) {
            html += '<div class="text-success"><i class="fas fa-check-circle"></i> لا توجد مشاكل - الإعلان جاهز للنشر</div>';
        }
        out.innerHTML = html;
    } catch (e) {
        out.innerHTML = '<span class="text-danger">فشل التحقق</span>';
    }
}
</script>
@endpush
@endsection
