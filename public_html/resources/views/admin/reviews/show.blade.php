@extends('admin.layouts.app')

@section('title', 'تفاصيل التقييم')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.reviews.index') }}" class="text-muted text-decoration-none small"><i class="fas fa-arrow-right"></i> العودة للتقييمات</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-star text-warning"></i> تفاصيل التقييم</span>
                <span class="text-warning">
                    @for($i=1;$i<=5;$i++)
                        <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                    @endfor
                </span>
            </div>
            <div class="card-body">
                @if($review->title)
                <h5>{{ $review->title }}</h5>
                @endif
                @if($review->content)
                <p class="mb-0">{{ $review->content }}</p>
                @endif

                @if($review->pros || $review->cons)
                <div class="row g-3 mt-3">
                    @if($review->pros)
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#f0fdf4;">
                            <div class="fw-bold small text-success mb-2"><i class="fas fa-thumbs-up"></i> الإيجابيات</div>
                            <ul class="small mb-0">
                                @foreach($review->pros as $pro)
                                <li>{{ $pro }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                    @if($review->cons)
                    <div class="col-md-6">
                        <div class="p-3 rounded-3" style="background:#fef2f2;">
                            <div class="fw-bold small text-danger mb-2"><i class="fas fa-thumbs-down"></i> السلبيات</div>
                            <ul class="small mb-0">
                                @foreach($review->cons as $con)
                                <li>{{ $con }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>

        @if($review->order)
        <div class="card mt-4">
            <div class="card-header"><i class="fas fa-receipt"></i> معلومات الطلب</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="small text-muted">رقم الطلب</div>
                        <div class="fw-bold">#{{ $review->order->order_number ?? $review->order_id }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">الحالة</div>
                        <div>{{ $review->order->status }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">مشتريات موثقة</div>
                        <div>@if($review->is_verified_purchase) <span class="badge bg-success">نعم</span> @else <span class="badge bg-secondary">لا</span> @endif</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">الإجراءات</div>
            <div class="card-body">
                @if(!$review->is_approved)
                <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-check"></i> اعتماد التقييم</button>
                </form>
                @else
                <form action="{{ route('admin.reviews.reject', $review) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-warning w-100"><i class="fas fa-times"></i> إلغاء الاعتماد</button>
                </form>
                @endif
                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('حذف التقييم؟')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100"><i class="fas fa-trash"></i> حذف</button>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">معلومات العميل</div>
            <div class="card-body">
                @if($review->user)
                <div class="fw-bold">{{ $review->user->name }}</div>
                <div class="small text-muted">{{ $review->user->email }}</div>
                @else
                <div class="text-muted small">زائر</div>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">المنتج</div>
            <div class="card-body">
                @if($review->product)
                <div class="d-flex align-items-center gap-2">
                    @if($review->product->main_image_url)
                    <img src="{{ $review->product->main_image_url }}" style="width:48px;height:48px;border-radius:8px;object-fit:cover;">
                    @endif
                    <div>
                        <div class="fw-bold small">{{ $review->product->name_ar }}</div>
                        <a href="{{ route('admin.products.edit', $review->product) }}" class="small">عرض المنتج</a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
