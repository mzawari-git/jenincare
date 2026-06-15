@extends('admin.layouts.app')

@section('title', 'الكائنات ثلاثية الأبعاد - ' . $storeScene->name_ar)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="mb-1">الكائنات ثلاثية الأبعاد</h4>
            <p class="text-muted mb-0 small">مشهد: {{ $storeScene->name_ar }} @if($storeScene->name_en) ({{ $storeScene->name_en }}) @endif</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.store-scenes.edit', $storeScene) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-right ms-1"></i>
                العودة للمشهد
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>إدارة الكائنات ثلاثية الأبعاد</span>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addObjectModal">
                <i class="fas fa-plus ms-1"></i>
                إضافة كائن
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>النوع</th>
                            <th>التسمية</th>
                            <th>الموقع (X, Y, Z)</th>
                            <th>القياس</th>
                            <th>قابل للمشي</th>
                            <th>تصادم</th>
                            <th>فعال</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($objects as $obj)
                        <tr>
                            <td>{{ $obj->id }}</td>
                            <td>
                                <span class="badge bg-{{ $obj->object_type === 'product_display' ? 'info' : ($obj->object_type === 'wall' ? 'secondary' : ($obj->object_type === 'sign' ? 'warning' : 'primary')) }}">
                                    {{ $obj->object_type }}
                                </span>
                            </td>
                            <td>{{ $obj->label_ar ?: '—' }}</td>
                            <td><code>{{ $obj->position_x }}, {{ $obj->position_y }}, {{ $obj->position_z }}</code></td>
                            <td>{{ $obj->scale }}x</td>
                            <td>
                                <span class="badge bg-{{ $obj->is_walkable ? 'success' : 'danger' }}">
                                    {{ $obj->is_walkable ? 'نعم' : 'لا' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $obj->is_collision ? 'danger' : 'secondary' }}">
                                    {{ $obj->is_collision ? 'نعم' : 'لا' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $obj->is_active ? 'success' : 'secondary' }}">
                                    {{ $obj->is_active ? 'نشط' : 'معطل' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-primary edit-obj"
                                        data-id="{{ $obj->id }}"
                                        data-type="{{ $obj->object_type }}"
                                        data-label_ar="{{ $obj->label_ar }}"
                                        data-label_en="{{ $obj->label_en }}"
                                        data-px="{{ $obj->position_x }}"
                                        data-py="{{ $obj->position_y }}"
                                        data-pz="{{ $obj->position_z }}"
                                        data-rx="{{ $obj->rotation_x }}"
                                        data-ry="{{ $obj->rotation_y }}"
                                        data-rz="{{ $obj->rotation_z }}"
                                        data-scale="{{ $obj->scale }}"
                                        data-color="{{ $obj->color }}"
                                        data-walkable="{{ $obj->is_walkable ? '1' : '0' }}"
                                        data-collision="{{ $obj->is_collision ? '1' : '0' }}"
                                        data-active="{{ $obj->is_active ? '1' : '0' }}"
                                        title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.store-scenes.3d-objects.destroy', ['storeScene' => $storeScene, 'object' => $obj]) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('حذف الكائن؟')" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-cube fa-2x mb-2 d-block"></i>
                                لا توجد كائنات ثلاثية الأبعاد في هذا المشهد بعد
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scene 3D toggle -->
    <div class="card mt-3">
        <div class="card-body">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="enable3dToggle"
                    {{ $storeScene->{'3d_enabled'} ? 'checked' : '' }}
                    onchange="toggle3d(this, {{ $storeScene->id }})">
                <label class="form-check-label" for="enable3dToggle">
                    تفعيل الوضع ثلاثي الأبعاد لهذا المشهد
                </label>
            </div>
            <small class="text-muted">عند التفعيل، سيتم عرض المشهد في بيئة ثلاثية الأبعاد بدلاً من العرض البانورامي 360°</small>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="addObjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.store-scenes.3d-objects.store', $storeScene) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">إضافة كائن ثلاثي الأبعاد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">النوع</label>
                            <select name="object_type" class="form-select" required>
                                <option value="product_display">عرض منتج</option>
                                <option value="shelf">رف</option>
                                <option value="wall">جدار</option>
                                <option value="floor">أرضية</option>
                                <option value="sign">لافتة</option>
                                <option value="decor">ديكور</option>
                                <option value="lighting">إضاءة</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">القياس (Scale)</label>
                            <input type="number" name="scale" class="form-control" step="0.1" value="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الموقع X</label>
                            <input type="number" name="position_x" class="form-control" step="0.1" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الموقع Y</label>
                            <input type="number" name="position_y" class="form-control" step="0.1" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الموقع Z</label>
                            <input type="number" name="position_z" class="form-control" step="0.1" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الدوران X</label>
                            <input type="number" name="rotation_x" class="form-control" step="0.1" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الدوران Y</label>
                            <input type="number" name="rotation_y" class="form-control" step="0.1" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الدوران Z</label>
                            <input type="number" name="rotation_z" class="form-control" step="0.1" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">التسمية (عربي)</label>
                            <input type="text" name="label_ar" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">التسمية (إنجليزي)</label>
                            <input type="text" name="label_en" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">اللون</label>
                            <input type="color" name="color" class="form-control form-control-color" value="#8B5CF6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">مسار النموذج (GLTF/GLB)</label>
                            <input type="text" name="model_path" class="form-control" placeholder="/models/shelf.glb">
                        </div>
                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_walkable" value="1" checked>
                                        <label class="form-check-label">قابل للمشي</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_collision" value="1">
                                        <label class="form-check-label">تصادم</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                        <label class="form-check-label">نشط</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editObjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editObjectForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">تعديل الكائن ثلاثي الأبعاد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">النوع</label>
                            <select name="object_type" class="form-select" id="edit_type" required>
                                <option value="product_display">عرض منتج</option>
                                <option value="shelf">رف</option>
                                <option value="wall">جدار</option>
                                <option value="floor">أرضية</option>
                                <option value="sign">لافتة</option>
                                <option value="decor">ديكور</option>
                                <option value="lighting">إضاءة</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">القياس</label>
                            <input type="number" name="scale" class="form-control" step="0.1" id="edit_scale">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">X</label>
                            <input type="number" name="position_x" class="form-control" step="0.1" id="edit_px">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Y</label>
                            <input type="number" name="position_y" class="form-control" step="0.1" id="edit_py">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Z</label>
                            <input type="number" name="position_z" class="form-control" step="0.1" id="edit_pz">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">دوران X</label>
                            <input type="number" name="rotation_x" class="form-control" step="0.1" id="edit_rx">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">دوران Y</label>
                            <input type="number" name="rotation_y" class="form-control" step="0.1" id="edit_ry">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">دوران Z</label>
                            <input type="number" name="rotation_z" class="form-control" step="0.1" id="edit_rz">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">التسمية (عربي)</label>
                            <input type="text" name="label_ar" class="form-control" id="edit_label_ar">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">التسمية (إنجليزي)</label>
                            <input type="text" name="label_en" class="form-control" id="edit_label_en">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">اللون</label>
                            <input type="color" name="color" class="form-control form-control-color" id="edit_color">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">مسار النموذج</label>
                            <input type="text" name="model_path" class="form-control" id="edit_model_path">
                        </div>
                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_walkable" value="1" id="edit_walkable">
                                        <label class="form-check-label">قابل للمشي</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_collision" value="1" id="edit_collision">
                                        <label class="form-check-label">تصادم</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_active">
                                        <label class="form-check-label">نشط</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggle3d(el, sceneId) {
    fetch(`{{ url('/admin/store-scenes') }}/${sceneId}/toggle-3d`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ enabled: el.checked }),
    });
}

