@extends('admin.layouts.app')

@section('title', 'التصنيفات')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">التصنيفات</h1>
        <p class="text-muted small mb-0">إدارة تصنيفات المنتجات</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-pink"><i class="fas fa-plus"></i> إضافة تصنيف</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>التصنيف الأب</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td class="fw-bold">{{ $category->id }}</td>
                    <td>{{ $category->name }}</td>
                    <td class="text-muted">{{ $category->parent->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-pink"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد؟')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-4 text-muted">لا توجد تصنيفات.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $categories->links('pagination::bootstrap-5') }}</div>
@endsection

