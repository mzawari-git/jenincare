@extends($layoutPath)

@section('title', 'تفاصيل الطلب #' . $order->order_number . ' - ' . ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))

@section('content')
<section class="pt-32 pb-8 bg-gradient-to-b border-b border-white/5 text-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-white">تفاصيل الطلب #{{ $order->order_number }}</h1>
    </div>
</section>

<div class="container" style="padding:0 16px 60px;">
    <div class="row g-4">
        <div class="col-lg-3">
            @include('frontend.account.sidebar')
        </div>

        <div class="col-lg-9" style="display:flex;flex-direction:column;gap:24px;">
            <div style="background:var(--glass-bg);border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden;">
                <div style="padding:20px 24px;border-bottom:1px solid var(--glass-border);">
                    <h2 style="font-size:1.15rem;margin:0;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-info-circle" style="color:var(--brand-500);"></i> معلومات الطلب
                    </h2>
                </div>
                <div style="padding:24px;">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <p style="color:var(--ink-dim);font-size:.85rem;margin-bottom:2px;">رقم الطلب</p>
                            <p style="font-weight:700;font-size:1.1rem;margin:0;">{{ $order->order_number }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p style="color:var(--ink-dim);font-size:.85rem;margin-bottom:2px;">التاريخ</p>
                            <p style="font-weight:700;margin:0;">{{ $order->created_at->format('Y-m-d h:i A') }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p style="color:var(--ink-dim);font-size:.85rem;margin-bottom:2px;">الحالة</p>
                            <p style="margin:0;">
                                @switch($order->status)
                                    @case('pending') <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:9999px;font-size:.85rem;font-weight:600;background:#FEF3C7;color:#92400E;">قيد الانتظار</span> @break
                                    @case('confirmed') <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:9999px;font-size:.85rem;font-weight:600;background:#DBEAFE;color:#1E40AF;">مؤكد</span> @break
                                    @case('processing') <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:9999px;font-size:.85rem;font-weight:600;background:#DBEAFE;color:#1E40AF;">قيد المعالجة</span> @break
                                    @case('shipped') <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:9999px;font-size:.85rem;font-weight:600;background:#DBEAFE;color:#1E40AF;">تم الشحن</span> @break
                                    @case('delivered') <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:9999px;font-size:.85rem;font-weight:600;background:#D1FAE5;color:#065F46;">تم التوصيل</span> @break
                                    @case('cancelled') <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:9999px;font-size:.85rem;font-weight:600;background:#FEE2E2;color:#991B1B;">ملغي</span> @break
                                    @default <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:9999px;font-size:.85rem;font-weight:600;background:var(--glass-border);color:var(--ink);">{{ $order->status }}</span>
                                @endswitch
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <p style="color:var(--ink-dim);font-size:.85rem;margin-bottom:2px;">طريقة الدفع</p>
                            <p style="font-weight:700;margin:0;">
                                @switch($order->payment_method)
                                    @case('cod') الدفع عند الاستلام @break
                                    @case('bank_transfer') تحويل بنكي @break
                                    @case('credit_card') بطاقة ائتمان @break
                                    @default {{ $order->payment_method }}
                                @endswitch
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div style="background:var(--glass-bg);border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden;">
                <div style="padding:20px 24px;border-bottom:1px solid var(--glass-border);">
                    <h2 style="font-size:1.15rem;margin:0;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-receipt" style="color:var(--brand-500);"></i> المنتجات
                    </h2>
                </div>
                <div style="padding:0;">
                    <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="border-bottom:2px solid var(--glass-border);">
                                    <th style="padding:12px 8px;text-align:right;font-size:.85rem;font-weight:600;color:var(--ink-dim);">المنتج</th>
                                    <th style="padding:12px 8px;text-align:right;font-size:.85rem;font-weight:600;color:var(--ink-dim);">الكمية</th>
                                    <th style="padding:12px 8px;text-align:right;font-size:.85rem;font-weight:600;color:var(--ink-dim);">سعر الوحدة</th>
                                    <th style="padding:12px 8px;text-align:right;font-size:.85rem;font-weight:600;color:var(--ink-dim);">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr style="border-bottom:1px solid var(--glass-border);">
                                    <td style="padding:12px 8px;">
                                        <div style="display:flex;align-items:center;gap:12px;">
                                            @if($item->product_image)
                                            <img src="{{ url('files/' . $item->product_image) }}" style="width:48px;height:48px;border-radius:8px;object-fit:cover;">
                                            @endif
                                            <span style="font-weight:600;font-size:.9rem;">{{ $item->product_name }}</span>
                                        </div>
                                    </td>
                                    <td style="padding:12px 8px;font-size:.9rem;">{{ $item->quantity }}</td>
                                    <td style="padding:12px 8px;font-size:.9rem;">{{ number_format($item->unit_price, 2) }} ₪</td>
                                    <td style="padding:12px 8px;font-weight:700;color:var(--brand-500);font-size:.9rem;">{{ number_format($item->subtotal, 2) }} ₪</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="padding:12px 8px;text-align:left;font-weight:700;font-size:.9rem;">المجموع</td>
                                    <td style="padding:12px 8px;font-weight:800;color:var(--brand-500);font-size:1.1rem;">{{ number_format($order->total_amount, 2) }} ₪</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div style="background:var(--glass-bg);border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden;">
                <div style="padding:20px 24px;border-bottom:1px solid var(--glass-border);">
                    <h2 style="font-size:1.15rem;margin:0;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-truck" style="color:var(--brand-500);"></i> معلومات الشحن
                    </h2>
                </div>
                <div style="padding:24px;">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <p style="color:var(--ink-dim);font-size:.85rem;margin-bottom:2px;">الاسم</p>
                            <p style="font-weight:700;margin:0;">{{ $order->customer_name }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p style="color:var(--ink-dim);font-size:.85rem;margin-bottom:2px;">الهاتف</p>
                            <p style="font-weight:700;margin:0;" dir="ltr">{{ $order->customer_phone }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p style="color:var(--ink-dim);font-size:.85rem;margin-bottom:2px;">البريد الإلكتروني</p>
                            <p style="font-weight:700;margin:0;">{{ $order->customer_email }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p style="color:var(--ink-dim);font-size:.85rem;margin-bottom:2px;">العنوان</p>
                            <p style="font-weight:700;margin:0;">{{ $order->shipping_address }}, {{ $order->shipping_city }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
