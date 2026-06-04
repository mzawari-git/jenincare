@extends('admin.layouts.app')
@section('title', 'التقارير والتحليلات')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-chart-bar text-pink"></i> التقارير والتحليلات</h3>
            <p class="text-muted mb-0">نظرة شاملة على أداء المتجر والمبيعات والعملاء</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="background:linear-gradient(135deg, #EC4899, #BE185D)">
                <i class="fas fa-shopping-bag stat-icon"></i>
                <div class="stat-value">{{ number_format($totalOrders) }}</div>
                <div class="stat-label">إجمالي الطلبات</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="background:linear-gradient(135deg, #10B981, #047857)">
                <i class="fas fa-dollar-sign stat-icon"></i>
                <div class="stat-value">{{ number_format($totalRevenue, 2) }} ₪</div>
                <div class="stat-label">الإيرادات</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="background:linear-gradient(135deg, #3B82F6, #1D4ED8)">
                <i class="fas fa-users stat-icon"></i>
                <div class="stat-value">{{ number_format($totalCustomers) }}</div>
                <div class="stat-label">العملاء</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="background:linear-gradient(135deg, #F59E0B, #B45309)">
                <i class="fas fa-box stat-icon"></i>
                <div class="stat-value">{{ number_format($totalProducts) }}</div>
                <div class="stat-label">المنتجات</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="background:linear-gradient(135deg, #EF4444, #991B1B)">
                <i class="fas fa-clock stat-icon"></i>
                <div class="stat-value">{{ number_format($pendingOrders) }}</div>
                <div class="stat-label">طلبات معلقة</div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="stat-card" style="background:linear-gradient(135deg, #8B5CF6, #5B21B6)">
                <i class="fas fa-file-export stat-icon"></i>
                <div class="stat-value d-flex gap-2 justify-content-center" style="font-size:1.4rem">
                    <a href="{{ route('admin.reports.sales') }}" class="text-white" title="تقارير المبيعات"><i class="fas fa-chart-line"></i></a>
                    <a href="{{ route('admin.reports.products') }}" class="text-white" title="تقارير المنتجات"><i class="fas fa-boxes"></i></a>
                    <a href="{{ route('admin.reports.users') }}" class="text-white" title="تقارير العملاء"><i class="fas fa-user-chart"></i></a>
                </div>
                <div class="stat-label">تقارير متقدمة</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-3 px-3 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="fas fa-chart-line text-pink"></i> آخر الطلبات</h5>
                    <a href="{{ route('admin.reports.sales') }}" class="btn btn-sm btn-outline-pink rounded-pill">عرض الكل</a>
                </div>
                <div class="card-body px-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">رقم الطلب</th>
                                    <th>العميل</th>
                                    <th>الحالة</th>
                                    <th>الإجمالي</th>
                                    <th>التاريخ</th>
                                    <th class="pe-3">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td class="ps-3 fw-bold text-pink">{{ $order->order_number }}</td>
                                    <td>{{ $order->customer_name ?? $order->user?->name ?? 'زائر' }}</td>
                                    <td>
                                        <span class="badge rounded-pill bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : ($order->status === 'cancelled' ? 'danger' : 'info')) }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($order->total_amount, 2) }} ₪</td>
                                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                    <td class="pe-3">
                                        <a href="{{ route('admin.reports.invoice', $order) }}" class="btn btn-sm btn-outline-pink rounded-pill"><i class="fas fa-file-invoice"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">لا توجد طلبات بعد</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-3 px-3 pb-0">
                    <h5 class="fw-bold mb-0"><i class="fas fa-star text-pink"></i> المنتجات الأكثر مبيعاً</h5>
                </div>
                <div class="card-body">
                    @forelse($topProducts as $product)
                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded-3">
                        <div>
                            <div class="fw-bold text-truncate" style="max-width:180px">{{ $product->product_name }}</div>
                            <small class="text-muted">{{ $product->total_qty }} قطعة | {{ number_format($product->total_revenue, 2) }} ₪</small>
                        </div>
                        <span class="badge bg-pink rounded-pill">#{{ $loop->iteration }}</span>
                    </div>
                    @empty
                    <p class="text-center text-muted py-4">لا توجد مبيعات بعد</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-3 px-3 pb-0">
                    <h5 class="fw-bold mb-0"><i class="fas fa-print text-pink"></i> أدوات الطباعة والتصدير</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="{{ route('admin.reports.sales') }}" class="text-decoration-none">
                                <div class="p-4 bg-light rounded-4 text-center hover-shadow transition">
                                    <i class="fas fa-file-invoice-dollar display-6 text-pink mb-2"></i>
                                    <h6 class="fw-bold">تقرير المبيعات</h6>
                                    <small class="text-muted">عرض وتصدير جميع الطلبات مع الفلترة</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.reports.products') }}" class="text-decoration-none">
                                <div class="p-4 bg-light rounded-4 text-center hover-shadow transition">
                                    <i class="fas fa-box-open display-6 text-success mb-2"></i>
                                    <h6 class="fw-bold">تقرير المنتجات</h6>
                                    <small class="text-muted">المنتجات الأكثر مبيعاً والمخزون المنخفض</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.reports.users') }}" class="text-decoration-none">
                                <div class="p-4 bg-light rounded-4 text-center hover-shadow transition">
                                    <i class="fas fa-users display-6 text-primary mb-2"></i>
                                    <h6 class="fw-bold">تقرير العملاء</h6>
                                    <small class="text-muted">نشاط العملاء وعدد الطلبات والإنفاق</small>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="row g-3 mt-3">
                        <div class="col-md-4">
                            <a href="{{ route('admin.reports.delivery') }}" class="text-decoration-none">
                                <div class="p-4 bg-light rounded-4 text-center hover-shadow transition">
                                    <i class="fas fa-truck display-6 text-pink mb-2"></i>
                                    <h6 class="fw-bold">تقرير التوصيل</h6>
                                    <small class="text-muted">متابعة عمليات التوصيل والسائقين والمناطق</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    color: #fff; border-radius: 20px; padding: 1.5rem 1rem; position: relative; overflow: hidden;
    min-height: 140px; display: flex; flex-direction: column; justify-content: center; align-items: center;
    text-align: center; transition: all .3s;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,.15); }
.stat-icon { position: absolute; top: 10px; right: 15px; font-size: 2.5rem; opacity: .2; }
.stat-value { font-size: 1.5rem; font-weight: 800; position: relative; z-index: 1; }
.stat-label { font-size: .78rem; opacity: .9; position: relative; z-index: 1; margin-top: 4px; }
.text-pink { color: #d97a8c !important; }
.bg-pink { background: #d97a8c !important; }
.btn-outline-pink { border-color: #d97a8c; color: #d97a8c; }
.btn-outline-pink:hover { background: #d97a8c; color: #fff; }
.hover-shadow:hover { box-shadow: 0 8px 25px rgba(0,0,0,.1); }
.transition { transition: all .3s; }
</style>
@endsection
