@extends('admin.layouts.app')

@section('title', 'SEO متقدم')

@push('styles')
<style>
.seo-score-circle { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .8rem; flex-shrink: 0; }
.seo-score-high { background: #DCFCE7; color: #16A34A; }
.seo-score-mid { background: #FEF3C7; color: #D97706; }
.seo-score-low { background: #FEE2E2; color: #DC2626; }
.seo-check { color: #16A34A; }
.seo-cross { color: #DC2626; opacity: .4; }
.seo-table td { font-size: .8rem; }
.seo-table .seo-meta { max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-search" style="color:var(--pink-600);margin-left:8px;"></i>SEO متقدم</h1>
        <p class="text-muted small mb-0">تحليل وتحسين محركات البحث لجميع المنتجات</p>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.seo.auto-all') }}" method="POST" onsubmit="return confirm('توليد SEO تلقائياً لجميع المنتجات الناقصة؟')">
            @csrf
            <button class="btn btn-outline-pink"><i class="fas fa-magic"></i> توليد SEO التلقائي</button>
        </form>
        <form action="{{ route('admin.seo.ai-all') }}" method="POST" onsubmit="return confirm('توليد SEO ذكي (AI) للمنتجات الناقصة؟ سيتم معالجة 100 منتج في كل مرة.')">
            @csrf
            <button class="btn btn-pink"><i class="fas fa-robot"></i> توليد SEO ذكي AI</button>
        </form>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md">
        <div class="stat-card-new text-center">
            <div class="stat-value-new" style="font-size:1.5rem;">{{ $stats['total'] }}</div>
            <div class="stat-label-new">إجمالي المنتجات</div>
        </div>
    </div>
    <div class="col-md">
        <div class="stat-card-new text-center">
            <div class="stat-value-new" style="font-size:1.5rem;color:#16a34a;">{{ $stats['seo_ready'] }}</div>
            <div class="stat-label-new">جاهزة SEO</div>
            <div class="progress-thin mt-2">
                <div class="progress-bar bg-success" style="width:{{ $stats['total'] > 0 ? round(($stats['seo_ready']/$stats['total'])*100) : 0 }}%"></div>
            </div>
        </div>
    </div>
    <div class="col-md">
        <div class="stat-card-new text-center">
            <div class="stat-value-new" style="font-size:1.5rem;color:#dc2626;">{{ $stats['missing_meta'] }}</div>
            <div class="stat-label-new">ناقصة Meta</div>
        </div>
    </div>
    <div class="col-md">
        <div class="stat-card-new text-center">
            <div class="stat-value-new" style="font-size:1.5rem;color:#d97706;">{{ $stats['missing_keywords'] }}</div>
            <div class="stat-label-new">ناقصة كلمات مفتاحية</div>
        </div>
    </div>
    <div class="col-md">
        <div class="stat-card-new text-center">
            <div class="stat-value-new" style="font-size:1.5rem;color:#6b7280;">{{ $stats['missing_og'] }}</div>
            <div class="stat-label-new">ناقصة OG Image</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="بحث عن منتج..." value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">كل المنتجات</option>
                    <option value="missing_meta" {{ request('filter') === 'missing_meta' ? 'selected' : '' }}>ناقصة Meta</option>
                    <option value="missing_keywords" {{ request('filter') === 'missing_keywords' ? 'selected' : '' }}>ناقصة الكلمات المفتاحية</option>
                    <option value="missing_og" {{ request('filter') === 'missing_og' ? 'selected' : '' }}>ناقصة OG Image</option>
                    <option value="seo_ready" {{ request('filter') === 'seo_ready' ? 'selected' : '' }}>جاهزة SEO</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-pink"><i class="fas fa-filter"></i></button>
                @if(request()->anyFilled(['search','filter']))
                <a href="{{ route('admin.seo.index') }}" class="btn btn-sm btn-outline-secondary">إلغاء</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Products Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 seo-table align-middle">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>Meta Title</th>
                        <th>Meta Description</th>
                        <th>الكلمات المفتاحية</th>
                        <th>OG Image</th>
                        <th style="width:60px;">النتيجة</th>
                        <th style="width:100px;">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:36px;height:36px;border-radius:6px;background:var(--pink-50);overflow:hidden;flex-shrink:0;">
                                    @if($product->main_image_url)
                                        <img src="{{ $product->main_image_url }}" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--pink-600);font-size:.7rem;"><i class="fas fa-box"></i></div>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-bold small">{{ \Str::limit($product->name_ar, 35) }}</div>
                                    <div class="text-muted" style="font-size:.65rem;">{{ $product->sku ?? '#' . $product->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($product->meta_title)
                                <span class="seo-meta text-success small">{{ \Str::limit($product->meta_title, 40) }}</span>
                            @else
                                <i class="fas fa-times seo-cross"></i>
                            @endif
                        </td>
                        <td>
                            @if($product->meta_description)
                                <span class="seo-meta text-success small">{{ \Str::limit($product->meta_description, 40) }}</span>
                            @else
                                <i class="fas fa-times seo-cross"></i>
                            @endif
                        </td>
                        <td>
                            @php
                                $kws = $product->meta_keywords;
                                if (is_string($kws)) $kws = json_decode($kws, true);
                            @endphp
                            @if(!empty($kws))
                                <span class="badge bg-info rounded-pill">{{ is_array($kws) ? count($kws) : 1 }} كلمة</span>
                            @else
                                <i class="fas fa-times seo-cross"></i>
                            @endif
                        </td>
                        <td>
                            @if($product->og_image)
                                <i class="fas fa-check seo-check"></i>
                            @else
                                <i class="fas fa-times seo-cross"></i>
                            @endif
                        </td>
                        <td>
                            @php $sc = $product->seo_score; @endphp
                            <div class="seo-score-circle {{ $sc >= 80 ? 'seo-score-high' : ($sc >= 50 ? 'seo-score-mid' : 'seo-score-low') }}">
                                {{ $sc }}%
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.seo.edit', $product->id) }}" class="btn btn-sm btn-outline-secondary" title="تعديل SEO"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.seo.auto', $product->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-info" title="توليد تلقائي"><i class="fas fa-magic"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">لا توجد منتجات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">{{ $products->links() }}</div>
@endsection
