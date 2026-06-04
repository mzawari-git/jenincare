@extends('admin.layouts.app')

@section('title', 'تفاصيل الشركة')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">{{ $company->company_name }}</h1>
        <p class="text-muted small mb-0">تفاصيل شركة B2B</p>
    </div>
    <div>
        @if($company->status === 'pending')
        <form action="{{ route('admin.b2b.company-approve', $company) }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-success btn-sm"><i class="fas fa-check"></i> اعتماد</button>
        </form>
        <form action="{{ route('admin.b2b.company-reject', $company) }}" method="POST" class="d-inline">
            @csrf
            <input type="text" name="rejection_reason" placeholder="سبب الرفض" class="form-control form-control-sm d-inline-block" style="width:auto">
            <button class="btn btn-danger btn-sm"><i class="fas fa-times"></i> رفض</button>
        </form>
        @endif
        <a href="{{ route('admin.b2b.companies') }}" class="btn btn-secondary btn-sm">العودة</a>
    </div>
</div>
<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">معلومات الشركة</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th class="w-50">الحالة</th><td><span class="badge bg-{{ $company->status === 'approved' ? 'success' : ($company->status === 'rejected' ? 'danger' : 'warning') }} rounded-pill">{{ $company->status === 'approved' ? 'معتمد' : ($company->status === 'rejected' ? 'مرفوض' : 'قيد الانتظار') }}</span></td></tr>
                    <tr><th>رقم السجل التجاري</th><td>{{ $company->cr_number ?? '-' }}</td></tr>
                    <tr><th>الرقم الضريبي</th><td>{{ $company->tax_id ?? '-' }}</td></tr>
                    <tr><th>الحد الائتماني</th><td>{{ number_format($company->credit_limit, 2) }}</td></tr>
                    <tr><th>شروط الدفع</th><td>{{ $company->payment_terms ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">معلومات الاتصال</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th class="w-50">المالك</th><td>{{ $company->user->name ?? 'N/A' }}</td></tr>
                    <tr><th>البريد الإلكتروني</th><td>{{ $company->user->email ?? '-' }}</td></tr>
                    <tr><th>رقم الهاتف</th><td>{{ $company->phone ?? '-' }}</td></tr>
                    <tr><th>العنوان</th><td>{{ $company->address ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
