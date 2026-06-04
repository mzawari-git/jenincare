@extends('admin.layouts.app')

@section('title', 'كوبونات الخصم')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">كوبونات الخصم</h1>
        <p class="text-muted small mb-0">إدارة أكواد الخصم والعروض</p>
    </div>
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-pink">
        <i class="fas fa-plus"></i> إضافة كوبون جديد
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>الكود</th>
                    <th>النوع</th>
                    <th>القيمة</th>
                    <th>الحد الأدنى</th>
                    <th>الاستخدام</th>
                    <th>الحالة</th>
                    <th>ينتهي</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                <tr>
                    <td class="fw-bold font-monospace">{{ $coupon->code }}</td>
                    <td>
                        @if($coupon->type === 'percentage')
                            <span class="badge bg-info">نسبة مئوية</span>
                        @else
                            <span class="badge bg-secondary">قيمة ثابتة</span>
                        @endif
                    </td>
                    <td class="fw-bold">
                        @if($coupon->type === 'percentage')
                            {{ $coupon->value }}%
                        @else
                            {{ number_format($coupon->value, 2) }} ₪
                        @endif
                    </td>
                    <td class="text-muted">{{ $coupon->min_order_amount ? number_format($coupon->min_order_amount, 2) . ' ₪' : '—' }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <span class="fw-bold">{{ $coupon->used_count }}</span>
                            <span class="text-muted">/</span>
                            <span class="text-muted">{{ $coupon->max_uses ?? '∞' }}</span>
                        </div>
                    </td>
                    <td>
                        @if($coupon->is_active)
                            <span class="badge bg-success">مفعل</span>
                        @else
                            <span class="badge bg-danger">معطل</span>
                        @endif
                        @if($coupon->expires_at && $coupon->expires_at->isPast())
                            <span class="badge bg-warning text-dark">منتهي</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : '—' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('حذف كود الخصم؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5 text-muted">لا توجد كوبونات بعد</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $coupons->links() }}</div>
@endsection
