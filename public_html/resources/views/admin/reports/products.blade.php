@extends('admin.layouts.app')
@section('title', 'تقرير المنتجات')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-box-open text-pink"></i> تقرير المنتجات</h3>
            <p class="text-muted mb-0">تحليل مبيعات المنتجات والمخزون</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.products.export', request()->all()) }}" class="btn btn-success rounded-pill"><i class="fas fa-file-excel"></i> تصدير Excel</a>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary rounded-pill"><i class="fas fa-arrow-right"></i> العودة</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">من تاريخ</label>
                    <input type="date" name="date_from" class="form-control rounded-3" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">إلى تاريخ</label>
                    <input type="date" name="date_to" class="form-control rounded-3" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-pink rounded-pill"><i class="fas fa-filter"></i> فلترة</button>
                    <a href="{{ route('admin.reports.products') }}" class="btn btn-outline-secondary rounded-pill"><i class="fas fa-redo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-3 px-3 pb-0">
                    <h5 class="fw-bold"><i class="fas fa-star text-pink"></i> المنتجات المباعة</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>المنتج</th>
                                <th>الرمز</th>
                                <th>الكمية المباعة</th>
                                <th>عدد الطلبات</th>
                                <th>الإيرادات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $item)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $loop->iteration + $products->firstItem() - 1 }}</td>
                                <td class="fw-bold">{{ $item->product_name }}</td>
                                <td><code>{{ $item->product_sku }}</code></td>
                                <td><span class="badge bg-success rounded-pill">{{ $item->total_qty }}</span></td>
                                <td>{{ $item->order_count }}</td>
                                <td class="fw-bold text-pink">{{ number_format($item->total_revenue, 2) }} ₪</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">لا توجد بيانات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($products->hasPages())
                <div class="card-footer bg-transparent border-0 px-3 py-2">{{ $products->links() }}</div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-3 px-3 pb-0">
                    <h5 class="fw-bold"><i class="fas fa-exclamation-triangle text-warning"></i> مخزون منخفض</h5>
                </div>
                <div class="card-body">
                    @forelse($lowStock as $product)
                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded-3 {{ $product->quantity <= 3 ? 'bg-danger bg-opacity-10' : 'bg-warning bg-opacity-10' }}">
                        <div>
                            <div class="fw-bold small">{{ $product->name }}</div>
                            <small class="text-muted">رمز: {{ $product->sku ?? '-' }}</small>
                        </div>
                        <span class="badge rounded-pill {{ $product->quantity <= 3 ? 'bg-danger' : 'bg-warning' }} fw-bold">{{ $product->quantity }}</span>
                    </div>
                    @empty
                    <p class="text-center text-muted py-3">لا يوجد مخزون منخفض</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.text-pink { color: #d97a8c !important; }
.bg-pink { background: #d97a8c !important; }
.btn-pink { background: #d97a8c; color: #fff; border: none; }
.btn-pink:hover { background: #c56174; color: #fff; }
</style>
@endsection
