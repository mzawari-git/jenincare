@extends($layoutPath)

@section('title', 'حسابي - ' . ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))

@section('content')
<section class="pt-32 pb-8 bg-gradient-to-b border-b border-white/5 text-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-white mb-2">حسابي</h1>
        <p class="text-white-dim">مرحباً بعودتك {{ Auth::user()->name }}</p>
    </div>
</section>

<div class="container" style="padding:0 16px 60px;">
    <div class="row g-4">
        <div class="col-lg-3">
            @include('frontend.account.sidebar')
        </div>

        <div class="col-lg-9">
            {{-- Affiliate Card --}}
            @php $affiliate = $affiliate ?? null; @endphp
            <div style="background:var(--glass-bg);border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden;margin-bottom:1.25rem;">
                <div style="padding:16px 24px;border-bottom:1px solid var(--glass-border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
                    <h2 style="font-size:1.15rem;margin:0;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-hand-holding-usd" style="color:#ec4899;"></i> التسويق بالعمولة
                    </h2>
                    @if($affiliate)
                    <a href="{{ route('affiliate.dashboard') }}" style="font-size:.75rem;font-weight:700;color:#ec4899;text-decoration:none;">لوحة التحكم الكاملة ←</a>
                    @endif
                </div>
                <div style="padding:24px;">
                    @if($affiliate)
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:.75rem;margin-bottom:1.25rem;">
                        <div style="background:rgba(236,72,153,0.08);border-radius:12px;padding:.85rem;text-align:center;">
                            <div style="font-size:.7rem;color:var(--ink-dim);margin-bottom:.25rem;">الرصيد المتاح</div>
                            <div style="font-size:1.25rem;font-weight:900;color:#ec4899;">{{ number_format($affiliate->wallet_balance, 0) }} ₪</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.04);border-radius:12px;padding:.85rem;text-align:center;">
                            <div style="font-size:.7rem;color:var(--ink-dim);margin-bottom:.25rem;">إجمالي الأرباح</div>
                            <div style="font-size:1.25rem;font-weight:900;color:#d4af37;">{{ number_format($affiliate->total_earned, 0) }} ₪</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.04);border-radius:12px;padding:.85rem;text-align:center;">
                            <div style="font-size:.7rem;color:var(--ink-dim);margin-bottom:.25rem;">النقرات</div>
                            <div style="font-size:1.25rem;font-weight:900;color:var(--ink);">{{ $affiliate->total_clicks }}</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.04);border-radius:12px;padding:.85rem;text-align:center;">
                            <div style="font-size:.7rem;color:var(--ink-dim);margin-bottom:.25rem;">التحويلات</div>
                            <div style="font-size:1.25rem;font-weight:900;color:var(--ink);">{{ $affiliate->total_conversions }}</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;background:rgba(255,255,255,0.04);border-radius:12px;padding:.6rem .85rem;">
                        <input type="text" readonly value="{{ $affiliate->referral_link }}" id="accountRefLink" style="flex:1;background:transparent;border:none;color:var(--ink-dim);font-size:.8rem;outline:none;direction:ltr;text-align:left;">
                        <button onclick="copyAccountRef()" style="padding:.4rem 1rem;border-radius:8px;font-size:.7rem;font-weight:700;color:#fff;background:#ec4899;border:none;cursor:pointer;white-space:nowrap;">نسخ الرابط</button>
                    </div>
                    @else
                    <div style="text-align:center;padding:1.5rem 1rem;">
                        <div style="font-size:2.5rem;margin-bottom:.75rem;">💎</div>
                        <h3 style="font-size:1rem;font-weight:700;color:var(--ink);margin-bottom:.5rem;">اربحِ 10% من كل طلبية عبر رابطك</h3>
                        <p style="font-size:.8rem;color:var(--ink-dim);margin-bottom:1rem;">انضمي لبرنامج التسويق بالعمولة واحصلي على رابطك الخاص لمشاركته مع الجميع</p>
                        <form action="{{ route('affiliate.register') }}" method="POST" style="display:inline-flex;gap:.5rem;">
                            @csrf
                            <input type="tel" name="phone" required style="background:rgba(255,255,255,0.05);border:1px solid var(--glass-border);border-radius:8px;padding:.5rem .75rem;color:var(--ink);font-size:.8rem;outline:none;" placeholder="رقم الهاتف">
                            <button type="submit" style="padding:.5rem 1.25rem;border-radius:8px;font-weight:700;font-size:.8rem;color:#fff;background:linear-gradient(135deg,#ec4899,#be185d);border:none;cursor:pointer;white-space:nowrap;">انضمي الآن</button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            <div style="background:var(--glass-bg);border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden;">
                <div style="padding:20px 24px;border-bottom:1px solid var(--glass-border);">
                    <h2 style="font-size:1.15rem;margin:0;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-clock" style="color:var(--brand-500);"></i> آخر الطلبات
                    </h2>
                </div>
                <div style="padding:24px;">
                    @if($orders->isEmpty())
                    <div style="text-align:center;padding:40px 20px;">
                        <div style="width:80px;height:80px;margin:0 auto 20px;border-radius:50%;background:var(--brand-100);display:flex;align-items:center;justify-content:center;font-size:2rem;color:var(--brand-500);">
                            <i class="fas fa-box"></i>
                        </div>
                        <h3 style="font-size:1.3rem;font-weight:700;margin-bottom:8px;color:var(--ink);">لا توجد طلبات</h3>
                        <p style="font-size:.95rem;color:var(--ink-dim);margin-bottom:24px;">لم تقم بطلب أي منتجات بعد</p>
                        <a href="{{ route('shop') }}" style="display:inline-flex;align-items:center;gap:8px;padding:12px 32px;background:linear-gradient(135deg,var(--brand-500),var(--brand-500));color:#fff;border:none;border-radius:50px;font-weight:700;font-size:.95rem;text-decoration:none;box-shadow:var(--neon-glow);">تسوق الآن</a>
                    </div>
                    @else
                    <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="border-bottom:2px solid var(--glass-border);">
                                    <th style="padding:12px 8px;text-align:right;font-size:.85rem;font-weight:600;color:var(--ink-dim);">رقم الطلب</th>
                                    <th style="padding:12px 8px;text-align:right;font-size:.85rem;font-weight:600;color:var(--ink-dim);">التاريخ</th>
                                    <th style="padding:12px 8px;text-align:right;font-size:.85rem;font-weight:600;color:var(--ink-dim);">الحالة</th>
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
                                    <td style="padding:12px 8px;font-weight:700;color:var(--brand-500);font-size:.9rem;">{{ number_format($order->total_amount, 2) }} ₪</td>
                                    <td style="padding:12px 8px;">
                                        <a href="{{ route('orders.show', $order->id) }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:transparent;color:var(--brand-500);border:1px solid var(--brand-200);border-radius:50px;font-size:.8rem;font-weight:600;text-decoration:none;transition:all .2s;" onmouseover="this.style.borderColor='var(--brand-500)'" onmouseout="this.style.borderColor='var(--brand-200)'">عرض</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
function copyAccountRef() {
    var input = document.getElementById('accountRefLink');
    input.select(); input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(function() {
        var btn = event.target.closest('button');
        var orig = btn.innerHTML;
        btn.innerHTML = 'تم النسخ!';
        setTimeout(function() { btn.innerHTML = orig; }, 2000);
    });
}
</script>
