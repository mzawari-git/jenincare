@extends('admin.layouts.app')

@section('title', 'تفاصيل المسوّق')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.affiliates.index') }}" class="text-pink-400 text-sm font-bold mb-3 inline-block">&larr; العودة للقائمة</a>
    <h2 class="text-xl font-black mb-1">{{ $affiliate->name }}</h2>
    <p class="text-ink-dim text-sm">{{ $affiliate->email }} | {{ $affiliate->phone }}</p>
</div>

<div class="grid md:grid-cols-2 gap-6">
    <div>
        <div class="glass-panel rounded-2xl p-5 mb-4">
            <h3 class="font-black mb-3">معلومات الحساب</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="text-ink-dim">الحالة</div>
                <div>
                    <form action="{{ route('admin.affiliates.status', $affiliate) }}" method="POST" class="inline-flex gap-2">
                        @csrf @method('PATCH')
                        <select name="status" onchange="this.form.submit()" class="bg-white/5 border border-white/10 rounded-lg px-2 py-1 text-xs">
                            <option value="active" {{ $affiliate->status === 'active' ? 'selected' : '' }}>نشط</option>
                            <option value="inactive" {{ $affiliate->status === 'inactive' ? 'selected' : '' }}>غير نشط</option>
                            <option value="suspended" {{ $affiliate->status === 'suspended' ? 'selected' : '' }}>موقوف</option>
                            <option value="pending" {{ $affiliate->status === 'pending' ? 'selected' : '' }}>معلق</option>
                        </select>
                    </form>
                </div>
                <div class="text-ink-dim">المستوى</div>
                <div>
                    <form action="{{ route('admin.affiliates.tier', $affiliate) }}" method="POST" class="inline-flex gap-2">
                        @csrf @method('PATCH')
                        <select name="tier_level" onchange="this.form.submit()" class="bg-white/5 border border-white/10 rounded-lg px-2 py-1 text-xs">
                            <option value="bronze" {{ $affiliate->tier_level === 'bronze' ? 'selected' : '' }}>برونزي</option>
                            <option value="silver" {{ $affiliate->tier_level === 'silver' ? 'selected' : '' }}>فضي</option>
                            <option value="gold" {{ $affiliate->tier_level === 'gold' ? 'selected' : '' }}>ذهبي</option>
                            <option value="platinum" {{ $affiliate->tier_level === 'platinum' ? 'selected' : '' }}>بلاتيني</option>
                        </select>
                    </form>
                </div>
                <div class="text-ink-dim">كود الإحالة</div>
                <div><code class="text-xs bg-white/5 px-2 py-1 rounded">{{ $affiliate->referral_code }}</code></div>
                <div class="text-ink-dim">الرصيد</div>
                <div class="font-bold" style="color:#ec4899;">{{ number_format($affiliate->wallet_balance, 0) }} ₪</div>
                <div class="text-ink-dim">إجمالي الأرباح</div>
                <div>{{ number_format($affiliate->total_earned, 0) }} ₪</div>
                <div class="text-ink-dim">إجمالي المدفوع</div>
                <div>{{ number_format($affiliate->total_paid, 0) }} ₪</div>
            </div>
        </div>

        <div class="glass-panel rounded-2xl p-5 mb-4">
            <h3 class="font-black mb-3">إعدادات العمولة</h3>
            <form action="{{ route('admin.affiliates.commission', $affiliate) }}" method="POST" class="flex items-end gap-3">
                @csrf @method('PATCH')
                <div>
                    <label class="text-ink-dim text-xs block mb-1">النوع</label>
                    <select name="commission_type" class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm">
                        <option value="percentage" {{ $affiliate->commission_type === 'percentage' ? 'selected' : '' }}>نسبة مئوية</option>
                        <option value="fixed" {{ $affiliate->commission_type === 'fixed' ? 'selected' : '' }}>مبلغ ثابت</option>
                        <option value="hybrid" {{ $affiliate->commission_type === 'hybrid' ? 'selected' : '' }}>مختلط</option>
                    </select>
                </div>
                <div>
                    <label class="text-ink-dim text-xs block mb-1">القيمة</label>
                    <input type="number" name="commission_value" value="{{ $affiliate->commission_value }}" step="0.01" min="0" class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm w-24">
                </div>
                <button type="submit" class="bg-pink-500 text-white text-xs px-4 py-2 rounded-lg font-bold">حفظ</button>
            </form>
        </div>

        <div class="glass-panel rounded-2xl p-5">
            <h3 class="font-black mb-3">ملاحظات إدارية</h3>
            <form action="{{ route('admin.affiliates.notes', $affiliate) }}" method="POST">
                @csrf @method('PATCH')
                <textarea name="notes" rows="3" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm mb-2">{{ $affiliate->notes }}</textarea>
                <button type="submit" class="bg-pink-500 text-white text-xs px-4 py-2 rounded-lg font-bold">حفظ</button>
            </form>
        </div>
    </div>

    <div>
        <div class="glass-panel rounded-2xl p-5 mb-4">
            <h3 class="font-black mb-3">آخر العمولات</h3>
            @if($affiliate->commissions->isEmpty())
                <p class="text-ink-dim text-sm">لا توجد عمولات.</p>
            @else
                <table class="w-full text-sm">
                    <thead><tr class="text-ink-dim text-xs"><th class="pb-2">الطلب</th><th class="pb-2">المبلغ</th><th class="pb-2">الحالة</th></tr></thead>
                    <tbody>
                        @foreach($affiliate->commissions->take(10) as $c)
                        <tr class="border-b border-white/5">
                            <td class="py-2">#{{ $c->order_id }}</td>
                            <td class="py-2">{{ number_format($c->commission_amount, 0) }} ₪</td>
                            <td class="py-2 text-xs">{{ $c->status }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="glass-panel rounded-2xl p-5">
            <h3 class="font-black mb-3">آخر المدفوعات</h3>
            @if($affiliate->payouts->isEmpty())
                <p class="text-ink-dim text-sm">لا توجد مدفوعات.</p>
            @else
                <table class="w-full text-sm">
                    <thead><tr class="text-ink-dim text-xs"><th class="pb-2">المبلغ</th><th class="pb-2">الطريقة</th><th class="pb-2">الحالة</th></tr></thead>
                    <tbody>
                        @foreach($affiliate->payouts->take(10) as $p)
                        <tr class="border-b border-white/5">
                            <td class="py-2">{{ number_format($p->amount, 0) }} ₪</td>
                            <td class="py-2 text-xs">{{ $p->method }}</td>
                            <td class="py-2 text-xs">{{ $p->status }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
