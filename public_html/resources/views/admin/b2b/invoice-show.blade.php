@extends('admin.layouts.app')

@section('title', 'تفاصيل الفاتورة')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">الفاتورة {{ $invoice->invoice_number ?? '#' . $invoice->id }}</h1>
        <p class="text-muted small mb-0">{{ $invoice->company->company_name ?? 'N/A' }}</p>
    </div>
    <a href="{{ route('admin.b2b.invoices') }}" class="btn btn-secondary btn-sm">العودة</a>
</div>
<div class="card">
    <div class="card-header">تفاصيل الفاتورة</div>
    <div class="card-body">
        <table class="table table-sm mb-0">
            <tr><th class="w-25">الشركة</th><td>{{ $invoice->company->company_name ?? 'N/A' }}</td></tr>
            <tr><th>المبلغ</th><td class="fw-bold">{{ number_format($invoice->total_amount ?? $invoice->amount ?? 0, 2) }} ₪</td></tr>
            <tr><th>الحالة</th><td><span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : 'warning' }} rounded-pill">{{ $invoice->status === 'paid' ? 'مدفوع' : 'قيد الانتظار' }}</span></td></tr>
            <tr><th>تاريخ الاستحقاق</th><td>{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</td></tr>
            <tr><th>تاريخ الإنشاء</th><td>{{ $invoice->created_at->format('d/m/Y H:i') }}</td></tr>
        </table>
    </div>
</div>
@endsection
