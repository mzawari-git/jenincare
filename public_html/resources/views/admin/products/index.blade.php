@extends('admin.layouts.app')

@section('title', 'المنتجات')

@section('content')

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.products.index') }}" class="text-decoration-none">
            <div class="stat-card text-center py-3">
                <div style="font-size:1.6rem;font-weight:800;color:var(--gray-800);">{{ $stats['total'] }}</div>
                <div style="font-size:.75rem;color:var(--gray-500);">كل المنتجات</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.products.index', ['status' => 'active']) }}" class="text-decoration-none">
            <div class="stat-card text-center py-3" style="border:1px solid #DCFCE7;">
                <div style="font-size:1.6rem;font-weight:800;color:#16A34A;">{{ $stats['active'] }}</div>
                <div style="font-size:.75rem;color:var(--gray-500);">نشط</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="{{ route('admin.products.index', ['status' => 'inactive']) }}" class="text-decoration-none">
            <div class="stat-card text-center py-3" style="border:1px solid #FEE2E2;">
                <div style="font-size:1.6rem;font-weight:800;color:#991B1B;">{{ $stats['inactive'] }}</div>
                <div style="font-size:.75rem;color:var(--gray-500);">غير نشط</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card text-center py-3" style="border:1px solid #FEF3C7;">
            <div style="font-size:1.6rem;font-weight:800;color:#D97706;">{{ $stats['low_stock'] }}</div>
            <div style="font-size:.75rem;color:var(--gray-500);">مخزون منخفض</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card text-center py-3" style="border:1px solid #FEE2E2;">
            <div style="font-size:1.6rem;font-weight:800;color:#DC2626;">{{ $stats['out_of_stock'] }}</div>
            <div style="font-size:.75rem;color:var(--gray-500);">نفذ المخزون</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card text-center py-3">
            <div style="font-size:1.6rem;font-weight:800;color:var(--pink-600);">{{ number_format($stats['total_value']) }} ₪</div>
            <div style="font-size:.75rem;color:var(--gray-500);">قيمة المخزون</div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h5 mb-0">قائمة المنتجات</h1>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.products.import.template') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-download"></i> قالب CSV</a>
        <a href="{{ route('admin.products.import') }}" class="btn btn-sm btn-outline-pink"><i class="fas fa-file-excel"></i> استيراد Excel</a>
        <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-pink"><i class="fas fa-plus"></i> إضافة منتج</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form action="{{ route('admin.products.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="ابحث باسم المنتج أو SKU..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">كل الحالات</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>مسودة</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="stock" class="form-select form-select-sm">
                    <option value="">كل المخزون</option>
                    <option value="in" {{ request('stock') === 'in' ? 'selected' : '' }}>متوفر</option>
                    <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>منخفض</option>
                    <option value="out" {{ request('stock') === 'out' ? 'selected' : '' }}>نافذ</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-pink btn-sm w-100"><i class="fas fa-filter"></i> تصفية</button>
            </div>
            @if(request()->anyFilled(['search', 'status', 'stock']))
            <div class="col-md-1">
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm w-100" title="مسح التصفية"><i class="fas fa-times"></i></a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>المنتج</th>
                    <th>SKU</th>
                    <th>سعر التجزئة</th>
                    <th>جملة</th>
                    <th>المخزون</th>
                    <th>الحالة</th>
                    <th style="width:80px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td class="fw-bold text-muted small">{{ $product->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($product->main_image_url)
                            <img src="{{ $product->main_image_url }}" style="width:36px;height:36px;border-radius:8px;object-fit:cover;">
                            @else
                            <div style="width:36px;height:36px;border-radius:8px;background:var(--gray-100);display:flex;align-items:center;justify-content:center;color:var(--gray-400);"><i class="fas fa-box"></i></div>
                            @endif
                            <div>
                                <div class="fw-bold small">{{ $product->name_ar }}</div>
                                @if($product->name_en)
                                <div class="text-muted" style="font-size:.7rem;">{{ $product->name_en }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-muted small" style="font-family:monospace;">{{ $product->sku ?? '-' }}</td>
                    <td class="fw-bold" style="color:var(--pink-600);">{{ number_format($product->b2c_price ?? 0, 2) }} ₪</td>
                    <td class="text-muted small">{{ $product->b2b_price ? number_format($product->b2b_price, 2) . ' ₪' : '-' }}</td>
                    <td>
                        @php
                            $qty = $product->stock_quantity ?? 0;
                            if ($qty > 10) { $sBg = '#DCFCE7'; $sC = '#16A34A'; }
                            elseif ($qty > 0) { $sBg = '#FEF3C7'; $sC = '#D97706'; }
                            else { $sBg = '#FEE2E2'; $sC = '#DC2626'; }
                        @endphp
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:9999px;font-size:.75rem;font-weight:600;background:{{ $sBg }};color:{{ $sC }};">{{ $qty }}</span>
                    </td>
                    <td>
                        @if($product->status === 'active')
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:9999px;font-size:.75rem;font-weight:600;background:#DCFCE7;color:#16A34A;">نشط</span>
                        @else
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:9999px;font-size:.75rem;font-weight:600;background:var(--gray-100);color:var(--gray-500);">{{ $product->status }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm" style="color:var(--pink-600);padding:4px 6px;" title="تعديل"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm" style="color:#DC2626;padding:4px 6px;" onclick="return confirm('حذف هذا المنتج؟')" title="حذف"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5 text-muted">
                    <i class="fas fa-box-open mb-2" style="font-size:2.5rem;display:block;opacity:.2;"></i>
                    لا توجد منتجات. <a href="{{ route('admin.products.create') }}">أضف أول منتج</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
@endsection
