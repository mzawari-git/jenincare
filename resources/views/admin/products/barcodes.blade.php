@extends('admin.layouts.app')

@section('title', 'إدارة الباركود والطباعة')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">إدارة الباركود والطباعة</h1>
            <p class="text-muted mb-0">توليد وطباعة باركود المنتجات على ورق A4 للمستودع</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.barcodes.export') }}" class="btn btn-outline-success" title="تصدير الباركود إلى CSV">
                <i class="fas fa-file-csv me-1"></i>
            </a>
            <a href="{{ route('admin.barcodes.generate-missing') }}" class="btn btn-outline-primary" onclick="return confirm('توليد باركود لجميع المنتجات التي لا تحتوي على باركود؟')">
                <i class="fas fa-magic me-1"></i> توليد باركود للمنتجات الفارغة
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#printOptionsModal">
                <i class="fas fa-print me-1"></i> طباعة المحدد
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.barcodes.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="بحث بالاسم أو SKU أو الباركود...">
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">جميع الأقسام</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">جميع الحالات</option>
                        <option value="has_barcode" {{ request('status') == 'has_barcode' ? 'selected' : '' }}>به باركود</option>
                        <option value="no_barcode" {{ request('status') == 'no_barcode' ? 'selected' : '' }}>بدون باركود</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-filter me-1"></i> تصفية
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Products Table --}}
    <form id="printForm" method="POST" action="{{ route('admin.barcodes.print') }}" target="_blank">
        @csrf
        <input type="hidden" name="select_all" id="selectAllInput" value="0">
        <input type="hidden" name="layout" value="a4_24">
        <input type="hidden" name="barcode_position" value="bottom">
        <input type="hidden" name="show_name" value="1">
        <input type="hidden" name="show_price" value="1">
        <input type="hidden" name="show_brand" value="1">
        @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
        @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
        @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2 px-3 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <label class="d-flex align-items-center gap-1 mb-0" style="cursor:pointer;font-size:13px;">
                        <input type="checkbox" id="selectAllMatching" onchange="toggleSelectAllMatching(this)" class="form-check-input mt-0">
                        <span>اختر الكل المطابق <span id="matchingCount" class="badge bg-secondary">?</span></span>
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                        <i class="fas fa-times"></i> إلغاء الكل
                    </button>
                </div>
                <small class="text-muted" id="selectedCountLabel">0 منتج محدد</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleAll(this)">
                                </th>
                                <th width="70">العدد</th>
                                <th>المنتج</th>
                                <th>SKU</th>
                                <th>الباركود الحالي</th>
                                <th>القسم</th>
                                <th>السعر</th>
                                <th width="180">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input product-checkbox" name="ids[]" value="{{ $product->id }}" onchange="updateLabelCount()">
                                </td>
                                <td>
                                    <input type="number" name="qty[{{ $product->id }}]" value="1" min="1" max="9999" class="form-control form-control-sm qty-input" style="width:55px;" oninput="updateLabelCount()">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($product->main_image_url)
                                            <img src="{{ $product->main_image_url }}" alt="" class="rounded" width="40" height="40" style="object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                                <i class="fas fa-box text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold">{{ $product->name_ar }}</div>
                                            <small class="text-muted">{{ Str::limit($product->name_en, 30) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><code>{{ $product->sku }}</code></td>
                                <td>
                                    @if($product->barcode)
                                        <span class="badge bg-success">{{ $product->barcode }}</span>
                                        <canvas class="barcode-mini d-block mt-1" data-barcode="{{ $product->barcode }}" width="100" height="20" style="max-width:100px;height:20px;"></canvas>
                                    @else
                                        <span class="badge bg-warning text-dark">بدون باركود</span>
                                    @endif
                                </td>
                                <td>{{ $product->category?->name_ar ?? '-' }}</td>
                                <td>{{ number_format($product->b2c_price, 0) }} ₪</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editBarcode{{ $product->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if($product->barcode)
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="previewBarcode({{ $product->id }}, '{{ $product->barcode }}', '{{ addslashes($product->name_ar) }}', '{{ number_format($product->b2c_price, 0) }}')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="quickPrint({{ $product->id }})">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">لا توجد منتجات مطابقة للبحث</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div id="labelCounter" class="badge bg-dark text-white px-3 py-2">
                            <i class="fas fa-tag me-1"></i>
                            <span id="labelCountText">0 منتج × 1 نسخة = 0 ملصق</span>
                        </div>
                    </div>
                    <div>
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Edit Barcode Modals (must be outside printForm) --}}
@foreach($products as $product)
<div class="modal fade" id="editBarcode{{ $product->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.barcodes.update', $product) }}">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">تحديث الباركود — {{ $product->name_ar }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">رقم الباركود</label>
                        <input type="text" name="barcode" value="{{ $product->barcode }}" class="form-control" maxlength="100" placeholder="مثال: 6261234567890">
                        <div class="form-text">اترك فارغاً لإزالة الباركود</div>
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
@endforeach

{{-- Print Options Modal --}}
<div class="modal fade" id="printOptionsModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered" dir="rtl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-print me-2"></i>خيارات الطباعة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">

                    {{-- Layout / Paper Size --}}
                    <div class="col-12">
                        <label class="form-label fw-bold mb-2">حجم الورق</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="print-option d-block text-center p-3 border rounded cursor-pointer {{ request('layout', 'a4_24') == 'a4_24' ? 'border-primary bg-light' : '' }}" onclick="selectPrintLayout(this, 'a4_24')">
                                    <input type="radio" name="layout" value="a4_24" class="d-none" {{ request('layout', 'a4_24') == 'a4_24' ? 'checked' : '' }}>
                                    <div class="fw-bold">A4</div>
                                    <small class="text-muted">24 ملصق</small>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="print-option d-block text-center p-3 border rounded cursor-pointer" onclick="selectPrintLayout(this, 'a4_12')">
                                    <input type="radio" name="layout" value="a4_12" class="d-none">
                                    <div class="fw-bold">A4</div>
                                    <small class="text-muted">12 ملصق</small>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="print-option d-block text-center p-3 border rounded cursor-pointer" onclick="selectPrintLayout(this, 'a4_6')">
                                    <input type="radio" name="layout" value="a4_6" class="d-none">
                                    <div class="fw-bold">A4</div>
                                    <small class="text-muted">6 ملصق</small>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="print-option d-block text-center p-3 border rounded cursor-pointer" onclick="selectPrintLayout(this, 'a5_12')">
                                    <input type="radio" name="layout" value="a5_12" class="d-none">
                                    <div class="fw-bold">A5</div>
                                    <small class="text-muted">12 ملصق</small>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="print-option d-block text-center p-3 border rounded cursor-pointer" onclick="selectPrintLayout(this, 'a5_6')">
                                    <input type="radio" name="layout" value="a5_6" class="d-none">
                                    <div class="fw-bold">A5</div>
                                    <small class="text-muted">6 ملصق</small>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="print-option d-block text-center p-3 border rounded cursor-pointer" onclick="selectPrintLayout(this, 'a5_4')">
                                    <input type="radio" name="layout" value="a5_4" class="d-none">
                                    <div class="fw-bold">A5</div>
                                    <small class="text-muted">4 ملصق</small>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="print-option d-block text-center p-3 border rounded cursor-pointer" onclick="selectPrintLayout(this, 'a6_8')">
                                    <input type="radio" name="layout" value="a6_8" class="d-none">
                                    <div class="fw-bold">A6</div>
                                    <small class="text-muted">8 ملصق</small>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="print-option d-block text-center p-3 border rounded cursor-pointer" onclick="selectPrintLayout(this, 'a6_4')">
                                    <input type="radio" name="layout" value="a6_4" class="d-none">
                                    <div class="fw-bold">A6</div>
                                    <small class="text-muted">4 ملصق</small>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="print-option d-block text-center p-3 border rounded cursor-pointer" onclick="selectPrintLayout(this, 'a6_2')">
                                    <input type="radio" name="layout" value="a6_2" class="d-none">
                                    <div class="fw-bold">A6</div>
                                    <small class="text-muted">2 ملصق</small>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="print-option d-block text-center p-3 border rounded cursor-pointer" onclick="selectPrintLayout(this, 'thermal')">
                                    <input type="radio" name="layout" value="thermal" class="d-none">
                                    <div class="fw-bold">حراري</div>
                                    <small class="text-muted">80mm</small>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="print-option d-block text-center p-3 border rounded cursor-pointer" onclick="selectPrintLayout(this, 'custom')">
                                    <input type="radio" name="layout" value="custom" class="d-none">
                                    <div class="fw-bold">مخصص</div>
                                    <small class="text-muted">مقاس مخصص</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Custom Size --}}
                    <div class="col-12" id="modalCustomSize" style="display:none;">
                        <div class="row g-2">
                            <div class="col-auto">
                                <label class="form-label">العرض (ملم)</label>
                                <input type="number" name="width" value="50" class="form-control" style="width:100px;">
                            </div>
                            <div class="col-auto">
                                <label class="form-label">الارتفاع (ملم)</label>
                                <input type="number" name="height" value="30" class="form-control" style="width:100px;">
                            </div>
                        </div>
                    </div>

                    {{-- Barcode Position --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2">مكان الباركود</label>
                        <div class="d-flex gap-2">
                            <label class="d-flex flex-column align-items-center p-3 border rounded cursor-pointer position-radio" onclick="selectPosition(this, 'top')">
                                <input type="radio" name="barcode_position" value="top" class="d-none">
                                <i class="fas fa-barcode fa-2x mb-1 text-primary"></i>
                                <span>فوق</span>
                            </label>
                            <label class="d-flex flex-column align-items-center p-3 border rounded cursor-pointer position-radio active" onclick="selectPosition(this, 'bottom')">
                                <input type="radio" name="barcode_position" value="bottom" class="d-none" checked>
                                <i class="fas fa-barcode fa-2x mb-1 text-primary"></i>
                                <span>تحت</span>
                            </label>
                        </div>
                    </div>

                    {{-- Label Elements --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2">عناصر الملصق</label>
                        <div class="d-flex flex-wrap gap-3">
                            <label class="d-flex align-items-center gap-2 cursor-pointer p-2 border rounded" style="cursor:pointer;">
                                <input type="checkbox" name="show_name" value="1" checked>
                                <span>الاسم</span>
                            </label>
                            <label class="d-flex align-items-center gap-2 cursor-pointer p-2 border rounded" style="cursor:pointer;">
                                <input type="checkbox" name="show_price" value="1" checked>
                                <span>السعر</span>
                            </label>
                            <label class="d-flex align-items-center gap-2 cursor-pointer p-2 border rounded" style="cursor:pointer;">
                                <input type="checkbox" name="show_brand" value="1" checked>
                                <span>العلامة التجارية</span>
                            </label>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="col-12">
                        <div class="bg-light p-3 rounded text-center">
                            <span id="modalLabelCount" class="fw-bold fs-5">0</span>
                            <span class="text-muted"> ملصق للطباعة</span>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="submitPrintFromModal()">
                    <i class="fas fa-print me-1"></i> طباعة
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.print-option { cursor: pointer; transition: all .15s; }
.print-option:hover { border-color: #0d6efd; background: #f0f7ff; }
.print-option.border-primary { border-width: 2px; }
.position-radio { cursor: pointer; min-width: 80px; transition: all .15s; }
.position-radio:hover { border-color: #0d6efd; }
.position-radio.active { border-color: #0d6efd; background: #e8f0fe; }
</style>

<script>
function toggleAll(source) {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = source.checked);
    updateLabelCount();
}

function highlightPosition(el) {
    document.querySelectorAll('.position-option').forEach(opt => opt.classList.remove('active'));
    el.closest('.position-option').classList.add('active');
}

function selectPrintLayout(el, value) {
    document.querySelectorAll('.print-option').forEach(opt => opt.classList.remove('border-primary', 'bg-light'));
    el.classList.add('border-primary', 'bg-light');
    el.querySelector('input') && (el.querySelector('input').checked = true);
    document.getElementById('modalCustomSize').style.display = value === 'custom' ? 'block' : 'none';
}

function selectPosition(el, value) {
    document.querySelectorAll('.position-radio').forEach(opt => opt.classList.remove('active'));
    el.classList.add('active');
    el.querySelector('input') && (el.querySelector('input').checked = true);
}

function updateLabelCount() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    let totalProduct = checked.length;
    let totalLabels = 0;
    checked.forEach(cb => {
        const row = cb.closest('tr');
        const qtyInput = row.querySelector('.qty-input');
        const qty = parseInt(qtyInput?.value) || 1;
        totalLabels += qty;
    });
    const el = document.getElementById('labelCountText');
    if (el) {
        el.textContent = totalProduct + ' منتج × ' + (totalLabels > 0 ? Math.round(totalLabels / totalProduct) : '1') + ' نسخة = ' + totalLabels + ' ملصق';
    }
    const selLabel = document.getElementById('selectedCountLabel');
    if (selLabel) {
        selLabel.textContent = totalProduct + ' منتج محدد';
    }
    const modalCount = document.getElementById('modalLabelCount');
    if (modalCount) modalCount.textContent = totalLabels;
}

function submitPrintFromModal() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    const selectAllMode = document.getElementById('selectAllMatching')?.checked;
    if (!selectAllMode && checked.length === 0) {
        alert('يرجى اختيار منتج واحد على الأقل للطباعة.');
        return;
    }
    if (selectAllMode) {
        document.getElementById('selectAllInput').value = '1';
    }

    const form = document.getElementById('printForm');
    const modal = document.getElementById('printOptionsModal');

    // Sync layout radio from modal to form
    const layoutRadio = modal.querySelector('input[name="layout"]:checked');
    if (layoutRadio) {
        let formLayout = form.querySelector('input[name="layout"]');
        if (!formLayout) {
            formLayout = document.createElement('input');
            formLayout.type = 'hidden';
            formLayout.name = 'layout';
            form.appendChild(formLayout);
        }
        formLayout.value = layoutRadio.value;
    }

    // Sync width/height for custom
    const widthInput = modal.querySelector('input[name="width"]');
    const heightInput = modal.querySelector('input[name="height"]');
    if (widthInput && heightInput) {
        let fw = form.querySelector('input[name="width"]');
        let fh = form.querySelector('input[name="height"]');
        if (!fw) { fw = document.createElement('input'); fw.type = 'hidden'; fw.name = 'width'; form.appendChild(fw); }
        if (!fh) { fh = document.createElement('input'); fh.type = 'hidden'; fh.name = 'height'; form.appendChild(fh); }
        fw.value = widthInput.value;
        fh.value = heightInput.value;
    }

    // Sync barcode position
    const posRadio = modal.querySelector('input[name="barcode_position"]:checked');
    if (posRadio) {
        let fp = form.querySelector('input[name="barcode_position"]');
        if (!fp) { fp = document.createElement('input'); fp.type = 'hidden'; fp.name = 'barcode_position'; form.appendChild(fp); }
        fp.value = posRadio.value;
    }

    // Sync show_name, show_price, show_brand
    ['show_name', 'show_price', 'show_brand'].forEach(name => {
        const cb = modal.querySelector('input[name="' + name + '"]');
        let fc = form.querySelector('input[name="' + name + '"]');
        if (!fc) { fc = document.createElement('input'); fc.type = 'hidden'; fc.name = name; form.appendChild(fc); }
        fc.value = cb && cb.checked ? '1' : '0';
    });

    form.submit();
}

function toggleSelectAllMatching(cb) {
    if (cb.checked) {
        document.querySelectorAll('.product-checkbox').forEach(c => c.checked = true);
    } else {
        document.querySelectorAll('.product-checkbox').forEach(c => c.checked = false);
    }
    updateLabelCount();
}

function deselectAll() {
    document.getElementById('selectAllMatching').checked = false;
    document.getElementById('selectAll').checked = false;
    document.querySelectorAll('.product-checkbox').forEach(c => c.checked = false);
    updateLabelCount();
}

function updateMatchingCount() {
    const el = document.getElementById('matchingCount');
    if (!el) return;
    const params = new URLSearchParams(window.location.search);
    fetch('{{ route("admin.barcodes.count") }}?' + params.toString())
        .then(r => r.json())
        .then(d => { el.textContent = d.count ?? '?'; })
        .catch(() => { el.textContent = '?'; });
}

function quickPrint(productId) {
    document.querySelector('input.product-checkbox[value="' + productId + '"]').checked = true;
    const row = document.querySelector('input.product-checkbox[value="' + productId + '"]')?.closest('tr');
    const qty = row ? (parseInt(row.querySelector('.qty-input')?.value) || 1) : 1;
    document.querySelectorAll('.product-checkbox').forEach(c => { if (c.value != productId) c.checked = false; });
    updateLabelCount();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('printOptionsModal')).show();
}

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('product-checkbox')) {
        updateLabelCount();
    }
});
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('qty-input')) {
        updateLabelCount();
    }
});

