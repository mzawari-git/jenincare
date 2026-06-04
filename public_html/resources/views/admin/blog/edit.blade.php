@extends('admin.layouts.app')

@section('title', 'تعديل مقال')
@php $isEdit = true; @endphp

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.blog.index') }}" class="text-pink-400 text-sm font-bold mb-3 inline-block">&larr; العودة للمقالات</a>
    <h2 class="text-xl font-black">تعديل: {{ $post->title_ar }}</h2>
</div>

<form action="{{ route('admin.blog.update', $post->id) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('admin.blog._form')
    <div class="mt-6 flex gap-3">
        <button type="submit" class="btn-primary"><i class="fas fa-save ml-1"></i> حفظ التعديلات</button>
        <a href="#" onclick="event.preventDefault(); if(confirm('متأكد من الحذف؟')) document.getElementById('delete-form').submit();" class="btn-ghost text-red-400"><i class="fas fa-trash ml-1"></i> حذف</a>
    </div>
</form>
<form id="delete-form" action="{{ route('admin.blog.destroy', $post->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endsection
