@extends('admin.layouts.app')

@section('title', 'العلامات التجارية')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">العلامات التجارية</h1>
        <p class="text-muted small mb-0">إدارة العلامات التجارية للمنتجات</p>
    </div>
    <a href="{{ route('admin.brands.create') }}" class="btn btn-pink"><i class="fas fa-plus"></i> إضافة علامة تجارية</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>الرابط</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($brands as $brand)
                <tr>
                    <td class="fw-bold">{{ $brand->id }}</td>
                    <td>{{ $brand->name }}</td>
                    <td class="text-muted small">{{ $brand->slug }}</td>
                    <td>
                        <span class="badge bg-{{ $brand->is_active ? 'success' : 'secondary' }} rounded-pill">
                            {{ $brand->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-sm btn-outline-pink"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد؟')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4 text-muted">لا توجد علامات تجارية.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $brands->links('pagination::bootstrap-5') }}</div>
@endsection
