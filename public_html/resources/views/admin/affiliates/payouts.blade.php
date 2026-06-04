@extends('admin.layouts.app')

@section('title', 'المدفوعات')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-black mb-1">طلبات السحب</h2>
        <p class="text-ink-dim text-sm">إدارة ومعالجة طلبات سحب المسوّقين</p>
    </div>
    <a href="{{ route('admin.affiliates.index') }}" class="text-pink-400 text-sm font-bold">&larr; المسوّقين</a>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
    <div class="glass-panel rounded-2xl p-4"><div class="text-ink-dim text-xs">الإجمالي</div><div class="text-2xl font-black">{{ number_format($stats['total'], 0) }} ₪</div></div>
    <div class="glass-panel rounded-2xl p-4" style="border:1px solid rgba(234,179,8,0.3);"><div class="text-ink-dim text-xs">قيد الانتظار</div><div class="text-2xl font-black text-yellow-500">{{ $stats['pending'] }}</div></div>
    <div class="glass-panel rounded-2xl p-4" style="border:1px solid rgba(34,197,94,0.3);"><div class="text-ink-dim text-xs">مدفوعة</div><div class="text-2xl font-black text-green-500">{{ number_format($stats['paid'], 0) }} ₪</div></div>
</div>

<div class="glass-panel rounded-2xl p-4">
    <table class="w-full text-sm">
        <thead><tr class="text-ink-dim text-xs border-b border-white/5"><th class="pb-3 text-right">المسوّق</th><th class="pb-3 text-right">المبلغ</th><th class="pb-3 text-right">الطريقة</th><th class="pb-3 text-right">الحالة</th><th class="pb-3 text-right">التاريخ</th><th class="pb-3"></th></tr></thead>
        <tbody>
            @foreach($payouts as $p)
            <tr class="border-b border-white/5">
                <td class="py-3 font-bold">{{ $p->affiliate?->name ?? '-' }}</td>
                <td class="py-3 font-bold text-pink-400">{{ number_format($p->amount, 0) }} ₪</td>
                <td class="py-3 text-xs">{{ $p->method === 'bank_transfer' ? 'تحويل بنكي' : ($p->method === 'paypal' ? 'PayPal' : 'محفظة') }}
                    @if($p->iban)<div class="text-ink-dim ltr text-left" dir="ltr">{{ $p->iban }}</div>@endif
                    @if($p->paypal_email)<div class="text-ink-dim ltr text-left" dir="ltr">{{ $p->paypal_email }}</div>@endif
                </td>
                <td class="py-3 text-xs">
                    @if($p->status === 'pending')<span class="text-yellow-400">قيد الانتظار</span>
                    @elseif($p->status === 'paid')<span class="text-green-400">مدفوع</span>
                    @elseif($p->status === 'rejected')<span class="text-red-400">مرفوض</span>
                    @else<span>{{ $p->status }}</span>@endif
                </td>
                <td class="py-3 text-ink-dim text-xs">{{ $p->created_at->format('Y-m-d') }}</td>
                <td class="py-3">
                    @if($p->status === 'pending')
                    <form action="{{ route('admin.affiliates.payouts.process', $p) }}" method="POST" class="inline">@csrf @method('PATCH')
                        <input type="hidden" name="action" value="approve">
                        <button class="text-green-400 text-xs font-bold ml-2">موافقة</button>
                    </form>
                    <form action="{{ route('admin.affiliates.payouts.process', $p) }}" method="POST" class="inline">@csrf @method('PATCH')
                        <input type="hidden" name="action" value="reject">
                        <button class="text-red-400 text-xs font-bold">رفض</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $payouts->links() }}</div>
</div>
@endsection
