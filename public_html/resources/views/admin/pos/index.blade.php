@php
    $title = __('نقطة البيع - POS');
@endphp

@extends('admin.layouts.app')

@section('title', $title)

@push('styles')
<style>
    :root {
        --pos-header: 60px;
        --pos-products-width: 55%;
    }

    .pos-wrapper {
        display: flex;
        gap: 1rem;
        height: calc(100vh - var(--header-height, 60px) - 140px);
        min-height: 500px;
        position: relative;
    }

    .pos-products-panel {
        flex: 1;
        min-width: 0;
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .pos-products-panel .panel-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--gray-100);
        display: flex;
        align-items: center;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .pos-products-panel .panel-header .search-box {
        flex: 1;
        min-width: 200px;
        position: relative;
    }

    .pos-products-panel .panel-header .search-box input {
        padding-right: 2.5rem;
        border-radius: 10px;
        border: 2px solid var(--gray-200);
        height: 44px;
        font-size: .95rem;
        transition: all .2s;
    }

    .pos-products-panel .panel-header .search-box input:focus {
        border-color: var(--pink-400);
        box-shadow: 0 0 0 3px rgba(219,39,119,0.1);
    }

    .pos-products-panel .panel-header .search-box .search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        font-size: .9rem;
    }

    .pos-products-panel .panel-header .search-box .clear-search {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        cursor: pointer;
        display: none;
        font-size: .8rem;
    }

    .pos-products-panel .panel-header .search-box .clear-search:hover {
        color: var(--gray-600);
    }

    .pos-products-panel .panel-header select {
        width: auto;
        min-width: 150px;
        border-radius: 10px;
        border: 2px solid var(--gray-200);
        height: 44px;
        font-size: .9rem;
    }

    .pos-products-grid {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: .75rem;
    }

    .pos-product-card {
        background: var(--gray-50);
        border-radius: 12px;
        border: 2px solid transparent;
        padding: 1rem .75rem;
        text-align: center;
        cursor: pointer;
        transition: all .2s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .5rem;
        user-select: none;
        position: relative;
    }

    .pos-product-card:hover {
        border-color: var(--pink-200);
        background: var(--pink-50);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(219,39,119,0.1);
    }

    .pos-product-card:active {
        transform: scale(0.97);
    }

    .pos-product-card .product-img {
        width: 64px;
        height: 64px;
        border-radius: 10px;
        object-fit: cover;
        background: var(--gray-100);
    }

    .pos-product-card .product-img-placeholder {
        width: 64px;
        height: 64px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--pink-100), var(--pink-200));
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--pink-600);
        font-size: 1.5rem;
    }

    .pos-product-card .product-name {
        font-size: .8rem;
        font-weight: 600;
        color: var(--gray-800);
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.1em;
    }

    .pos-product-card .product-price {
        font-size: .95rem;
        font-weight: 800;
        color: var(--pink-600);
    }

    .pos-product-card .product-sku {
        font-size: .65rem;
        color: var(--gray-400);
    }

    .pos-product-card .stock-badge {
        position: absolute;
        top: 6px;
        left: 6px;
        font-size: .6rem;
        padding: 2px 6px;
        border-radius: 6px;
        font-weight: 600;
    }

    .pos-product-card .stock-badge.low {
        background: #fef3c7;
        color: #d97706;
    }

    .pos-product-card .stock-badge.out {
        background: #fef2f2;
        color: #ef4444;
    }

    .pos-product-card.added {
        border-color: #22c55e;
        background: #f0fdf4;
    }

    .pos-product-card.added .product-price {
        color: #22c55e;
    }

    .pos-products-empty {
        grid-column: 1 / -1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        color: var(--gray-400);
    }

    .pos-products-empty i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: .4;
    }

    /* Cart Panel */
    .pos-cart-panel {
        width: 400px;
        min-width: 320px;
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .pos-cart-panel .cart-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--gray-100);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .pos-cart-panel .cart-header h5 {
        margin: 0;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .pos-cart-panel .cart-header .cart-count {
        background: var(--pink-100);
        color: var(--pink-600);
        font-size: .75rem;
        padding: 2px 10px;
        border-radius: 20px;
        font-weight: 700;
    }

    .pos-cart-panel .cart-body {
        flex: 1;
        overflow-y: auto;
        padding: .75rem;
    }

    .pos-cart-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--gray-400);
        text-align: center;
        padding: 2rem;
    }

    .pos-cart-empty i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: .4;
    }

    .pos-cart-empty p {
        font-size: .85rem;
    }

    .cart-item {
        display: flex;
        gap: .75rem;
        padding: .75rem;
        border-radius: 12px;
        background: var(--gray-50);
        margin-bottom: .5rem;
        transition: all .2s;
        position: relative;
    }

    .cart-item:hover {
        background: var(--gray-100);
    }

    .cart-item .item-img {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
        background: var(--gray-100);
    }

    .cart-item .item-img-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--pink-100), var(--pink-200));
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--pink-600);
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .cart-item .item-info {
        flex: 1;
        min-width: 0;
    }

    .cart-item .item-name {
        font-size: .8rem;
        font-weight: 600;
        color: var(--gray-800);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cart-item .item-price {
        font-size: .75rem;
        color: var(--gray-500);
    }

    .cart-item .item-qty-controls {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
    }

    .cart-item .item-qty-controls .qty-btn {
        width: 26px;
        height: 26px;
        border-radius: 6px;
        border: 1px solid var(--gray-200);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .65rem;
        cursor: pointer;
        transition: all .15s;
        color: var(--gray-600);
    }

    .cart-item .item-qty-controls .qty-btn:hover {
        background: var(--pink-50);
        border-color: var(--pink-300);
        color: var(--pink-600);
    }

    .cart-item .item-qty-controls .qty-value {
        width: 36px;
        text-align: center;
        font-weight: 700;
        font-size: .85rem;
        color: var(--gray-800);
    }

    .cart-item .item-total {
        font-size: .9rem;
        font-weight: 800;
        color: var(--pink-600);
        align-self: center;
        min-width: 60px;
        text-align: left;
    }

    .cart-item .item-remove {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: none;
        background: rgba(239,68,68,0.1);
        color: #ef4444;
        font-size: .6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0;
        transition: all .15s;
    }

    .cart-item:hover .item-remove {
        opacity: 1;
    }

    .cart-item .item-remove:hover {
        background: #ef4444;
        color: #fff;
    }

    /* Cart Footer */
    .pos-cart-panel .cart-footer {
        border-top: 1px solid var(--gray-100);
        padding: 1rem 1.25rem;
    }

    .cart-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .35rem 0;
        font-size: .85rem;
    }

    .cart-summary-row.total {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--gray-800);
        padding: .6rem 0;
        border-top: 2px dashed var(--gray-200);
        margin-top: .25rem;
    }

    .cart-summary-row.total .amount {
        color: var(--pink-600);
        font-size: 1.3rem;
    }

    .cart-customer-fields {
        display: flex;
        flex-direction: column;
        gap: .5rem;
        margin-bottom: .75rem;
    }

    .cart-customer-fields .row {
        margin: 0 -4px;
    }

    .cart-customer-fields .row > div {
        padding: 0 4px;
    }

    .cart-customer-fields input,
    .cart-customer-fields select {
        font-size: .82rem;
        border-radius: 8px;
        border: 1px solid var(--gray-200);
        height: 38px;
    }

    .cart-customer-fields input:focus,
    .cart-customer-fields select:focus {
        border-color: var(--pink-400);
        box-shadow: 0 0 0 3px rgba(219,39,119,0.1);
    }

    .btn-checkout {
        width: 100%;
        height: 50px;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        background: linear-gradient(135deg, var(--pink-600), var(--pink-500));
        color: #fff;
        border: none;
        transition: all .2s;
    }

    .btn-checkout:hover {
        background: linear-gradient(135deg, var(--pink-700), var(--pink-600));
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(219,39,119,0.3);
    }

    .btn-checkout:disabled {
        opacity: .5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .payment-methods {
        display: flex;
        gap: .5rem;
        margin-bottom: .75rem;
    }

    .payment-methods .pm-btn {
        flex: 1;
        padding: .5rem;
        border-radius: 8px;
        border: 2px solid var(--gray-200);
        background: #fff;
        font-size: .78rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s;
        text-align: center;
        color: var(--gray-600);
    }

    .payment-methods .pm-btn:hover {
        border-color: var(--gray-300);
    }

    .payment-methods .pm-btn.active {
        border-color: var(--pink-500);
        background: var(--pink-50);
        color: var(--pink-600);
    }

    .payment-methods .pm-btn i {
        display: block;
        font-size: 1.1rem;
        margin-bottom: 2px;
    }

    /* Recent Sales Bar */
    .recent-sales-bar {
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
        padding: .75rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        overflow-x: auto;
        margin-top: 1rem;
    }

    .recent-sales-bar .rs-label {
        font-size: .75rem;
        font-weight: 600;
        color: var(--gray-500);
        white-space: nowrap;
    }

    .recent-sales-bar .rs-items {
        display: flex;
        gap: .5rem;
        flex: 1;
    }

    .recent-sales-bar .rs-item {
        padding: .35rem .75rem;
        border-radius: 8px;
        background: var(--gray-50);
        white-space: nowrap;
        font-size: .75rem;
        color: var(--gray-600);
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-shrink: 0;
    }

    .recent-sales-bar .rs-item .rs-total {
        font-weight: 700;
        color: var(--pink-600);
    }

    /* Loading spinner */
    .pos-loading {
        display: none;
        align-items: center;
        justify-content: center;
        padding: 3rem;
    }

    .pos-loading.show {
        display: flex;
    }

    .spinner-pink {
        width: 40px;
        height: 40px;
        border: 3px solid var(--gray-200);
        border-top-color: var(--pink-500);
        border-radius: 50%;
        animation: spin .6s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Toast for POS */
    .pos-toast {
        position: fixed;
        top: 1rem;
        left: 50%;
        transform: translateX(-50%) translateY(-100px);
        z-index: 99999;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,.15);
        padding: .75rem 1.25rem;
        display: flex;
        align-items: center;
        gap: .75rem;
        font-size: .9rem;
        font-weight: 600;
        transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1);
        border-right: 4px solid #22c55e;
    }

    .pos-toast.show {
        transform: translateX(-50%) translateY(0);
    }

    .pos-toast.error {
        border-right-color: #ef4444;
    }

    .pos-toast i {
        font-size: 1.2rem;
    }

    .pos-toast .toast-close {
        margin-right: auto;
        cursor: pointer;
        color: var(--gray-400);
        font-size: .8rem;
        padding: 4px;
    }

    /* Scanner input hidden */
    #barcodeScanner {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 0;
        height: 0;
    }

    /* Mobile */
    @media (max-width: 991px) {
        .pos-wrapper {
            flex-direction: column;
            height: auto;
            min-height: auto;
        }

        .pos-cart-panel {
            width: 100%;
            min-width: 0;
            max-height: 500px;
        }

        .pos-products-grid {
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        }

        .recent-sales-bar {
            flex-wrap: wrap;
        }
    }

    /* Quick quantity modal */
    .qty-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.4);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .qty-modal-overlay.show {
        display: flex;
    }

    .qty-modal {
        background: #fff;
        border-radius: 16px;
        padding: 1.5rem;
        width: 300px;
        box-shadow: 0 20px 60px rgba(0,0,0,.2);
        text-align: center;
    }

    .qty-modal h6 {
        font-weight: 700;
        margin-bottom: .5rem;
    }

    .qty-modal .qty-display {
        font-size: 3rem;
        font-weight: 800;
        color: var(--pink-600);
        padding: 1rem 0;
    }

    .qty-modal .qty-modal-btns {
        display: flex;
        gap: .5rem;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .qty-modal .qty-modal-btns button {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        border: 2px solid var(--gray-200);
        background: #fff;
        font-size: 1.3rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s;
        color: var(--gray-700);
    }

    .qty-modal .qty-modal-btns button:hover {
        border-color: var(--pink-300);
        color: var(--pink-600);
    }

    .qty-modal .qty-confirm {
        width: 100%;
        height: 44px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--pink-600), var(--pink-500));
        color: #fff;
        border: none;
        font-weight: 700;
        font-size: .95rem;
        cursor: pointer;
    }

    .qty-modal .qty-confirm:hover {
        background: linear-gradient(135deg, var(--pink-700), var(--pink-600));
    }
