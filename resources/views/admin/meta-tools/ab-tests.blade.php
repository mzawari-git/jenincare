@extends('admin.layouts.app')
@section('title', 'A/B Testing')
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold"><i class="fas fa-flask text-purple me-2"></i>A/B Testing</h4><p class="text-muted mb-0 small">اختبار المقارنة للإعلانات على Meta و Google</p></div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTestModal"><i class="fas fa-plus me-1"></i>اختبار جديد</button>
    </div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom"><h6 class="mb-0 fw-bold">الاختبارات الجارية</h6></div>
        <div class="card-body p-0">
            @forelse($tests as $test)
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                <div><strong>{{ $test->name }}</strong><br><small class="text-muted">{{ $test->test_type }} - {{ $test->platform }} - بدأ {{ $test->started_at ? \Carbon\Carbon::parse($test->started_at)->diffForHumans() : '' }}</small></div>
                <button class="btn btn-sm btn-outline-info" onclick="analyzeTest({{ $test->id }})">تحليل</button>
            </div>
            @empty
            <div class="text-center py-5"><i class="fas fa-flask fa-3x text-muted mb-3 opacity-25"></i><h5 class="text-muted">لا توجد اختبارات جارية</h5></div>
            @endforelse
        </div>
    </div>
</div>
<div class="modal fade" id="createTestModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('admin.ab-tests.create') }}" method="POST">
    @csrf
    <div class="modal-header"><h5 class="modal-title fw-bold">اختبار A/B جديد</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label fw-bold">اسم الاختبار</label><input type="text" class="form-control" name="name" required></div>
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">المنصة</label><select class="form-select" name="platform"><option value="meta">Meta</option><option value="google">Google</option></select></div>
        <div class="col-md-6 mb-3"><label class="form-label fw-bold">نوع الاختبار</label><select class="form-select" name="test_type"><option value="headline">عنوان</option><option value="primary_text">نص أساسي</option><option value="image">صورة</option><option value="cta">دعوة لاتخاذ إجراء</option></select></div></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button><button type="submit" class="btn btn-primary">إنشاء</button></div>
    </form>
</div></div></div>
<script>
function analyzeTest(id) {
    fetch('{{ route('admin.ab-tests.index') }}/' + id + '/analyze').then(r=>r.json()).then(d=>{
        let msg = `النتيجة:\nCTR A: ${d.variant_a?.ctr}% | CTR B: ${d.variant_b?.ctr}%\nالثقة: ${d.confidence}%\nالفائز: ${d.winner || 'لم يُحدد بعد'}\nالتحسن: ${d.lift}%`;
        alert(msg);
    });
}
</script>
@endsection