@extends('admin.layouts.app')

@section('title', 'تقييمات المنتجات')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">تقييمات المنتجات</h1>
        <p class="text-muted small mb-0">إدارة واعتماد تقييمات العملاء</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card-new text-center">
            <div class="stat-value-new" style="font-size:1.5rem;">{{ $stats['total'] }}</div>
            <div class="stat-label-new">إجمالي التقييمات</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-new text-center">
            <div class="stat-value-new" style="font-size:1.5rem;color:#f59e0b;">{{ $stats['pending'] }}</div>
            <div class="stat-label-new">بانتظار المراجعة</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-new text-center">
            <div class="stat-value-new" style="font-size:1.5rem;color:#10b981;">{{ $stats['approved'] }}</div>
            <div class="stat-label-new">مقبول</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="small text-muted">الحالة</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">الكل</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>بانتظار المراجعة</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>مقبول</option>
                </select>
            </div>
            <div class="col-auto">
                <label class="small text-muted">التقييم</label>
                <select name="rating" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">الكل</option>
                    @foreach([5,4,3,2,1] as $r)
                    <option value="{{ $r }}" {{ request('rating') == $r ? 'selected' : '' }}>{{ $r }} نجوم</option>
                    @endforeach
                </select>
            </div>
            @if(request()->anyFilled(['status','rating']))
            <div class="col-auto">
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-outline-secondary">إلغاء الفلتر</a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>العميل</th>
                    <th>التقييم</th>
                    <th>التعليق</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:32px;height:32px;border-radius:6px;background:var(--pink-50);overflow:hidden;flex-shrink:0;">
                                @if($review->product && $review->product->main_image_url)
                                    <img src="{{ $review->product->main_image_url }}" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--pink-600);font-size:.7rem;"><i class="fas fa-box"></i></div>
                                @endif
                            </div>
                            <div>
                                <div class="small fw-bold text-truncate" style="max-width:180px;">{{ $review->product->name_ar ?? '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="small">{{ $review->user->name ?? 'زائر' }}</td>
                    <td>
                        <span class="text-warning">
                            @for($i=1;$i<=5;$i++)
                                <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}" style="font-size:.75rem;"></i>
                            @endfor
                        </span>
                    </td>
                    <td class="small text-muted" style="max-width:200px;">
                        <div class="text-truncate">{{ $review->content ?? $review->title ?? '—' }}</div>
                    </td>
                    <td>
                        @if($review->is_approved)
                            <span class="badge bg-success">مقبول</span>
                        @else
                            <span class="badge bg-warning text-dark">قيد المراجعة</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $review->created_at->format('Y-m-d') }}</td>
                    <td>
                        <a href="{{ route('admin.reviews.show', $review) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">لا توجد تقييمات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $reviews->links() }}</div>
@endsection