</style>
@endpush

@section('content')
<div>
    {{-- Stats Row --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="stat-card-new d-flex align-items-center gap-3">
                <div class="stat-icon-new" style="background:#fdf2f8;color:#db2777;">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div>
                    <div class="stat-value-new" style="font-size:1.3rem;">{{ $todaySales }}</div>
                    <div class="stat-label-new">مبيعات اليوم</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card-new d-flex align-items-center gap-3">
                <div class="stat-icon-new" style="background:#f0fdf4;color:#22c55e;">
                    <i class="fas fa-shekel-sign"></i>
                </div>
                <div>
                    <div class="stat-value-new" style="font-size:1.3rem;">₪{{ number_format($todayRevenue, 2) }}</div>
                    <div class="stat-label-new">إيرادات اليوم</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card-new d-flex align-items-center gap-3">
                <div class="stat-icon-new" style="background:#eff6ff;color:#2563eb;">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div>
                    <div class="stat-value-new" style="font-size:1.3rem;">{{ $totalSales }}</div>
                    <div class="stat-label-new">إجمالي المبيعات</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card-new d-flex align-items-center gap-3">
                <div class="stat-icon-new" style="background:#fef3c7;color:#d97706;">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <div class="stat-value-new" style="font-size:1.3rem;">₪{{ number_format($totalRevenue, 2) }}</div>
                    <div class="stat-label-new">إجمالي الإيرادات</div>
                </div>
            </div>
        </div>
    </div>

    {{-- POS Main --}}
    <div class="pos-wrapper">
        {{-- Products Panel --}}
        <div class="pos-products-panel">
            <div class="panel-header">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="productSearch" class="form-control" placeholder="بحث بالاسم أو الباركود أو SKU..." autofocus>
                    <i class="fas fa-times clear-search" id="clearSearch"></i>
                </div>
                <select id="categoryFilter" class="form-select">
                    <option value="">كل التصنيفات</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name_ar }}</option>
                    @endforeach
                </select>
                <button class="btn btn-outline-pink btn-sm" onclick="document.getElementById('barcodeScanner').focus()" title="مسح باركود">
                    <i class="fas fa-barcode"></i>
                </button>
            </div>

            <div id="productsLoading" class="pos-loading">
                <div class="spinner-pink"></div>
            </div>

            <div class="pos-products-grid" id="productsGrid">
                @foreach($products as $product)
                    <div class="pos-product-card" data-id="{{ $product->id }}"
                         data-name="{{ $product->name }}"
                         data-price="{{ $product->b2c_price }}"
                         data-stock="{{ $product->available_quantity }}"
                         data-image="{{ $product->main_image_url }}"
                         data-sku="{{ $product->sku }}"
                         onclick="addToCart(this)">
                        @if($product->main_image_url)
                            <img src="{{ $product->main_image_url }}" alt="" class="product-img" loading="lazy">
                        @else
                            <div class="product-img-placeholder"><i class="fas fa-box"></i></div>
                        @endif
                        <div class="product-name">{{ $product->name }}</div>
                        <div class="product-price">₪{{ number_format($product->b2c_price, 2) }}</div>
                        <div class="product-sku">{{ $product->sku }}</div>
                        @if($product->available_quantity <= 5 && $product->track_inventory)
                            <span class="stock-badge {{ $product->available_quantity == 0 ? 'out' : 'low' }}">
                                {{ $product->available_quantity == 0 ? 'غير متوفر' : $product->available_quantity . ' قطع' }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Cart Panel --}}
        <div class="pos-cart-panel" id="cartPanel">
            <div class="cart-header">
                <h5>
                    <i class="fas fa-shopping-basket" style="color:var(--pink-600);"></i>
                    سلة المشتريات
                    <span class="cart-count" id="cartCount">0</span>
                </h5>
                <button class="btn btn-sm btn-outline-danger" onclick="clearCart()" id="clearCartBtn" style="display:none;">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>

            <div class="cart-body" id="cartBody">
                <div class="pos-cart-empty" id="cartEmpty">
                    <i class="fas fa-shopping-basket"></i>
                    <p>السلة فارغة<br><span style="font-size:.75rem;">اختر المنتجات من القائمة</span></p>
                </div>
                <div id="cartItems"></div>
            </div>

            <div class="cart-footer" id="cartFooter" style="display:none;">
                <div class="cart-customer-fields">
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="text" id="customerName" class="form-control" placeholder="اسم العميل (اختياري)">
                        </div>
                        <div class="col-6">
                            <input type="tel" id="customerPhone" class="form-control" placeholder="رقم الهاتف (اختياري)">
                        </div>
                    </div>
                    <input type="email" id="customerEmail" class="form-control" placeholder="البريد الإلكتروني (اختياري)">
                </div>

                <label style="font-size:.75rem;font-weight:600;color:var(--gray-500);margin-bottom:.35rem;display:block;">طريقة الدفع</label>
                <div class="payment-methods">
                    <div class="pm-btn active" data-method="cash" onclick="selectPayment(this)">
                        <i class="fas fa-money-bill-wave"></i> نقداً
                    </div>
                    <div class="pm-btn" data-method="card" onclick="selectPayment(this)">
                        <i class="fas fa-credit-card"></i> بطاقة
                    </div>
                    <div class="pm-btn" data-method="transfer" onclick="selectPayment(this)">
                        <i class="fas fa-exchange-alt"></i> تحويل
                    </div>
                </div>

                <div class="cart-summary-row">
                    <span>المجموع الفرعي</span>
                    <span class="amount" id="subtotalDisplay">₪0.00</span>
                </div>
                <div class="cart-summary-row total">
                    <span>الإجمالي</span>
                    <span class="amount" id="totalDisplay">₪0.00</span>
                </div>

                <button class="btn-checkout" id="checkoutBtn" onclick="submitSale()">
                    <i class="fas fa-check-circle"></i> إتمام البيع
                </button>
            </div>
        </div>
    </div>

    {{-- Recent Sales --}}
    <div class="recent-sales-bar">
        <div class="rs-label"><i class="fas fa-history" style="color:var(--pink-600);"></i> آخر المبيعات</div>
        <div class="rs-items" id="recentSalesList">
            @forelse($recentSales as $sale)
                <div class="rs-item">
                    <i class="fas fa-receipt" style="color:var(--gray-400);"></i>
                    <span>{{ $sale->pos_sale_id }}</span>
                    <span class="rs-total">₪{{ number_format($sale->order_total, 2) }}</span>
                    <span style="color:var(--gray-400);font-size:.65rem;">{{ $sale->created_at->format('H:i') }}</span>
                </div>
            @empty
                <div class="rs-item" style="color:var(--gray-400);">
                    <i class="fas fa-inbox"></i> لا توجد مبيعات بعد
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Hidden barcode scanner input --}}
<input type="text" id="barcodeScanner" autocomplete="off">

