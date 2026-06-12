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
            <button type="button" class="btn btn-primary" onclick="submitPrintForm()">
                <i class="fas fa-print me-1"></i> طباعة المحدد A4
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
                {{-- Professional Print Options --}}
                <div class="row g-3 align-items-end">
                    {{-- Layout --}}
                    <div class="col-auto">
                        <label class="form-label mb-1" style="font-size:.75rem;font-weight:600;">تخطيط الطباعة</label>
                        <select name="layout" class="form-select form-select-sm" style="width:auto;min-width:160px;" onchange="toggleCustomSize(this)">
                            <option value="a4_24">A4 — 24 ملصق (5×3 سم)</option>
                            <option value="a4_12">A4 — 12 ملصق (7×4 سم)</option>
                            <option value="a4_6">A4 — 6 ملصق (10×5 سم)</option>
                            <option value="a5_12">A5 — 12 ملصق (5×3 سم)</option>
                            <option value="a5_6">A5 — 6 ملصق (7×4 سم)</option>
                            <option value="a5_4">A5 — 4 ملصق (10×5 سم)</option>
                            <option value="a6_8">A6 — 8 ملصق (5×3 سم)</option>
                            <option value="a6_4">A6 — 4 ملصق (7×4 سم)</option>
                            <option value="a6_2">A6 — 2 ملصق (10×5 سم)</option>
                            <option value="thermal">حراري 80mm</option>
                            <option value="custom">مقاس مخصص</option>
                        </select>
                    </div>
                    <div class="col-auto" id="customSizeFields" style="display:none;">
                        <div class="row g-1 align-items-center">
                            <div class="col-auto">
                                <label class="form-label mb-0" style="font-size:.7rem;">العرض (ملم)</label>
                                <input type="number" name="width" value="50" class="form-control form-control-sm" style="width:70px;">
                            </div>
                            <div class="col-auto">
                                <label class="form-label mb-0" style="font-size:.7rem;">الارتفاع (ملم)</label>
                                <input type="number" name="height" value="30" class="form-control form-control-sm" style="width:70px;">
                            </div>
                        </div>
                    </div>

                    {{-- Barcode Position --}}
                    <div class="col-auto">
                        <label class="form-label mb-1" style="font-size:.75rem;font-weight:600;">مكان الباركود</label>
                        <div class="d-flex gap-1">
                            <label class="position-option {{ request('barcode_position', 'bottom') === 'top' ? 'active' : '' }}">
                                <input type="radio" name="barcode_position" value="top" {{ request('barcode_position', 'bottom') === 'top' ? 'checked' : '' }} onchange="highlightPosition(this)">
                                <span class="d-flex flex-column align-items-center px-2 py-1 border rounded" style="cursor:pointer;font-size:11px;min-width:50px;">
                                    <i class="fas fa-barcode fa-lg mb-1" style="color:#0d6efd;"></i>
                                    <span style="font-size:9px;">فوق</span>
                                </span>
                            </label>
                            <label class="position-option {{ request('barcode_position', 'bottom') === 'bottom' ? 'active' : '' }}">
                                <input type="radio" name="barcode_position" value="bottom" {{ request('barcode_position', 'bottom') === 'bottom' ? 'checked' : '' }} onchange="highlightPosition(this)">
                                <span class="d-flex flex-column align-items-center px-2 py-1 border rounded" style="cursor:pointer;font-size:11px;min-width:50px;">
                                    <i class="fas fa-barcode fa-lg mb-1" style="color:#0d6efd;"></i>
                                    <span style="font-size:9px;">تحت</span>
                                    <i class="fas fa-arrow-down" style="font-size:8px;color:#6c757d;"></i>
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Toggles --}}
                    <div class="col-auto">
                        <label class="form-label mb-1" style="font-size:.75rem;font-weight:600;">عناصر الملصق</label>
                        <div class="d-flex gap-2">
                            <label class="d-flex align-items-center gap-1" style="cursor:pointer;font-size:12px;">
                                <input type="hidden" name="show_name" value="0">
                                <input type="checkbox" name="show_name" value="1" checked class="form-check-input mt-0" style="width:16px;height:16px;">
                                <span>الاسم</span>
                            </label>
                            <label class="d-flex align-items-center gap-1" style="cursor:pointer;font-size:12px;">
                                <input type="hidden" name="show_price" value="0">
                                <input type="checkbox" name="show_price" value="1" checked class="form-check-input mt-0" style="width:16px;height:16px;">
                                <span>السعر</span>
                            </label>
                            <label class="d-flex align-items-center gap-1" style="cursor:pointer;font-size:12px;">
                                <input type="hidden" name="show_brand" value="0">
                                <input type="checkbox" name="show_brand" value="1" checked class="form-check-input mt-0" style="width:16px;height:16px;">
                                <span>العلامة</span>
                            </label>
                        </div>
                    </div>

                    {{-- Label Counter --}}
                    <div class="col-auto">
                        <div class="d-flex align-items-center gap-2" style="height:100%;">
                            <div id="labelCounter" class="badge bg-dark text-white fs-6 px-3 py-2">
                                <i class="fas fa-tag me-1"></i>
                                <span id="labelCountText">0 منتج × 1 نسخة = 0 ملصق</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-auto">
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

