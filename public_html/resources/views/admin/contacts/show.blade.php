@extends('admin.layouts.app')

@section('title', 'رسالة من ' . $contactMessage->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">رسالة من {{ $contactMessage->name }}</h1>
        <p class="text-muted small mb-0">{{ $contactMessage->created_at->format('d/m/Y H:i') }}</p>
    </div>
    <div>
        @if(!$contactMessage->is_read)
        <form action="{{ route('admin.contacts.mark-read', $contactMessage) }}" method="POST" class="d-inline">
            @csrf
            <button class="btn btn-success btn-sm"><i class="fas fa-check"></i> تحديد كمقروء</button>
        </form>
        @endif
        <a href="{{ route('admin.contacts.index') }}" class="btn btn-secondary btn-sm">العودة</a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <table class="table table-sm mb-3">
            <tr><th class="w-25">الاسم</th><td>{{ $contactMessage->name }}</td></tr>
            <tr><th>البريد الإلكتروني</th><td><a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></td></tr>
            <tr><th>رقم الهاتف</th><td>{{ $contactMessage->phone ?? '-' }}</td></tr>
            <tr><th>الموضوع</th><td>{{ $contactMessage->subject ?? '-' }}</td></tr>
        </table>
        <div class="border rounded p-3 bg-light">
            <p class="mb-0">{{ nl2br(e($contactMessage->message)) }}</p>
        </div>
    </div>
</div>
@endsection
