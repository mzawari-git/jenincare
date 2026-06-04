@extends($layoutPath)

@section('title', 'طلباتي - ' . ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))

@section('content')
<section class="pt-32 pb-8 bg-gradient-to-b border-b border-white/5 text-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-white mb-2">طلباتي</h1>
        <p class="text-white-dim">تتبع ومراجعة جميع طلباتك السابقة</p>
    </div>
</section>

<div class="container" style="padding:0 16px 60px;">
    <div class="row g-4">
        <div class="col-lg-3">
            @include('frontend.account.sidebar')
        </div>

        <div class="col-lg-9">
            <div style="background:var(--glass-bg);border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden;">
                <div style="padding:20px 24px;border-bottom:1px solid var(--glass-border);">
                    <h2 style="font-size:1.15rem;margin:0;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-box" style="color:var(--brand-500);"></i> جميع الطلبات
                    </h2>
                </div>
                <div style="padding:0;">
                    @if($orders->isEmpty())
                    <div style="text-align:center;padding:60px 20px;">
                        <div style="font-size:4rem;color:var(--gray-300);margin-bottom:20px;">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h3 style="font-size:1.3rem;font-weight:700;margin-bottom:8px;color:var(--ink);">لا توجد طلبات</h3>
                        <p style="font-size:.95rem;color:var(--ink-dim);margin-bottom:24px;">لم تقم بطلب أي منتجات بعد. ابدأ بتصفح متجرنا.</p>
                        <a href="{{ route('shop') }}" style="display:inline-flex;align-items:center;gap:8px;padding:12px 32px;background:linear-gradient(135deg,var(--brand-500),var(--brand-500));color:#fff;border:none;border-radius:50px;font-weight:700;font-size:.95rem;text-decoration:none;box-shadow:var(--neon-glow);">تصفح المتجر</a>
                    </div>
                    @else
                    <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="border-bottom:2px solid var(--glass-border);">
                                    <th style="padding:12px 8px;text-align:right;font-size:.85rem;font-weight:600;color:var(--ink-dim);">رقم الطلب</th>
                                    <th style="padding:12px 8px;text-align:right;font-size:.85rem;font-weight:600;color:var(--ink-dim);">التاريخ</th>
                                    <th style="padding:12px 8px;text-align:right;font-size:.85rem;font-weight:600;color:var(--ink-dim);">الحالة</th>
                                    <th style="padding:12px 8px;text-align:right;font-size:.85rem;font-weight:600;color:var(--ink-dim);">طريقة الدفع</th>
                                    <th style="padding:12px 8px;text-align:right;font-size:.85rem;font-weight:600;color:var(--ink-dim);">الإجمالي</th>
                                    <th style="padding:12px 8px;text-align:right;font-size:.85rem;font-weight:600;color:var(--ink-dim);"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr style="border-bottom:1px solid var(--glass-border);">
                                    <td style="padding:12px 8px;font-weight:600;font-size:.9rem;">{{ $order->order_number }}</td>
                                    <td style="padding:12px 8px;color:var(--ink-dim);font-size:.85rem;">{{ $order->created_at->format('Y-m-d') }}</td>
                                    <td style="padding:12px 8px;">
                                        @switch($order->status)
                                            @case('pending') <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:9999px;font-size:.8rem;font-weight:600;background:#FEF3C7;color:#92400E;">قيد الانتظار</span> @break
                                            @case('confirmed') <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:9999px;font-size:.8rem;font-weight:600;background:#DBEAFE;color:#1E40AF;">مؤكد</span> @break
                                            @case('processing') <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:9999px;font-size:.8rem;font-weight:600;background:#DBEAFE;color:#1E40AF;">قيد المعالجة</span> @break
                                            @case('shipped') <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:9999px;font-size:.8rem;font-weight:600;background:#DBEAFE;color:#1E40AF;">تم الشحن</span> @break
                                            @case('delivered') <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:9999px;font-size:.8rem;font-weight:600;background:#D1FAE5;color:#065F46;">تم التوصيل</span> @break
                                            @case('cancelled') <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:9999px;font-size:.8rem;font-weight:600;background:#FEE2E2;color:#991B1B;">ملغي</span> @break
                                            @default <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:9999px;font-size:.8rem;font-weight:600;background:var(--glass-border);color:var(--ink);">{{ $order->status }}</span>
                                        @endswitch
                                    </td>
                                    <td style="padding:12px 8px;font-size:.85rem;color:var(--ink-dim);">
                                        @switch($order->payment_method)
                                            @case('cod') الدفع عند الاستلام @break
                                            @case('bank_transfer') تحويل بنكي @break
                                            @case('credit_card') بطاقة ائتمان @break
                                            @default {{ $order->payment_method }}
                                        @endswitch
                                    </td>
                                    <td style="padding:12px 8px;font-weight:700;color:var(--brand-500);font-size:.9rem;">{{ number_format($order->total_amount, 2) }} ₪</td>
                                    <td style="padding:12px 8px;">
                                        <a href="{{ route('orders.show', $order->id) }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:transparent;color:var(--brand-500);border:1px solid var(--brand-200);border-radius:50px;font-size:.8rem;font-weight:600;text-decoration:none;transition:all .2s;" onmouseover="this.style.borderColor='var(--brand-500)'" onmouseout="this.style.borderColor='var(--brand-200)'">التفاصيل</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(method_exists($orders, 'links'))
                    <div style="padding:16px 24px;border-top:1px solid var(--glass-border);">
                        {{ $orders->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
