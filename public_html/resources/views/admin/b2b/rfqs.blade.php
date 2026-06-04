@extends('admin.layouts.app')

@section('title', 'طلبات العروض')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">طلبات العروض (RFQ)</h1>
        <p class="text-muted small mb-0">إدارة طلبات عروض الأسعار من شركات B2B</p>
    </div>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الشركة</th>
                    <th>المنتجات</th>
                    <th>المبلغ</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rfqs as $rfq)
                <tr>
                    <td class="fw-bold">#{{ $rfq->id }}</td>
                    <td>{{ $rfq->company->company_name ?? 'N/A' }}</td>
                    <td>{{ $rfq->items_count ?? $rfq->items->count() ?? 0 }}</td>
                    <td>{{ number_format($rfq->total_amount ?? 0, 2) }} ₪</td>
                    <td>
                        <span class="badge bg-{{ $rfq->status === 'accepted' ? 'success' : ($rfq->status === 'rejected' ? 'danger' : 'warning') }} rounded-pill">
                            {{ $rfq->status === 'accepted' ? 'مقبول' : ($rfq->status === 'rejected' ? 'مرفوض' : ($rfq->status === 'quoted' ? 'تم التسعير' : 'قيد الانتظار')) }}
                        </span>
                    </td>
                    <td class="text-muted">{{ $rfq->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('admin.b2b.rfq-show', $rfq) }}" class="btn btn-sm btn-outline-pink"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">لا توجد طلبات عروض.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $rfqs->links('pagination::bootstrap-5') }}</div>
@endsection
