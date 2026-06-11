@extends('admin.layouts.app')
@section('title', 'تفاصيل الجمهور - ' . $audience->name)
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-users text-primary me-2"></i>{{ $audience->name }}
            </h4>
            <p class="text-muted mb-0 small">
                <span class="badge bg-{{ $audience->status_badge }} me-1">{{ $audience->status }}</span>
                <span class="ms-2"><i class="fab fa-{{ $audience->platform === 'meta' ? 'facebook' : 'google' }}"></i> {{ ucfirst($audience->platform) }}</span>
            </p>
        </div>
        <a href="{{ route('admin.audiences.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i>العودة
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold">{{ number_format($audience->audience_size ?? 0) }}</div>
                    <div class="small">حجم الجمهور</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm {{ $audience->fatigue_score >= 70 ? 'bg-danger' : ($audience->fatigue_score >= 40 ? 'bg-warning' : 'bg-success') }} text-white">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold">{{ $audience->fatigue_score ?? 0 }}%</div>
                    <div class="small">مؤشر الإجهاد</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold">{{ $audience->performance_ctr ?? 0 }}%</div>
                    <div class="small">نسبة النقر (CTR)</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold">{{ $audience->performance_roas ?? 0 }}x</div>
                    <div class="small">عائد الإنفاق (ROAS)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light fw-bold">تحليلات الأداء</div>
                <div class="card-body">
                    @if($insights->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>الظهور</th>
                                        <th>النقرات</th>
                                        <th>CTR</th>
                                        <th>الإنفاق</th>
                                        <th>التحويلات</th>
                                        <th>CPA</th>
                                        <th>ROAS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($insights as $insight)
                                    <tr>
                                        <td>{{ $insight->date->format('Y-m-d') }}</td>
                                        <td>{{ number_format($insight->impressions) }}</td>
                                        <td>{{ number_format($insight->clicks) }}</td>
                                        <td>{{ $insight->ctr }}%</td>
                                        <td>${{ number_format($insight->spend, 2) }}</td>
                                        <td>{{ $insight->conversions }}</td>
                                        <td>${{ number_format($insight->cpa, 2) }}</td>
                                        <td>{{ $insight->roas }}x</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">لا توجد تحليلات أداء بعد</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-light fw-bold">معلومات الجمهور</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th>المعرف</th><td>{{ $audience->id }}</td></tr>
                        <tr><th>معرف المنصة</th><td><small>{{ $audience->platform_audience_id ?? '-' }}</small></td></tr>
                        <tr><th>المصدر</th><td>{{ $audience->source_type }}</td></tr>
                        <tr><th>نسبة المماثلة</th><td>{{ $audience->lookalike_ratio ? $audience->lookalike_ratio . '%' : '-' }}</td></tr>
                        <tr><th>آخر مزامنة</th><td>{{ $audience->last_synced_at ? \Carbon\Carbon::parse($audience->last_synced_at)->diffForHumans() : '-' }}</td></tr>
                    </table>
                </div>
            </div>
            @if(isset($fatigue))
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light fw-bold">تحليل الإجهاد</div>
                <div class="card-body">
                    <div class="progress mb-2" style="height:10px;">
                        <div class="progress-bar bg-{{ $audience->fatigue_color }}" style="width:{{ $audience->fatigue_score }}%"></div>
                    </div>
                    <p class="small text-muted mb-0">
                        @if($audience->fatigue_score >= 70)
                            الجمهور يعاني من إجهاد عالٍ - يوصى بتجديد الجمهور أو إنشاء جمهور جديد
                        @elseif($audience->fatigue_score >= 40)
                            الجمهور يظهر علامات إجهاد مبكرة - راقب الأداء
                        @else
                            الجمهور بحالة جيدة - يمكن الاستمرار في الاستهداف
                        @endif
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
