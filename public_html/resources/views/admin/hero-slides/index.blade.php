@extends('admin.layouts.app')

@section('title', 'السلايدشو')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">السلايدشو الرئيسي</h1>
        <p class="text-muted small mb-0">إدارة شرائح العرض في الصفحة الرئيسية</p>
    </div>
    <a href="{{ route('admin.hero-slides.create') }}" class="btn btn-pink"><i class="fas fa-plus"></i> إضافة شريحة</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:60px;">#</th>
                    <th>العنوان</th>
                    <th>المنتج</th>
                    <th>الرابط</th>
                    <th>الترتيب</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($slides as $slide)
                <tr>
                    <td class="fw-bold">{{ $slide->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($slide->image_url)
                            <img src="{{ $slide->image_url }}" style="width:48px;height:48px;border-radius:8px;object-fit:cover;">
                            @endif
                            <div>
                                <div class="fw-bold">{{ $slide->title_ar }}</div>
                                @if($slide->subtitle_ar)
                                <small class="text-muted">{{ $slide->subtitle_ar }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($slide->product)
                        <span class="badge bg-light text-dark">{{ $slide->product->name_ar }}</span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($slide->button_url)
                        <code class="small">{{ Str::limit($slide->button_url, 30) }}</code>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $slide->sort_order }}</td>
                    <td>
                        <form action="{{ route('admin.hero-slides.toggle', $slide) }}" method="POST">
                            @csrf
                            <button type="submit" class="badge border-0 {{ $slide->is_active ? 'bg-success' : 'bg-secondary' }}" style="cursor:pointer;">
                                {{ $slide->is_active ? 'نشط' : 'مخفي' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.hero-slides.edit', $slide) }}" class="btn btn-sm btn-outline-pink"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('admin.hero-slides.destroy', $slide) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من حذف هذه الشريحة؟')"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">
                    <i class="fas fa-images mb-2" style="font-size:2rem;display:block;opacity:.3;"></i>
                    لا توجد شرائح. <a href="{{ route('admin.hero-slides.create') }}">أضف أول شريحة</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
