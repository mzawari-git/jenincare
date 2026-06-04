@extends('admin.layouts.app')

@section('title', 'مقال جديد')
@php $isEdit = false; @endphp

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.blog.index') }}" class="text-pink-400 text-sm font-bold mb-3 inline-block">&larr; العودة للمقالات</a>
    <h2 class="text-xl font-black">مقال جديد</h2>
</div>

<form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.blog._form')
    <div class="mt-6">
        <button type="submit" class="btn-primary"><i class="fas fa-save ml-1"></i> نشر المقال</button>
    </div>
</form>
@endsection
