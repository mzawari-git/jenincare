@extends('admin.layouts.app')

@section('title', 'المدونة والمقالات')

@push('styles')
<style>
.blog-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; }
.blog-header h2 { font-size: 1.35rem; font-weight: 800; color: var(--gray-800); margin: 0; display: flex; align-items: center; gap: .5rem; }
.blog-header h2 i { color: var(--pink-600); font-size: 1.1rem; }
.blog-header .header-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.stat-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: .75rem; margin-bottom: 1.5rem; }
.stat-cards .stat-box { background: #fff; border-radius: 12px; padding: 1rem; border: 1px solid var(--gray-200); text-align: center; transition: all .2s; }
.stat-cards .stat-box:hover { box-shadow: 0 4px 12px rgba(0,0,0,.06); transform: translateY(-1px); }
.stat-cards .stat-box .stat-num { font-size: 1.5rem; font-weight: 800; color: var(--gray-800); }
.stat-cards .stat-box .stat-lbl { font-size: .75rem; color: var(--gray-500); margin-top: 2px; }
.stat-cards .stat-box .stat-icon { font-size: 1rem; margin-bottom: 4px; }

.blog-table-card { background: #fff; border-radius: 16px; border: 1px solid var(--gray-200); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }

.blog-table-card .table { margin: 0; }
.blog-table-card .table thead th {
    background: var(--gray-50);
    font-size: .75rem;
    font-weight: 700;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: .03em;
    border-bottom: 1px solid var(--gray-200);
    padding: .85rem 1rem;
}
.blog-table-card .table tbody td {
    padding: .85rem 1rem;
    vertical-align: middle;
    font-size: .875rem;
    border-bottom: 1px solid var(--gray-100);
}
.blog-table-card .table tbody tr:last-child td { border-bottom: none; }
.blog-table-card .table tbody tr:hover { background: var(--pink-50); }
.blog-table-card .table tbody tr.deleted { opacity: .55; background: repeating-linear-gradient(-45deg, transparent, transparent 8px, rgba(0,0,0,.01) 8px, rgba(0,0,0,.01) 16px); }

.blog-thumb { width: 42px; height: 42px; border-radius: 10px; object-fit: cover; flex-shrink: 0; background: var(--gray-100); }
.blog-thumb-placeholder { width: 42px; height: 42px; border-radius: 10px; background: var(--pink-50); display: flex; align-items: center; justify-content: center; color: var(--pink-600); font-size: 1rem; flex-shrink: 0; }

.cat-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 9999px; font-size: .7rem; font-weight: 700; }

.status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 9999px; font-size: .7rem; font-weight: 700; border: none; cursor: pointer; transition: all .2s; }
.status-badge:hover { filter: brightness(1.1); transform: translateY(-1px); }