<style>
.position-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.position-option.active .border {
    border-color: #0d6efd !important;
    background: #e8f0fe;
    box-shadow: 0 0 0 2px rgba(13,110,253,0.2);
}
.position-option .border:hover {
    border-color: #0d6efd;
}
</style>

<script>
function toggleAll(source) {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = source.checked);
    updateLabelCount();
}

function toggleCustomSize(select) {
    document.getElementById('customSizeFields').style.display = select.value === 'custom' ? 'inline-flex' : 'none';
}

function highlightPosition(el) {
    document.querySelectorAll('.position-option').forEach(opt => opt.classList.remove('active'));
    el.closest('.position-option').classList.add('active');
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
        el.textContent = totalProduct + ' منتج × ' + (totalLabels > 0 ? (totalLabels / totalProduct).toFixed(0) : '1') + ' نسخة = ' + totalLabels + ' ملصق';
    }
    const selLabel = document.getElementById('selectedCountLabel');
    if (selLabel) {
        selLabel.textContent = totalProduct + ' منتج محدد';
    }
}

function submitPrintForm() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    const selectAllMode = document.getElementById('selectAllMatching')?.checked;
    if (!selectAllMode && checked.length === 0) {
        alert('يرجى اختيار منتج واحد على الأقل للطباعة.');
        return;
    }
    if (selectAllMode) {
        document.getElementById('selectAllInput').value = '1';
    }
    document.getElementById('printForm').submit();
}

function toggleSelectAllMatching(cb) {
    if (cb.checked) {
        // Check all visible checkboxes
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
    const row = document.querySelector('input.product-checkbox[value="' + productId + '"]')?.closest('tr');
    const qty = row ? (parseInt(row.querySelector('.qty-input')?.value) || 1) : 1;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.barcodes.print") }}';
    form.target = '_blank';
    const csrf = document.querySelector('input[name="_token"]').value;
    form.innerHTML = `
        <input type="hidden" name="_token" value="${csrf}">
        <input type="hidden" name="ids[]" value="${productId}">
        <input type="hidden" name="qty[${productId}]" value="${qty}">
        <input type="hidden" name="layout" value="a4_24">
        <input type="hidden" name="width" value="50">
        <input type="hidden" name="height" value="30">
        <input type="hidden" name="barcode_position" value="bottom">
        <input type="hidden" name="show_name" value="1">
        <input type="hidden" name="show_price" value="1">
        <input type="hidden" name="show_brand" value="1">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// Auto-select qty of parent when checkbox is checked
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

// Preview barcode modal
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
        // If EAN13 fails (non-numeric), try CODE128
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

// Render mini barcodes on page load
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
