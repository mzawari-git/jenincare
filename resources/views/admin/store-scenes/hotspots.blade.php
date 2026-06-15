@extends('admin.layouts.app')

@section('title', 'النقاط التفاعلية - ' . $storeScene->name_ar)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">النقاط التفاعلية: {{ $storeScene->name_ar }}</h1>
        <p class="text-muted small mb-0">أضف نقاطاً ساخنة على المشهد لربطها بالمنتجات</p>
    </div>
    <div>
        <a href="{{ route('virtual-store.scene', $storeScene->slug) }}" class="btn btn-sm btn-outline-info ms-1" target="_blank">
            <i class="fas fa-eye ms-1"></i> معاينة
        </a>
        <a href="{{ route('admin.store-scenes.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right ms-1"></i> العودة
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">إضافة نقطة تفاعلية</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.store-scenes.hotspots.store', $storeScene) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">المنتج *</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">اختر منتجاً</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name_ar }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Pitch (زاوية رأسية)</label>
                            <input type="number" step="0.01" name="pitch" class="form-control" value="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Yaw (زاوية أفقية)</label>
                            <input type="number" step="0.01" name="yaw" class="form-control" value="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تسمية (عربي)</label>
                        <input type="text" name="label_ar" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تسمية (إنجليزي)</label>
                        <input type="text" name="label_en" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نوع الأيقونة</label>
                        <select name="icon_type" class="form-select">
                            <option value="product">منتج</option>
                            <option value="discount">خصم</option>
                            <option value="info">معلومة</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-pink w-100"><i class="fas fa-plus ms-1"></i> إضافة</button>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h6>كيفية تحديد الإحداثيات</h6>
                <ol class="small mb-0 ps-3">
                    <li>افتح المشهد في المتصفح</li>
                    <li>اضغط F12 → Console</li>
                    <li>حرك الفأرة فوق المنتج</li>
                    <li>اكتب: <code>__getCoords()</code></li>
                    <li>انسخ قيم pitch/yaw</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">النقاط الحالية ({{ $storeScene->hotspots->count() }})</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>المنتج</th>
                            <th>Pitch</th>
                            <th>Yaw</th>
                            <th>التسمية</th>
                            <th>النوع</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($storeScene->hotspots as $hotspot)
                        <tr>
                            <td>
                                @if($hotspot->product)
                                <strong>{{ $hotspot->product->name_ar }}</strong>
                                <br><small class="text-muted">{{ $hotspot->product->sku }}</small>
                                @else
                                <span class="text-muted">منتج محذوف</span>
                                @endif
                            </td>
                            <td>{{ $hotspot->pitch }}</td>
                            <td>{{ $hotspot->yaw }}</td>
                            <td>{{ $hotspot->label_ar ?: '-' }}</td>
                            <td><span class="badge bg-info">{{ $hotspot->icon_type }}</span></td>
                            <td class="text-end">
                                <form action="{{ route('admin.store-scenes.hotspots.destroy', $hotspot) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف النقطة؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">لا توجد نقاط تفاعلية بعد</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
