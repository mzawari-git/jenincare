@extends('admin.layouts.app')

@section('title', 'المستخدمون')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">المستخدمون</h1>
        <p class="text-muted small mb-0">إدارة جميع المستخدمين المسجلين</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-pink">
        <i class="fas fa-plus"></i> إضافة مستخدم
    </a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الهاتف</th>
                    <th>الدور</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="fw-bold">{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td class="text-muted small">{{ $user->email }}</td>
                    <td>{{ $user->phone ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'b2b' ? 'info' : 'secondary') }} rounded-pill">
                            {{ $user->role === 'admin' ? 'مدير' : ($user->role === 'b2b' ? 'أعمال' : 'عميل') }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }} rounded-pill">
                            {{ $user->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-pink"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد؟')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">لا يوجد مستخدمون.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $users->links('pagination::bootstrap-5') }}</div>
@endsection
