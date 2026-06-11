@extends('admin.layouts.app')
@section('title', 'مكتبة الإعلانات')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-search" style="color:#EF4444;margin-left:8px;"></i> مكتبة الإعلانات</h4>
        <p class="text-muted small mb-0">البحث في مكتبة إعلانات فيسبوك وتحليل إعلانات المنافسين</p>
    </div>
    <a href="{{ route('admin.meta-pro-tools.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right"></i> العودة</a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold"><i class="fas fa-search"></i> بحث في المكتبة</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.meta-pro-tools.ad-library.search') }}" id="adSearchForm">
                    @csrf
                    <div class="mb-3">
                        <label class="fw-bold small">كلمة البحث</label>
                        <input class="form-control" name="query" required placeholder="اسم الصفحة أو الكلمة المفتاحية">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small">المنصة</label>
                        <select class="form-select" name="platform">
                            <option value="">الكل</option>
                            <option value="facebook">فيسبوك</option>
                            <option value="instagram">إنستغرام</option>
                            <option value="messenger">Messenger</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small">الدولة</label>
                        <select class="form-select" name="country">
                            <option value="PS">فلسطين</option>
                            <option value="JO">الأردن</option>
                            <option value="SA">السعودية</option>
                            <option value="AE">الإمارات</option>
                            <option value="EG">مصر</option>
                            <option value="US">الولايات المتحدة</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small">العدد</label>
                        <select class="form-select" name="limit">
                            <option value="10">10</option>
                            <option value="20" selected>20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100" id="btn-search">
                        <span id="btn-search-text"><i class="fas fa-search"></i> بحث</span>
                        <span id="btn-search-spin" class="d-none"><span class="spinner-border spinner-border-sm"></span> جاري...</span>
                    </button>
                </form>
                <hr>
                <h6 class="fw-bold small">بحث سريع حسب المجال</h6>
                <div class="d-flex gap-1 flex-wrap">
                    <button class="btn btn-sm btn-outline-primary" onclick="searchIndustry('beauty')">تجميل</button>
                    <button class="btn btn-sm btn-outline-primary" onclick="searchIndustry('salon')">صالونات</button>
                    <button class="btn btn-sm btn-outline-primary" onclick="searchIndustry('medical')">طبي</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list"></i> النتائج <span id="results-count" class="text-muted small"></span></span>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-success" onclick="searchByPage()"><i class="fas fa-building"></i> بحث بصفحة</button>
                </div>
            </div>
            <div class="card-body" id="library-results" style="min-height:400px;max-height:650px;overflow-y:auto;">
                @if(isset($results) && $results['success'])
                    @include('admin.meta-pro-tools._ad_library_results', ['results' => $results])
                @elseif(isset($results) && !$results['success'])
                    <div class="alert alert-danger">{{ $results['message'] ?? 'فشل البحث' }}</div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-search fa-4x mb-3"></i>
                    <p>ابحث عن إعلانات المنافسين لتحليلها</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pageSearchModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-building"></i> بحث بإسم الصفحة</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="fw-bold small">اسم الصفحة</label>
                <input class="form-control" id="page-name-input" placeholder="اسم الصفحة على فيسبوك">
            </div>
            <button class="btn btn-primary w-100" onclick="searchByPageName()"><i class="fas fa-search"></i> بحث</button>
        </div>
        <div id="page-results" class="modal-footer d-block"></div>
    </div></div>
</div>

@push('scripts')
<script>
async function searchIndustry(industry) {
    const el = document.getElementById('library-results');
    el.innerHTML = '<div class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> جاري البحث...</div>';
    try {
        const r = await fetch('{{ route('admin.meta-pro-tools.ad-library.industry') }}', {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json'},
            body: JSON.stringify({ industry, limit: 30 })
        });
        const d = await r.json();
        if (d.competitors) {
            let html = '';
            let total = 0;
            for (const [name, ads] of Object.entries(d.competitors)) {
                total += ads.length;
                html += `<div class="mb-3"><h6 class="fw-bold">${name} (${ads.length})</h6>`;
                ads.forEach((ad, i) => {
                    html += `<div class="border rounded p-2 mb-1 small">
                        <div><b>${ad.creative_title || 'بدون عنوان'}</b></div>
                        <div class="text-muted">${(ad.creative_body || '').substring(0, 100)}${(ad.creative_body || '').length > 100 ? '...' : ''}</div>
                    </div>`;
                });
                html += '</div>';
            }
            document.getElementById('results-count').textContent = `(إجمالي ${total} إعلان)`;
            el.innerHTML = html || '<p class="text-muted text-center py-4">لا توجد نتائج</p>';
        } else {
            el.innerHTML = '<p class="text-muted text-center py-4">لا توجد نتائج</p>';
        }
    } catch(e) {
        el.innerHTML = '<div class="alert alert-danger">فشل البحث</div>';
    }
}

function searchByPage() {
    $('#pageSearchModal').modal('show');
}

async function searchByPageName() {
    const name = document.getElementById('page-name-input').value.trim();
    if (!name) return;
    const el = document.getElementById('page-results');
    el.innerHTML = '<span class="spinner-border spinner-border-sm"></span> جاري البحث...';
    try {
        const r = await fetch('{{ route('admin.meta-pro-tools.ad-library.by-page') }}', {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json'},
            body: JSON.stringify({ page_name: name, limit: 20 })
        });
        const d = await r.json();
        if (d.success && d.ads.length) {
            let html = `<b>تم العثور على ${d.ads.length} إعلان</b><br>`;
            d.ads.forEach(ad => {
                html += `<div class="border rounded p-2 mt-1 small text-end">
                    <div><b>${ad.creative_title || 'بدون عنوان'}</b></div>
                    <div class="text-muted">${(ad.creative_body || '').substring(0, 100)}</div>
                </div>`;
            });
            el.innerHTML = html;
        } else {
            el.innerHTML = '<span class="text-muted">لا توجد إعلانات منشورة لهذه الصفحة</span>';
        }
    } catch(e) {
        el.innerHTML = '<span class="text-danger">فشل البحث</span>';
    }
}
</script>
@endpush
@endsection
