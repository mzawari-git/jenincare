@extends('admin.layouts.app')
@section('title', 'فاتورة - ' . $order->order_number)

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-file-invoice text-pink"></i> فاتورة {{ $order->order_number }}</h3>
            <p class="text-muted mb-0">{{ $order->created_at->format('Y-m-d H:i') }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div class="dropdown">
                <button class="btn btn-pink rounded-pill dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-print"></i> طباعة PDF
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.reports.invoice.pdf', [$order, 'a4']) }}" target="_blank"><i class="fas fa-file-pdf text-danger"></i> A4 - صفحة كاملة</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.reports.invoice.pdf', [$order, 'a5']) }}" target="_blank"><i class="fas fa-file-pdf text-danger"></i> A5 - نصف صفحة</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.reports.invoice.pdf', [$order, 'a6']) }}" target="_blank"><i class="fas fa-file-pdf text-danger"></i> A6 - ربع صفحة</a></li>
                </ul>
            </div>
            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-secondary rounded-pill"><i class="fas fa-eye"></i> تفاصيل الطلب</a>
            <a href="{{ route('admin.reports.sales') }}" class="btn btn-outline-secondary rounded-pill"><i class="fas fa-arrow-right"></i> العودة</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4" id="invoicePreview">
        <div class="card-body p-4">
            <div class="invoice-body">
                <div class="row mb-4">
                    <div class="col-6">
                        @if(!empty($siteSettings['site_logo_url']))
                            <img src="{{ $siteSettings['site_logo_url'] }}" alt="Logo" style="height:50px;margin-bottom:10px;">
                        @endif
                        <h4 class="fw-bold text-pink mb-1">{{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}</h4>
                        <small class="text-muted">{{ $siteSettings['site_description'] ?? '' }}</small>
                    </div>
                    <div class="col-6 text-end">
                        <h2 class="fw-bold text-pink mb-1">فاتورة</h2>
                        <div class="fw-bold fs-5">{{ $order->order_number }}</div>
                        <small class="text-muted">التاريخ: {{ $order->created_at->format('Y-m-d') }}</small>
                    </div>
                </div>
                <hr>
                <div class="row mb-4">
                    <div class="col-6">
                        <h6 class="fw-bold text-pink">معلومات العميل</h6>
                        <p class="mb-1 fw-bold">{{ $order->customer_name ?? $order->user?->name ?? 'زائر' }}</p>
                        <p class="mb-1 small">{{ $order->customer_email ?? $order->user?->email ?? '' }}</p>
                        <p class="mb-1 small">{{ $order->customer_phone ?? $order->user?->phone ?? '' }}</p>
                        @if($order->shipping_address)
                        <p class="mb-0 small text-muted">{{ $order->shipping_address }}, {{ $order->shipping_city }}</p>
                        @endif
                    </div>
                    <div class="col-6 text-end">
                        <h6 class="fw-bold text-pink">معلومات الطلب</h6>
                        <p class="mb-1"><span class="badge rounded-pill bg-{{ $order->status==='completed'?'success':($order->status==='pending'?'warning':($order->status==='cancelled'?'danger':'info')) }}">{{ $order->status }}</span></p>
                        <p class="mb-1 small">حالة الدفع: <span class="fw-bold">{{ $order->payment_status }}</span></p>
                        <p class="mb-0 small">طريقة الدفع: {{ $order->payment_method ?? 'الدفع عند الاستلام' }}</p>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>المنتج</th>
                                <th>الرمز</th>
                                <th class="text-center">الكمية</th>
                                <th class="text-center">سعر الوحدة</th>
                                <th class="text-center">الخصم</th>
                                <th class="text-center">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($item->product_image)
                                            <img src="{{ $item->product_image }}" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:8px;">
                                        @endif
                                        <span>{{ $item->product_name }}</span>
                                    </div>
                                </td>
                                <td><code>{{ $item->product_sku }}</code></td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-center">{{ number_format($item->unit_price, 2) }} ₪</td>
                                <td class="text-center">{{ number_format($item->discount_amount, 2) }} ₪</td>
                                <td class="text-center fw-bold">{{ number_format($item->total, 2) }} ₪</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        @if($order->shipping_notes)
                        <div class="p-3 bg-light rounded-3">
                            <h6 class="fw-bold text-pink">ملاحظات الشحن</h6>
                            <p class="mb-0 small">{{ $order->shipping_notes }}</p>
                        </div>
                        @endif
                        @if($order->customer_notes)
                        <div class="p-3 bg-light rounded-3 mt-2">
                            <h6 class="fw-bold text-pink">ملاحظات العميل</h6>
                            <p class="mb-0 small">{{ $order->customer_notes }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3">
                            <table class="table table-sm mb-0">
                                <tr><td class="text-muted">المجموع الفرعي</td><td class="text-end fw-bold">{{ number_format($order->subtotal, 2) }} ₪</td></tr>
                                <tr><td class="text-muted">الخصم</td><td class="text-end text-danger">- {{ number_format($order->discount_amount, 2) }} ₪</td></tr>
                                <tr><td class="text-muted">الشحن</td><td class="text-end">{{ number_format($order->shipping_cost, 2) }} ₪</td></tr>
                                <tr><td class="text-muted">الضريبة</td><td class="text-end">{{ number_format($order->tax_amount, 2) }} ₪</td></tr>
                                <tr class="border-top"><td class="fw-bold fs-5 text-pink">الإجمالي</td><td class="text-end fw-bold fs-5 text-pink">{{ number_format($order->total_amount, 2) }} ₪</td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4 pt-3 border-top">
                    <small class="text-muted">{{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }} - شكراً لتعاملكم معنا</small>
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
.invoice-body { font-family: 'Tajawal', sans-serif; }
@media print {
    .admin-sidebar, .admin-header, .btn, .dropdown, nav { display: none !important; }
    .admin-main { margin-right: 0 !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>
@endsection
