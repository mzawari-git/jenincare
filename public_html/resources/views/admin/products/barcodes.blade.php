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
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleAll(this)">
                                </th>
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
                                    <input type="checkbox" class="form-check-input product-checkbox" name="ids[]" value="{{ $product->id }}">
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
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="quickPrint({{ $product->id }})">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Edit Barcode Modal --}}
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
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">لا توجد منتجات مطابقة للبحث</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div>
                    <select name="layout" class="form-select form-select-sm d-inline-block w-auto">
                        <option value="a4_24">A4 — 24 ملصق (5×3 سم)</option>
                        <option value="a4_12">A4 — 12 ملصق (7×4 سم)</option>
                        <option value="a4_6">A4 — 6 ملصق (10×5 سم)</option>
                    </select>
                </div>
                <div>
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function toggleAll(source) {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = source.checked);
}

function submitPrintForm() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    if (checked.length === 0) {
        alert('يرجى اختيار منتج واحد على الأقل للطباعة.');
        return;
    }
    document.getElementById('printForm').submit();
}

function quickPrint(productId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.barcodes.print") }}';
    form.target = '_blank';
    form.innerHTML = `
        @csrf
        <input type="hidden" name="ids[]" value="${productId}">
        <input type="hidden" name="layout" value="a4_24">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
@endsection
