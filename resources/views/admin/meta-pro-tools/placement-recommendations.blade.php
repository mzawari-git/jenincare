@extends('admin.layouts.app')
@section('title', 'توصيات أماكن الظهور')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="fas fa-map-pin" style="color:#F59E0B;margin-left:8px;"></i> توصيات أماكن الظهور</h4>
        <p class="text-muted small mb-0">أفضل أماكن عرض الإعلانات حسب الهدف والصناعة</p>
    </div>
    <a href="{{ route('admin.meta-pro-tools.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right"></i> العودة</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold"><i class="fas fa-th-list"></i> مصفوفة التوصيات (الهدف ? المكان)</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>الهدف</th>
                            <th>التوصية الأولى</th>
                            <th>التوصية الثانية</th>
                            <th>التوصية الثالثة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matrix as $row)
                        <tr>
                            <td><b>{{ $row['objective_name'] }}</b><br><small class="text-muted">{{ $row['objective'] }}</small></td>
                            @foreach($row['placement_details'] as $p)
                            <td>
                                @if($p)
                                <div class="border rounded p-2 text-center">
                                    <i class="{{ $p['icon'] ?? 'fas fa-map-pin' }}" style="color:var(--pink-600);"></i>
                                    <div class="fw-bold">{{ $p['name'] }}</div>
                                    <small class="text-muted">تكلفة: {{ $p['cost_factor'] }} • تفاعل: {{ $p['engagement'] }}</small>
                                </div>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold"><i class="fas fa-info-circle"></i> تفاصيل أماكن الظهور</div>
            <div class="card-body">
                @foreach($allPlacements as $key => $p)
                <div class="border rounded p-3 mb-2">
                    <div class="d-flex justify-content-between">
                        <div>
                            <b>{{ $p['name'] }}</b>
                            <span class="badge bg-light text-dark ms-2">{{ $p['cost_factor'] }}</span>
                        </div>
                        <span class="text-muted small">وصول: {{ $p['reach'] }} • تفاعل: {{ $p['engagement'] }}</span>
                    </div>
                    <div class="small text-muted mt-1">{{ $p['description'] }}</div>
                    <div class="d-flex gap-2 mt-1">
                        <div><small class="text-success">مميزات:</small>
                            @foreach($p['pros'] as $pro)
                            <span class="badge bg-success-subtle text-success">{{ $pro }}</span>
                            @endforeach
                        </div>
                        <div><small class="text-danger">عيوب:</small>
                            @foreach($p['cons'] as $con)
                            <span class="badge bg-danger-subtle text-danger">{{ $con }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold"><i class="fas fa-scale-balanced"></i> مقارنة التكاليف</div>
            <div class="card-body">
                <table class="table table-sm small">
                    <thead>
                        <tr><th>المكان</th><th>التكلفة</th><th>الوصول</th><th>التفاعل</th></tr>
                    </thead>
                    <tbody>
                        @foreach($costComparison as $key => $c)
                        <tr>
                            <td><b>{{ $c['name'] }}</b></td>
                            <td><span class="badge bg-{{ $c['cost_factor'] === 'منخفض' ? 'success' : ($c['cost_factor'] === 'متوسط' ? 'warning' : 'danger') }}">{{ $c['cost_factor'] }}</span></td>
                            <td>{{ $c['reach'] }}</td>
                            <td>{{ $c['engagement'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header bg-light fw-bold"><i class="fas fa-lightbulb"></i> نصائح سريعة</div>
            <div class="card-body small">
                <ul class="mb-0">
                    <li class="py-1">استخدم <b>Instagram Feed</b> للإعلانات البصرية (مثل صالونات التجميل)</li>
                    <li class="py-1"><b>Stories</b> مناسبة للعروض المحدودة والتفاعل السريع</li>
                    <li class="py-1"><b>Marketplace</b> ممتاز للمنتجات والخدمات المحلية</li>
                    <li class="py-1"><b>Video Feeds</b> يزيد التفاعل بنسبة تصل إلى 40%</li>
                    <li class="py-1">اجمع بين 3-5 أماكن ظهور لتحقيق أقصى وصول</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