{{-- Quantity Modal --}}
<div class="qty-modal-overlay" id="qtyModal">
    <div class="qty-modal">
        <h6 id="qtyModalTitle">الكمية</h6>
        <div class="qty-display" id="qtyDisplay">1</div>
        <div class="qty-modal-btns">
            <button onclick="adjustQty(-5)">-5</button>
            <button onclick="adjustQty(-1)">-1</button>
            <button onclick="adjustQty(1)">+1</button>
            <button onclick="adjustQty(5)">+5</button>
        </div>
        <div class="row g-2 mb-3">
            <div class="col-4"><button class="btn btn-sm btn-outline-secondary w-100" onclick="setQty(1)">1</button></div>
            <div class="col-4"><button class="btn btn-sm btn-outline-secondary w-100" onclick="setQty(2)">2</button></div>
            <div class="col-4"><button class="btn btn-sm btn-outline-secondary w-100" onclick="setQty(3)">3</button></div>
            <div class="col-4"><button class="btn btn-sm btn-outline-secondary w-100" onclick="setQty(5)">5</button></div>
            <div class="col-4"><button class="btn btn-sm btn-outline-secondary w-100" onclick="setQty(10)">10</button></div>
            <div class="col-4"><button class="btn btn-sm btn-outline-secondary w-100" onclick="setQty(20)">20</button></div>
        </div>
        <button class="qty-confirm" onclick="confirmQty()">تأكيد</button>
    </div>