document.querySelectorAll('.edit-obj').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        document.getElementById('edit_type').value = this.dataset.type;
        document.getElementById('edit_scale').value = this.dataset.scale;
        document.getElementById('edit_px').value = this.dataset.px;
        document.getElementById('edit_py').value = this.dataset.py;
        document.getElementById('edit_pz').value = this.dataset.pz;
        document.getElementById('edit_rx').value = this.dataset.rx;
        document.getElementById('edit_ry').value = this.dataset.ry;
        document.getElementById('edit_rz').value = this.dataset.rz;
        document.getElementById('edit_label_ar').value = this.dataset.label_ar;
        document.getElementById('edit_label_en').value = this.dataset.label_en;
        document.getElementById('edit_color').value = this.dataset.color || '#8B5CF6';
        document.getElementById('edit_walkable').checked = this.dataset.walkable === '1';
        document.getElementById('edit_collision').checked = this.dataset.collision === '1';
        document.getElementById('edit_active').checked = this.dataset.active === '1';
        document.getElementById('editObjectForm').action = `{{ url('/admin/store-scenes/${document.querySelector('[data-scene]')?.dataset.scene || ''}/3d-objects') }}/${id}`;
        new bootstrap.Modal(document.getElementById('editObjectModal')).show();
    });
});
</script>
@endpush
