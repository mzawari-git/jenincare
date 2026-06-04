@extends('frontend.layouts.editorial.app')

@section('title', 'لوحة تحكم التسويق | شركة جنين للتجميل')

@section('content')
<section style="background:#ffffff;min-height:100vh;padding:6rem 1rem 4rem;">
    <div style="max-width:1100px;margin:0 auto;">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2.5rem;flex-wrap:wrap;gap:1rem;">
            <div>
                <h1 style="font-size:clamp(1.5rem,4vw,2.25rem);font-weight:900;color:#0f172a;margin-bottom:.25rem;">لوحة تحكم التسويق</h1>
                <p style="color:#64748b;font-size:.85rem;">مرحباً {{ $affiliate->name }} | <span style="color:#be185d;font-weight:700;">
                    {{ $affiliate->tier_level === 'platinum' ? 'بلاتيني ⭐' : ($affiliate->tier_level === 'gold' ? 'ذهبي 🥇' : ($affiliate->tier_level === 'silver' ? 'فضي 🥈' : 'برونزي 🥉')) }}
                </span></p>
            </div>
            <a href="{{ route('affiliate.tools') }}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border-radius:9999px;font-size:.85rem;font-weight:700;color:#be185d;border:2px solid #fbcfe8;text-decoration:none;transition:all .2s;">
                <i class="ph ph-share-network"></i> أدوات التسويق
            </a>
        </div>

        {{-- Stats --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.75rem;margin-bottom:2rem;">
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1rem;padding:1.25rem;">
                <div style="color:#94a3b8;font-size:.7rem;margin-bottom:.3rem;">نقرات اليوم</div>
                <div style="font-size:1.5rem;font-weight:900;color:#0f172a;">{{ $todayClicks }}</div>
            </div>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1rem;padding:1.25rem;">
                <div style="color:#94a3b8;font-size:.7rem;margin-bottom:.3rem;">نقرات الشهر</div>
                <div style="font-size:1.5rem;font-weight:900;color:#0f172a;">{{ $monthClicks }}</div>
            </div>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1rem;padding:1.25rem;">
                <div style="color:#94a3b8;font-size:.7rem;margin-bottom:.3rem;">تحويلات</div>
                <div style="font-size:1.5rem;font-weight:900;color:#0f172a;">{{ $totalConversions }}</div>
            </div>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1rem;padding:1.25rem;">
                <div style="color:#94a3b8;font-size:.7rem;margin-bottom:.3rem;">نسبة التحويل</div>
                <div style="font-size:1.5rem;font-weight:900;color:#0891b2;">{{ $totalClicks > 0 ? round(($totalConversions/$totalClicks)*100,1) : 0 }}%</div>
            </div>
            <div style="background:#fdf2f8;border:2px solid #f9a8d4;border-radius:1rem;padding:1.25rem;">
                <div style="color:#9d174d;font-size:.7rem;margin-bottom:.3rem;">الرصيد المتاح</div>
                <div style="font-size:1.5rem;font-weight:900;color:#be185d;">{{ number_format($affiliate->wallet_balance, 0) }} ₪</div>
            </div>
            <div style="background:#fffbeb;border:2px solid #fde68a;border-radius:1rem;padding:1.25rem;">
                <div style="color:#92400e;font-size:.7rem;margin-bottom:.3rem;">إجمالي الأرباح</div>
                <div style="font-size:1.5rem;font-weight:900;color:#b45309;">{{ number_format($affiliate->total_earned, 0) }} ₪</div>
            </div>
        </div>

        {{-- Referral Link --}}
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1.25rem;padding:1.25rem;margin-bottom:1.25rem;">
            <h3 style="font-size:1.05rem;font-weight:900;color:#0f172a;margin-bottom:1rem;"><i class="ph ph-link" style="color:#ec4899;margin-left:.35rem;"></i>رابط التسويق الخاص بكِ</h3>
            <div style="display:flex;align-items:center;gap:.75rem;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:.75rem;">
                <input type="text" readonly value="{{ $affiliate->referral_link }}" id="refLink" style="flex:1;background:transparent;color:#334155;font-size:.85rem;border:none;outline:none;direction:ltr;text-align:left;">
                <button onclick="copyRefLink()" style="padding:.5rem 1.25rem;border-radius:.5rem;font-size:.8rem;font-weight:700;color:#fff;background:#ec4899;border:none;cursor:pointer;white-space:nowrap;">نسخ <i class="ph ph-copy" style="margin-right:.25rem;"></i></button>
            </div>
        </div>

        {{-- Payout Request --}}
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1.25rem;padding:1.25rem;margin-bottom:1.25rem;">
            <h3 style="font-size:1.05rem;font-weight:900;color:#0f172a;margin-bottom:1rem;"><i class="ph ph-wallet" style="color:#b45309;margin-left:.35rem;"></i>طلب سحب الأرباح</h3>
            <form action="{{ route('affiliate.payout.request') }}" method="POST">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.75rem;">
                    <input type="number" name="amount" placeholder="المبلغ (الحد الأدنى 50 ₪)" min="50" max="{{ $affiliate->wallet_balance }}" step="1" required style="background:#fff;border:1px solid #cbd5e1;border-radius:.75rem;padding:.7rem 1rem;color:#0f172a;font-size:.85rem;outline:none;">
                    <select name="method" required style="background:#fff;border:1px solid #cbd5e1;border-radius:.75rem;padding:.7rem 1rem;color:#0f172a;font-size:.85rem;outline:none;">
                        <option value="">اختر طريقة الدفع</option>
                        <option value="bank_transfer">تحويل بنكي</option>
                        <option value="paypal">PayPal</option>
                        <option value="mobile_wallet">محفظة إلكترونية</option>
                    </select>
                    <input type="text" name="paypal_email" placeholder="بريد PayPal أو IBAN" style="background:#fff;border:1px solid #cbd5e1;border-radius:.75rem;padding:.7rem 1rem;color:#0f172a;font-size:.85rem;outline:none;direction:ltr;text-align:left;">
                </div>
                <button type="submit" style="margin-top:.75rem;padding:.6rem 2rem;border-radius:9999px;font-size:.85rem;font-weight:700;color:#fff;background:linear-gradient(135deg,#ec4899,#be185d);border:none;cursor:pointer;">طلب سحب</button>
            </form>
        </div>

        {{-- Recent Commissions --}}
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1.25rem;padding:1.25rem;margin-bottom:1.25rem;">
            <h3 style="font-size:1.05rem;font-weight:900;color:#0f172a;margin-bottom:1rem;"><i class="ph ph-coin" style="color:#b45309;margin-left:.35rem;"></i>آخر العمولات</h3>
            @if($recentCommissions->isEmpty())
                <p style="color:#94a3b8;font-size:.85rem;">لا توجد عمولات بعد. شاركي رابطك ليبدأ الربح!</p>
            @else
                <div style="overflow-x:auto;">
                    <table style="width:100%;font-size:.85rem;text-align:right;border-collapse:collapse;">
                        <thead><tr style="color:#94a3b8;font-size:.7rem;border-bottom:1px solid #e2e8f0;"><th style="padding-bottom:.75rem;">الطلب</th><th style="padding-bottom:.75rem;">قيمة الطلب</th><th style="padding-bottom:.75rem;">العمولة</th><th style="padding-bottom:.75rem;">الحالة</th><th style="padding-bottom:.75rem;">التاريخ</th></tr></thead>
                        <tbody>
                            @foreach($recentCommissions as $c)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:.7rem 0;color:#475569;">#{{ $c->order_id ?? '-' }}</td>
                                <td style="padding:.7rem 0;color:#64748b;">{{ number_format($c->order_amount, 0) }} ₪</td>
                                <td style="padding:.7rem 0;font-weight:700;color:#be185d;">{{ number_format($c->commission_amount, 0) }} ₪</td>
                                <td style="padding:.7rem 0;">
                                    @if($c->status === 'pending')<span style="color:#b45309;">معلقة</span>
                                    @elseif($c->status === 'approved')<span style="color:#16a34a;">موافق عليها</span>
                                    @elseif($c->status === 'paid')<span style="color:#0891b2;">مدفوعة</span>
                                    @else<span style="color:#dc2626;">{{ $c->status }}</span>@endif
                                </td>
                                <td style="padding:.7rem 0;color:#94a3b8;font-size:.8rem;">{{ $c->created_at->format('Y-m-d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Recent Payouts --}}
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1.25rem;padding:1.25rem;">
            <h3 style="font-size:1.05rem;font-weight:900;color:#0f172a;margin-bottom:1rem;"><i class="ph ph-bank" style="color:#ec4899;margin-left:.35rem;"></i>آخر عمليات السحب</h3>
            @if($recentPayouts->isEmpty())
                <p style="color:#94a3b8;font-size:.85rem;">لا توجد عمليات سحب بعد.</p>
            @else
                <div style="overflow-x:auto;">
                    <table style="width:100%;font-size:.85rem;text-align:right;border-collapse:collapse;">
                        <thead><tr style="color:#94a3b8;font-size:.7rem;border-bottom:1px solid #e2e8f0;"><th style="padding-bottom:.75rem;">المبلغ</th><th style="padding-bottom:.75rem;">الطريقة</th><th style="padding-bottom:.75rem;">الحالة</th><th style="padding-bottom:.75rem;">التاريخ</th></tr></thead>
                        <tbody>
                            @foreach($recentPayouts as $p)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:.7rem 0;font-weight:700;color:#334155;">{{ number_format($p->amount, 0) }} ₪</td>
                                <td style="padding:.7rem 0;color:#64748b;">{{ $p->method === 'bank_transfer' ? 'تحويل بنكي' : ($p->method === 'paypal' ? 'PayPal' : 'محفظة') }}</td>
                                <td style="padding:.7rem 0;">
                                    @if($p->status === 'pending')<span style="color:#b45309;">قيد المراجعة</span>
                                    @elseif($p->status === 'paid')<span style="color:#16a34a;">مدفوع</span>
                                    @else<span style="color:#dc2626;">مرفوض</span>@endif
                                </td>
                                <td style="padding:.7rem 0;color:#94a3b8;font-size:.8rem;">{{ $p->created_at->format('Y-m-d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</section>

<script>
function copyRefLink() {
    var input = document.getElementById('refLink');
    input.select(); input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(function() {
        var btn = event.target.closest('button');
        var orig = btn.innerHTML;
        btn.innerHTML = 'تم النسخ! <i class="ph ph-check" style="margin-right:.25rem;"></i>';
        setTimeout(function() { btn.innerHTML = orig; }, 2000);
    });
}
</script>
@endsection
