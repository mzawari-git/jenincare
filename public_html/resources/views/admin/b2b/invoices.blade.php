@extends('admin.layouts.app')

@section('title', 'الفواتير')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">فواتير B2B</h1>
        <p class="text-muted small mb-0">إدارة فواتير الشركات والمدفوعات</p>
    </div>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>الشركة</th>
                    <th>المبلغ</th>
                    <th>الحالة</th>
                    <th>تاريخ الاستحقاق</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td class="fw-bold">{{ $invoice->invoice_number ?? '#' . $invoice->id }}</td>
                    <td>{{ $invoice->company->company_name ?? 'N/A' }}</td>
                    <td>{{ number_format($invoice->total_amount ?? $invoice->amount ?? 0, 2) }} ₪</td>
                    <td>
                        <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning') }} rounded-pill">
                            {{ $invoice->status === 'paid' ? 'مدفوع' : ($invoice->status === 'overdue' ? 'متأخر' : 'قيد الانتظار') }}
                        </span>
                    </td>
                    <td class="text-muted">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</td>
                    <td>
                        <a href="{{ route('admin.b2b.invoice-show', $invoice) }}" class="btn btn-sm btn-outline-pink"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">لا توجد فواتير.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $invoices->links('pagination::bootstrap-5') }}</div>
@endsection
