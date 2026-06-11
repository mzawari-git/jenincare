@extends('admin.layouts.app')

@section('title', 'AI Creative Copilot')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-magic text-purple me-2"></i>AI Creative Copilot
            </h4>
            <p class="text-muted mb-0 small">توليد نسخ إعلانية احترافية بالذكاء الاصطناعي لـ Meta و Google</p>
        </div>
        <a href="{{ route('admin.ai-creative.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-history me-1"></i>النسخ المحفوظة
        </a>
    </div>

    @if(isset($error))
        <div class="alert alert-danger">{{ $error }}</div>
    @endif

    @if(isset($success) && $success)
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>تم التوليد بنجاح عبر {{ $provider ?? 'AI' }}
        </div>
    @endif

    <div class="row">
        <!-- Generation Form -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-cog me-2"></i>إعدادات التوليد</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.ai-creative.generate') }}" method="POST" id="generateForm">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">المنصة الإعلانية</label>
                            <div class="d-flex gap-2">
                                <label class="flex-fill">
                                    <input type="radio" class="btn-check" name="platform" value="meta" checked>
                                    <div class="btn btn-outline-primary w-100 text-center py-2">
                                        <i class="fab fa-facebook me-1"></i>Meta
                                    </div>
                                </label>
                                <label class="flex-fill">
                                    <input type="radio" class="btn-check" name="platform" value="google">
                                    <div class="btn btn-outline-danger w-100 text-center py-2">
                                        <i class="fab fa-google me-1"></i>Google
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">المنتج / الخدمة</label>
                            <input type="text" class="form-control" name="product_name" required
                                   placeholder="مثال: باقة العناية بالبشرة الشاملة" value="{{ old('product_name') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">وصف الخدمة</label>
                            <textarea class="form-control" name="service_description" rows="3"
                                      placeholder="وصف تفصيلي للخدمة يساعد AI على توليد نسخ أفضل">{{ old('service_description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">هدف الحملة</label>
                            <select class="form-select" name="objective">
                                <option value="conversions">التحويلات (الحجوزات)</option>
                                <option value="traffic">الزيارات</option>
                                <option value="awareness">التوعية بالعلامة التجارية</option>
                                <option value="leads">العملاء المحتملون</option>
                                <option value="engagement">التفاعل</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">النبرة</label>
                            <select class="form-select" name="tone">
                                <option value="professional">احترافية وموثوقة</option>
                                <option value="friendly">ودية وم亲切ة</option>
                                <option value="luxury">فاخرة ورفيعة</option>
                                <option value="urgent">عاجلة ونادرة</option>
                                <option value="educational">تعليمية وświadقية</option>
                                <option value="emotional">عاطفية ومُلهمة</option>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-8">
                                <label class="form-label fw-bold">الجمهور المستهدف</label>
                                <input type="text" class="form-control" name="audience"
                                       value="{{ old('audience', 'نساء في فلسطين المهتمات بالعناية بالجمال والبشرة') }}">
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-bold">عدد النسخ</label>
                                <input type="number" class="form-control" name="num_variations" value="5" min="1" max="10">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2" id="generateBtn">
                            <i class="fas fa-magic me-2"></i>توليد النسخ الإعلانية
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Generated Variations -->
        <div class="col-lg-7">
            @if(isset($variations) && count($variations) > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-spell-check me-2"></i>النسخ المولدة ({{ count($variations) }})</h6>
                        <span class="badge bg-success">مُولّد عبر {{ $provider ?? 'AI' }}</span>
                    </div>
                    <div class="card-body" style="max-height:600px;overflow-y:auto;">
                        @foreach($variations as $i => $v)
                            <div class="border rounded-3 p-3 mb-3 {{ ($v['compliance_score'] ?? 0) >= 80 ? 'border-success' : (($v['compliance_score'] ?? 0) >= 50 ? 'border-warning' : 'border-danger') }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-light text-dark">نسخة {{ $i + 1 }}</span>
                                    <div class="d-flex gap-1">
                                        <span class="badge {{ ($v['quality_score'] ?? 0) >= 70 ? 'bg-success' : (($v['quality_score'] ?? 0) >= 50 ? 'bg-warning' : 'bg-danger') }}">
                                            جودة: {{ $v['quality_score'] ?? 0 }}
                                        </span>
                                        <span class="badge {{ ($v['compliance_score'] ?? 0) >= 80 ? 'bg-success' : (($v['compliance_score'] ?? 0) >= 50 ? 'bg-warning' : 'bg-danger') }}">
                                            امتثال: {{ $v['compliance_score'] ?? 0 }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <small class="text-muted fw-bold">العنوان:</small>
                                    <div class="fw-bold">{{ $v['headline'] }}</div>
                                </div>

                                <div class="mb-2">
                                    <small class="text-muted fw-bold">النص الأساسي:</small>
                                    <div>{{ $v['primary_text'] }}</div>
                                </div>

                                @if(!empty($v['description']))
                                <div class="mb-2">
                                    <small class="text-muted fw-bold">الوصف:</small>
                                    <div class="text-muted">{{ $v['description'] }}</div>
                                </div>
                                @endif

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary">{{ $v['cta'] }}</span>
                                    <small class="text-muted">{{ mb_strlen($v['headline'] ?? '') }} / {{ $platform === 'google' ? 30 : 40 }} حرف</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="card-footer bg-white border-top">
                        <form action="{{ route('admin.ai-creative.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="platform" value="{{ $platform ?? 'meta' }}">
                            <input type="hidden" name="variations" value="{{ json_encode($variations) }}">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-save me-2"></i>حفظ جميع النسخ كمسودات
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-magic fa-4x text-muted mb-3 opacity-25"></i>
                        <h5 class="text-muted">ابدأ بتوليد النسخ الإعلانية</h5>
                        <p class="text-muted mb-4">اختر الإعدادات وسنتوليد لك نسخاً إعلانية احترافية بالذكاء الاصطناعي</p>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="row g-2 text-start">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center gap-2 text-muted small">
                                            <i class="fas fa-check text-success"></i>10 نسخ في 30 ثانية
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center gap-2 text-muted small">
                                            <i class="fas fa-check text-success"></i>فحص الامتثال التلقائي
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center gap-2 text-muted small">
                                            <i class="fas fa-check text-success"></i>متوافق مع سياسات المنصات
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center gap-2 text-muted small">
                                            <i class="fas fa-check text-success"></i>عربي + إنجليزي
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.getElementById('generateForm')?.addEventListener('submit', function() {
    document.getElementById('generateBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري التوليد...';
    document.getElementById('generateBtn').disabled = true;
});
</script>
@endsection
