@extends('admin.layouts.app')

@section('title', 'الروابط - ' . $storeScene->name_ar)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">الروابط: {{ $storeScene->name_ar }}</h1>
        <p class="text-muted small mb-0">ربط المشاهد ببعضها للتنقل بين أقسام المتجر</p>
    </div>
    <a href="{{ route('admin.store-scenes.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-right ms-1"></i> العودة
    </a>
</div>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">إضافة رابط</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.store-scenes.connections.store', $storeScene) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">المشهد الوجهة *</label>
                        <select name="to_scene_id" class="form-select" required>
                            <option value="">اختر مشهداً</option>
                            @foreach($scenes as $s)
                            <option value="{{ $s->id }}">{{ $s->name_ar }} @if($s->aisle)({{ $s->aisle }})@endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الاتجاه</label>
                        <select name="direction" class="form-select">
                            <option value="forward">أمام</option>
                            <option value="left">يسار</option>
                            <option value="right">يمين</option>
                            <option value="back">خلف</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تسمية (عربي)</label>
                        <input type="text" name="label_ar" class="form-control" placeholder="مثلاً: انتقل لقسم العناية">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تسمية (إنجليزي)</label>
                        <input type="text" name="label_en" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-pink w-100"><i class="fas fa-link ms-1"></i> إضافة رابط</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">الروابط الحالية</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>من</th>
                            <th>الاتجاه</th>
                            <th>إلى</th>
                            <th>التسمية</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($storeScene->connectionsFrom as $conn)
                        <tr>
                            <td><strong>{{ $storeScene->name_ar }}</strong></td>
                            <td>
                                <span class="badge bg-secondary">
                                    @switch($conn->direction)
                                        @case('left') ← يسار @break
                                        @case('right') → يمين @break
                                        @case('back') ↓ خلف @break
                                        @default ↑ أمام
                                    @endswitch
                                </span>
                            </td>
                            <td>{{ $conn->toScene?->name_ar ?? 'محذوف' }}</td>
                            <td>{{ $conn->label_ar ?: '-' }}</td>
                            <td class="text-end">
                                <form action="{{ route('admin.store-scenes.connections.destroy', $conn) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف الرابط؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">لا توجد روابط بعد</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
