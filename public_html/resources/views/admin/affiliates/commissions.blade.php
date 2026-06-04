@extends('admin.layouts.app')

@section('title', 'العمولات')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-black mb-1">العمولات</h2>
        <p class="text-ink-dim text-sm">مراجعة وإدارة العمولات</p>
    </div>
    <a href="{{ route('admin.affiliates.index') }}" class="text-pink-400 text-sm font-bold">&larr; المسوّقين</a>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="glass-panel rounded-2xl p-4"><div class="text-ink-dim text-xs">الإجمالي</div><div class="text-2xl font-black">{{ number_format($stats['total'], 0) }} ₪</div></div>
    <div class="glass-panel rounded-2xl p-4" style="border:1px solid rgba(234,179,8,0.3);"><div class="text-ink-dim text-xs">معلقة</div><div class="text-2xl font-black text-yellow-500">{{ number_format($stats['pending'], 0) }} ₪</div></div>
    <div class="glass-panel rounded-2xl p-4" style="border:1px solid rgba(34,197,94,0.3);"><div class="text-ink-dim text-xs">موافق عليها</div><div class="text-2xl font-black text-green-500">{{ number_format($stats['approved'], 0) }} ₪</div></div>
    <div class="glass-panel rounded-2xl p-4" style="border:1px solid rgba(6,182,212,0.3);"><div class="text-ink-dim text-xs">مدفوعة</div><div class="text-2xl font-black text-cyan-500">{{ number_format($stats['paid'], 0) }} ₪</div></div>
</div>

<div class="glass-panel rounded-2xl p-4">
    <table class="w-full text-sm">
        <thead><tr class="text-ink-dim text-xs border-b border-white/5"><th class="pb-3 text-right">المسوّق</th><th class="pb-3 text-right">الطلب</th><th class="pb-3 text-right">قيمة الطلب</th><th class="pb-3 text-right">العمولة</th><th class="pb-3 text-right">النسبة</th><th class="pb-3 text-right">الحالة</th><th class="pb-3 text-right">التاريخ</th><th class="pb-3"></th></tr></thead>
        <tbody>
            @foreach($commissions as $c)
            <tr class="border-b border-white/5">
                <td class="py-3 font-bold">{{ $c->affiliate?->name ?? '-' }}</td>
                <td class="py-3">#{{ $c->order_id }}</td>
                <td class="py-3">{{ number_format($c->order_amount, 0) }} ₪</td>
                <td class="py-3 font-bold text-pink-400">{{ number_format($c->commission_amount, 0) }} ₪</td>
                <td class="py-3">{{ $c->commission_rate }}%</td>
                <td class="py-3 text-xs">
                    @if($c->status === 'pending')<span class="text-yellow-400">معلقة</span>
                    @elseif($c->status === 'approved')<span class="text-green-400">موافق</span>
                    @elseif($c->status === 'paid')<span class="text-cyan-400">مدفوع</span>
                    @elseif($c->status === 'rejected')<span class="text-red-400">مرفوض</span>
                    @else<span>{{ $c->status }}</span>@endif
                </td>
                <td class="py-3 text-ink-dim text-xs">{{ $c->created_at->format('Y-m-d') }}</td>
                <td class="py-3">
                    @if($c->status === 'pending')
                    <form action="{{ route('admin.affiliates.commissions.approve', $c) }}" method="POST" class="inline">@csrf @method('PATCH')<button class="text-green-400 text-xs font-bold ml-2">موافقة</button></form>
                    <button onclick="rejectComm({{ $c->id }})" class="text-red-400 text-xs font-bold">رفض</button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $commissions->links() }}</div>
</div>

@foreach($commissions as $c)
<form id="reject-form-{{ $c->id }}" action="{{ route('admin.affiliates.commissions.reject', $c) }}" method="POST" style="display:none;">@csrf @method('PATCH')<input name="notes" value="مرفوض من الإدارة"></form>
@endforeach
<script>
function rejectComm(id) { if (confirm('متأكد من رفض هذه العمولة؟')) document.getElementById('reject-form-'+id).submit(); }
</script>
@endsection
