@extends('admin.layouts.app')

@section('title', 'إدارة صفحة العرض (Landing Page)')

@section('content')
<h1 class="h4 mb-2">إدارة صفحة العرض <span style="color:var(--pink-600);">(Landing Page)</span></h1>
<p class="text-muted small mb-4">من هنا تتحكم بالمنتجات التي تظهر في صفحة <code>/products-landing</code>. فعّل أي منتج ليظهر فوراً في الصفحة.</p>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show py-2 small">
    {{ session('success') }}
    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
</div>
@endif

@if($landingProducts->isNotEmpty())
<div class="alert alert-info py-2 small mb-4">
    <i class="fas fa-info-circle"></i> يوجد <strong>{{ $landingProducts->count() }}</strong> منتج(ة) مفعّل(ة) حالياً في صفحة العرض.
    <a href="{{ url('/products-landing') }}" target="_blank" class="alert-link">
        <i class="fas fa-external-link-alt"></i> معاينة الصفحة
    </a>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>المنتج</th>
                    <th>السعر</th>
                    <th>المخزون</th>
                    <th style="width:120px;">الحالة في Landing</th>
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
                    <td class="fw-bold" style="color:var(--pink-600);">{{ number_format($product->b2c_price ?? 0, 2) }} ₪</td>
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
                        @if($product->show_on_landing)
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:9999px;font-size:.75rem;font-weight:600;background:#DCFCE7;color:#16A34A;">
                            <i class="fas fa-check-circle"></i> مفعّل
                        </span>
                        @else
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:9999px;font-size:.75rem;font-weight:600;background:var(--gray-100);color:var(--gray-500);">
                            <i class="fas fa-times-circle"></i> معطّل
                        </span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('admin.products.toggle-landing', $product) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $product->show_on_landing ? 'btn-outline-danger' : 'btn-outline-success' }}" style="padding:4px 10px;font-size:.75rem;">
                                @if($product->show_on_landing)
                                <i class="fas fa-eye-slash"></i> إخفاء
                                @else
                                <i class="fas fa-eye"></i> إظهار
                                @endif
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">
                    <i class="fas fa-box-open mb-2" style="font-size:2.5rem;display:block;opacity:.2;"></i>
                    لا توجد منتجات.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
@endsection