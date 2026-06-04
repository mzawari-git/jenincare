@extends('admin.layouts.app')

@section('title', 'تفاصيل طلب العرض')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">طلب عرض #{{ $rfq->id }}</h1>
        <p class="text-muted small mb-0">{{ $rfq->company->company_name ?? 'N/A' }}</p>
    </div>
    <div>
        <form action="{{ route('admin.b2b.rfq-status', $rfq) }}" method="POST" class="d-inline">
            @csrf
            <select name="status" class="form-select form-select-sm d-inline-block" style="width:auto" onchange="this.form.submit()">
                <option value="pending" {{ $rfq->status === 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                <option value="quoted" {{ $rfq->status === 'quoted' ? 'selected' : '' }}>تم التسعير</option>
                <option value="accepted" {{ $rfq->status === 'accepted' ? 'selected' : '' }}>مقبول</option>
                <option value="rejected" {{ $rfq->status === 'rejected' ? 'selected' : '' }}>مرفوض</option>
            </select>
        </form>
        <a href="{{ route('admin.b2b.rfqs') }}" class="btn btn-secondary btn-sm">العودة</a>
    </div>
</div>
<div class="card">
    <div class="card-header">المنتجات المطلوبة</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>ملاحظات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rfq->items as $item)
                <tr>
                    <td>{{ $item->product_name ?? 'منتج #'.$item->product_id }}</td>
                    <td class="fw-bold">{{ $item->quantity }}</td>
                    <td class="text-muted">{{ $item->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center py-3 text-muted">لا توجد منتجات.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
