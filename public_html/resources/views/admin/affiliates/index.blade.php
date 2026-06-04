@extends('admin.layouts.app')

@section('title', 'إدارة المسوّقين')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-black mb-1">برنامج التسويق بالعمولة</h2>
    <p class="text-ink-dim text-sm">إدارة المسوّقين والعمولات والمدفوعات</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="glass-panel rounded-2xl p-4">
        <div class="text-ink-dim text-xs mb-1">إجمالي المسوّقين</div>
        <div class="text-2xl font-black">{{ $stats['total'] }}</div>
    </div>
    <div class="glass-panel rounded-2xl p-4" style="border:1px solid rgba(34,197,94,0.3);">
        <div class="text-ink-dim text-xs mb-1">نشط</div>
        <div class="text-2xl font-black text-green-500">{{ $stats['active'] }}</div>
    </div>
    <div class="glass-panel rounded-2xl p-4" style="border:1px solid rgba(234,179,8,0.3);">
        <div class="text-ink-dim text-xs mb-1">معلق</div>
        <div class="text-2xl font-black text-yellow-500">{{ $stats['pending'] }}</div>
    </div>
    <div class="glass-panel rounded-2xl p-4" style="border:1px solid rgba(236,72,153,0.3);">
        <div class="text-ink-dim text-xs mb-1">إجمالي الأرباح</div>
        <div class="text-2xl font-black text-pink-500">{{ number_format($stats['total_earned'], 0) }} ₪</div>
    </div>
</div>

<div class="glass-panel rounded-2xl p-4 mb-6">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-ink-dim text-xs border-b border-white/5">
                <th class="pb-3 text-right">المسوّق</th>
                <th class="pb-3 text-right">كود الإحالة</th>
                <th class="pb-3 text-right">المستوى</th>
                <th class="pb-3 text-right">النقرات</th>
                <th class="pb-3 text-right">العمولات</th>
                <th class="pb-3 text-right">الرصيد</th>
                <th class="pb-3 text-right">الحالة</th>
                <th class="pb-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($affiliates as $affiliate)
            <tr class="border-b border-white/5">
                <td class="py-3">
                    <div class="font-bold">{{ $affiliate->name }}</div>
                    <div class="text-ink-dim text-xs">{{ $affiliate->email }}</div>
                </td>
                <td class="py-3"><code class="text-xs bg-white/5 px-2 py-1 rounded">{{ $affiliate->referral_code }}</code></td>
                <td class="py-3">
                    @if($affiliate->tier_level === 'platinum')<span style="color:#e5e7eb;">بلاتيني</span>
                    @elseif($affiliate->tier_level === 'gold')<span style="color:#d4af37;">ذهبي</span>
                    @elseif($affiliate->tier_level === 'silver')<span style="color:#9ca3af;">فضي</span>
                    @else<span style="color:#b45309;">برونزي</span>@endif
                </td>
                <td class="py-3">{{ $affiliate->clicks_count }}</td>
                <td class="py-3">{{ number_format($affiliate->total_commission ?? 0, 0) }} ₪</td>
                <td class="py-3 font-bold" style="color:#ec4899;">{{ number_format($affiliate->wallet_balance, 0) }} ₪</td>
                <td class="py-3">
                    @if($affiliate->status === 'active')<span class="text-green-400 text-xs">نشط</span>
                    @elseif($affiliate->status === 'pending')<span class="text-yellow-400 text-xs">معلق</span>
                    @elseif($affiliate->status === 'suspended')<span class="text-red-400 text-xs">موقوف</span>
                    @else<span class="text-ink-dim text-xs">غير نشط</span>@endif
                </td>
                <td class="py-3"><a href="{{ route('admin.affiliates.show', $affiliate) }}" class="text-pink-400 text-xs font-bold hover:text-pink-300">تفاصيل</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $affiliates->links() }}</div>
</div>
@endsection