.action-btn-icon { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: var(--gray-400); border: none; background: transparent; transition: all .15s; text-decoration: none; font-size: .8rem; }
.action-btn-icon:hover { background: var(--gray-100); color: var(--gray-700); }
.action-btn-icon.edit:hover { background: var(--pink-50); color: var(--pink-600); }
.action-btn-icon.delete:hover { background: #FEE2E2; color: #dc2626; }
.action-btn-icon.restore:hover { background: #DCFCE7; color: #16a34a; }

.search-box { display: flex; align-items: center; gap: .5rem; padding: .5rem .75rem; background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: 10px; transition: all .2s; }
.search-box:focus-within { border-color: var(--pink-400); box-shadow: 0 0 0 3px rgba(219,39,119,.08); }
.search-box input { border: none; background: transparent; outline: none; font-size: .8rem; color: var(--gray-700); min-width: 180px; }
.search-box input::placeholder { color: var(--gray-400); }
.search-box i { color: var(--gray-400); font-size: .85rem; }

.filter-select { padding: .5rem .75rem; background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: 10px; font-size: .8rem; color: var(--gray-700); outline: none; transition: all .2s; }
.filter-select:focus { border-color: var(--pink-400); box-shadow: 0 0 0 3px rgba(219,39,119,.08); }

.flex-between { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem; }
.flex-gap { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }

.featured-star { color: #f59e0b; font-size: .75rem; }

.tab-btn { padding: .4rem .85rem; border-radius: 8px; border: 1px solid var(--gray-200); background: #fff; color: var(--gray-600); font-size: .8rem; font-weight: 600; cursor: pointer; transition: all .15s; }
.tab-btn:hover { border-color: var(--pink-300); color: var(--pink-600); }
.tab-btn.active { background: var(--pink-600); color: #fff; border-color: var(--pink-600); }
</style>
@endpush

@section('content')
@php
    $total = $posts->total();
    $published = $posts->filter(fn($p) => $p->is_published && !$p->trashed())->count();
    $featured = $posts->filter(fn($p) => $p->is_featured)->count();
    $hidden = $posts->filter(fn($p) => !$p->is_published && !$p->trashed())->count();
@endphp

{{-- Stats --}}
<div class="stat-cards">
    <div class="stat-box">
        <div class="stat-icon" style="color:var(--pink-600);"><i class="fas fa-newspaper"></i></div>
        <div class="stat-num">{{ $total }}</div>
        <div class="stat-lbl">إجمالي المقالات</div>
    </div>
    <div class="stat-box">
        <div class="stat-icon" style="color:#16a34a;"><i class="fas fa-check-circle"></i></div>
        <div class="stat-num">{{ $published }}</div>
        <div class="stat-lbl">منشور</div>
    </div>
    <div class="stat-box">
        <div class="stat-icon" style="color:#dc2626;"><i class="fas fa-eye-slash"></i></div>
        <div class="stat-num">{{ $hidden }}</div>
        <div class="stat-lbl">مخفي</div>
    </div>
    <div class="stat-box" style="cursor:pointer;" onclick="setTab('trash')">
        <div class="stat-icon" style="color:#78716c;"><i class="fas fa-trash-alt"></i></div>
        <div class="stat-num">{{ $trashedCount }}</div>
        <div class="stat-lbl">سلة المحذوفات</div>
    </div>
    <div class="stat-box">
        <div class="stat-icon" style="color:#f59e0b;"><i class="fas fa-star"></i></div>
        <div class="stat-num">{{ $featured }}</div>
        <div class="stat-lbl">مميز</div>
    </div>
</div>

{{-- Header --}}
<div class="blog-header">
    <h2><i class="fas fa-newspaper"></i> المدونة والمقالات</h2>
    <div class="header-actions">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="blogSearch" placeholder="بحث في المقالات..." oninput="filterPosts()">
        </div>
        <select class="filter-select" id="blogCategoryFilter" onchange="filterPosts()">
            <option value="">جميع الأقسام</option>
            <option value="articles">مقالات عن المنتجات</option>
            <option value="tips">نصائح للعناية</option>
            <option value="news">أخبار التجميل</option>
            <option value="guides">أدلة الاستخدام</option>
        </select>
        <a href="{{ route('admin.blog.create') }}" class="btn btn-pink btn-sm rounded-pill px-3" style="padding:.5rem 1rem;">
            <i class="fas fa-plus"></i> مقال جديد
        </a>
    </div>
</div>

{{-- Tabs --}}
<div style="display:flex;align-items:center;gap:4px;margin-bottom:1rem;flex-wrap:wrap;">
    <button class="tab-btn active" data-tab="all" onclick="setTab('all')">الكل</button>
    <button class="tab-btn" data-tab="published" onclick="setTab('published')">منشور</button>
    <button class="tab-btn" data-tab="hidden" onclick="setTab('hidden')">مخفي</button>
    <button class="tab-btn" data-tab="trash" onclick="setTab('trash')">
        سلة المحذوفات @if($trashedCount > 0)<span class="badge bg-danger rounded-pill" style="font-size:.55rem;">{{ $trashedCount }}</span>@endif
    </button>
    <div style="margin-right:auto;">
        @if($trashedCount > 0)
        <form action="{{ route('admin.blog.empty-trash') }}" method="POST" class="d-inline" onsubmit="return confirm('سيتم حذف {{ $trashedCount }} مقال نهائياً. متأكد؟')">
            @csrf @method('DELETE')
            <button class="btn btn-sm" style="border:1px solid var(--gray-200);border-radius:8px;color:var(--gray-500);padding:.3rem .6rem;font-size:.75rem;">
                <i class="fas fa-trash-alt"></i> إفراغ السلة
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Table --}}
<div class="blog-table-card">
    <table class="table">
        <thead>
            <tr>
                <th style="width:40%;">المقال</th>
                <th>القسم</th>
                <th>التاريخ</th>
                <th>الحالة</th>
                <th style="width:120px;"></th>
            </tr>
        </thead>
        <tbody id="blogTableBody">
            @forelse($posts as $post)
            <tr class="{{ $post->trashed() ? 'deleted' : '' }}" data-category="{{ $post->category }}">
                <td>
                    <div class="d-flex align-items-center gap-3">
                        @if($post->image_url)
                            <img src="{{ $post->image_url }}" alt="" class="blog-thumb">
                        @else
                            <div class="blog-thumb-placeholder"><i class="fas fa-file-alt"></i></div>
                        @endif
                        <div style="min-width:0;">
                            <div class="fw-bold" style="font-size:.9rem;color:var(--gray-800);display:flex;align-items:center;gap:4px;">
                                @if($post->is_featured)<span class="featured-star"><i class="fas fa-star"></i></span>@endif
                                {{ $post->title_ar }}
                                @if($post->trashed())<span class="badge bg-danger rounded-pill" style="font-size:.55rem;">محذوف</span>@endif
                            </div>
                            <div style="font-size:.75rem;color:var(--gray-400);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:320px;">
                                {{ Str::limit(strip_tags($post->excerpt_ar ?? $post->content_ar), 80) }}
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="cat-badge" style="background:{{ $post->category_color }}15;color:{{ $post->category_color }};">
                        <span style="width:6px;height:6px;border-radius:50%;background:{{ $post->category_color }};display:inline-block;"></span>
                        {{ $post->category_label }}
                    </span>
                </td>
                <td style="color:var(--gray-500);font-size:.8rem;white-space:nowrap;">
                    <div>{{ $post->created_at->format('Y/m/d') }}</div>
                    <div style="font-size:.65rem;">{{ $post->created_at->format('H:i') }}</div>
                </td>
                <td>
                    <form action="{{ route('admin.blog.toggle', $post->id) }}" method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        @if($post->is_published)
                            <button class="status-badge" style="background:#DCFCE7;color:#16a34a;">
                                <i class="fas fa-check-circle"></i> منشور
                            </button>
                        @else
                            <button class="status-badge" style="background:#FEE2E2;color:#dc2626;">
                                <i class="fas fa-eye-slash"></i> مخفي
                            </button>
                        @endif
                    </form>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-1" style="direction:ltr;">
                        <a href="{{ route('admin.blog.edit', $post->id) }}" class="action-btn-icon edit" title="تعديل">
                            <i class="fas fa-pen"></i>
                        </a>
                        @if($post->trashed())
                            <form action="{{ route('admin.blog.restore', $post->id) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="action-btn-icon restore" title="استعادة"><i class="fas fa-trash-restore"></i></button>
                            </form>
                        @else
                            <form action="{{ route('admin.blog.destroy', $post->id) }}" method="POST" class="d-inline" onsubmit="return confirm('متأكد من حذف هذا المقال؟')">
                                @csrf @method('DELETE')
                                <button class="action-btn-icon delete" title="حذف"><i class="fas fa-trash"></i></button>
                            </form>
                        @endif
                        <a href="{{ route('blog.show', $post->slug ?? $post->id) }}" target="_blank" class="action-btn-icon" title="عرض">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr class="empty-row">
                <td colspan="5" class="text-center py-5">
                    <div style="font-size:2.5rem;color:var(--gray-200);margin-bottom:.5rem;"><i class="fas fa-newspaper"></i></div>
                    <div style="color:var(--gray-500);font-weight:600;">لا توجد مقالات بعد</div>
                    <div style="color:var(--gray-400);font-size:.85rem;margin-top:4px;">أضف أول مقال الآن</div>
                    <a href="{{ route('admin.blog.create') }}" class="btn btn-pink btn-sm rounded-pill mt-3 px-4">
                        <i class="fas fa-plus"></i> إضافة مقال
                    </a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($posts->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $posts->links() }}
</div>
@endif
@endsection

@push('scripts')
<script>
let currentTab = 'all';

function filterPosts() {
    const query = document.getElementById('blogSearch').value.toLowerCase();
    const category = document.getElementById('blogCategoryFilter').value;
    const rows = document.querySelectorAll('#blogTableBody tr');
    rows.forEach(row => {
        if (row.classList.contains('empty-row')) return;
        const text = row.textContent.toLowerCase();
        const cat = row.dataset.category || '';
        const isTrashed = row.classList.contains('deleted');
        const isPublished = !isTrashed && row.querySelector('.status-badge')?.innerHTML.includes('منشور');
        const matchSearch = !query || text.includes(query);
        const matchCategory = !category || cat === category;
        let matchTab = true;
        if (currentTab === 'published') matchTab = !!isPublished;
        else if (currentTab === 'hidden') matchTab = !isTrashed && !isPublished;
        else if (currentTab === 'trash') matchTab = isTrashed;
        row.style.display = matchSearch && matchCategory && matchTab ? '' : 'none';
    });
}

function setTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tab);
    });
    filterPosts();
}
</script>
@endpush
