@extends('admin.layouts.app')

@section('title', 'إدارة الجولة الافتراضية')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">المشاهد البانورامية</h1>
        <p class="text-muted small mb-0">إدارة مشاهد الجولة الافتراضية 360° للمتجر</p>
    </div>
    <a href="{{ route('admin.store-scenes.create') }}" class="btn btn-pink">
        <i class="fas fa-plus"></i> إضافة مشهد
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>المشهد</th>
                    <th>القسم</th>
                    <th>الممر</th>
                    <th>النقاط التفاعلية</th>
                    <th>فيديو</th>
                    <th>الترتيب</th>
                    <th>الحالة</th>
                    <th style="width:200px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($scenes as $scene)
                <tr>
                    <td>{{ $scene->id }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            @if($scene->thumbnail)
                            <img src="{{ $scene->thumbnail }}" alt="" style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px;" class="ms-2">
                            @endif
                            <div>
                                <strong>{{ $scene->name_ar }}</strong>
                                @if($scene->name_en)
                                <br><small class="text-muted">{{ $scene->name_en }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>{{ $scene->section }}</td>
                    <td>{{ $scene->aisle }}</td>
                    <td>
                        <span class="badge bg-info">{{ $scene->hotspots_count ?? 0 }}</span>
                    </td>
                    <td>
                        @if($scene->video_path)
                        <span class="badge bg-success" title="{{ $scene->video_path }}"><i class="fas fa-video"></i></span>
                        @else
                        <span class="badge bg-secondary"><i class="fas fa-video-slash"></i></span>
                        @endif
                    </td>
                    <td>{{ $scene->sort_order }}</td>
                    <td>
                        <span class="badge bg-{{ $scene->is_active ? 'success' : 'secondary' }}">
                            {{ $scene->is_active ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.store-scenes.hotspots', $scene) }}" class="btn btn-sm btn-outline-info" title="النقاط التفاعلية">
                            <i class="fas fa-tag"></i>
                        </a>
                        <a href="{{ route('admin.store-scenes.connections', $scene) }}" class="btn btn-sm btn-outline-primary" title="الروابط">
                            <i class="fas fa-link"></i>
                        </a>
                        <a href="{{ route('admin.store-scenes.edit', $scene) }}" class="btn btn-sm btn-outline-warning" title="تعديل">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.store-scenes.destroy', $scene) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد؟')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="حذف"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-store-slash fa-2x mb-2"></i><br>
                        لا توجد مشاهد بعد. <a href="{{ route('admin.store-scenes.create') }}">أضف مشهداً جديداً</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
