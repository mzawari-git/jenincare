@extends('admin.layouts.app')

@section('title', 'رسائل التواصل')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">رسائل التواصل</h1>
        <p class="text-muted small mb-0">عرض الرسائل الواردة من نموذج الاتصال</p>
    </div>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الموضوع</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $msg)
                <tr class="{{ !$msg->is_read ? 'fw-bold' : '' }}">
                    <td class="text-muted small">{{ $msg->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $msg->name }}</td>
                    <td class="text-muted small">{{ $msg->email }}</td>
                    <td>{{ $msg->subject ?? '-' }}</td>
                    <td>
                        @if($msg->is_read)
                            <span class="badge bg-secondary rounded-pill">مقروء</span>
                        @else
                            <span class="badge bg-warning text-dark rounded-pill">جديد</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.contacts.show', $msg) }}" class="btn btn-sm btn-outline-pink"><i class="fas fa-eye"></i></a>
                        <form action="{{ route('admin.contacts.destroy', $msg) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد؟')"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">لا توجد رسائل.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $messages->links('pagination::bootstrap-5') }}</div>
@endsection
