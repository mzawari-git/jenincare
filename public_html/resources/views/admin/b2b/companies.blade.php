@extends('admin.layouts.app')

@section('title', 'شركات B2B')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">شركات B2B</h1>
        <p class="text-muted small mb-0">إدارة تسجيلات الشركات</p>
    </div>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الشركة</th>
                    <th>المالك</th>
                    <th>رقم السجل</th>
                    <th>الحالة</th>
                    <th>الحد الائتماني</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                <tr>
                    <td class="fw-bold">{{ $company->id }}</td>
                    <td>{{ $company->company_name }}</td>
                    <td>{{ $company->user->name ?? 'N/A' }}</td>
                    <td class="text-muted small">{{ $company->cr_number ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $company->status === 'approved' ? 'success' : ($company->status === 'rejected' ? 'danger' : 'warning') }} rounded-pill">
                            {{ $company->status === 'approved' ? 'معتمد' : ($company->status === 'rejected' ? 'مرفوض' : 'قيد الانتظار') }}
                        </span>
                    </td>
                    <td class="fw-bold">{{ number_format($company->credit_limit, 2) }}</td>
                    <td>
                        <a href="{{ route('admin.b2b.company-show', $company) }}" class="btn btn-sm btn-outline-pink"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">لا توجد شركات.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $companies->links('pagination::bootstrap-5') }}</div>
@endsection