</div>

{{-- POS Toast --}}
<div class="pos-toast" id="posToast">
    <i class="fas fa-check-circle" style="color:#22c55e;"></i>
    <span id="toastMessage">تمت الإضافة</span>
    <i class="fas fa-times toast-close" onclick="hideToast()"></i>
</div>
@endsection

@push('scripts')
<script>
    // State
    let cart = [];
    let selectedPayment = 'cash';
    let pendingProductId = null;
    let pendingQty = 1;
    let searchTimeout = null;

    // DOM refs
    const productsGrid = document.getElementById('productsGrid');
    const productsLoading = document.getElementById('productsLoading');
    const cartItems = document.getElementById('cartItems');
    const cartEmpty = document.getElementById('cartEmpty');
    const cartFooter = document.getElementById('cartFooter');
    const cartCount = document.getElementById('cartCount');
    const subtotalDisplay = document.getElementById('subtotalDisplay');
    const totalDisplay = document.getElementById('totalDisplay');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const productSearch = document.getElementById('productSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const clearSearch = document.getElementById('clearSearch');
    const qtyModal = document.getElementById('qtyModal');
    const qtyDisplay = document.getElementById('qtyDisplay');
    const qtyModalTitle = document.getElementById('qtyModalTitle');
    const posToast = document.getElementById('posToast');
    const toastMessage = document.getElementById('toastMessage');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const barcodeInput = document.getElementById('barcodeScanner');

    // Add to cart
    function addToCart(el) {
        const id = parseInt(el.dataset.id);
        const name = el.dataset.name;
        const price = parseFloat(el.dataset.price);
        const stock = parseInt(el.dataset.stock);

        if (stock <= 0) {
            showToast('هذا المنتج غير متوفر حالياً', true);
            return;
        }

        const existing = cart.find(i => i.product_id === id);
        if (existing) {
            if (existing.quantity >= stock) {
                showToast('الكمية المطلوبة غير متوفرة', true);
                return;
            }
            existing.quantity++;
            renderCart();
            el.classList.add('added');
            showToast('تم زيادة الكمية');
            return;
        }

        pendingProductId = id;
        pendingQty = 1;
        qtyModalTitle.textContent = name;
        qtyDisplay.textContent = '1';
        qtyModal.classList.add('show');
    }

    function adjustQty(delta) {
        const stock = getPendingStock();
        let newQty = pendingQty + delta;
        if (newQty < 1) newQty = 1;
        if (newQty > stock) newQty = stock;
        pendingQty = newQty;
        qtyDisplay.textContent = pendingQty;
    }

    function setQty(val) {
        const stock = getPendingStock();
        pendingQty = Math.min(val, stock);
        if (pendingQty < 1) pendingQty = 1;
        qtyDisplay.textContent = pendingQty;
    }

    function getPendingStock() {
        const card = document.querySelector(`.pos-product-card[data-id="${pendingProductId}"]`);
        return card ? parseInt(card.dataset.stock) : 999;
    }

    function confirmQty() {
        if (!pendingProductId) return;
        const card = document.querySelector(`.pos-product-card[data-id="${pendingProductId}"]`);
        if (!card) return;

        cart.push({
            product_id: pendingProductId,
            name: card.dataset.name,
            price: parseFloat(card.dataset.price),
            quantity: pendingQty,
            stock: parseInt(card.dataset.stock),
            image: card.dataset.image || '',
            sku: card.dataset.sku || '',
        });

        qtyModal.classList.remove('show');
        card.classList.add('added');
        pendingProductId = null;
        renderCart();
        showToast('تمت إضافة المنتج');
    }

    // Quantity modal keyboard
    document.addEventListener('keydown', function(e) {
        if (!qtyModal.classList.contains('show')) return;
        if (e.key === 'Enter') { confirmQty(); e.preventDefault(); }
        if (e.key === 'Escape') { qtyModal.classList.remove('show'); e.preventDefault(); }
        if (e.key === '+' || e.key === '=') { adjustQty(1); e.preventDefault(); }
        if (e.key === '-') { adjustQty(-1); e.preventDefault(); }
    });

    // Remove from cart
    function removeFromCart(index) {
        const item = cart[index];
        cart.splice(index, 1);
        const card = document.querySelector(`.pos-product-card[data-id="${item.product_id}"]`);
        if (card) card.classList.remove('added');
        renderCart();
    }

    // Change quantity in cart
    function changeQty(index, delta) {
        const item = cart[index];
        const newQty = item.quantity + delta;
        if (newQty < 1) {
            removeFromCart(index);
            return;
        }
        if (newQty > item.stock) {
            showToast('الكمية المطلوبة غير متوفرة', true);
            return;
        }
        item.quantity = newQty;
        renderCart();
    }

    // Clear cart
    function clearCart() {
        document.querySelectorAll('.pos-product-card.added').forEach(el => el.classList.remove('added'));
        cart = [];
        renderCart();
    }

    // Render cart
    function renderCart() {
        if (cart.length === 0) {
            cartEmpty.style.display = 'flex';
            cartItems.innerHTML = '';
            cartFooter.style.display = 'none';
            clearCartBtn.style.display = 'none';
            cartCount.textContent = '0';
            return;
        }

        cartEmpty.style.display = 'none';
        cartFooter.style.display = 'block';
        clearCartBtn.style.display = 'inline-block';
        cartCount.textContent = cart.length;

        let html = '';
        let subtotal = 0;
        let totalQty = 0;

        cart.forEach((item, index) => {
            const lineTotal = item.price * item.quantity;
            subtotal += lineTotal;
            totalQty += item.quantity;

            html += `
                <div class="cart-item">
                    <button class="item-remove" onclick="removeFromCart(${index})" title="إزالة">
                        <i class="fas fa-times"></i>
                    </button>
                    ${item.image
                        ? `<img src="${item.image}" alt="" class="item-img" loading="lazy">`
                        : `<div class="item-img-placeholder"><i class="fas fa-box"></i></div>`
                    }
                    <div class="item-info">
                        <div class="item-name">${item.name}</div>
                        <div class="item-price">₪${item.price.toFixed(2)}</div>
                        <div class="item-qty-controls">
                            <button class="qty-btn" onclick="changeQty(${index}, -1)"><i class="fas fa-minus"></i></button>
                            <span class="qty-value">${item.quantity}</span>
                            <button class="qty-btn" onclick="changeQty(${index}, 1)"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="item-total">₪${lineTotal.toFixed(2)}</div>
                </div>
            `;
        });

        cartItems.innerHTML = html;
        subtotalDisplay.textContent = '₪' + subtotal.toFixed(2);
        totalDisplay.textContent = '₪' + subtotal.toFixed(2);
    }

    // Payment method selection
    function selectPayment(el) {
        document.querySelectorAll('.pm-btn').forEach(b => b.classList.remove('active'));
        el.classList.add('active');
        selectedPayment = el.dataset.method;
    }

    // Submit sale
    async function submitSale() {
        if (cart.length === 0) {
            showToast('السلة فارغة', true);
            return;
        }

        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '<div class="spinner-border spinner-border-sm" style="color:#fff;"></div> جاري المعالجة...';

        const items = cart.map(i => ({
            product_id: i.product_id,
            quantity: i.quantity,
            price: i.price,
        }));

        const payload = {
            items: items,
            payment_method: selectedPayment,
            customer_name: document.getElementById('customerName').value.trim() || null,
            customer_phone: document.getElementById('customerPhone').value.trim() || null,
            customer_email: document.getElementById('customerEmail').value.trim() || null,
        };

        try {
            const response = await fetch('{{ route('admin.pos.sale.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (data.success) {
                showToast('تم تسجيل البيع بنجاح! رقم المرجع: ' + data.data.pos_sale_id);
                clearCart();
                document.getElementById('customerName').value = '';
                document.getElementById('customerPhone').value = '';
                document.getElementById('customerEmail').value = '';
                loadRecentSales();
                updateStats();
            } else {
                showToast(data.message || 'حدث خطأ أثناء تسجيل البيع', true);
            }
        } catch (err) {
            showToast('فشل الاتصال بالخادم', true);
        } finally {
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = '<i class="fas fa-check-circle"></i> إتمام البيع';
        }
    }

    // Load recent sales
    async function loadRecentSales() {
        try {
            const response = await fetch('{{ route('admin.pos.recent-sales') }}');
            const data = await response.json();
            const list = document.getElementById('recentSalesList');

            if (data.sales && data.sales.length > 0) {
                list.innerHTML = data.sales.map(s => `
                    <div class="rs-item">
                        <i class="fas fa-receipt" style="color:var(--gray-400);"></i>
                        <span>${s.pos_sale_id}</span>
                        <span class="rs-total">₪${s.total.toFixed(2)}</span>
                        <span style="color:var(--gray-400);font-size:.65rem;">${s.created_at}</span>
                    </div>
                `).join('');
            } else {
                list.innerHTML = '<div class="rs-item" style="color:var(--gray-400);"><i class="fas fa-inbox"></i> لا توجد مبيعات بعد</div>';
            }
        } catch(e) {}
    }

    // Update stats
    async function updateStats() {
        try {
            const res = await fetch(window.location.href);
            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            document.querySelector('.pos-wrapper').parentElement.innerHTML =
                doc.querySelector('.pos-wrapper').parentElement.innerHTML;
        } catch(e) {}
    }

    // Search products
    function searchProducts() {
        const q = productSearch.value.trim();
        const categoryId = categoryFilter.value;

        if (q.length > 0) {
            clearSearch.style.display = 'block';
        } else {
            clearSearch.style.display = 'none';
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(async () => {
            productsLoading.classList.add('show');
            productsGrid.style.display = 'none';

            try {
                const params = new URLSearchParams();
                if (q) params.set('q', q);
                if (categoryId) params.set('category_id', categoryId);

                const response = await fetch('{{ route('admin.pos.products.search') }}?' + params.toString());
                const data = await response.json();

                if (data.products && data.products.length > 0) {
                    renderProducts(data.products);
                } else {
                    productsGrid.innerHTML = `
                        <div class="pos-products-empty">
                            <i class="fas fa-search"></i>
                            <p>لا توجد منتجات مطابقة للبحث</p>
                        </div>
                    `;
                }
            } catch(e) {
                productsGrid.innerHTML = `
                    <div class="pos-products-empty">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>حدث خطأ أثناء البحث</p>
                    </div>
                `;
            } finally {
                productsLoading.classList.remove('show');
                productsGrid.style.display = 'grid';
            }
        }, 300);
    }

    function renderProducts(products) {
        productsGrid.innerHTML = products.map(p => {
            const inCart = cart.some(i => i.product_id === p.id);
            const stockBadge = p.stock <= 5 && p.track_inventory
                ? `<span class="stock-badge ${p.stock == 0 ? 'out' : 'low'}">${p.stock == 0 ? 'غير متوفر' : p.stock + ' قطع'}</span>`
                : '';

            return `
                <div class="pos-product-card ${inCart ? 'added' : ''}"
                     data-id="${p.id}"
                     data-name="${p.name}"
                     data-price="${p.price}"
                     data-stock="${p.stock}"
                     data-image="${p.image || ''}"
                     data-sku="${p.sku || ''}"
                     onclick="addToCart(this)">
                    ${p.image
                        ? `<img src="${p.image}" alt="" class="product-img" loading="lazy">`
                        : `<div class="product-img-placeholder"><i class="fas fa-box"></i></div>`
                    }
                    <div class="product-name">${p.name}</div>
                    <div class="product-price">₪${p.price.toFixed(2)}</div>
                    <div class="product-sku">${p.sku || ''}</div>
                    ${stockBadge}
                </div>
            `;
        }).join('');
    }

    // Category filter change
    categoryFilter.addEventListener('change', searchProducts);

    // Search input
    productSearch.addEventListener('input', searchProducts);

    // Clear search
    clearSearch.addEventListener('click', function() {
        productSearch.value = '';
        clearSearch.style.display = 'none';
        categoryFilter.value = '';
        searchProducts();
        productSearch.focus();
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (qtyModal.classList.contains('show')) return;

        // Ctrl+K or / to focus search
        if ((e.ctrlKey && e.key === 'k') || (e.key === '/' && !['INPUT', 'SELECT', 'TEXTAREA'].includes(e.target.tagName))) {
            e.preventDefault();
            productSearch.focus();
        }

        // Escape to clear cart
        if (e.key === 'Escape' && cart.length > 0) {
            if (confirm('تفريغ السلة؟')) clearCart();
        }

        // F2 for new sale / quick clear
        if (e.key === 'F2') {
            e.preventDefault();
            if (cart.length > 0) {
                if (confirm('بدء عملية بيع جديدة؟')) clearCart();
            }
        }
    });

    // Barcode scanner
    let barcodeBuffer = '';
    let barcodeTimer = null;

    barcodeInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && barcodeBuffer.length > 1) {
            e.preventDefault();
            const code = barcodeBuffer.trim();
            barcodeBuffer = '';
            findProductByBarcode(code);
        }
    });

    barcodeInput.addEventListener('keypress', function(e) {
        clearTimeout(barcodeTimer);
        barcodeBuffer += e.key;
        barcodeTimer = setTimeout(() => { barcodeBuffer = ''; }, 100);
    });

    async function findProductByBarcode(code) {
        const response = await fetch('{{ route('admin.pos.products.search') }}?q=' + encodeURIComponent(code));
        const data = await response.json();
        if (data.products && data.products.length > 0) {
            const p = data.products[0];
            const card = document.querySelector(`.pos-product-card[data-id="${p.id}"]`);
            if (card) {
                addToCart(card);
            } else {
                showToast('المنتج غير معروض في القائمة', true);
            }
        } else {
            showToast('لم يتم العثور على المنتج', true);
        }
    }

    // Toast
    function showToast(msg, isError = false) {
        toastMessage.textContent = msg;
        posToast.className = 'pos-toast' + (isError ? ' error' : '');
        posToast.querySelector('i:first-child').className = isError ? 'fas fa-exclamation-circle' : 'fas fa-check-circle';
        posToast.querySelector('i:first-child').style.color = isError ? '#ef4444' : '#22c55e';
        posToast.classList.add('show');
        clearTimeout(window.toastTimeout);
        window.toastTimeout = setTimeout(hideToast, 3500);
    }

    function hideToast() {
        posToast.classList.remove('show');
    }

    // Click outside qty modal to close
    qtyModal.addEventListener('click', function(e) {
        if (e.target === qtyModal) qtyModal.classList.remove('show');
    });

    // Keyboard: Ctrl+Enter to submit
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            if (cart.length > 0) {
                e.preventDefault();
                submitSale();
            }
        }
    });

    // Focus barcode on Shift+F1
    document.addEventListener('keydown', function(e) {
        if (e.shiftKey && e.key === 'F1') {
            e.preventDefault();
            barcodeInput.focus();
            showToast('نمط المسح نشط - امسح الباركود الآن');
        }
    });

    // Init render
    renderCart();
</script>
@endpush
