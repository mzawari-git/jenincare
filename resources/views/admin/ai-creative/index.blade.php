@extends('admin.layouts.app')

@section('title', 'النسخ الإعلانية المحفوظة')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-file-alt text-purple me-2"></i>النسخ الإعلانية المحفوظة
            </h4>
            <p class="text-muted mb-0 small">جميع النسخ الإعلانية المولدة والمحفوظة</p>
        </div>
        <a href="{{ route('admin.ai-creative.generate-form') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-magic me-1"></i>توليد نسخ جديدة
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(isset($variations) && count($variations) > 0)
        <div class="row">
            @foreach($variations as $i => $v)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-light text-dark">نسخة {{ $i + 1 }}</span>
                                <span class="badge {{ ($v['quality_score'] ?? 0) >= 70 ? 'bg-success' : 'bg-warning' }}">
                                    {{ $v['quality_score'] ?? 0 }}/100
                                </span>
                            </div>
                            <h6 class="fw-bold mb-2">{{ $v['headline'] ?? '-' }}</h6>
                            <p class="text-muted small mb-2">{{ $v['primary_text'] ?? '-' }}</p>
                            @if(!empty($v['description']))
                                <p class="text-muted small fst-italic mb-2">"{{ $v['description'] }}"</p>
                            @endif
                            <span class="badge bg-primary">{{ $v['cta'] ?? 'CTA' }}</span>
                        </div>
                        <div class="card-footer bg-white border-top">
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary flex-fill" onclick="copyToClipboard(this)" data-text="{{ e($v['headline'] ?? '') }}">
                                    <i class="fas fa-copy me-1"></i>نسخ العنوان
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteVariation({{ $i }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-magic fa-4x text-muted mb-3 opacity-25"></i>
                <h5 class="text-muted">لا توجد نسخ محفوظة</h5>
                <p class="text-muted">ابدأ بتوليد نسخ إعلانية بالذكاء الاصطناعي</p>
                <a href="{{ route('admin.ai-creative.generate-form') }}" class="btn btn-primary">
                    <i class="fas fa-magic me-2"></i>توليد نسخ الآن
                </a>
            </div>
        </div>
    @endif
</div>

<script>
function copyToClipboard(btn) {
    const text = btn.getAttribute('data-text');
    navigator.clipboard.writeText(text).then(() => {
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>تم النسخ';
        btn.classList.add('btn-success');
        setTimeout(() => {
            btn.innerHTML = original;
            btn.classList.remove('btn-success');
        }, 2000);
    });
}
</script>
@endsection