let previewModalInstance = null;
function previewBarcode(id, barcode, name, price) {
    const modalEl = document.getElementById('barcodePreviewModal');
    document.getElementById('previewBarcodeName').textContent = name;
    document.getElementById('previewBarcodeNumber').textContent = barcode;
    document.getElementById('previewBarcodePrice').textContent = price + ' ₪';
    
    const canvas = document.getElementById('previewBarcodeCanvas');
    try {
        JsBarcode(canvas, barcode, {
            format: 'EAN13',
            width: 3,
            height: 80,
            displayValue: false,
            margin: 5,
        });
    } catch(e) {
        try {
            JsBarcode(canvas, barcode, {
                format: 'CODE128',
                width: 2,
                height: 80,
                displayValue: false,
                margin: 5,
            });
        } catch(e2) {
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
        }
    }
    
    if (!previewModalInstance) {
        previewModalInstance = new bootstrap.Modal(modalEl);
    }
    previewModalInstance.show();
}

document.addEventListener('DOMContentLoaded', function() {
    updateMatchingCount();
    document.querySelectorAll('.barcode-mini').forEach(function(canvas) {
        const code = canvas.getAttribute('data-barcode');
        if (!code) return;
        try {
            JsBarcode(canvas, code, {
                format: 'EAN13',
                width: 1,
                height: 15,
                displayValue: false,
                margin: 0,
                background: 'transparent',
            });
        } catch(e) {
            try {
                JsBarcode(canvas, code, {
                    format: 'CODE128',
                    width: 1,
                    height: 15,
                    displayValue: false,
                    margin: 0,
                    background: 'transparent',
                });
            } catch(e2) {}
        }
    });
});
</script>

{{-- Barcode Preview Modal --}}
<div class="modal fade" id="barcodePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title mx-auto" id="previewBarcodeName"></h5>
                <button type="button" class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <canvas id="previewBarcodeCanvas" width="300" height="100" class="mx-auto d-block"></canvas>
                <div class="mt-2 fw-bold" style="font-family:monospace;letter-spacing:1px;direction:ltr;" id="previewBarcodeNumber"></div>
                <div class="mt-1" style="font-size:1.2rem;font-weight:800;color:#dc2626;" id="previewBarcodePrice"></div>
            </div>
            <div class="modal-footer border-0 justify-content-center pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary" onclick="document.querySelector('button[data-bs-target]')?.click() || quickPrint(0)">طباعة</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3/dist/JsBarcode.all.min.js"></script>
@endpush
