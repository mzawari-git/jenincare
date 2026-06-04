@extends('admin.layouts.app')
@section('title', 'تقرير المبيعات')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-chart-bar text-pink"></i> تقرير المبيعات</h3>
            <p class="text-muted mb-0">عرض وتحليل جميع الطلبات مع إمكانية التصدير</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.sales.export', request()->all()) }}" class="btn btn-success rounded-pill"><i class="fas fa-file-excel"></i> تصدير Excel</a>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary rounded-pill"><i class="fas fa-arrow-right"></i> العودة</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">من تاريخ</label>
                    <input type="date" name="date_from" class="form-control rounded-3" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">إلى تاريخ</label>
                    <input type="date" name="date_to" class="form-control rounded-3" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">حالة الطلب</label>
                    <select name="status" class="form-select rounded-3">
                        <option value="">الكل</option>
                        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>معلق</option>
                        <option value="processing" {{ request('status')=='processing'?'selected':'' }}>قيد المعالجة</option>
                        <option value="completed" {{ request('status')=='completed'?'selected':'' }}>مكتمل</option>
                        <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>ملغي</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">حالة الدفع</label>
                    <select name="payment_status" class="form-select rounded-3">
                        <option value="">الكل</option>
                        <option value="paid" {{ request('payment_status')=='paid'?'selected':'' }}>مدفوع</option>
                        <option value="pending" {{ request('payment_status')=='pending'?'selected':'' }}>معلق</option>
                        <option value="failed" {{ request('payment_status')=='failed'?'selected':'' }}>فشل</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-pink rounded-pill"><i class="fas fa-filter"></i> فلترة</button>
                    <a href="{{ route('admin.reports.sales') }}" class="btn btn-outline-secondary rounded-pill"><i class="fas fa-redo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-box bg-success bg-opacity-10 border border-success border-opacity-25 rounded-4 p-3 text-center">
                <div class="text-success fw-bold small">عدد الطلبات</div>
                <div class="fs-4 fw-bold text-success">{{ $totals->count ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box bg-pink bg-opacity-10 border border-pink border-opacity-25 rounded-4 p-3 text-center">
                <div class="text-pink fw-bold small">الإجمالي</div>
                <div class="fs-4 fw-bold text-pink">{{ number_format($totals->total ?? 0, 2) }} ₪</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-4 p-3 text-center">
                <div class="text-warning fw-bold small">الشحن</div>
                <div class="fs-4 fw-bold text-warning">{{ number_format($totals->shipping ?? 0, 2) }} ₪</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-4 p-3 text-center">
                <div class="text-danger fw-bold small">الخصومات</div>
                <div class="fs-4 fw-bold text-danger">{{ number_format($totals->discount ?? 0, 2) }} ₪</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">رقم الطلب</th>
                        <th>العميل</th>
                        <th>عدد المنتجات</th>
                        <th>الحالة</th>
                        <th>الدفع</th>
                        <th>الإجمالي</th>
                        <th>التاريخ</th>
                        <th class="pe-3">فاتورة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td class="ps-3 fw-bold text-pink">{{ $order->order_number }}</td>
                        <td>{{ $order->customer_name ?? $order->user?->name ?? 'زائر' }}</td>
                        <td>{{ $order->items->sum('quantity') }}</td>
                        <td><span class="badge rounded-pill bg-{{ $order->status==='completed'?'success':($order->status==='pending'?'warning':($order->status==='cancelled'?'danger':'info')) }}">{{ $order->status }}</span></td>
                        <td><span class="badge rounded-pill bg-{{ $order->payment_status==='paid'?'success':($order->payment_status==='pending'?'warning':'danger') }}">{{ $order->payment_status }}</span></td>
                        <td class="fw-bold">{{ number_format($order->total_amount, 2) }} ₪</td>
                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td class="pe-3">
                            <a href="{{ route('admin.reports.invoice', $order) }}" class="btn btn-sm btn-outline-pink rounded-pill" title="عرض الفاتورة"><i class="fas fa-file-invoice"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">لا توجد طلبات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="card-footer bg-transparent border-0 px-3 py-2">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>

<style>
.text-pink { color: #d97a8c !important; }
.bg-pink { background: #d97a8c !important; }
.border-pink { border-color: #d97a8c !important; }
.btn-pink { background: #d97a8c; color: #fff; border: none; }
.btn-pink:hover { background: #c56174; color: #fff; }
.btn-outline-pink { border-color: #d97a8c; color: #d97a8c; }
.btn-outline-pink:hover { background: #d97a8c; color: #fff; }
</style>
@endsection
