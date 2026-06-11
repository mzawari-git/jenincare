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
        --pos-radius-sm: 10px;
        --pos-radius-md: 14px;
        --pos-radius-lg: 18px;
        --pos-shadow-sm: 0 1px 3px rgba(0,0,0,.04);
        --pos-shadow-md: 0 4px 12px rgba(0,0,0,.06);
        --pos-shadow-lg: 0 8px 30px rgba(0,0,0,.08);
        --pos-transition: all .2s cubic-bezier(0.4,0,0.2,1);
    }

    /* Override layout for full-screen POS */
    .admin-main {
        height: 100vh !important;
        overflow: hidden !important;
        min-height: 0 !important;
    }
    .admin-main > .admin-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
        padding: .35rem .6rem !important;
        background: radial-gradient(ellipse at 50% 0%, rgba(219,39,119,.03) 0%, transparent 60%);
    }
    .admin-footer { display: none !important; }

    .pos-wrapper {
        display: flex;
        gap: .65rem;
        flex: 1;
        min-height: 0;
        position: relative;
    }

    .pos-products-panel {
        flex: 1;
        min-width: 0;
        background: #fff;
        border-radius: var(--pos-radius-lg);
        box-shadow: var(--pos-shadow-sm);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,.04);
    }

    .pos-products-panel .panel-header {
        padding: .5rem .65rem;
        border-bottom: 1px solid var(--gray-100);
        display: flex;
        align-items: center;
        gap: .4rem;
        flex-wrap: wrap;
        background: linear-gradient(180deg, #fafafa, #fff);
    }

    .pos-products-panel .panel-header .search-box {
        flex: 1;
        min-width: 150px;
        position: relative;
    }

    .pos-products-panel .panel-header .search-box input {
        padding-right: 2.2rem;
        border-radius: var(--pos-radius-sm);
        border: 2px solid var(--gray-200);
        height: 34px;
        font-size: .78rem;
        transition: var(--pos-transition);
        background: var(--gray-50);
    }

    .pos-products-panel .panel-header .search-box input:focus {
        border-color: var(--pink-400);
        box-shadow: 0 0 0 3px rgba(219,39,119,0.1);
        background: #fff;
    }

    .pos-products-panel .panel-header .search-box input::placeholder {
        color: var(--gray-400);
        font-size: .82rem;
    }

    /* Search suggest dropdown */
    .search-suggest {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 999;
        background: #fff;
        border: 1px solid var(--gray-200);
        border-radius: var(--pos-radius-sm);
        box-shadow: 0 8px 30px rgba(0,0,0,.12);
        max-height: 360px;
        overflow-y: auto;
        display: none;
        margin-top: 2px;
    }
    .search-suggest.show { display: block; }
    .search-suggest .ss-item {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .45rem .65rem;
        cursor: pointer;
        transition: all .1s;
        border-bottom: 1px solid var(--gray-50);
    }
    .search-suggest .ss-item:last-child { border-bottom: none; }
    .search-suggest .ss-item:hover { background: var(--pink-50); }
    .search-suggest .ss-item.ss-active { background: var(--pink-100); }
    .search-suggest .ss-item .ss-img {
        width: 32px; height: 32px; border-radius: 6px; object-fit: cover; flex-shrink: 0;
        background: var(--gray-50);
    }
    .search-suggest .ss-item .ss-img-placeholder {
        width: 32px; height: 32px; border-radius: 6px; flex-shrink: 0;
        background: var(--pink-50); display: flex; align-items: center; justify-content: center;
        color: var(--pink-300); font-size: .7rem;
    }
    .search-suggest .ss-item .ss-info { flex: 1; min-width: 0; }
    .search-suggest .ss-item .ss-info .ss-name { font-size: .75rem; font-weight: 600; color: var(--gray-800); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .search-suggest .ss-item .ss-info .ss-sku { font-size: .6rem; color: var(--gray-400); }
    .search-suggest .ss-item .ss-price { font-size: .8rem; font-weight: 700; color: var(--pink-600); white-space: nowrap; }
    .search-suggest .ss-item .ss-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

    .pos-products-panel .panel-header .search-box .search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        font-size: .82rem;
        pointer-events: none;
    }

    .pos-products-panel .panel-header .search-box .clear-search {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        cursor: pointer;
        display: none;
        font-size: .75rem;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--gray-200);
        align-items: center;
        justify-content: center;
        transition: var(--pos-transition);
    }

    .pos-products-panel .panel-header .search-box .clear-search:hover {
        background: var(--gray-400);
        color: #fff;
    }

    /* Category filter pills */
    .pos-products-panel .panel-header .category-pills {
        display: flex;
        gap: .25rem;
        flex-wrap: wrap;
        flex-shrink: 0;
    }
    .pos-products-panel .panel-header .cpill {
        padding: .22rem .5rem;
        border-radius: 6px;
        border: 1.5px solid transparent;
        background: var(--gray-50);
        font-size: .64rem;
        font-weight: 600;
        color: var(--gray-500);
        cursor: pointer;
        transition: var(--pos-transition);
        white-space: nowrap;
        min-height: 26px;
    }
    .pos-products-panel .panel-header .cpill:hover {
        border-color: var(--pink-200);
        color: var(--pink-600);
        background: var(--pink-50);
    }
    .pos-products-panel .panel-header .cpill.active {
        border-color: var(--pink-600);
        background: var(--pink-600);
        color: #fff;
        box-shadow: 0 2px 8px rgba(219,39,119,0.25);
    }

    .pos-products-grid {
        flex: 1;
        overflow-y: auto;
        padding: .6rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(125px, 1fr));
        gap: .5rem;
        align-content: start;
    }

    /* ── Compact Product Card ── */
    .pos-product-card {
        background: #fff;
        border-radius: var(--pos-radius-sm);
        border: 1.5px solid var(--gray-100);
        padding: .5rem .4rem .45rem;
        text-align: center;
        cursor: pointer;
        transition: all .15s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .25rem;
        user-select: none;
        position: relative;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }

    .pos-product-card:hover {
        border-color: var(--pink-200);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(219,39,119,0.1);
    }

    .pos-product-card:active {
        transform: scale(0.95);
    }

    .pos-product-card .discount-badge {
        position: absolute;
        top: 4px;
        right: 4px;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff;
        font-size: .5rem;
        font-weight: 800;
        padding: 1px 5px;
        border-radius: 4px;
        z-index: 2;
        line-height: 1.3;
        box-shadow: 0 2px 4px rgba(239,68,68,.25);
    }

    .pos-product-card .product-img {
        width: 56px;
        height: 56px;
        border-radius: 8px;
        object-fit: cover;
        background: var(--gray-50);
        border: 1px solid var(--gray-100);
        transition: var(--pos-transition);
    }

    .pos-product-card:hover .product-img {
        border-color: var(--pink-200);
    }

    .pos-product-card .product-img-placeholder {
        width: 56px;
        height: 56px;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--pink-50), var(--pink-100));
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--pink-400);
        font-size: 1.1rem;
        border: 1px solid var(--pink-100);
    }

    .pos-product-card .product-name {
        font-size: .7rem;
        font-weight: 600;
        color: var(--gray-800);
        line-height: 1.25;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
        width: 100%;
    }

    .pos-product-card .product-price {
        font-size: .82rem;
        letter-spacing: -.01em;
        font-weight: 800;
        color: var(--pink-600);
        line-height: 1;
    }

    .pos-product-card .product-sku {
        display: none;
    }
    .pos-product-card:hover .product-sku {
        display: block;
        position: absolute;
        bottom: 2px;
        left: 50%;
        transform: translateX(-50%);
        font-size: .5rem;
        color: var(--gray-400);
        background: rgba(255,255,255,.9);
        padding: 0 4px;
        border-radius: 2px;
    }

    /* Stock indicator dot */
    .pos-product-card .stock-dot {
        position: absolute;
        top: 4px;
        left: 4px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        border: 1.5px solid #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,.12);
        z-index: 2;
    }
    .pos-product-card .stock-dot.sd-ok { background: #22c55e; }
    .pos-product-card .stock-dot.sd-low { background: #f59e0b; }
    .pos-product-card .stock-dot.sd-out { background: #ef4444; }
    .pos-product-card .stock-dot.sd-untracked { background: #d1d5db; }

    .pos-product-card.added {
        border-color: #22c55e;
        background: #f0fdf4;
        animation: addedPulse .35s cubic-bezier(0.4,0,0.2,1);
    }
    @keyframes addedPulse {
        0% { transform: scale(1); }
        40% { transform: scale(1.06); }
        100% { transform: scale(1); }
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
        padding: 2rem 1.5rem;
        color: var(--gray-400);
    }

    .pos-products-empty i.empty-icon {
        font-size: 2.4rem;
        margin-bottom: .6rem;
        opacity: .35;
    }

    .pos-products-empty .empty-categories {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        justify-content: center;
        margin-top: .5rem;
        max-width: 360px;
    }
    .pos-products-empty .empty-categories .empty-cat-btn {
        padding: .45rem .85rem;
        border-radius: 10px;
        background: var(--gray-50);
        border: 1.5px solid var(--gray-200);
        font-size: .78rem;
        font-weight: 600;
        color: var(--gray-600);
        cursor: pointer;
        transition: var(--pos-transition);
    }
    .pos-products-empty .empty-categories .empty-cat-btn:hover {
        border-color: var(--pink-300);
        background: var(--pink-50);
        color: var(--pink-600);
    }

    /* Cart Panel */
    .pos-cart-panel {
        width: 360px;
        min-width: 300px;
        background: #fff;
        border-radius: var(--pos-radius-lg);
        box-shadow: var(--pos-shadow-sm);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,.04);
        height: 100%;
    }

    .pos-cart-panel .cart-header {
        padding: .85rem 1.1rem;
        border-bottom: 1px solid var(--gray-100);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(180deg, #fafafa, #fff);
    }

    .pos-cart-panel .cart-header h5 {
        margin: 0;
        font-weight: 700;
        font-size: .95rem;
        display: flex;
        align-items: center;
        gap: .45rem;
    }

    .pos-cart-panel .cart-header .cart-count {
        background: var(--pink-100);
        color: var(--pink-600);
        font-size: .7rem;
        padding: 1px 9px;
        border-radius: 20px;
        font-weight: 700;
    }

    .pos-cart-panel .cart-body {
        flex: 1;
        overflow-y: auto;
        padding: .65rem;
        min-height: 0;
    }

    /* Cart item slide-in animation */
    .cart-item {
        animation: cartSlideIn .25s cubic-bezier(0.4,0,0.2,1);
    }
    @keyframes cartSlideIn {
        from { opacity: 0; transform: translateX(16px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .cart-item.removing {
        animation: cartSlideOut .2s cubic-bezier(0.4,0,1,1) forwards;
    }
    @keyframes cartSlideOut {
        to { opacity: 0; transform: translateX(16px); height: 0; padding: 0; margin: 0; overflow: hidden; }
    }

    /* Cart item edit highlight */
    .cart-item.edited {
        animation: cartEditPulse .35s cubic-bezier(0.4,0,0.2,1);
    }
    @keyframes cartEditPulse {
        0% { background: transparent; }
        30% { background: #fef3c7; }
        100% { background: transparent; }
    }

    /* Receipt preview fade-in */
    .receipt-fade-in {
        animation: receiptFadeIn .3s ease;
    }
    @keyframes receiptFadeIn {
        from { opacity: 0; transform: scale(.97); }
        to { opacity: 1; transform: scale(1); }
    }

    /* Auto-print countdown pulse */
    .countdown-pulse {
        animation: countPulse 1s ease-in-out infinite;
    }
    @keyframes countPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); color: #dc2626; }
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
        font-size: 2.6rem;
        margin-bottom: .75rem;
        opacity: .35;
    }

    .pos-cart-empty p {
        font-size: .85rem;
    }

    .cart-item {
        display: flex;
        align-items: center;
        gap: .4rem;
        padding: .4rem .55rem;
        border-radius: 8px;
        background: #fff;
        margin-bottom: .25rem;
        transition: var(--pos-transition);
        position: relative;
        min-height: 42px;
        border: 1px solid rgba(0,0,0,.04);
    }

    .cart-item:hover {
        border-color: var(--pink-200);
        box-shadow: 0 2px 8px rgba(219,39,119,.06);
    }

    .cart-item .item-img {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        object-fit: cover;
        flex-shrink: 0;
        background: var(--gray-100);
    }

    .cart-item .item-img-placeholder {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        background: linear-gradient(135deg, var(--pink-100), var(--pink-200));
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--pink-600);
        font-size: .75rem;
        flex-shrink: 0;
    }

    .cart-item .item-info {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: .4rem;
    }

    .cart-item .item-name {
        font-size: .72rem;
        font-weight: 600;
        color: var(--gray-800);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
        min-width: 0;
    }

    .cart-item .item-price {
        font-size: .65rem;
        color: var(--gray-500);
        white-space: nowrap;
        flex-shrink: 0;
        cursor: pointer;
    }
    .cart-item .item-price:hover { color: var(--pink-600); }

    .cart-item .item-qty-controls {
        display: flex;
        align-items: center;
        gap: 2px;
        flex-shrink: 0;
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
        font-size: .6rem;
        cursor: pointer;
        transition: var(--pos-transition);
        color: var(--gray-600);
    }

    .cart-item .item-qty-controls .qty-btn:hover {
        background: var(--pink-50);
        border-color: var(--pink-300);
        color: var(--pink-600);
    }

    .cart-item .item-qty-controls .qty-btn:active {
        transform: scale(.88);
    }

    .cart-item .item-qty-controls .qty-value {
        width: 26px;
        text-align: center;
        font-weight: 700;
        font-size: .72rem;
        color: var(--gray-800);
    }

    .cart-item .item-total {
        font-size: .75rem;
        font-weight: 800;
        color: var(--pink-600);
        flex-shrink: 0;
        min-width: 46px;
        text-align: right;
    }

    .cart-item .item-extra {
        display: flex;
        align-items: center;
        gap: 2px;
        flex-shrink: 0;
    }
    .cart-item .item-extra button {
        width: 22px;
        height: 22px;
        border-radius: 4px;
        border: 1px solid transparent;
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .55rem;
        cursor: pointer;
        color: var(--gray-400);
        transition: var(--pos-transition);
    }
    .cart-item .item-extra button:hover {
        background: var(--gray-100);
        border-color: var(--gray-200);
        color: var(--gray-600);
    }
    .cart-item .item-extra button.has-discount {
        background: #fef3c7;
        color: #d97706;
        border-color: #fde68a;
    }

    .cart-item .item-discount-inline {
        display: none;
        padding: .2rem .45rem;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 6px;
        gap: 3px;
        align-items: center;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 5;
        justify-content: center;
        backdrop-filter: blur(2px);
        background: rgba(255,251,235,.95);
    }
    .cart-item .item-discount-inline.show { display: flex; }
    .cart-item .item-discount-inline input,
    .cart-item .item-discount-inline select {
        height: 22px;
        font-size: .58rem;
        border-radius: 4px;
        border: 1px solid #fde68a;
        outline: none;
    }
    .cart-item .item-discount-inline input { width: 44px; text-align: center; }
    .cart-item .item-discount-inline select { width: 36px; }
    .cart-item .item-discount-inline button {
        padding: 0 5px;
        height: 22px;
        font-size: .55rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .cart-item .staff-badge {
        font-size: .5rem;
        padding: 0 5px;
        height: 18px;
        border-radius: 4px;
        border: 1px solid var(--gray-200);
        background: transparent;
        color: var(--gray-400);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 2px;
        flex-shrink: 0;
    }
    .cart-item .staff-badge.active {
        background: #e0f2fe;
        border-color: #7dd3fc;
        color: #0369a1;
    }

    .cart-item .item-remove {
        position: absolute;
        top: -4px;
        right: -4px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid #fff;
        background: rgba(239,68,68,0.12);
        color: #ef4444;
        font-size: .5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0;
        transition: var(--pos-transition);
        z-index: 2;
    }

    .cart-item:hover .item-remove {
        opacity: 1;
    }

    .cart-item .item-remove:hover {
        background: #ef4444;
        color: #fff;
        transform: scale(1.15);
    }

    .cart-item .item-staff-popup {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 6;
        background: rgba(255,255,255,.96);
        border: 1px solid #bae6fd;
        border-radius: 8px;
        box-shadow: var(--pos-shadow-md);
        padding: .3rem;
        align-items: center;
        justify-content: center;
        gap: .3rem;
        backdrop-filter: blur(2px);
    }
    .cart-item .item-staff-popup.show { display: flex; }
    .cart-item .item-staff-popup select {
        height: 26px;
        font-size: .6rem;
        border-radius: 4px;
        border: 1px solid var(--gray-200);
        padding: 0 4px;
    }
    .cart-item .item-staff-popup .staff-popup-close {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: none;
        background: transparent;
        color: var(--gray-400);
        font-size: .55rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .cart-item .item-staff-popup .staff-popup-close:hover {
        background: var(--gray-100);
        color: #ef4444;
    }

    /* Cart Footer - always visible at bottom */
    .pos-cart-panel .cart-footer {
        border-top: 1px solid var(--gray-100);
        padding: .65rem .85rem .75rem;
        background: linear-gradient(0deg, #fafafa, #fff);
        flex-shrink: 0;
        box-shadow: 0 -4px 12px rgba(0,0,0,.03);
        position: sticky;
        bottom: 0;
        z-index: 5;
        will-change: transform;
    }

    .cart-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .3rem 0;
        font-size: .82rem;
    }

    .cart-summary-row.total {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--gray-800);
        padding: .55rem 0;
        border-top: 2px dashed var(--gray-200);
        margin-top: .2rem;
    }

    .cart-summary-row.discount-row {
        border-top: 1px solid var(--gray-100);
        margin-top: .1rem;
        padding-top: .3rem;
    }

    .discount-badge-btn {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .18rem .55rem;
        border-radius: 20px;
        background: #fffbeb;
        border: 1.5px solid #fde68a;
        font-size: .75rem;
        font-weight: 700;
        color: #d97706;
        cursor: pointer;
        transition: var(--pos-transition);
    }
    .discount-badge-btn:hover {
        background: #fef3c7;
        border-color: #f59e0b;
        box-shadow: 0 2px 8px rgba(245,158,11,.2);
    }
    .discount-badge-btn.no-discount {
        background: var(--gray-50);
        border-color: var(--gray-200);
        color: var(--gray-400);
        font-weight: 500;
    }
    .discount-badge-btn.no-discount:hover {
        background: var(--gray-100);
        border-color: var(--gray-300);
        box-shadow: none;
    }

    /* Reprint button */
    .btn-reprint {
        border: 1px solid var(--gray-200);
        background: #fff;
        color: #7c3aed;
        font-size: .72rem;
        padding: .2rem .5rem;
        border-radius: 7px;
        transition: var(--pos-transition);
        display: inline-flex;
        align-items: center;
        gap: .25rem;
    }
    .btn-reprint:hover {
        background: #f3e8ff;
        border-color: #a855f7;
        color: #6d28d9;
        transform: translateY(-1px);
    }

    .cart-summary-row.total .amount {
        color: var(--pink-600);
        font-size: 1.25rem;
    }

    .cart-customer-fields {
        display: flex;
        flex-direction: column;
        gap: .45rem;
        margin-bottom: .65rem;
    }

    .cart-customer-fields .row {
        margin: 0 -3px;
    }

    .cart-customer-fields .row > div {
        padding: 0 3px;
    }

    .cart-customer-fields input,
    .cart-customer-fields select {
        font-size: .78rem;
        border-radius: var(--pos-radius-sm);
        border: 1px solid var(--gray-200);
        height: 36px;
        transition: var(--pos-transition);
    }

    .cart-customer-fields input:focus,
    .cart-customer-fields select:focus {
        border-color: var(--pink-400);
        box-shadow: 0 0 0 3px rgba(219,39,119,0.1);
    }

    .btn-checkout {
        width: 100%;
        height: 54px;
        border-radius: var(--pos-radius-sm);
        font-size: 1.05rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        background: linear-gradient(135deg, var(--pink-600), #e11d6f);
        color: #fff;
        border: none;
        transition: var(--pos-transition);
        position: relative;
        overflow: hidden;
        letter-spacing: .01em;
    }

    .btn-checkout::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, transparent 30%, rgba(255,255,255,.08) 50%, transparent 70%);
        pointer-events: none;
    }

    .btn-checkout:hover {
        background: linear-gradient(135deg, var(--pink-700), #be185d);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(219,39,119,0.35);
    }

    .btn-checkout:not(:disabled) {
        animation: checkoutGlow 2s ease-in-out infinite;
    }
    @keyframes checkoutGlow {
        0%, 100% { box-shadow: 0 4px 15px rgba(219,39,119,0.25); }
        50% { box-shadow: 0 4px 28px rgba(219,39,119,0.45); }
    }

    .btn-checkout:disabled {
        opacity: .45;
        cursor: not-allowed;
        transform: none;
        box-shadow: none !important;
        animation: none;
    }

    .payment-methods {
        display: flex;
        gap: .4rem;
        margin-bottom: .65rem;
    }

    .payment-methods .pm-btn {
        flex: 1;
        padding: .45rem .3rem;
        border-radius: var(--pos-radius-sm);
        border: 2px solid var(--gray-200);
        background: #fff;
        font-size: .72rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--pos-transition);
        text-align: center;
        color: var(--gray-600);
    }

    .payment-methods .pm-btn:hover {
        border-color: var(--pink-300);
        background: var(--pink-50);
        transform: translateY(-1px);
    }

    .payment-methods .pm-btn.active {
        border-color: var(--pink-600);
        background: var(--pink-600);
        color: #fff;
        box-shadow: 0 3px 10px rgba(219,39,119,0.25);
    }

    .payment-methods .pm-btn i {
        display: block;
        font-size: 1.05rem;
        margin-bottom: 1px;
    }

    /* Recent Sales Bar */
    .recent-sales-bar {
        background: #fff;
        border-radius: var(--pos-radius-sm);
        box-shadow: var(--pos-shadow-sm);
        padding: .45rem .75rem;
        display: flex;
        align-items: center;
        gap: .65rem;
        overflow-x: auto;
        margin-top: .6rem;
        flex-shrink: 0;
        border: 1px solid rgba(0,0,0,.04);
    }
    .recent-sales-bar .rs-label {
        font-size: .68rem;
        font-weight: 600;
        color: var(--gray-500);
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: .3rem;
    }
    .recent-sales-bar .rs-items {
        display: flex;
        gap: .35rem;
        flex: 1;
        overflow-x: auto;
    }
    .recent-sales-bar .rs-item {
        padding: .3rem .55rem;
        border-radius: 6px;
        background: var(--gray-50);
        white-space: nowrap;
        font-size: .7rem;
        color: var(--gray-600);
        display: flex;
        align-items: center;
        gap: .35rem;
        flex-shrink: 0;
        position: relative;
        overflow: hidden;
        cursor: default;
        transition: var(--pos-transition);
        min-height: 34px;
    }
    .recent-sales-bar .rs-item:hover {
        background: var(--pink-50);
    }
    .recent-sales-bar .rs-item .rs-total {
        font-weight: 700;
        color: var(--pink-600);
        font-size: .72rem;
    }
    .sale-search-bar {
        display: flex;
        align-items: center;
        gap: .3rem;
        padding: .2rem .6rem;
        background: #fff;
        border-radius: 20px;
        flex-shrink: 0;
        border: 2px solid var(--pink-200);
        transition: var(--pos-transition);
        box-shadow: 0 2px 6px rgba(0,0,0,.05);
    }
    .sale-search-bar:focus-within {
        border-color: var(--pink-500);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(219,39,119,0.12);
    }
    .sale-search-bar input {
        border: none;
        background: #fff;
        font-size: .75rem;
        outline: none;
        width: 100px;
        color: var(--gray-700);
    }
    .sale-search-bar input::placeholder {
        color: var(--gray-500);
        font-weight: 500;
    }
    .rs-item-actions {
        display: none;
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,.95);
        padding: .2rem .35rem;
        gap: .2rem;
        justify-content: center;
        border-radius: 0 0 6px 6px;
    }
    .rs-item:hover .rs-item-actions {
        display: flex;
    }
    .rs-item-actions button {
        padding: .2rem .35rem;
        border-radius: 4px;
        border: none;
        font-size: .58rem;
        cursor: pointer;
        min-height: 28px;
        transition: all .1s;
        display: flex;
        align-items: center;
        gap: .15rem;
    }
    .rs-item-actions .rsa-view { background: #e0f2fe; color: #0369a1; }
    .rs-item-actions .rsa-print { background: #f3e8ff; color: #7c3aed; }
    .rs-item-actions .rsa-edit { background: #dbeafe; color: #2563eb; }
    .rs-item-actions .rsa-refund { background: #fef3c7; color: #b45309; }
    .rs-item-actions .rsa-delete { background: #fce7f3; color: #be123c; }
    .rs-item-actions button:hover { opacity: .8; transform: scale(1.05); }

    /* Sales Modal Table */
    .sm-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .78rem;
    }
    .sm-table th {
        text-align: right;
        padding: .5rem .6rem;
        background: #f8fafc;
        color: var(--gray-500);
        font-weight: 600;
        font-size: .7rem;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    .sm-table td {
        padding: .5rem .6rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .sm-table tbody tr:hover {
        background: #f8fafc;
    }
    .sm-table tbody tr:last-child td {
        border-bottom: none;
    }
    .sale-id-badge {
        background: var(--pink-100);
        color: var(--pink-600);
        padding: 2px 8px;
        border-radius: 20px;
        font-size: .68rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .sm-total {
        font-weight: 700;
        color: #16a34a;
        font-size: .82rem;
    }
    .sm-payment {
        font-size: .68rem;
        color: var(--gray-500);
        background: #f1f5f9;
        padding: 2px 8px;
        border-radius: 20px;
    }
    .sm-btn {
        width: 28px;
        height: 28px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: .65rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all .12s;
    }
    .sm-btn:hover { transform: scale(1.12); }
    .sm-view { background: #e0f2fe; color: #0369a1; }
    .sm-print { background: #f3e8ff; color: #7c3aed; }
    .sm-edit { background: #dbeafe; color: #2563eb; }
    .sm-refund { background: #fef3c7; color: #b45309; }
    .sm-delete { background: #fce7f3; color: #be123c; }

    /* Spinner */
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
        opacity: 0;
        z-index: 99999;
        background: #fff;
        border-radius: var(--pos-radius-sm);
        box-shadow: 0 10px 40px rgba(0,0,0,.15);
        padding: .7rem 1.15rem;
        display: flex;
        align-items: center;
        gap: .65rem;
        font-size: .85rem;
        font-weight: 600;
        transition: transform .35s cubic-bezier(0.4, 0, 0.2, 1), opacity .35s ease;
        border-right: 4px solid #22c55e;
    }

    .pos-toast.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }

    .pos-toast.error {
        border-right-color: #ef4444;
    }

    .pos-toast i {
        font-size: 1.1rem;
    }

    .pos-toast .toast-close {
        margin-right: auto;
        cursor: pointer;
        color: var(--gray-400);
        font-size: .75rem;
        padding: 3px;
        transition: var(--pos-transition);
        border-radius: 4px;
    }
    .pos-toast .toast-close:hover {
        background: var(--gray-100);
        color: var(--gray-600);
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

    /* Receipt preview inner styles */
    .receipt-preview-80mm {
        width: 80mm;
        background: #fff;
        padding: 10px 8px;
        font-family: 'Courier New', Courier, monospace;
        font-size: 10px;
        line-height: 1.4;
        box-shadow: 0 0 10px rgba(0,0,0,.1);
    }
    .receipt-preview-80mm .rp-header {
        text-align: center;
        border-bottom: 1px dashed #333;
        padding-bottom: 6px;
        margin-bottom: 6px;
    }
    .receipt-preview-80mm .rp-header h3 {
        font-size: 13px;
        font-weight: bold;
        margin: 0;
    }
    .receipt-preview-80mm .rp-header .rp-info {
        font-size: 9px;
        color: #555;
    }
    .receipt-preview-80mm .rp-divider {
        border-top: 1px dashed #333;
        margin: 6px 0;
    }
    .receipt-preview-80mm .rp-row {
        display: flex;
        justify-content: space-between;
        font-size: 10px;
        padding: 1px 0;
    }
    .receipt-preview-80mm .rp-row.total {
        font-size: 13px;
        font-weight: bold;
        border-top: 2px solid #333;
        padding-top: 4px;
        margin-top: 4px;
    }
    .receipt-preview-80mm .rp-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 2px 0;
        border-bottom: 1px dotted #ddd;
        font-size: 9px;
    }
    .receipt-preview-80mm .rp-item .rp-item-name {
        flex: 1;
        padding: 0 4px;
    }
    .receipt-preview-80mm .rp-item .rp-item-thumb {
        width: 24px;
        height: 24px;
        border-radius: 3px;
        object-fit: cover;
    }
    .receipt-preview-80mm .rp-footer {
        text-align: center;
        font-size: 9px;
        color: #888;
        border-top: 1px dashed #333;
        padding-top: 6px;
        margin-top: 6px;
    }
    .receipt-preview-80mm .rp-qr {
        text-align: center;
        margin: 4px 0;
    }
    .receipt-preview-80mm .rp-qr canvas {
        width: 50px !important;
        height: 50px !important;
    }

    /* Suspended cart item */
    .suspended-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        border-radius: var(--pos-radius-sm);
        background: var(--gray-50);
        margin-bottom: 8px;
        transition: var(--pos-transition);
        cursor: pointer;
        border: 1px solid var(--gray-200);
    }
    .suspended-item:hover {
        border-color: var(--pink-300);
        background: var(--pink-50);
        box-shadow: var(--pos-shadow-sm);
    }
    .suspended-item .si-info {
        flex: 1;
    }
    .suspended-item .si-info .si-name {
        font-weight: 600;
        font-size: .88rem;
    }
    .suspended-item .si-info .si-meta {
        font-size: .72rem;
        color: var(--gray-500);
    }
    .suspended-item .si-total {
        font-weight: 800;
        color: var(--pink-600);
        font-size: 1rem;
        margin-left: 12px;
    }
    .suspended-item .si-actions {
        display: flex;
        gap: 4px;
    }
    .suspended-item .si-actions button {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .7rem;
        cursor: pointer;
        transition: var(--pos-transition);
    }
    .suspended-item .si-actions button:active {
        transform: scale(.9);
    }
    .suspended-item .si-actions .si-restore {
        background: var(--pink-100);
        color: var(--pink-600);
    }
    .suspended-item .si-actions .si-restore:hover {
        background: var(--pink-600);
        color: #fff;
    }
    .suspended-item .si-actions .si-delete {
        background: #fef2f2;
        color: #ef4444;
    }
    .suspended-item .si-actions .si-delete:hover {
        background: #ef4444;
        color: #fff;
    }

    /* Favorites / Quick Access Bar */
    .pos-quick-bar {
        background: #fff;
        border-radius: var(--pos-radius-sm);
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
        padding: .25rem .5rem;
        display: flex;
        align-items: center;
        gap: .3rem;
        overflow-x: auto;
        margin-bottom: .3rem;
        min-height: 34px;
        border: 1px solid rgba(0,0,0,.04);
    }
    .pos-quick-bar .qb-label {
        font-size: .5rem;
        font-weight: 600;
        color: var(--gray-400);
        white-space: nowrap;
        padding-left: .3rem;
        border-left: 1px solid var(--gray-200);
        text-transform: uppercase;
        letter-spacing: .3px;
    }
    .pos-quick-bar .qb-items {
        display: flex;
        gap: .2rem;
        flex: 1;
    }
    .pos-quick-bar .qb-item {
        padding: .2rem .4rem;
        border-radius: 5px;
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        font-size: .62rem;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: all .12s ease;
        display: flex;
        align-items: center;
        gap: .2rem;
        flex-shrink: 0;
        min-height: 26px;
    }
    .pos-quick-bar .qb-item:hover {
        border-color: var(--pink-300);
        background: var(--pink-50);
        color: var(--pink-600);
        transform: translateY(-1px);
    }
    .pos-quick-bar .qb-item .qb-img {
        width: 16px;
        height: 16px;
        border-radius: 3px;
        object-fit: cover;
    }
    .pos-quick-bar .qb-item .qb-price {
        color: var(--pink-600);
        font-size: .55rem;
    }
    .pos-quick-bar .qb-toggle-btn {
        padding: .15rem .35rem;
        border-radius: 4px;
        border: 1px dashed var(--gray-300);
        background: transparent;
        font-size: .55rem;
        color: var(--gray-400);
        cursor: pointer;
        white-space: nowrap;
        flex-shrink: 0;
        transition: var(--pos-transition);
    }
    .pos-quick-bar .qb-toggle-btn:hover {
        border-color: var(--pink-300);
        color: var(--pink-500);
    }

    /* Customer search */
    .customer-search-wrapper {
        position: relative;
    }
    .customer-search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 100;
        background: #fff;
        border: 1px solid var(--gray-200);
        border-radius: 10px;
        box-shadow: 0 8px 30px rgba(0,0,0,.12);
        max-height: 200px;
        overflow-y: auto;
        display: none;
    }
    .customer-search-results.show {
        display: block;
    }
    .customer-search-results .csr-item {
        padding: .5rem .75rem;
        cursor: pointer;
        font-size: .8rem;
        display: flex;
        align-items: center;
        gap: .5rem;
        transition: all .1s;
    }
    .customer-search-results .csr-item:hover {
        background: var(--pink-50);
    }
    .customer-search-results .csr-item .csr-icon {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--gray-100);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .65rem;
        color: var(--gray-500);
        flex-shrink: 0;
    }
    .customer-search-results .csr-item .csr-info {
        flex: 1;
    }
    .customer-search-results .csr-item .csr-info .csr-name {
        font-weight: 600;
    }
    .customer-search-results .csr-item .csr-info .csr-meta {
        font-size: .65rem;
        color: var(--gray-400);
    }
    .customer-search-results .csr-create {
        padding: .5rem .75rem;
        border-top: 1px solid var(--gray-100);
        cursor: pointer;
        font-size: .78rem;
        color: var(--pink-600);
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .customer-search-results .csr-create:hover {
        background: var(--pink-50);
    }
    .customer-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        background: var(--pink-50);
        color: var(--pink-600);
        padding: .2rem .6rem;
        border-radius: 20px;
        font-size: .7rem;
        font-weight: 600;
        cursor: pointer;
    }
    .customer-badge i {
        font-size: .6rem;
    }

    /* Price override */
    .price-override-input {
        width: 65px;
        height: 26px;
        border: 1px solid var(--pink-300);
        border-radius: 6px;
        text-align: center;
        font-size: .75rem;
        font-weight: 700;
        color: var(--pink-600);
        background: var(--pink-50);
        outline: none;
    }
    .price-override-input:focus {
        border-color: var(--pink-500);
        box-shadow: 0 0 0 2px rgba(219,39,119,0.15);
    }

    /* Split payment */
    .split-payment-btn {
        padding: .35rem .6rem;
        border-radius: 6px;
        border: 1px dashed var(--gray-300);
        background: transparent;
        font-size: .7rem;
        color: var(--gray-500);
        cursor: pointer;
        transition: all .15s;
        display: flex;
        align-items: center;
        gap: .35rem;
    }
    .split-payment-btn:hover {
        border-color: var(--pink-300);
        color: var(--pink-500);
    }
    .split-payment-btn.active {
        border-style: solid;
        border-color: var(--pink-500);
        background: var(--pink-50);
        color: var(--pink-600);
    }

    /* Order notes */
    .order-notes-input {
        font-size: .75rem;
        border-radius: var(--pos-radius-sm);
        border: 1px solid var(--gray-200);
        resize: none;
        height: 31px;
        padding: .2rem .5rem;
        width: 100%;
        transition: var(--pos-transition);
    }
    .order-notes-input:focus {
        border-color: var(--pink-400);
        box-shadow: 0 0 0 3px rgba(219,39,119,0.1);
    }

    /* Quick create product modal */
    .quick-create-btn {
        padding: .25rem .5rem;
        border-radius: 6px;
        border: 1px dashed var(--gray-300);
        background: transparent;
        font-size: .65rem;
        color: var(--gray-400);
        cursor: pointer;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .quick-create-btn:hover {
        border-color: var(--green-400);
        color: var(--green-600);
    }

    /* Refund modal */
    .refund-item {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .5rem;
        border-radius: 8px;
        background: var(--gray-50);
        margin-bottom: .35rem;
    }
    .refund-item input[type="number"] {
        width: 60px;
        height: 30px;
        border-radius: 6px;
        border: 1px solid var(--gray-200);
        text-align: center;
        font-size: .8rem;
    }

    /* Favorite button on products */
    .fav-btn {
        position: absolute;
        top: 4px;
        left: 4px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: none;
        background: rgba(255,255,255,.85);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: .65rem;
        color: var(--gray-400);
        transition: all .15s;
        z-index: 2;
    }
    .fav-btn:hover { color: #f43f5e; transform: scale(1.1); }
    .fav-btn.active { color: #f43f5e; }

    /* Quick quantity modal */
    .qty-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.4);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(2px);
    }

    .qty-modal-overlay.show {
        display: flex;
    }

    .qty-modal {
        background: #fff;
        border-radius: var(--pos-radius-lg);
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
        line-height: 1;
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
        transition: var(--pos-transition);
        color: var(--gray-700);
    }

    .qty-modal .qty-modal-btns button:hover {
        border-color: var(--pink-300);
        color: var(--pink-600);
        background: var(--pink-50);
    }

    .qty-modal .qty-modal-btns button:active {
        transform: scale(.92);
    }

    .qty-modal .qty-confirm {
        width: 100%;
        height: 44px;
        border-radius: var(--pos-radius-sm);
        background: linear-gradient(135deg, var(--pink-600), #e11d6f);
        color: #fff;
        border: none;
        font-weight: 700;
        font-size: .95rem;
        cursor: pointer;
        transition: var(--pos-transition);
    }
    .qty-modal .qty-confirm:hover {
        background: linear-gradient(135deg, var(--pink-700), #be185d);
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(219,39,119,.3);
    }

    .qty-modal .qty-confirm:hover {
        background: linear-gradient(135deg, var(--pink-700), var(--pink-600));
    }

    /* ===== Sale Transaction Management Styles ===== */

    /* Sale detail modal */
    .sale-detail-header {
        background: linear-gradient(135deg, #db2777, #be185d, #9d174d);
        color: #fff;
        padding: 1rem 1.25rem;
        border-radius: .5rem .5rem 0 0;
        position: relative;
        overflow: hidden;
    }
    .sale-detail-header::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, transparent 30%, rgba(255,255,255,.06) 50%, transparent 70%);
        pointer-events: none;
    }
    .sale-detail-body {
        padding: 1.25rem;
    }
    .sale-detail-field {
        display: flex;
        justify-content: space-between;
        padding: .35rem 0;
        border-bottom: 1px solid var(--gray-100);
        font-size: .8rem;
    }
    .sale-detail-field .sdf-label {
        color: var(--gray-500);
        font-weight: 600;
    }
    .sale-detail-field .sdf-value {
        font-weight: 600;
        color: var(--gray-800);
    }
    .sale-detail-items {
        max-height: 250px;
        overflow-y: auto;
        margin-top: .75rem;
    }
    .sale-detail-item {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .5rem 0;
        border-bottom: 1px solid var(--gray-50);
        font-size: .78rem;
    }
    .sale-detail-item .sdi-name {
        flex: 1;
        font-weight: 600;
    }
    .sale-detail-item .sdi-qty {
        color: var(--gray-500);
        font-size: .7rem;
    }
    .sale-detail-item .sdi-price {
        font-weight: 700;
        color: var(--pink-600);
    }
    .sale-detail-actions {
        display: flex;
        gap: .5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--gray-200);
    }
    .sale-detail-actions button {
        flex: 1;
        padding: .55rem .45rem;
        border-radius: var(--pos-radius-sm);
        border: none;
        font-size: .75rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--pos-transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .3rem;
        min-height: 44px;
    }
    .sale-detail-actions button:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(0,0,0,.15);
    }
    .sale-detail-actions button:active {
        transform: scale(.97);
    }
    .sale-detail-actions .btn-edit-sale {
        background: #3b82f6;
        color: #fff;
    }
    .sale-detail-actions .btn-edit-sale:hover { background: #2563eb; }
    .sale-detail-actions .btn-refund-sale {
        background: #f59e0b;
        color: #fff;
    }
    .sale-detail-actions .btn-refund-sale:hover { background: #d97706; }
    .sale-detail-actions .btn-print-sale {
        background: #8b5cf6;
        color: #fff;
    }
    .sale-detail-actions .btn-print-sale:hover { background: #7c3aed; }
    .sale-detail-actions .btn-delete-sale {
        background: #ef4444;
        color: #fff;
    }
    .sale-detail-actions .btn-delete-sale:hover { background: #dc2626; }

    /* Delete confirmation */
    .delete-confirm-box {
        text-align: center;
        padding: 1.5rem;
    }
    .delete-confirm-box .dc-icon {
        font-size: 3rem;
        color: #ef4444;
        margin-bottom: 1rem;
    }
    .delete-confirm-box h5 {
        font-weight: 700;
        margin-bottom: .5rem;
    }
    .delete-confirm-box p {
        font-size: .85rem;
        color: var(--gray-500);
        margin-bottom: 1.25rem;
    }

    /* Status badges */
    .status-badge {
        display: inline-block;
        padding: .1rem .4rem;
        border-radius: 10px;
        font-size: .58rem;
        font-weight: 700;
    }
    .status-badge.completed { background: #dcfce7; color: #15803d; }
    .status-badge.cancelled { background: #fce7f3; color: #be123c; }
    .status-badge.refunded { background: #fef3c7; color: #b45309; }

    /* KS - Daily Stats Summary */
    .pos-daily-summary {
        display: flex;
        gap: .3rem;
        margin-bottom: .3rem;
        flex-wrap: wrap;
    }
    .pos-daily-summary .ds-item {
        flex: 1;
        min-width: 60px;
        background: #fff;
        border-radius: var(--pos-radius-sm);
        padding: .25rem .3rem .2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
        text-align: center;
        transition: all .15s ease;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,.04);
    }
    .pos-daily-summary .ds-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
    }
    .pos-daily-summary .ds-item.ds-today::before { background: linear-gradient(90deg, #db2777, #f43f5e); }
    .pos-daily-summary .ds-item.ds-revenue::before { background: linear-gradient(90deg, #16a34a, #22c55e); }
    .pos-daily-summary .ds-item.ds-orders::before { background: linear-gradient(90deg, #2563eb, #3b82f6); }
    .pos-daily-summary .ds-item.ds-revtotal::before { background: linear-gradient(90deg, #7c3aed, #a855f7); }
    .pos-daily-summary .ds-item.ds-action::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); opacity: 0; }
    .pos-daily-summary .ds-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0,0,0,.06);
    }
    .pos-daily-summary .ds-item .ds-icon {
        font-size: .65rem;
        margin-bottom: 0;
        opacity: .65;
    }
    .pos-daily-summary .ds-item.ds-today .ds-icon { color: #db2777; }
    .pos-daily-summary .ds-item.ds-revenue .ds-icon { color: #16a34a; }
    .pos-daily-summary .ds-item.ds-orders .ds-icon { color: #2563eb; }
    .pos-daily-summary .ds-item.ds-revtotal .ds-icon { color: #7c3aed; }
    .pos-daily-summary .ds-item .ds-value {
        font-size: 1rem;
        font-weight: 900;
        color: var(--pink-600);
        line-height: 1.1;
        letter-spacing: -.02em;
    }
    .pos-daily-summary .ds-item .ds-label {
        font-size: .48rem;
        color: var(--gray-400);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .3px;
        margin-top: 0;
        line-height: 1.1;
    }
    .pos-daily-summary .ds-item.ds-today .ds-value { color: #db2777; }
    .pos-daily-summary .ds-item.ds-revenue .ds-value { color: #16a34a; }
    .pos-daily-summary .ds-item.ds-orders .ds-value { color: #2563eb; }
    .pos-daily-summary .ds-item.ds-revtotal .ds-value { color: #7c3aed; }
    /* Action buttons row */
    .pos-daily-summary .ds-item.ds-action {
        cursor: pointer;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        flex: 0 0 auto;
        padding: .2rem .3rem;
        border: 1.5px dashed var(--gray-200);
        background: var(--gray-50);
    }
    .pos-daily-summary .ds-item.ds-action::before { opacity: 0; }
    .pos-daily-summary .ds-item.ds-action:hover {
        border-color: var(--pink-300);
        background: var(--pink-50);
        transform: translateY(-1px);
        box-shadow: var(--pos-shadow-sm);
    }
    .pos-daily-summary .ds-item.ds-action .ds-value {
        font-size: .85rem;
        color: var(--gray-500);
    }
    .pos-daily-summary .ds-item.ds-action .ds-label {
        color: var(--gray-400);
    }
    .pos-daily-summary .ds-item.ds-action.ds-discount { border-color: #f59e0b33; }
    .pos-daily-summary .ds-item.ds-action.ds-discount:hover { border-color: #f59e0b; background: #fffbeb; }
    .pos-daily-summary .ds-item.ds-action.ds-discount .ds-value { color: #d97706; }
    .pos-daily-summary .ds-item.ds-action.ds-reports .ds-value { color: #db2777; }

    /* Edit sale modal - cart item rows */
    .edit-cart-item {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .4rem .5rem;
        background: var(--gray-50);
        border-radius: 8px;
        margin-bottom: .35rem;
    }
    .edit-cart-item .eci-name {
        flex: 1;
        font-size: .75rem;
        font-weight: 600;
    }
    .edit-cart-item .eci-qty input {
        width: 50px;
        height: 26px;
        border-radius: 6px;
        border: 1px solid var(--gray-200);
        text-align: center;
        font-size: .75rem;
    }
    .edit-cart-item .eci-price input {
        width: 70px;
        height: 26px;
        border-radius: 6px;
        border: 1px solid var(--gray-200);
        text-align: center;
        font-size: .75rem;
    }
    .edit-cart-item .eci-total {
        font-size: .75rem;
        font-weight: 700;
        color: var(--pink-600);
        min-width: 55px;
        text-align: left;
    }
    .edit-cart-item .eci-remove {
        color: #ef4444;
        cursor: pointer;
        font-size: .7rem;
        padding: .2rem;
    }

    /* Refund enhanced */
    .refund-summary {
        background: var(--gray-50);
        border-radius: 8px;
        padding: .75rem;
        margin-top: .75rem;
    }
    .refund-summary .rs-row {
        display: flex;
        justify-content: space-between;
        font-size: .8rem;
        padding: .15rem 0;
    }
    .refund-summary .rs-row.rs-total {
        border-top: 2px solid var(--gray-200);
        margin-top: .35rem;
        padding-top: .35rem;
        font-weight: 700;
        font-size: .9rem;
    }

    /* Keyboard shortcuts bar - fixed at top, always visible */
    .pos-shortcuts-bar {
        display: flex;
        align-items: center;
        gap: .2rem;
        flex-wrap: wrap;
        padding: .15rem .35rem;
        background: linear-gradient(135deg, #1e1b2e, #2d1b36);
        border-radius: var(--pos-radius-sm);
        margin-bottom: .3rem;
        flex-shrink: 0;
        box-shadow: 0 1px 6px rgba(0,0,0,.1);
        border: 1px solid rgba(255,255,255,.06);
        direction: ltr;
        min-height: 28px;
    }
    .pos-shortcuts-bar .kb-item {
        display: inline-flex;
        align-items: center;
        gap: .2rem;
        padding: .15rem .35rem;
        border-radius: 4px;
        font-size: .62rem;
        font-weight: 700;
        border: 1.5px solid rgba(255,255,255,.06);
        cursor: pointer;
        transition: all .12s ease;
        user-select: none;
        background: rgba(255,255,255,.03);
        color: #e2e8f0;
        min-height: 22px;
    }
    .pos-shortcuts-bar .kb-item:hover {
        transform: translateY(-1px);
        background: rgba(255,255,255,.1);
        border-color: rgba(255,255,255,.12);
    }
    .pos-shortcuts-bar .kb-item:active {
        transform: scale(.93);
    }
    .pos-shortcuts-bar .kb-item kbd {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        font-size: .58rem;
        font-weight: 800;
        font-family: inherit;
        background: rgba(255,255,255,.1);
        border-radius: 3px;
        min-width: 20px;
        line-height: 1.3;
        color: #fbbf24;
        letter-spacing: .3px;
    }
    .pos-shortcuts-bar .kb-item .kb-label {
        color: #cbd5e1;
        font-size: .6rem;
    }
    .pos-shortcuts-bar .kb-item:hover .kb-label {
        color: #fff;
    }

    /* Floating shortcuts help button */
    .kb-help-btn {
        position: fixed;
        bottom: 1rem;
        right: 1rem;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--pink-600), #e11d6f);
        color: #fff;
        border: none;
        font-size: 1.1rem;
        font-weight: 800;
        cursor: pointer;
        z-index: 9999;
        box-shadow: 0 4px 20px rgba(219,39,119,.35);
        transition: var(--pos-transition);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .kb-help-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 30px rgba(219,39,119,.45);
    }
    .kb-help-btn:active { transform: scale(.9); }

    /* Keyboard shortcuts popup */
    .kb-popup {
        display: none;
        position: fixed;
        bottom: 5rem;
        right: 1rem;
        z-index: 9998;
        background: #fff;
        border-radius: var(--pos-radius-md);
        box-shadow: 0 20px 60px rgba(0,0,0,.2);
        padding: 1rem;
        width: 320px;
        max-width: 90vw;
        border: 1px solid var(--gray-100);
    }
    .kb-popup.show { display: block; }
    .kb-popup h6 {
        font-weight: 700;
        font-size: .85rem;
        margin-bottom: .65rem;
        color: var(--gray-800);
        display: flex;
        align-items: center;
        gap: .4rem;
    }
    .kb-popup .kbp-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .35rem;
    }
    .kb-popup .kbp-item {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .4rem .5rem;
        border-radius: 6px;
        background: var(--gray-50);
        font-size: .72rem;
        font-weight: 600;
        color: var(--gray-700);
    }
    .kb-popup .kbp-item kbd {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 2px 7px;
        font-size: .65rem;
        font-weight: 800;
        font-family: inherit;
        background: var(--gray-200);
        border-radius: 4px;
        min-width: 30px;
        min-height: 24px;
        color: var(--gray-800);
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
    }
    .kb-popup .kbp-close {
        position: absolute;
        top: 8px;
        left: 8px;
        background: none;
        border: none;
        color: var(--gray-400);
        cursor: pointer;
        font-size: .8rem;
        padding: 4px;
        border-radius: 4px;
    }
    .kb-popup .kbp-close:hover { background: var(--gray-100); }

    /* ===== Multi-Cart Tabs ===== */
    .cart-tabs {
        display: flex;
        gap: .25rem;
        padding: 0 1.1rem .35rem;
        border-bottom: 1px solid var(--gray-100);
        flex-shrink: 0;
        overflow-x: auto;
        background: linear-gradient(0deg, #fafafa, #fff);
    }
    .cart-tabs .ct-tab {
        display: flex;
        align-items: center;
        gap: .3rem;
        padding: .35rem .7rem;
        border-radius: 8px 8px 0 0;
        font-size: .7rem;
        font-weight: 600;
        color: var(--gray-500);
        cursor: pointer;
        border: 1px solid transparent;
        border-bottom: none;
        transition: var(--pos-transition);
        white-space: nowrap;
        min-height: 36px;
        position: relative;
    }
    .cart-tabs .ct-tab:hover {
        background: var(--gray-50);
        color: var(--gray-700);
    }
    .cart-tabs .ct-tab.active {
        background: #fff;
        border-color: var(--gray-200);
        color: var(--pink-600);
    }
    .cart-tabs .ct-tab.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--pink-600);
    }
    .cart-tabs .ct-tab .ct-close {
        font-size: .55rem;
        color: var(--gray-400);
        padding: 2px;
        border-radius: 50%;
        transition: var(--pos-transition);
        margin-right: 2px;
    }
    .cart-tabs .ct-tab .ct-close:hover {
        background: var(--gray-200);
        color: #ef4444;
    }
    .cart-tabs .ct-add {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 7px;
        border: 1.5px dashed var(--gray-300);
        background: transparent;
        color: var(--gray-400);
        font-size: .75rem;
        cursor: pointer;
        transition: var(--pos-transition);
        flex-shrink: 0;
        align-self: center;
    }
    .cart-tabs .ct-add:hover {
        border-color: var(--pink-300);
        color: var(--pink-500);
        background: var(--pink-50);
    }

    /* ===== Customer Search Above Cart ===== */
    .cart-customer-top {
        padding: .45rem .65rem;
        border-bottom: 1px solid var(--gray-100);
        flex-shrink: 0;
        background: #fff;
    }
    .cart-customer-top .cct-input {
        border-radius: var(--pos-radius-sm);
        border: 2px solid var(--gray-200);
        height: 40px;
        font-size: .8rem;
        transition: var(--pos-transition);
        padding-right: 2.2rem;
        width: 100%;
    }
    .cart-customer-top .cct-input:focus {
        border-color: var(--pink-400);
        box-shadow: 0 0 0 3px rgba(219,39,119,0.1);
    }
    .cart-customer-top .cct-icon {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        font-size: .75rem;
    }

    /* ===== Fast Cash Buttons ===== */
    /* ===== Professional Payment Modal ===== */
    .pay-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.55);
        z-index: 99999;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
        animation: payFadeIn .2s ease;
    }
    .pay-overlay.show { display: flex; }
    @keyframes payFadeIn { from { opacity:0; } to { opacity:1; } }
    @keyframes paySlideUp { from { opacity:0;transform:translateY(30px)scale(.96); } to { opacity:1;transform:translateY(0)scale(1); } }
    .pay-modal {
        background: #fff;
        border-radius: 20px;
        width: 420px;
        max-width: 95vw;
        box-shadow: 0 30px 100px rgba(0,0,0,.3);
        text-align: center;
        animation: paySlideUp .25s ease;
        overflow: hidden;
    }
    .pay-head {
        background: linear-gradient(135deg, var(--pink-700), #be185d);
        padding: 1rem 1.5rem .75rem;
        color: #fff;
    }
    .pay-head-icon { font-size: 1.6rem; margin-bottom: .15rem; }
    .pay-head-method { font-size: .85rem; font-weight: 500; opacity: .9; }
    .pay-total {
        font-size: 2.4rem;
        font-weight: 900;
        color: var(--pink-600);
        padding: .75rem .5rem .25rem;
        letter-spacing: -.02em;
    }
    .pay-divider { height: 1px; background: var(--gray-200); margin: 0 1.5rem; }
    #payBody { padding: .75rem 1.5rem; }
    .pay-cash-section { }
    .pay-cash-label { font-size: .78rem; color: var(--gray-500); font-weight: 600; margin-bottom: .5rem; display: flex; align-items: center; gap: .35rem; justify-content: center; }
    .pay-cash-input-wrap { position: relative; width: 180px; margin: 0 auto .6rem; }
    .pay-cash-input {
        width: 100%;
        height: 48px;
        text-align: center;
        font-size: 1.5rem;
        font-weight: 800;
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        padding: .5rem 1rem .5rem 1.8rem;
        transition: .15s;
        direction: ltr;
    }
    .pay-cash-input:focus { outline: none; border-color: var(--pink-400); box-shadow: 0 0 0 3px rgba(219,39,119,.1); }
    .pay-cash-currency { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 800; font-size: 1.1rem; color: var(--gray-400); }
    .pay-quick-btns {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: .35rem;
        margin-bottom: .6rem;
    }
    .pay-quick-btns button {
        height: 40px;
        border-radius: 8px;
        border: 1.5px solid var(--gray-200);
        background: #fff;
        font-size: .82rem;
        font-weight: 700;
        color: var(--gray-700);
        cursor: pointer;
        transition: .12s;
    }
    .pay-quick-btns button:hover { border-color: var(--pink-400); background: var(--pink-50); color: var(--pink-600); transform: translateY(-1px); }
    .pay-quick-btns button:active { transform: scale(.95); }
    .pay-change {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        padding: .55rem .75rem;
        border-radius: 10px;
        background: #f0fdf4;
        color: #16a34a;
        font-size: .85rem;
        font-weight: 600;
        margin-bottom: .25rem;
    }
    .pay-change strong { font-size: 1.3rem; font-weight: 900; }
    .pay-card-section, .pay-transfer-section { padding: .5rem 0; }
    .pay-card-anim { font-size: 2.5rem; color: var(--pink-500); margin-bottom: .25rem; }
    .pay-card-msg { font-size: .82rem; color: var(--gray-500); }
    .pay-summary {
        padding: .5rem 1.5rem .75rem;
        background: var(--gray-50);
        border-top: 1px solid var(--gray-100);
    }
    .pay-summary-row { display: flex; justify-content: space-between; font-size: .75rem; color: var(--gray-500); padding: 1px 0; }
    .pay-actions {
        display: flex;
        gap: .5rem;
        padding: .75rem 1.5rem 1.25rem;
    }
    .pay-actions button {
        flex: 1;
        height: 44px;
        border-radius: 12px;
        font-weight: 700;
        font-size: .85rem;
        cursor: pointer;
        transition: .15s;
        border: none;
    }
    .pay-btn-cancel { background: var(--gray-100); color: var(--gray-600); }
    .pay-btn-cancel:hover { background: var(--gray-200); }
    .pay-btn-confirm {
        background: linear-gradient(135deg, var(--pink-600), #e11d6f);
        color: #fff;
        box-shadow: 0 4px 15px rgba(219,39,119,.25);
    }
    .pay-btn-confirm:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(219,39,119,.35); }
    .pay-btn-confirm:disabled { opacity: .45; cursor: not-allowed; transform: none; box-shadow: none; }

    /* ===== Offline Mode Indicator ===== */
    .offline-indicator {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .2rem .55rem;
        border-radius: 20px;
        font-size: .65rem;
        font-weight: 700;
        transition: var(--pos-transition);
        flex-shrink: 0;
    }
    .offline-indicator.online {
        background: #f0fdf4;
        color: #15803d;
        border: 1.5px solid #bbf7d0;
    }
    .offline-indicator.offline {
        background: #fef2f2;
        color: #dc2626;
        border: 1.5px solid #fecaca;
        animation: pulseOffline 1.5s ease-in-out infinite;
    }
    @keyframes pulseOffline {
        0%, 100% { opacity: 1; }
        50% { opacity: .6; }
    }
    .offline-indicator .oi-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    .offline-indicator.online .oi-dot { background: #22c55e; box-shadow: 0 0 6px rgba(34,197,94,.4); }
    .offline-indicator.offline .oi-dot { background: #ef4444; box-shadow: 0 0 6px rgba(239,68,68,.4); }
</style>
@endpush

@section('content')
<div style="flex:1;display:flex;flex-direction:column;min-height:0;height:100%;">
    {{-- POS Daily Summary --}}
    <div class="pos-daily-summary" id="dailySummary">
        <div class="ds-item ds-today">
            <div class="ds-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="ds-value" id="dsTodaySales">{{ $todaySales }}</div>
            <div class="ds-label">مبيعات اليوم</div>
        </div>
        <div class="ds-item ds-revenue">
            <div class="ds-icon"><i class="fas fa-wallet"></i></div>
            <div class="ds-value" id="dsTodayRev">₪{{ number_format($todayRevenue, 2) }}</div>
            <div class="ds-label">ايرادات اليوم</div>
        </div>
        <div class="ds-item ds-orders">
            <div class="ds-icon"><i class="fas fa-file-invoice"></i></div>
            <div class="ds-value" id="dsTotalSales">{{ $totalSales }}</div>
            <div class="ds-label">اجمالي المبيعات</div>
        </div>
        <div class="ds-item ds-revtotal">
            <div class="ds-icon"><i class="fas fa-coins"></i></div>
            <div class="ds-value" id="dsTotalRev">₪{{ number_format($totalRevenue, 2) }}</div>
            <div class="ds-label">اجمالي الايرادات</div>
        </div>
        <a href="javascript:void(0)" class="ds-item ds-action ds-reports" onclick="openSalesModal()" title="عرض كل المبيعات">
            <div class="ds-value" style="font-size:1.1rem;"><i class="fas fa-file-invoice"></i></div>
            <div class="ds-label">التقارير</div>
        </a>
        <div class="offline-indicator online" id="offlineIndicator" title="حالة الاتصال">
            <span class="oi-dot"></span>
            <span id="offlineText">متصل</span>
        </div>
    </div>

    {{-- Quick Access Favorites Bar --}}
    <div class="pos-quick-bar" id="quickBar">
        <div class="qb-label"><i class="fas fa-star" style="color:#f59e0b;"></i> المفضلة</div>
        <div class="qb-items" id="quickBarItems">
            <span style="font-size:.7rem;color:var(--gray-400);">جاري التحميل...</span>
        </div>
    </div>



    {{-- POS Keyboard Shortcuts Bar - ثابت في الأعلى --}}
    <div class="pos-shortcuts-bar" id="shortcutsBar">
        <span class="kb-item" onclick="document.getElementById('productSearch').focus()"><kbd>F4</kbd> <span class="kb-label">بحث</span></span>
        <span class="kb-item" onclick="if(confirm('بدء عملية بيع جديدة؟'))clearCart()"><kbd>F2</kbd> <span class="kb-label">جديد</span></span>
        <span class="kb-item" onclick="productSearch.value='';document.querySelectorAll('.cpill').forEach(b=>b.classList.remove('active'));document.querySelector('.cpill[data-cat=\"\"]').classList.add('active');categoryFilter.value='';searchProducts()"><kbd>F5</kbd> <span class="kb-label">تحديث</span></span>
        <span class="kb-item" onclick="if(cart.length>0)onCheckoutClick()"><kbd>F6</kbd> <span class="kb-label">دفع</span></span>
        <span class="kb-item" onclick="suspendCart()"><kbd>F8</kbd> <span class="kb-label">تعليق</span></span>
        <span class="kb-item" onclick="reprintLastReceipt()"><kbd>F12</kbd> <span class="kb-label">طباعة</span></span>
        <span class="kb-item" onclick="if(cart.length>0&&confirm('تفريغ السلة؟'))clearCart()"><kbd>Esc</kbd> <span class="kb-label">تفريغ</span></span>
        <span class="kb-item" onclick="openSuspendedList()"><kbd>F9</kbd> <span class="kb-label">معلقة</span></span>
        <span class="kb-item" onclick="document.getElementById('barcodeScanner').focus()" style="margin-right:auto;"><kbd>⇧F1</kbd> <span class="kb-label">ماسح</span></span>
        <span class="kb-item" onclick="toggleKbPopup()" title="كل الاختصارات" style="background:rgba(255,255,255,.08);"><i class="fas fa-info-circle" style="font-size:.75rem;"></i></span>
    </div>

    {{-- Floating keyboard help button --}}
    <button class="kb-help-btn" id="kbHelpBtn" onclick="toggleKbPopup()" title="اختصارات لوحة المفاتيح">?</button>
    <div class="kb-popup" id="kbPopup">
        <button class="kbp-close" onclick="toggleKbPopup()"><i class="fas fa-times"></i></button>
        <h6><i class="fas fa-keyboard" style="color:var(--pink-600);"></i> اختصارات لوحة المفاتيح</h6>
        <div class="kbp-grid">
            <div class="kbp-item"><kbd>F4</kbd> بحث</div>
            <div class="kbp-item"><kbd>F2</kbd> فاتورة جديدة</div>
            <div class="kbp-item"><kbd>F5</kbd> تحديث</div>
            <div class="kbp-item"><kbd>F6</kbd> طريقة الدفع</div>
            <div class="kbp-item"><kbd>F8</kbd> تعليق السلة</div>
            <div class="kbp-item"><kbd>F9</kbd> طلبات معلقة</div>
            <div class="kbp-item"><kbd>F12</kbd> طباعة</div>
            <div class="kbp-item"><kbd>Esc</kbd> تفريغ السلة</div>
            <div class="kbp-item"><kbd>⇧F1</kbd> ماسح الباركود</div>
            <div class="kbp-item"><kbd>Ctrl+↵</kbd> إتمام البيع</div>
            <div class="kbp-item"><kbd>Ctrl+K</kbd> تركيز البحث</div>
            <div class="kbp-item"><kbd>/</kbd> تركيز البحث</div>
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
                    <div class="search-suggest" id="searchSuggest"></div>
                </div>
                <!-- Category filter pills -->
                <div class="category-pills" id="categoryPills">
                    <button class="cpill active" data-cat="" onclick="selectCategory(this)">الكل</button>
                    <button class="cpill cpill-best" data-cat="bestseller" onclick="selectCategory(this)" style="border-color:#f59e0b33;color:#d97706;">
                        <i class="fas fa-fire" style="font-size:.6rem;"></i> الأكثر مبيعاً
                    </button>
                    <button class="cpill cpill-offer" data-cat="offers" onclick="selectCategory(this)" style="border-color:#22c55e33;color:#16a34a;">
                        <i class="fas fa-tag" style="font-size:.6rem;"></i> العروض
                    </button>
                    @foreach($categories as $cat)
                        <button class="cpill" data-cat="{{ $cat->id }}" onclick="selectCategory(this)">{{ $cat->name_ar }}</button>
                    @endforeach
                </div>
                <input type="hidden" id="categoryFilter" value="">
                <button class="btn btn-outline-pink" onclick="quickCreateProduct()" title="منتج سريع (F2)" style="height:34px;padding:.2rem .55rem;border-radius:6px;display:flex;align-items:center;gap:.3rem;font-size:.72rem;font-weight:700;border-width:1.5px;">
                    <i class="fas fa-plus-circle" style="font-size:.82rem;"></i> <span class="d-none d-md-inline">سريع</span>
                </button>
                <button class="btn btn-outline-pink" onclick="document.getElementById('barcodeScanner').focus()" title="مسح باركود" style="height:34px;padding:.2rem .55rem;border-radius:6px;display:flex;align-items:center;gap:.3rem;font-size:.72rem;font-weight:700;border-width:1.5px;">
                    <i class="fas fa-barcode" style="font-size:.82rem;"></i> <span class="d-none d-md-inline">باركود</span>
                </button>
                <button class="btn btn-outline-secondary" onclick="toggleFullscreen()" id="fullscreenBtn" title="شاشة كاملة" style="height:34px;width:34px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:.85rem;border-width:1.5px;">
                    <i class="fas fa-expand"></i>
                </button>
            </div>
            <div class="receipt-size-pills" style="display:flex;gap:3px;padding:4px 0 0;flex-shrink:0;">
                <button class="rs-pill active" data-size="80mm" onclick="setReceiptSize(this)" title="فاتورة حرارية 80mm" style="padding:3px 8px;border-radius:4px;border:1px solid #e2e8f0;background:#fff;font-size:10px;cursor:pointer;font-weight:600;transition:.1s;color:#334155;">80mm</button>
                <button class="rs-pill" data-size="58mm" onclick="setReceiptSize(this)" title="فاتورة حرارية 58mm" style="padding:3px 8px;border-radius:4px;border:1px solid #e2e8f0;background:#fff;font-size:10px;cursor:pointer;font-weight:600;transition:.1s;color:#334155;">58mm</button>
                <button class="rs-pill" data-size="a4" onclick="setReceiptSize(this)" title="ورقة A4" style="padding:3px 8px;border-radius:4px;border:1px solid #e2e8f0;background:#fff;font-size:10px;cursor:pointer;font-weight:600;transition:.1s;color:#334155;">A4</button>
                <button class="rs-pill" data-size="a5" onclick="setReceiptSize(this)" title="ورقة A5" style="padding:3px 8px;border-radius:4px;border:1px solid #e2e8f0;background:#fff;font-size:10px;cursor:pointer;font-weight:600;transition:.1s;color:#334155;">A5</button>
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
                         data-discount="{{ $product->discount_percentage }}"
                         data-track-inventory="{{ $product->track_inventory ? '1' : '0' }}">
                        @if($product->discount_percentage > 0)
                            <span class="discount-badge">-{{ $product->discount_percentage }}%</span>
                        @endif
                        @if($product->main_image_url)
                            <img src="{{ $product->main_image_url }}" alt="" class="product-img" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        @else
                            <div class="product-img-placeholder"><i class="fas fa-box"></i></div>
                        @endif
                        <div class="product-name">{{ $product->name }}</div>
                        <div class="product-price">₪{{ number_format($product->b2c_price, 2) }}</div>
                        <div class="product-sku">{{ $product->sku }}</div>
                        @if($product->track_inventory)
                            <span class="stock-dot {{ $product->available_quantity <= 0 ? 'sd-out' : ($product->available_quantity <= 5 ? 'sd-low' : 'sd-ok') }}"></span>
                        @else
                            <span class="stock-dot sd-untracked"></span>
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
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-secondary" onclick="openSettings()" title="إعدادات الطباعة">
                        <i class="fas fa-cog"></i>
                    </button>
                    <button class="btn btn-sm btn-reprint" onclick="reprintLastReceipt()" id="reprintBtn" style="display:none;" title="طباعة الفاتورة السابقة">
                        <i class="fas fa-receipt"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-warning" onclick="suspendCart()" id="suspendBtn" style="display:none;" title="تعليق الطلب">
                        <i class="fas fa-pause"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="clearCart()" id="clearCartBtn" style="display:none;font-size:.72rem;border-radius:8px;padding:.2rem .55rem;height:32px;" title="تفريغ السلة بالكامل">
                        <i class="fas fa-trash-alt me-1"></i> تفريغ
                    </button>
                    <button class="btn btn-sm btn-outline-info" onclick="printCartReport()" id="printReportBtn" style="display:none;font-size:.72rem;border-radius:8px;padding:.2rem .55rem;height:32px;" title="طباعة تقرير السلة الحالية">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>

            {{-- Multi-Cart Tabs --}}
            <div class="cart-tabs" id="cartTabs">
                <div class="ct-tab active" data-tab="0" onclick="switchCartTab(0)">
                    <span>السلة 1</span>
                    <span class="ct-close" onclick="event.stopPropagation();closeCartTab(0)"><i class="fas fa-times"></i></span>
                </div>
                <div class="ct-add" onclick="addNewCartTab()" title="سلة جديدة">
                    <i class="fas fa-plus"></i>
                </div>
            </div>

            {{-- Customer Search Above Cart --}}
            <div class="cart-customer-top" id="cartCustomerTop" style="display:none;">
                <div style="position:relative;">
                    <i class="fas fa-user cct-icon"></i>
                    <input type="text" id="cartCustomerSearch" class="cct-input" placeholder="ابحث عن عميل بالاسم أو الهاتف..." autocomplete="off">
                    <div class="customer-search-results" id="cartCustomerResults"></div>
                </div>
                <div id="cartCustomerBadge" style="display:none;margin-top:.3rem;"></div>
                <input type="hidden" id="cartCustomerId" value="">
                <input type="hidden" id="cartCustomerPhone" value="">
                <input type="hidden" id="cartCustomerEmail" value="">
            </div>

            <div class="cart-body" id="cartBody">
                <div class="pos-cart-empty" id="cartEmpty">
                    <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--pink-50),var(--pink-100));display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                        <i class="fas fa-shopping-basket" style="font-size:2rem;color:var(--pink-300);"></i>
                    </div>
                    <p style="font-weight:600;color:var(--gray-500);margin-bottom:.25rem;">السلة فارغة</p>
                    <p style="font-size:.75rem;color:var(--gray-400);">اختر المنتجات من القائمة أو امسح باركود</p>
                </div>
                <div id="cartItems"></div>
            </div>

            <div class="cart-footer" id="cartFooter" style="display:none;">
                {{-- Hidden fields for backward compat with customer JS --}}
                <input type="hidden" id="customerName" value="">
                <input type="hidden" id="customerPhone" value="">
                <input type="hidden" id="customerEmail" value="">
                <div id="customerBadge" style="display:none;"></div>
                <div id="customerSearchResults" style="display:none;"></div>

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
            <div class="cart-summary-row discount-row" id="discountRow">
                <span style="display:flex;align-items:center;gap:.35rem;">
                    <i class="fas fa-tag" style="font-size:.65rem;color:#f59e0b;"></i>
                    الخصم
                </span>
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <span class="discount-badge-btn" id="discountBadgeBtn" onclick="openDiscountModal()" title="تعديل الخصم">
                        <span id="discountDisplayText">₪0.00</span>
                        <i class="fas fa-pen" style="font-size:.5rem;opacity:.6;"></i>
                    </span>
                </div>
                <input type="hidden" id="discountInput" value="0">
                <input type="hidden" id="discountType" value="fixed">
            </div>
            <div class="cart-summary-row tax-row" id="taxRow">
                <span style="display:flex;align-items:center;gap:.35rem;">
                    <i class="fas fa-percent" style="font-size:.65rem;color:#0d6efd;"></i>
                    الضريبة
                        <select id="taxRateSelect" onchange="renderCart()" style="border:none;background:transparent;font-size:.65rem;font-weight:600;color:var(--gray-500);cursor:pointer;width:48px;padding:0;">
                        <option value="0.15">15%</option>
                        <option value="0.09">9%</option>
                        <option value="0.05">5%</option>
                        <option value="0.00">0%</option>
                    </select>
                </span>
                <div style="display:flex;align-items:center;gap:.35rem;">
                    <span class="amount" id="taxDisplay" style="font-size:.78rem;">₪0.00</span>
                    <button onclick="toggleTax()" id="taxToggleBtn" style="border:none;background:transparent;padding:0;font-size:.85rem;cursor:pointer;line-height:1;" title="تشغيل/إيقاف الضريبة">
                        <i id="taxToggleIcon" class="fas fa-toggle-off" style="color:var(--gray-300);"></i>
                    </button>
                </div>
            </div>
            <div class="cart-summary-row total">
                <span>الإجمالي</span>
                <span class="amount" id="totalDisplay">₪0.00</span>
            </div>

                <div class="mb-1">
                    <textarea id="orderNotes" class="order-notes-input" placeholder="ملاحظات الطلب (اختياري)" rows="1"></textarea>
                </div>

                <div id="splitPaymentArea" style="display:none;margin-bottom:.5rem;">
                    <label style="font-size:.7rem;font-weight:600;color:var(--gray-500);margin-bottom:.25rem;display:block;">طرق الدفع المتعددة</label>
                    <div id="splitPaymentMethods"></div>
                    <div id="splitPaymentTotal" style="font-size:.7rem;color:var(--gray-400);margin-top:.25rem;"></div>
                </div>

                <div class="d-flex gap-2 mb-2">
                    <button class="split-payment-btn" id="splitPaymentToggle" onclick="toggleSplitPayment()">
                        <i class="fas fa-layer-group"></i> دفع مقسم
                    </button>
                    <button class="btn btn-outline-info flex-fill" onclick="openSuspendedList()" id="restoreBtn" style="font-size:.78rem;height:40px;border-radius:10px;padding:.2rem .7rem;font-weight:600;">
                        <i class="fas fa-history"></i> طلبات معلقة
                    </button>
                    <button class="btn btn-outline-warning flex-fill" onclick="suspendCart()" id="suspendFooterBtn" style="display:none;font-size:.78rem;height:40px;border-radius:10px;padding:.2rem .7rem;font-weight:600;">
                        <i class="fas fa-pause"></i> تعليق
                    </button>
                </div>

                <button class="btn-checkout" id="checkoutBtn" onclick="onCheckoutClick()">
                    <i class="fas fa-check-circle"></i> إتمام البيع
                </button>
            </div>
        </div>
    </div>

{{-- Recent Sales Bar --}}
<div class="recent-sales-bar">
    <div class="rs-label">
        <i class="fas fa-history" style="color:var(--pink-600);"></i> آخر المبيعات
        <span style="font-size:.6rem;color:var(--gray-400);font-weight:400;margin-right:4px;" id="rsCount"></span>
    </div>
    <div class="sale-search-bar">
        <i class="fas fa-search" style="font-size:.65rem;color:var(--gray-400);"></i>
        <input type="text" id="saleSearch" placeholder="بحث عن فاتورة..." oninput="searchSales()">
        <button class="btn btn-sm" style="padding:0;font-size:.7rem;color:var(--gray-400);" onclick="loadRecentSales()" title="تحديث"><i class="fas fa-sync-alt"></i></button>
        <button class="btn btn-sm" style="padding:2px 6px;font-size:.65rem;color:#db2777;font-weight:600;border:1px solid #f1c4d8;border-radius:6px;background:#fff;" onclick="openSalesModal()" title="عرض كل المبيعات"><i class="fas fa-list"></i> الكل</button>
    </div>
    <div class="rs-items" id="recentSalesList">
        @forelse($recentSales as $sale)
            <div class="rs-item" data-sale-id="{{ $sale->pos_sale_id }}" data-customer="{{ $sale->customer_name ?? '' }}" data-total="{{ $sale->order_total }}">
                <i class="fas fa-receipt" style="color:var(--gray-400);"></i>
                <span style="font-weight:600;font-size:.72rem;flex-shrink:0;" class="sale-id-text">{{ $sale->pos_sale_id }}</span>
                <span style="font-size:.65rem;color:var(--gray-500);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $sale->customer_name ?? 'بدون عميل' }}</span>
                <span class="rs-total">₪{{ number_format($sale->order_total, 2) }}</span>
                <span style="color:var(--gray-400);font-size:.6rem;">{{ $sale->created_at->format('H:i') }}</span>
                <div class="rs-item-actions">
                    <button class="rsa-view" onclick="openSaleDetail('{{ $sale->pos_sale_id }}')"><i class="fas fa-eye"></i> عرض</button>
                    <button class="rsa-print" onclick="reprintReceipt('{{ $sale->pos_sale_id }}')"><i class="fas fa-print"></i> طباعة</button>
                    <button class="rsa-edit" onclick="openEditSale('{{ $sale->pos_sale_id }}')"><i class="fas fa-edit"></i> تعديل</button>
                    <button class="rsa-refund" onclick="openRefund('{{ $sale->pos_sale_id }}')"><i class="fas fa-undo-alt"></i> إرجاع</button>
                    <button class="rsa-delete" onclick="openDeleteSale('{{ $sale->pos_sale_id }}')"><i class="fas fa-trash"></i> إلغاء</button>
                </div>
            </div>
        @empty
            <div class="rs-item" style="color:var(--gray-400);">
                <i class="fas fa-inbox"></i> لا توجد مبيعات بعد
            </div>
        @endforelse
        <script>document.getElementById('rsCount') && (document.getElementById('rsCount').textContent = '({{ $recentSales->count() }})');</script>
    </div>
</div>

{{-- Discount Modal --}}
<div class="modal fade" id="discountModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border:none;border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;border:none;">
                <h5 class="modal-title" style="color:#fff;font-size:.95rem;"><i class="fas fa-tag me-1"></i> خصم على الفاتورة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:1.25rem;">
                <div class="mb-3">
                    <label style="font-size:.8rem;font-weight:600;color:var(--gray-600);margin-bottom:.35rem;">قيمة الخصم</label>
                    <div style="display:flex;gap:.5rem;">
                        <input type="number" id="discountModalValue" class="form-control" value="0" step="0.01" min="0" style="flex:1;height:44px;font-size:.95rem;border-radius:10px;border:2px solid var(--gray-200);text-align:center;" onkeydown="if(event.key==='Enter')applyDiscountFromModal()">
                        <select id="discountModalType" class="form-select" style="width:80px;border-radius:10px;border:2px solid var(--gray-200);font-size:.85rem;">
                            <option value="fixed">₪</option>
                            <option value="percent">%</option>
                        </select>
                    </div>
                </div>
                <div style="display:flex;gap:.5rem;">
                    <button type="button" class="btn btn-secondary" style="flex:1;border-radius:10px;font-weight:600;font-size:.85rem;" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn" id="applyDiscountBtn" style="flex:1;border-radius:10px;font-weight:700;font-size:.85rem;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;border:none;" onclick="applyDiscountFromModal()">
                        <i class="fas fa-check"></i> تطبيق
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- All Sales Modal --}}
<div class="modal fade" id="salesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="border:none;border-radius:12px;overflow:hidden;">
            <div class="modal-header" style="background:#db2777;color:#fff;">
                <h5 class="modal-title" style="color:#fff;"><i class="fas fa-file-invoice me-1"></i> كل المبيعات <span id="salesModalCount" style="font-size:.75rem;color:rgba(255,255,255,.7);margin-right:4px;"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:1rem;">
                <div style="display:flex;gap:.5rem;margin-bottom:.75rem;">
                    <div style="flex:1;position:relative;">
                        <i class="fas fa-search" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:var(--gray-400);font-size:.8rem;"></i>
                        <input type="text" id="salesModalSearch" class="form-control" placeholder="بحث عن فاتورة..." style="padding-right:30px;font-size:.8rem;" oninput="filterSalesModal()">
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" onclick="loadSalesModal()" title="تحديث"><i class="fas fa-sync-alt"></i></button>
                </div>
                <div id="salesModalList" style="max-height:60vh;overflow-y:auto;">
                    <div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

{{-- Settings Modal (Unified Print Settings) --}}
<div class="modal fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-cog me-1"></i> إعدادات الطباعة الشاملة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height:65vh;overflow-y:auto;">

                {{-- === Receipt Content Section === --}}
                <div class="mb-3 p-2" style="background:#fef3f7;border-radius:8px;border-right:3px solid #db2777;">
                    <label class="form-label fw-bold" style="color:#db2777;font-size:.85rem;"><i class="fas fa-file-invoice me-1"></i> محتوى الفاتورة</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="form-check mb-1">
                                <input type="checkbox" class="form-check-input" id="showLogo" checked onchange="savePrintSettings()">
                                <label class="form-check-label" for="showLogo" style="font-size:.78rem;">شعار المتجر</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check mb-1">
                                <input type="checkbox" class="form-check-input" id="showQR" checked onchange="savePrintSettings()">
                                <label class="form-check-label" for="showQR" style="font-size:.78rem;">رمز QR</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check mb-1">
                                <input type="checkbox" class="form-check-input" id="showTaxNumber" checked onchange="savePrintSettings()">
                                <label class="form-check-label" for="showTaxNumber" style="font-size:.78rem;">الرقم الضريبي</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check mb-1">
                                <input type="checkbox" class="form-check-input" id="showContactInfo" checked onchange="savePrintSettings()">
                                <label class="form-check-label" for="showContactInfo" style="font-size:.78rem;">رقم الهاتف والعنوان</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check mb-1">
                                <input type="checkbox" class="form-check-input" id="showCustomerInfo" checked onchange="savePrintSettings()">
                                <label class="form-check-label" for="showCustomerInfo" style="font-size:.78rem;">معلومات العميل</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check mb-1">
                                <input type="checkbox" class="form-check-input" id="showProductImages" checked onchange="savePrintSettings()">
                                <label class="form-check-label" for="showProductImages" style="font-size:.78rem;">صور المنتجات</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- === Custom Text Section === --}}
                <div class="mb-3 p-2" style="background:#f0f9ff;border-radius:8px;border-right:3px solid #0ea5e9;">
                    <label class="form-label fw-bold" style="color:#0ea5e9;font-size:.85rem;"><i class="fas fa-pen me-1"></i> النصوص المخصصة</label>
                    <div class="mb-2">
                        <label style="font-size:.75rem;font-weight:600;color:#334155;">عنوان الفاتورة</label>
                        <input type="text" class="form-control form-control-sm" id="customHeaderTitle" placeholder="فاتورة مبيعات" onchange="savePrintSettings()" style="font-size:.8rem;">
                    </div>
                    <div class="mb-2">
                        <label style="font-size:.75rem;font-weight:600;color:#334155;">نص التذييل (السطر الأول)</label>
                        <input type="text" class="form-control form-control-sm" id="customFooterText" placeholder="شكراً لتعاملكم معنا" onchange="savePrintSettings()" style="font-size:.8rem;">
                    </div>
                    <div class="mb-1">
                        <label style="font-size:.75rem;font-weight:600;color:#334155;">نص تذييل تقرير السلة</label>
                        <input type="text" class="form-control form-control-sm" id="customCartFooter" placeholder="—— تقرير السلة ——" onchange="savePrintSettings()" style="font-size:.8rem;">
                    </div>
                </div>

                {{-- === Design Section === --}}
                <div class="mb-3 p-2" style="background:#f5f3ff;border-radius:8px;border-right:3px solid #7c3aed;">
                    <label class="form-label fw-bold" style="color:#7c3aed;font-size:.85rem;"><i class="fas fa-palette me-1"></i> التصميم</label>
                    <div class="row g-2">
                        <div class="col-4">
                            <label style="font-size:.75rem;font-weight:600;color:#334155;">اللون الأساسي</label>
                            <input type="color" class="form-control form-control-color form-control-sm" id="primaryColor" value="#db2777" onchange="savePrintSettings()" style="width:100%;height:32px;padding:2px;">
                        </div>
                        <div class="col-4">
                            <label style="font-size:.75rem;font-weight:600;color:#334155;">حجم الخط</label>
                            <select id="receiptFontSize" class="form-select form-select-sm" onchange="savePrintSettings()" style="font-size:.78rem;">
                                <option value="8">صغير (8px)</option>
                                <option value="10" selected>متوسط (10px)</option>
                                <option value="12">كبير (12px)</option>
                                <option value="14">كبير جداً (14px)</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label style="font-size:.75rem;font-weight:600;color:#334155;">هامش الطباعة</label>
                            <select id="paperMargin" class="form-select form-select-sm" onchange="savePrintSettings()" style="font-size:.78rem;">
                                <option value="2">صغير (2mm)</option>
                                <option value="5" selected>متوسط (5mm)</option>
                                <option value="10">كبير (10mm)</option>
                                <option value="15">كبير جداً (15mm)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- === Receipt Size & Auto Print === --}}
                <div class="mb-3 p-2" style="background:#fff7ed;border-radius:8px;border-right:3px solid #f97316;">
                    <label class="form-label fw-bold" style="color:#f97316;font-size:.85rem;"><i class="fas fa-print me-1"></i> الطباعة</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <label style="font-size:.75rem;font-weight:600;color:#334155;">حجم الفاتورة</label>
                            <select id="receiptSize" class="form-select form-select-sm" onchange="savePrintSettings()">
                                <option value="80mm">حراري 80mm</option>
                                <option value="58mm">حراري 58mm</option>
                                <option value="a4">A4</option>
                                <option value="a5">A5</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label style="font-size:.75rem;font-weight:600;color:#334155;">مهلة الطباعة التلقائية</label>
                            <select id="autoPrintDelay" class="form-select form-select-sm" onchange="savePrintSettings()" style="font-size:.78rem;">
                                <option value="0">فوري</option>
                                <option value="2">2 ثانية</option>
                                <option value="3" selected>3 ثوانٍ</option>
                                <option value="5">5 ثوانٍ</option>
                                <option value="10">10 ثوانٍ</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input type="checkbox" class="form-check-input" id="autoPrint" role="switch" onchange="savePrintSettings()">
                        <label class="form-check-label" for="autoPrint" style="font-size:.78rem;">طباعة الفاتورة تلقائياً بعد إتمام البيع</label>
                    </div>
                </div>

                {{-- === Cart Report Section === --}}
                <div class="mb-3 p-2" style="background:#f0fdf4;border-radius:8px;border-right:3px solid #22c55e;">
                    <label class="form-label fw-bold" style="color:#22c55e;font-size:.85rem;"><i class="fas fa-chart-bar me-1"></i> تقرير السلة</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="form-check mb-1">
                                <input type="checkbox" class="form-check-input" id="showCartReportHeader" checked onchange="savePrintSettings()">
                                <label class="form-check-label" for="showCartReportHeader" style="font-size:.78rem;">إظهار اسم الشركة والشعار</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check mb-1">
                                <input type="checkbox" class="form-check-input" id="showCartReportContact" checked onchange="savePrintSettings()">
                                <label class="form-check-label" for="showCartReportContact" style="font-size:.78rem;">إظهار الهاتف والعنوان</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- === Other Section === --}}
                <hr class="my-2">
                <div class="mb-3">
                    <div class="form-check form-switch mb-2">
                        <input type="checkbox" class="form-check-input" id="showRevenueStats" role="switch" checked onchange="savePrintSettings()">
                        <label class="form-check-label" for="showRevenueStats" style="font-size:.78rem;">عرض إحصائيات الإيرادات في الشريط العلوي</label>
                    </div>
                </div>
                <div class="mb-2">
                    <label style="font-size:.75rem;font-weight:600;color:#334155;">فتح الدرج (رابط HTTP)</label>
                    <input type="url" class="form-control form-control-sm" id="cashDrawerUrl" placeholder="http://192.168.1.100/open-drawer" onchange="savePrintSettings()" style="font-size:.8rem;">
                    <small class="text-muted" style="font-size:.6rem;">يُرسل طلب POST بعد كل بيع.</small>
                </div>

            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetPrintSettings()" style="font-size:.75rem;"><i class="fas fa-undo me-1"></i> استعادة الإعدادات الافتراضية</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

{{-- Print Preview Modal --}}
<div class="modal fade" id="printPreviewModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-receipt me-1"></i> معاينة الفاتورة</h5>
                <div style="font-size:.85rem;font-weight:700;color:#db2777;" id="printCountdown"></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="background:#f0f0f0;">
                <div id="receiptPreviewContainer" style="max-height:70vh;overflow-y:auto;display:flex;justify-content:center;padding:20px;">
                    <div id="receiptPreviewContent"></div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-success btn-lg px-4" onclick="printReceipt()" style="font-weight:800;border-radius:12px;">
                    <i class="fas fa-print me-2"></i> طباعة مباشرة
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Suspended Carts Modal --}}
<div class="modal fade" id="suspendedModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-history me-1"></i> الطلبات المعلقة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="suspendedListBody">
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                    <p>جاري التحميل...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

{{-- Refund Modal --}}
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#dc3545;color:#fff;">
                <h5 class="modal-title"><i class="fas fa-undo-alt me-1"></i> إرجاع مرتجع</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">فاتورة الإرجاع</label>
                    <input type="text" class="form-control" id="refundSaleId" readonly>
                </div>
                <div id="refundItemsContainer">
                    <div class="text-center py-3 text-muted" id="refundLoading">
                        <i class="fas fa-spinner fa-spin"></i> جاري تحميل المنتجات...
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-bold small">سبب الإرجاع (اختياري)</label>
                    <textarea id="refundReason" class="form-control" rows="2" placeholder="اذكر سبب الإرجاع..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-danger" id="confirmRefundBtn" onclick="confirmRefund()">
                    <i class="fas fa-check-circle"></i> تأكيد الإرجاع
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Quick Create Product Modal --}}
<div class="modal fade" id="quickProductModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#16a34a;color:#fff;">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-1"></i> إضافة منتج سريع</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">اسم المنتج <span class="text-danger">*</span></label>
                    <input type="text" id="qpName" class="form-control" placeholder="اسم المنتج بالعربية">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">السعر <span class="text-danger">*</span></label>
                    <input type="number" id="qpPrice" class="form-control" placeholder="0.00" step="0.01" min="0">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold small">SKU</label>
                        <input type="text" id="qpSku" class="form-control" placeholder="رمز المنتج">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small">باركود</label>
                        <input type="text" id="qpBarcode" class="form-control" placeholder="باركود (اختياري)">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">التصنيف</label>
                    <select id="qpCategory" class="form-select">
                        <option value="">بدون تصنيف</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn" style="background:#16a34a;color:#fff;" id="confirmQpBtn" onclick="confirmQuickProduct()">
                    <i class="fas fa-plus-circle"></i> إضافة
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Sale Detail Modal --}}
<div class="modal fade" id="saleDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border:none;border-radius:12px;overflow:hidden;">
            <div class="sale-detail-header">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <h5 class="modal-title" style="color:#fff;"><i class="fas fa-receipt me-1"></i> <span id="sddTitle">تفاصيل الفاتورة</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="sale-detail-body">
                <div id="sddContent">
                    <div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Sale Modal --}}
<div class="modal fade" id="editSaleModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border:none;border-radius:12px;overflow:hidden;">
            <div class="modal-header" style="background:#3b82f6;color:#fff;">
                <h5 class="modal-title" style="color:#fff;"><i class="fas fa-edit me-1"></i> تعديل الفاتورة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editSaleBody">
                <div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn" style="background:#3b82f6;color:#fff;" id="confirmEditBtn" onclick="confirmEditSale()">
                    <i class="fas fa-save"></i> حفظ التعديلات
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Sale Confirmation Modal --}}
<div class="modal fade" id="deleteSaleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border:none;border-radius:12px;">
            <div class="modal-body">
                <div class="delete-confirm-box">
                    <div class="dc-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <h5>إلغاء الفاتورة</h5>
                    <p>هل أنت متأكد من إلغاء هذه الفاتورة؟<br>سيتم إرجاع المنتجات إلى المخزون.</p>
                    <div class="mb-3">
                        <textarea id="deleteReason" class="form-control" rows="2" placeholder="سبب الإلغاء (اختياري)"></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary flex-fill" data-bs-dismiss="modal">رجوع</button>
                        <button type="button" class="btn btn-danger flex-fill" id="confirmDeleteBtn" onclick="doDeleteSale(document.getElementById('deleteSaleId').value,document.getElementById('deleteReason').value.trim())">
                            <i class="fas fa-trash"></i> تأكيد الإلغاء
                        </button>
                    </div>
                    <input type="hidden" id="deleteSaleId" value="">
                </div>
            </div>
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

{{-- Professional Payment Modal --}}
<div class="pay-overlay" id="payOverlay">
    <div class="pay-modal">
        <div class="pay-head">
            <div class="pay-head-icon" id="payHeadIcon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="pay-head-method" id="payHeadMethod">الدفع نقداً</div>
        </div>
        <div class="pay-total" id="payTotal">₪0.00</div>
        <div class="pay-divider"></div>
        <div id="payBody">
            <div class="pay-cash-section" id="payCashSection">
                <div class="pay-cash-label"><i class="fas fa-hand-holding-usd"></i> المبلغ الذي دفعه الزبون</div>
                <div class="pay-cash-input-wrap">
                    <input type="number" class="pay-cash-input" id="payCashInput" placeholder="0.00" step="5" min="0" oninput="payCashInputChanged()">
                    <span class="pay-cash-currency">₪</span>
                </div>
                <div class="pay-quick-btns" id="payQuickBtns">
                    <button onclick="payQuickAmount(20)">₪20</button>
                    <button onclick="payQuickAmount(50)">₪50</button>
                    <button onclick="payQuickAmount(100)">₪100</button>
                    <button onclick="payQuickAmount(200)">₪200</button>
                    <button onclick="payQuickAmount(50)" style="background:#fef3c7;">₪50 🟡</button>
                    <button onclick="payQuickAmount(100)" style="background:#fef3c7;">₪100 🟡</button>
                    <button onclick="payQuickAmount(200)" style="background:#fef3c7;">₪200 🟡</button>
                    <button onclick="payQuickAmount(500)">₪500</button>
                </div>
                <div class="pay-change" id="payChange" style="display:none;">
                    <i class="fas fa-arrow-left"></i>
                    <span>الباقي للزبون:</span>
                    <strong id="payChangeAmount">₪0.00</strong>
                </div>
            </div>
            <div class="pay-card-section" id="payCardSection" style="display:none;">
                <div class="pay-card-anim"><i class="fas fa-credit-card"></i></div>
                <div class="pay-card-msg">سيتم إتمام عملية البيع عبر جهاز الدفع</div>
                <div style="font-size:1.2rem;font-weight:700;margin-top:.5rem;" id="payCardTotal">₪0.00</div>
            </div>
            <div class="pay-transfer-section" id="payTransferSection" style="display:none;">
                <div class="pay-card-anim"><i class="fas fa-university"></i></div>
                <div class="pay-card-msg">تحويل بنكي — يُرجى تأكيد استلام التحويل</div>
                <div style="font-size:1.2rem;font-weight:700;margin-top:.5rem;" id="payTransferTotal">₪0.00</div>
            </div>
        </div>
        <div class="pay-summary" id="paySummary">
            <div class="pay-summary-row"><span>المجموع الفرعي</span><span id="paySubtotal">₪0.00</span></div>
            <div class="pay-summary-row" id="payDiscountRow"><span>الخصم</span><span id="payDiscount" style="color:#ef4444;">-₪0.00</span></div>
            <div class="pay-summary-row" id="payTaxRow"><span>الضريبة</span><span id="payTax">₪0.00</span></div>
        </div>
        <div class="pay-actions">
            <button class="pay-btn-cancel" onclick="closePayModal()"><i class="fas fa-times"></i> إلغاء</button>
            <button class="pay-btn-confirm" id="payConfirmBtn" onclick="payConfirm()">
                <i class="fas fa-check-circle"></i> <span id="payConfirmText">تأكيد الدفع</span>
            </button>
        </div>
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
    // Load QRCode library early for receipt preview
    if (typeof QRCode === 'undefined') {
        const qrScript = document.createElement('script');
        qrScript.src = 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js';
        document.head.appendChild(qrScript);
    }

    // ========== STATE ==========
    // Multi-cart support: carts array with current cart reference
    let carts = [[]];
    let currentCartIndex = 0;
    let cart = carts[0];
    let cartTabIdCounter = 1;
    let selectedPayment = 'cash';
    let pendingProductId = null;
    let pendingQty = 1;
    let searchTimeout = null;
    let lastSaleData = null;
    let taxEnabled = true;
    let taxRate = 0.15;
    window.posIdempotencyKey = null;
    let scrollPage = 1;
    let isLoadingMore = false;
    let hasMorePages = true;
    let isOnline = navigator.onLine;
    let offlineQueue = JSON.parse(localStorage.getItem('posOfflineQueue') || '[]');

    // DOM refs
    const productsGrid = document.getElementById('productsGrid');
    const productsLoading = document.getElementById('productsLoading');
    const cartItems = document.getElementById('cartItems');
    const cartEmpty = document.getElementById('cartEmpty');
    const cartFooter = document.getElementById('cartFooter');
    const cartCount = document.getElementById('cartCount');
    const subtotalDisplay = document.getElementById('subtotalDisplay');
    const taxDisplay = document.getElementById('taxDisplay');
    const taxRow = document.getElementById('taxRow');
    const taxToggleBtn = document.getElementById('taxToggleBtn');
    const taxToggleIcon = document.getElementById('taxToggleIcon');
    const taxRateSelect = document.getElementById('taxRateSelect');
    const totalDisplay = document.getElementById('totalDisplay');
    const clearCartBtn = document.getElementById('clearCartBtn');
    const productSearch = document.getElementById('productSearch');
    const categoryFilter = document.getElementById('categoryFilter');

    function selectCategory(btn) {
        document.querySelectorAll('.cpill').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        categoryFilter.value = btn.dataset.cat;
        searchProducts();
    }
    function selectCategoryByVal(catVal) {
        const target = document.querySelector(`#categoryPills .cpill[data-cat="${catVal}"]`);
        if (target) selectCategory(target);
    }
    const clearSearch = document.getElementById('clearSearch');
    const qtyModal = document.getElementById('qtyModal');
    const qtyDisplay = document.getElementById('qtyDisplay');
    const qtyModalTitle = document.getElementById('qtyModalTitle');
    const posToast = document.getElementById('posToast');
    const toastMessage = document.getElementById('toastMessage');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const barcodeInput = document.getElementById('barcodeScanner');
    const suspendBtn = document.getElementById('suspendBtn');
    const suspendFooterBtn = document.getElementById('suspendFooterBtn');
    const printReportBtn = document.getElementById('printReportBtn');
    const offlineIndicator = document.getElementById('offlineIndicator');
    const offlineText = document.getElementById('offlineText');

    // Toggle keyboard shortcuts help popup
    function toggleKbPopup() {
        const popup = document.getElementById('kbPopup');
        popup.classList.toggle('show');
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const popup = document.getElementById('kbPopup');
            if (popup.classList.contains('show')) {
                popup.classList.remove('show');
            }
        }
    });
    // Close popup on click outside
    document.addEventListener('click', function(e) {
        const popup = document.getElementById('kbPopup');
        const btn = document.getElementById('kbHelpBtn');
        if (popup.classList.contains('show') && !popup.contains(e.target) && !btn.contains(e.target)) {
            popup.classList.remove('show');
        }
    });

    // ========== MULTI-CART / TABS ==========
    function switchCartTab(index) {
        if (index === currentCartIndex) return;
        if (!carts[index]) return;
        cart = carts[index];
        currentCartIndex = index;
        renderCart();
        renderCartTabs();
        productSearch.focus();
    }

    function addNewCartTab() {
        const newCart = [];
        carts.push(newCart);
        cart = newCart;
        currentCartIndex = carts.length - 1;
        cartTabIdCounter++;
        renderCart();
        renderCartTabs();
        showToast('تم فتح سلة جديدة');
        productSearch.focus();
    }

    function closeCartTab(index) {
        if (carts.length <= 1) {
            showToast('لا يمكن إغلاق السلة الوحيدة', true);
            return;
        }
        if (carts[index].length > 0) {
            if (!confirm('السلة تحتوي على منتجات. هل تريد إغلاقها؟')) return;
        }
        carts.splice(index, 1);
        if (currentCartIndex >= carts.length) currentCartIndex = carts.length - 1;
        if (index <= currentCartIndex && currentCartIndex > 0) currentCartIndex--;
        cart = carts[currentCartIndex];
        renderCart();
        renderCartTabs();
    }

    function renderCartTabs() {
        const container = document.getElementById('cartTabs');
        if (!container) return;
        let html = '';
        carts.forEach((c, i) => {
            const count = c.length;
            html += `<div class="ct-tab ${i === currentCartIndex ? 'active' : ''}" data-tab="${i}" onclick="switchCartTab(${i})">
                <span>سلة ${i + 1}${count > 0 ? ` <span style="font-size:.55rem;color:var(--pink-500);font-weight:800;">(${count})</span>` : ''}</span>
                <span class="ct-close" onclick="event.stopPropagation();closeCartTab(${i})"><i class="fas fa-times"></i></span>
            </div>`;
        });
        html += `<div class="ct-add" onclick="addNewCartTab()" title="سلة جديدة"><i class="fas fa-plus"></i></div>`;
        container.innerHTML = html;
    }

    // ========== QUICK-PICK GRID (Top Products) ==========

    // ========== PROFESSIONAL PAYMENT MODAL ==========
    let payTotal = 0;
    let payGiven = 0;

    function openPayModal() {
        window.posIdempotencyKey = 'pos_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        const subtotal = cart.reduce((sum, i) => sum + i.price * i.quantity, 0);
        const discount = calculateDiscount(subtotal);
        const taxAmt = taxEnabled ? subtotal * (parseFloat(taxRateSelect.value) || 0) : 0;
        const itemDiscountTotal = cart.reduce((sum, i) => {
            const lineTotal = i.price * i.quantity;
            const disc = (i.itemDiscountType === 'percent' ? lineTotal * (i.itemDiscount / 100) : Math.min(i.itemDiscount || 0, lineTotal));
            return sum + disc;
        }, 0);
        payTotal = subtotal - discount - itemDiscountTotal + taxAmt;
        payGiven = 0;

        // Set head icon + label
        const icons = { cash: 'fa-money-bill-wave', card: 'fa-credit-card', transfer: 'fa-university' };
        const labels = { cash: 'الدفع نقداً', card: 'الدفع بالبطاقة', transfer: 'تحويل بنكي' };
        document.getElementById('payHeadIcon').innerHTML = `<i class="fas ${icons[selectedPayment] || 'fa-money-bill-wave'}"></i>`;
        document.getElementById('payHeadMethod').textContent = labels[selectedPayment] || 'الدفع';

        // Show/hide sections
        document.getElementById('payCashSection').style.display = selectedPayment === 'cash' ? 'block' : 'none';
        document.getElementById('payCardSection').style.display = selectedPayment === 'card' ? 'block' : 'none';
        document.getElementById('payTransferSection').style.display = selectedPayment === 'transfer' ? 'block' : 'none';

        // Update totals
        document.getElementById('payTotal').textContent = '₪' + payTotal.toFixed(2);
        document.getElementById('payCardTotal').textContent = '₪' + payTotal.toFixed(2);
        document.getElementById('payTransferTotal').textContent = '₪' + payTotal.toFixed(2);
        document.getElementById('paySubtotal').textContent = '₪' + subtotal.toFixed(2);
        const dEl = document.getElementById('payDiscountRow');
        if (discount > 0) { dEl.style.display = 'flex'; document.getElementById('payDiscount').textContent = '-₪' + discount.toFixed(2); }
        else dEl.style.display = 'none';
        const tEl = document.getElementById('payTaxRow');
        if (taxAmt > 0) { tEl.style.display = 'flex'; document.getElementById('payTax').textContent = '₪' + taxAmt.toFixed(2); }
        else tEl.style.display = 'none';

        // Reset cash input
        document.getElementById('payCashInput').value = '';
        document.getElementById('payChange').style.display = 'none';
        document.getElementById('payConfirmBtn').disabled = selectedPayment !== 'cash';

        if (selectedPayment !== 'cash') {
            document.getElementById('payConfirmText').textContent = 'تأكيد البيع';
            document.getElementById('payConfirmBtn').disabled = false;
        }

        document.getElementById('payOverlay').classList.add('show');
        if (selectedPayment === 'cash') setTimeout(() => document.getElementById('payCashInput').focus(), 200);
    }

    function closePayModal() {
        document.getElementById('payOverlay').classList.remove('show');
    }

    function payCashInputChanged() {
        const input = document.getElementById('payCashInput');
        const val = parseFloat(input.value) || 0;
        const change = val - payTotal;
        const changeEl = document.getElementById('payChange');
        const changeAmt = document.getElementById('payChangeAmount');
        const confirmBtn = document.getElementById('payConfirmBtn');
        if (val > 0) {
            changeEl.style.display = 'flex';
            if (change >= 0) {
                changeAmt.textContent = '₪' + change.toFixed(2);
                changeAmt.style.color = '#16a34a';
                confirmBtn.disabled = false;
                document.getElementById('payConfirmText').textContent = 'تأكيد الدفع';
            } else {
                changeAmt.textContent = '₪' + Math.abs(change).toFixed(2) + ' ناقص';
                changeAmt.style.color = '#ef4444';
                confirmBtn.disabled = true;
                document.getElementById('payConfirmText').textContent = 'المبلغ غير كافٍ';
            }
        } else {
            changeEl.style.display = 'none';
            confirmBtn.disabled = true;
        }
        payGiven = val;
    }

    function payQuickAmount(amount) {
        document.getElementById('payCashInput').value = amount;
        payCashInputChanged();
    }

    function payConfirm() {
        closePayModal();
        submitSale();
    }

    // ========== OFFLINE MODE ==========
    function updateOnlineStatus() {
        isOnline = navigator.onLine;
        if (isOnline) {
            offlineIndicator.className = 'offline-indicator online';
            offlineText.textContent = 'متصل';
            processOfflineQueue();
        } else {
            offlineIndicator.className = 'offline-indicator offline';
            offlineText.textContent = 'غير متصل';
            showToast('انقطع الاتصال - سيتم حفظ الفواتير محلياً', true);
        }
    }

    async function processOfflineQueue() {
        if (offlineQueue.length === 0) return;
        const queue = [...offlineQueue];
        offlineQueue = [];
        localStorage.setItem('posOfflineQueue', JSON.stringify(offlineQueue));
        for (const payload of queue) {
            try {
                const response = await fetch('{{ route('admin.pos.sale.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();
                if (data.success) showToast('تمت مزامنة فاتورة محلية: ' + data.data.pos_sale_id);
            } catch (e) {
                offlineQueue.push(payload);
                localStorage.setItem('posOfflineQueue', JSON.stringify(offlineQueue));
            }
        }
    }

    function saveOffline(payload) {
        offlineQueue.push(payload);
        localStorage.setItem('posOfflineQueue', JSON.stringify(offlineQueue));
        showToast('تم حفظ الفاتورة محلياً - ستتم المزامنة عند عودة الاتصال');
    }

    // ========== CUSTOMER SEARCH (Above Cart) ==========
    let cartCustomerSearchTimeout = null;
    let cartSelectedCustomerId = null;

    document.getElementById('cartCustomerSearch').addEventListener('input', function() {
        clearTimeout(cartCustomerSearchTimeout);
        const val = this.value.trim();
        if (val.length < 1) {
            document.getElementById('cartCustomerResults').classList.remove('show');
            return;
        }
        cartCustomerSearchTimeout = setTimeout(() => searchCartCustomers(val), 300);
    });

    document.getElementById('cartCustomerSearch').addEventListener('blur', function() {
        setTimeout(() => document.getElementById('cartCustomerResults').classList.remove('show'), 200);
    });

    document.getElementById('cartCustomerSearch').addEventListener('focus', function() {
        if (this.value.trim().length >= 1) {
            document.getElementById('cartCustomerResults').classList.add('show');
        }
    });

    async function searchCartCustomers(term) {
        try {
            const resp = await fetch('{{ route('admin.pos.searchCustomers') }}?q=' + encodeURIComponent(term), {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await resp.json();
            const container = document.getElementById('cartCustomerResults');
            if (!data.customers || data.customers.length === 0) {
                container.innerHTML = `<div class="csr-create" onclick="quickCreateCartCustomer()">
                    <i class="fas fa-plus-circle"></i> إنشاء عميل جديد "${escHtml(term)}"
                </div>`;
                container.classList.add('show');
                return;
            }
            let html = '';
            data.customers.forEach(c => {
                html += `<div class="csr-item" onclick="selectCartCustomer(${c.id},'${escJs(c.name)}','${escJs(c.phone||'')}','${escJs(c.email||'')}')">
                    <div class="csr-icon"><i class="fas fa-user"></i></div>
                    <div class="csr-info">
                        <div class="csr-name">${escHtml(c.name)}</div>
                        <div class="csr-meta">${escHtml(c.phone || '')} ${c.email ? '| ' + escHtml(c.email) : ''}</div>
                    </div>
                </div>`;
            });
            html += `<div class="csr-create" onclick="quickCreateCartCustomer()">
                <i class="fas fa-plus-circle"></i> إنشاء عميل جديد
            </div>`;
            container.innerHTML = html;
            container.classList.add('show');
        } catch (e) { console.error('Cart customer search error', e); }
    }

    function selectCartCustomer(id, name, phone, email) {
        cartSelectedCustomerId = id;
        document.getElementById('cartCustomerId').value = id;
        document.getElementById('cartCustomerSearch').value = name;
        document.getElementById('cartCustomerPhone').value = phone;
        document.getElementById('cartCustomerEmail').value = email;
        document.getElementById('cartCustomerResults').classList.remove('show');
        document.getElementById('cartCustomerBadge').innerHTML = `<span class="customer-badge" style="cursor:pointer;" onclick="clearCartCustomer()">
            <i class="fas fa-user"></i> ${escHtml(name)} <i class="fas fa-times" style="font-size:.55rem;"></i>
        </span>`;
        document.getElementById('cartCustomerBadge').style.display = 'block';
        // Also set old fields for backward compat
        document.getElementById('customerName').value = name;
        document.getElementById('customerPhone').value = phone;
        document.getElementById('customerEmail').value = email;
    }

    function clearCartCustomer() {
        cartSelectedCustomerId = null;
        document.getElementById('cartCustomerId').value = '';
        document.getElementById('cartCustomerSearch').value = '';
        document.getElementById('cartCustomerPhone').value = '';
        document.getElementById('cartCustomerEmail').value = '';
        document.getElementById('cartCustomerBadge').style.display = 'none';
        document.getElementById('cartCustomerBadge').innerHTML = '';
        document.getElementById('customerName').value = '';
        document.getElementById('customerPhone').value = '';
        document.getElementById('customerEmail').value = '';
    }

    function quickCreateCartCustomer() {
        const name = prompt('اسم العميل الجديد:');
        if (!name || name.trim() === '') return;
        const phone = prompt('رقم الهاتف (اختياري):', '');
        createQuickCustomer(name.trim(), phone || '');
    }

    // Override original createQuickCustomer to also update the cart customer fields
    const origCreateQuick = createQuickCustomer;
    createQuickCustomer = function(name, phone) {
        origCreateQuick(name, phone);
        // Also set cart customer fields
        if (selectedCustomerId) {
            document.getElementById('cartCustomerSearch').value = name;
            document.getElementById('cartCustomerPhone').value = phone;
            document.getElementById('cartCustomerId').value = selectedCustomerId;
        }
    };

    // Override selectCustomer to work with both old and new fields
    const origSelect = selectCustomer;
    selectCustomer = function(id, name, phone, email) {
        origSelect(id, name, phone, email);
        selectCartCustomer(id, name, phone, email);
    };

    // Override clearCustomer
    const origClear = clearCustomer;
    clearCustomer = function() {
        origClear();
        clearCartCustomer();
    };

    // Add to cart - direct add with qty=1 (no modal for speed)
    function addToCart(el) {
        const id = parseInt(el.dataset.id);
        const name = el.dataset.name;
        const price = parseFloat(el.dataset.price);
        const stock = parseInt(el.dataset.stock);
        const trackInventory = el.dataset.trackInventory === '1' || el.dataset.trackInventory === undefined;

        if (trackInventory && stock <= 0) {
            showToast('هذا المنتج غير متوفر حالياً', true);
            return;
        }

        const existing = cart.find(i => i.product_id === id);
        if (existing) {
            if (trackInventory && existing.quantity >= stock) {
                showToast('الكمية المطلوبة غير متوفرة', true);
                return;
            }
            existing.quantity++;
            renderCart();
            el.classList.add('added');
            showToast('تم زيادة الكمية');
            return;
        }

        // Direct add with quantity 1
        cart.push({
            product_id: id,
            name: name,
            price: price,
            quantity: 1,
            stock: stock,
            image: el.dataset.image || '',
            sku: el.dataset.sku || '',
            track_inventory: trackInventory,
            itemId: 'ci_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4),
            itemDiscount: 0,
            itemDiscountType: 'fixed',
            staffId: null,
        });

        el.classList.add('added');
        playScanBeep();
        renderCart();
        showToast('تمت إضافة ' + name);
    }

    function adjustQty(delta) {
        const stock = getPendingStock();
        const trackInventory = getPendingTrackInventory();
        let newQty = pendingQty + delta;
        if (newQty < 1) newQty = 1;
        if (trackInventory && newQty > stock) newQty = stock;
        pendingQty = newQty;
        qtyDisplay.textContent = pendingQty;
    }

    function setQty(val) {
        const stock = getPendingStock();
        const trackInventory = getPendingTrackInventory();
        if (trackInventory) {
            pendingQty = Math.min(val, stock);
        } else {
            pendingQty = Math.max(val, 1);
        }
        if (pendingQty < 1) pendingQty = 1;
        qtyDisplay.textContent = pendingQty;
    }

    function getPendingStock() {
        const card = document.querySelector(`.pos-product-card[data-id="${pendingProductId}"]`);
        return card ? parseInt(card.dataset.stock) : 999;
    }

    function getPendingTrackInventory() {
        const card = document.querySelector(`.pos-product-card[data-id="${pendingProductId}"]`);
        return card ? (card.dataset.trackInventory === '1' || card.dataset.trackInventory === undefined) : true;
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
            track_inventory: card.dataset.trackInventory === '1' || card.dataset.trackInventory === undefined,
            itemId: 'ci_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4),
            itemDiscount: 0,
            itemDiscountType: 'fixed',
            staffId: null,
        });

        qtyModal.classList.remove('show');
        card.classList.add('added');
        pendingProductId = null;
        renderCart();
        showToast('تمت إضافة المنتج');
    }

    // Open qty modal via right-click / long-press
    function openQtyModalFromCard(el) {
        const id = parseInt(el.dataset.id);
        const name = el.dataset.name;
        pendingProductId = id;
        pendingQty = 1;
        qtyModalTitle.textContent = name + ' - اختر الكمية';
        qtyDisplay.textContent = '1';
        qtyModal.classList.add('show');
    }

    // Quantity modal keyboard


    // Remove from cart (with animation)
    function removeFromCart(index) {
        const item = cart[index];
        const items = cartItems.querySelectorAll('.cart-item');
        if (items[index]) {
            items[index].classList.add('removing');
            setTimeout(() => {
                cart.splice(index, 1);
                const card = document.querySelector(`.pos-product-card[data-id="${item.product_id}"]`);
                if (card) card.classList.remove('added');
                renderCart();
            }, 200);
            return;
        }
        cart.splice(index, 1);
        const card = document.querySelector(`.pos-product-card[data-id="${item.product_id}"]`);
        if (card) card.classList.remove('added');
        renderCart();
    }

    // Change quantity in cart
    let lastEditedItemId = null;

    function changeQty(index, delta) {
        const item = cart[index];
        const newQty = item.quantity + delta;
        if (newQty < 1) {
            removeFromCart(index);
            return;
        }
        if (item.track_inventory !== false && newQty > item.stock) {
            showToast('الكمية المطلوبة غير متوفرة', true);
            return;
        }
        item.quantity = newQty;
        lastEditedItemId = item.itemId;
        debounceRenderCart();
    }

    let debounceCartTimer = null;
    function debounceRenderCart() {
        clearTimeout(debounceCartTimer);
        debounceCartTimer = setTimeout(() => {
            renderCart();
            if (lastEditedItemId) {
                highlightCartItem(lastEditedItemId);
                lastEditedItemId = null;
            }
        }, 10);
    }

    function highlightCartItem(itemId) {
        const el = document.querySelector(`#itemTotal-${CSS.escape(itemId)}`);
        if (el) {
            const item = el.closest('.cart-item');
            if (item) item.classList.add('edited');
        }
    }

    function setItemStaff(index, staffId) {
        if (cart[index]) {
            cart[index].staffId = staffId;
            renderCart();
            highlightCartItem(cart[index].itemId);
        }
    }

    function printCartReport() {
        if (cart.length === 0) {
            showToast('السلة فارغة، لا يوجد شيء للطباعة', true);
            return;
        }
        const settings = loadPrintSettings();
        const pc = settings.primaryColor || '#db2777';
        const siteName = '{{ $siteSettings["site_name"] ?? \App\Helpers\SettingsHelper::siteName() }}';
        const siteLogo = '{{ $siteSettings["site_logo_url"] ?? \App\Helpers\SettingsHelper::siteLogo() }}';
        const sitePhone = '{{ $siteSettings["site_phone"] ?? $siteSettings["contact_phone"] ?? "" }}';
        const siteAddress = '{{ $siteSettings["address"] ?? $siteSettings["site_address"] ?? "" }}';
        const cartFooter = settings.customCartFooter || '—— تقرير السلة ——';
        let rows = '';
        let total = 0;
        cart.forEach((item, i) => {
            const lineTotal = item.price * item.quantity;
            total += lineTotal;
            rows += `<tr><td style="padding:4px 6px;border-bottom:1px solid #eee;font-size:11px;">${i+1}</td>
            <td style="padding:4px 6px;border-bottom:1px solid #eee;font-size:11px;">${item.name}</td>
            <td style="padding:4px 6px;border-bottom:1px solid #eee;font-size:11px;text-align:center;">${item.quantity}</td>
            <td style="padding:4px 6px;border-bottom:1px solid #eee;font-size:11px;text-align:left;">₪${item.price.toFixed(2)}</td>
            <td style="padding:4px 6px;border-bottom:1px solid #eee;font-size:11px;text-align:left;font-weight:700;">₪${lineTotal.toFixed(2)}</td></tr>`;
        });
        const w = window.open('', '_blank', 'width=500,height=700');
        w.document.write(`<!DOCTYPE html><html dir="rtl"><head><meta charset="utf-8"><title>تقرير السلة - ${siteName}</title>
        <style>
        body{font-family:'Segoe UI',sans-serif;padding:15px;margin:0;color:#1e293b;}
        .report-header{text-align:center;border-bottom:2px solid ${pc};padding-bottom:10px;margin-bottom:12px;}
        .report-header .rl{max-width:60px;max-height:60px;margin-bottom:4px;}
        .report-header h2{color:${pc};font-size:17px;margin:4px 0 2px;}
        .report-header .ri{font-size:10px;color:#64748b;}
        .report-header .rc{font-size:10px;color:#64748b;margin-top:2px;}
        .date{text-align:center;color:#94a3b8;font-size:11px;margin-bottom:12px;}
        table{width:100%;border-collapse:collapse;}
        th{background:${pc};color:#fff;padding:6px 6px;font-size:11px;text-align:center;}
        th:first-child,th:nth-child(2){text-align:right;}
        td{font-size:11px;padding:4px 6px;border-bottom:1px solid #e2e8f0;}
        td:first-child,td:nth-child(2){text-align:right;}
        tr:nth-child(even){background:#f8fafc;}
        .total{text-align:left;font-size:15px;font-weight:800;margin-top:10px;padding-top:8px;border-top:2px solid ${pc};color:${pc};}
        .footer{text-align:center;color:#94a3b8;font-size:10px;margin-top:15px;border-top:1px solid #e2e8f0;padding-top:8px;}
        .print-btn{display:block;margin:15px auto 0;padding:10px 30px;background:${pc};color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;}
        .print-btn:hover{background:${pc}cc;}
        @media print{body{padding:8px;}.print-btn{display:none!important;}}
        </style></head><body>
        <div class="report-header">
            ${settings.showCartReportHeader && siteLogo ? `<img src="${siteLogo}" class="rl">` : ''}
            <h2>📋 ${settings.showCartReportHeader ? siteName : 'تقرير السلة'}</h2>
            ${settings.showCartReportContact && sitePhone ? `<div class="rc">📞 ${sitePhone}</div>` : ''}
            ${settings.showCartReportContact && siteAddress ? `<div class="rc">📍 ${siteAddress}</div>` : ''}
            <div class="ri">تقرير السلة</div>
        </div>
        <div class="date">${new Date().toLocaleDateString('ar-SA', {year:'numeric',month:'long',day:'numeric',hour:'2-digit',minute:'2-digit'})}</div>
        <table><thead><tr><th style="width:30px;">#</th><th>المنتج</th><th style="width:40px;">الكمية</th><th style="width:60px;">السعر</th><th style="width:60px;">المجموع</th></tr></thead>
        <tbody>${rows}</tbody></table>
        <div class="total">الإجمالي: ₪${total.toFixed(2)}</div>
        <div class="footer">${cartFooter}</div>
        <button class="print-btn" onclick="window.print()">🖨️ طباعة</button>
        </body></html>`);
        w.document.close();
    }

    function toggleStaffPopup(itemId) {
        const popup = document.getElementById('staffPopup-' + itemId);
        if (popup) {
            const isOpen = popup.classList.contains('show');
            document.querySelectorAll('.item-staff-popup.show').forEach(p => p.classList.remove('show'));
            if (!isOpen) popup.classList.add('show');
        }
    }

    function closeStaffPopup(itemId) {
        const popup = document.getElementById('staffPopup-' + itemId);
        if (popup) popup.classList.remove('show');
    }

    // Clear cart
    function clearCart() {
        const items = cartItems.querySelectorAll('.cart-item');
        items.forEach(el => el.classList.add('removing'));
        setTimeout(() => {
            document.querySelectorAll('.pos-product-card.added').forEach(el => el.classList.remove('added'));
            carts[currentCartIndex] = [];
            cart = carts[currentCartIndex];
            lastSaleData = null;
            splitPayments = [];
            document.getElementById('discountInput').value = '0';
            document.getElementById('discountType').value = 'fixed';
            document.getElementById('splitPaymentArea').style.display = 'none';
            document.getElementById('splitPaymentToggle').classList.remove('active');
            document.getElementById('cartCustomerTop').style.display = 'none';
            document.getElementById('cartCustomerSearch').value = '';
            document.getElementById('cartCustomerBadge').style.display = 'none';
            renderCart();
            renderCartTabs();
        }, items.length > 0 ? 250 : 0);
    }

    // Calculate discount
    function calculateDiscount(subtotal) {
        const val = parseFloat(document.getElementById('discountInput').value) || 0;
        const type = document.getElementById('discountType').value;
        if (val <= 0) return 0;
        if (type === 'percent') {
            return Math.min(subtotal * (val / 100), subtotal);
        }
        return Math.min(val, subtotal);
    }

    // Update discount display badge
    function updateDiscountDisplay() {
        const val = parseFloat(document.getElementById('discountInput').value) || 0;
        const type = document.getElementById('discountType').value;
        const badge = document.getElementById('discountBadgeBtn');
        const text = document.getElementById('discountDisplayText');
        if (val > 0) {
            badge.classList.remove('no-discount');
            text.textContent = type === 'percent' ? val + '%' : '₪' + val.toFixed(2);
        } else {
            badge.classList.add('no-discount');
            text.textContent = '₪0.00';
        }
    }

    function updateCartTotals() {
        renderCart();
    }

    // Tax toggle
    function toggleTax() {
        taxEnabled = !taxEnabled;
        renderCart();
    }

    function updateTaxUI() {
        if (taxEnabled) {
            taxRow.classList.remove('d-none');
            taxToggleIcon.className = 'fas fa-toggle-on';
            taxToggleIcon.style.color = '#0d6efd';
        } else {
            taxRow.classList.remove('d-none');
            taxToggleIcon.className = 'fas fa-toggle-off';
            taxToggleIcon.style.color = 'var(--gray-300)';
        }
    }

    // Revenue visibility
    function applyRevenueVisibility() {
        const s = loadPrintSettings();
        const summary = document.getElementById('dailySummary');
        if (!summary) return;
        const revItems = summary.querySelectorAll('.ds-revenue, .ds-revtotal');
        revItems.forEach(el => {
            el.style.display = s.showRevenueStats ? '' : 'none';
        });
    }

    // Render cart
    const STAFF_OPTIONS_HTML = '<option value="">بدون</option><option value="1">موظف 1</option><option value="2">موظف 2</option><option value="3">موظف 3</option>';

    function renderCart() {
        if (cart.length === 0) {
            cartEmpty.style.display = 'flex';
            cartItems.innerHTML = '';
            cartFooter.style.display = 'none';
            clearCartBtn.style.display = 'none';
            suspendBtn.style.display = 'none';
            suspendFooterBtn.style.display = 'none';
            printReportBtn.style.display = 'none';
            cartCount.textContent = '0';
            if (document.getElementById('splitPaymentArea').style.display !== 'none') toggleSplitPayment();
            return;
        }

        cartEmpty.style.display = 'none';
        cartFooter.style.display = 'block';
        clearCartBtn.style.display = 'inline-block';
        suspendBtn.style.display = 'inline-block';
        suspendFooterBtn.style.display = 'block';
        printReportBtn.style.display = 'inline-block';
        cartCount.textContent = cart.length;
        document.getElementById('cartCustomerTop').style.display = 'block';

        let html = '';
        let subtotal = 0;
        let totalQty = 0;
        let totalItemDiscount = 0;

        cart.forEach((item, index) => {
            if (!item.itemId) {
                item.itemId = 'ci_' + Date.now() + '_' + index + '_' + Math.random().toString(36).substr(2, 4);
            }
            if (item.itemDiscount === undefined) item.itemDiscount = 0;
            if (item.itemDiscountType === undefined) item.itemDiscountType = 'fixed';

            const lineTotal = item.price * item.quantity;
            const itemDiscount = item.itemDiscountType === 'percent'
                ? lineTotal * (item.itemDiscount / 100)
                : Math.min(item.itemDiscount, lineTotal);
            const discountedLine = lineTotal - itemDiscount;
            subtotal += lineTotal;
            totalQty += item.quantity;
            totalItemDiscount += itemDiscount;
            const overrideBadge = item.priceOverridden ? `<span style="font-size:.55rem;color:#f59e0b;margin-right:2px;" title="تم تعديل السعر: ${item.overrideNote || ''}"><i class="fas fa-pen"></i></span>` : '';
            const hasItemDiscount = item.itemDiscount > 0;

            // Staff assignment data
            const staffName = item.staffName || '';
            const staffOptions = item.staffId && item.staffId !== ''
                ? STAFF_OPTIONS_HTML.replace(`value="${item.staffId}"`, `value="${item.staffId}" selected`)
                : STAFF_OPTIONS_HTML;

            const hasStaff = item.staffId && item.staffId !== '';
            html += `
                <div class="cart-item">
                    <button class="item-remove" onclick="removeFromCart(${index})" title="إزالة المنتج">
                        <i class="fas fa-times"></i>
                    </button>
                    ${item.image
                        ? `<img src="${item.image}" alt="" class="item-img" loading="lazy">`
                        : `<div class="item-img-placeholder"><i class="fas fa-box"></i></div>`
                    }
                    <div class="item-info">
                        <div class="item-name">${item.name} ${overrideBadge}</div>
                        <div class="item-price" id="price-display-${item.itemId}" onclick="togglePriceOverride('${item.itemId}')" title="تعديل السعر">
                            ₪${item.price.toFixed(2)}
                            <span id="price-input-${item.itemId}" style="display:none;">
                                <input type="number" class="price-override-input" value="${item.price.toFixed(2)}" step="0.01" min="0" onkeydown="if(event.key==='Enter')confirmPriceOverride('${item.itemId}')">
                            </span>
                        </div>
                    </div>
                    <div class="item-qty-controls">
                        <button class="qty-btn" onclick="changeQty(${index}, -1)" title="إنقاص الكمية">–</button>
                        <span class="qty-value">${item.quantity}</span>
                        <button class="qty-btn" onclick="changeQty(${index}, 1)" title="زيادة الكمية">+</button>
                    </div>
                    <div class="item-extra">
                        <button class="staff-badge ${hasStaff ? 'active' : ''}" onclick="toggleStaffPopup('${item.itemId}')" title="${hasStaff ? 'تغيير الموظف' : 'تعيين موظف'}">
                            <i class="fas fa-user-tie"></i>
                        </button>
                        <button class="${hasItemDiscount ? 'has-discount' : ''}" onclick="toggleItemDiscount('${item.itemId}')" title="${hasItemDiscount ? 'خصم: ' + (item.itemDiscountType === 'percent' ? item.itemDiscount + '%' : '₪' + item.itemDiscount.toFixed(2)) : 'إضافة خصم'}">
                            <i class="fas fa-tag"></i>
                        </button>
                    </div>
                    <div class="item-total">
                        <span id="itemTotal-${item.itemId}">
                            ${hasItemDiscount ? `<span style="text-decoration:line-through;color:var(--gray-400);font-size:.6rem;">₪${lineTotal.toFixed(2)}</span> ` : ''}
                            <span style="${hasItemDiscount ? 'color:#dc2626;' : ''}">₪${Math.max(0, discountedLine).toFixed(2)}</span>
                        </span>
                    </div>
                    <div class="item-discount-inline ${hasItemDiscount ? 'show' : ''}" id="itemDiscountArea-${item.itemId}">
                        <span style="font-size:.55rem;font-weight:600;color:#d97706;">خصم:</span>
                        <input type="number" id="itemDiscountVal-${item.itemId}" value="${item.itemDiscount}" step="1" min="0">
                        <select id="itemDiscountType-${item.itemId}">
                            <option value="fixed" ${item.itemDiscountType === 'fixed' ? 'selected' : ''}>₪</option>
                            <option value="percent" ${item.itemDiscountType === 'percent' ? 'selected' : ''}>%</option>
                        </select>
                        <button style="background:#22c55e;color:#fff;" onclick="confirmItemDiscount('${item.itemId}')"><i class="fas fa-check"></i></button>
                        <button style="background:#ef4444;color:#fff;" onclick="clearItemDiscount('${item.itemId}')"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="item-staff-popup" id="staffPopup-${item.itemId}">
                        <select onchange="setItemStaff(${index}, this.value)">
                            ${staffOptions}
                        </select>
                        <button class="staff-popup-close" onclick="closeStaffPopup('${item.itemId}')"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            `;
        });

        cartItems.innerHTML = html;

        const discount = calculateDiscount(subtotal);
        taxRate = parseFloat(taxRateSelect.value) || 0;
        const taxAmount = taxEnabled ? subtotal * taxRate : 0;
        const total = subtotal - discount - totalItemDiscount + taxAmount;

        subtotalDisplay.textContent = '₪' + subtotal.toFixed(2);
        taxDisplay.textContent = '₪' + taxAmount.toFixed(2);
        totalDisplay.textContent = '₪' + total.toFixed(2);
        updateDiscountDisplay();
        updateTaxUI();
        renderCartTabs();
    }

    // Payment method selection
    function selectPayment(el) {
        document.querySelectorAll('.pm-btn').forEach(b => b.classList.remove('active'));
        el.classList.add('active');
        selectedPayment = el.dataset.method;
    }

    // Checkout click - open professional payment modal
    function onCheckoutClick() {
        if (cart.length === 0) {
            showToast('السلة فارغة', true);
            return;
        }
        openPayModal();
    }

    // Submit sale
    async function submitSale() {
        if (cart.length === 0) {
            showToast('السلة فارغة', true);
            return;
        }

        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '<div class="spinner-border spinner-border-sm" style="color:#fff;"></div> جاري المعالجة...';

        // Collect staff assignments from cart items
        const items = cart.map(i => {
            const staffId = i.staffId || null;
            return {
                product_id: i.product_id,
                quantity: i.quantity,
                price: i.price,
                item_discount: (i.itemDiscount || 0) > 0 ? i.itemDiscount : null,
                item_discount_type: (i.itemDiscount || 0) > 0 ? (i.itemDiscountType || 'fixed') : null,
                staff_id: staffId,
            };
        });

        const subtotal = cart.reduce((sum, i) => sum + i.price * i.quantity, 0);
        const discount = calculateDiscount(subtotal);
        const taxAmount = taxEnabled ? subtotal * (parseFloat(taxRateSelect.value) || 0) : 0;

        const payload = {
            items: items,
            payment_method: selectedPayment,
            customer_name: document.getElementById('customerName').value.trim() || null,
            customer_phone: document.getElementById('customerPhone').value.trim() || null,
            customer_email: document.getElementById('customerEmail').value.trim() || null,
            idempotency_key: window.posIdempotencyKey || null,
            discount: discount > 0 ? discount : null,
            discount_type: discount > 0 ? document.getElementById('discountType').value : null,
            tax_enabled: taxEnabled,
            tax_amount: taxAmount > 0 ? taxAmount : null,
            tax_rate: taxEnabled ? (parseFloat(taxRateSelect.value) || 0) : null,
            notes: document.getElementById('orderNotes').value.trim() || null,
            split_payments: splitPayments.length > 0 ? splitPayments : null,
        };

        // Offline mode: save locally if no connection
        if (!navigator.onLine) {
            saveOffline(payload);
            playSuccessBeep();
            showToast('تم حفظ الفاتورة محلياً (وضع غير متصل)');
            document.getElementById('reprintBtn').style.display = 'none';
            resetAfterSale();
            showConfetti();
            return;
        }

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
                window.posIdempotencyKey = null;
                playSuccessBeep();
                lastSaleData = {
                    posSaleId: data.data.pos_sale_id,
                    total: data.data.total,
                    subtotal: subtotal,
                    discount: discount,
                    tax_enabled: taxEnabled,
                    tax_amount: taxAmount,
                    tax_rate: parseFloat(taxRateSelect.value) || 0,
                    paymentMethod: selectedPayment,
                    customerName: document.getElementById('customerName').value.trim() || null,
                    customerPhone: document.getElementById('customerPhone').value.trim() || null,
                    customerEmail: document.getElementById('customerEmail').value.trim() || null,
                    items: cart,
                };
                document.getElementById('reprintBtn').style.display = 'inline-block';
                triggerCashDrawer();

                showToast('تم تسجيل البيع بنجاح! رقم المرجع: ' + data.data.pos_sale_id);
                showConfetti();
                resetAfterSale();

                setTimeout(() => buildReceiptPreview(lastSaleData, true), 500);
            } else {
                showToast(data.message || 'حدث خطأ أثناء تسجيل البيع', true);
            }
        } catch (err) {
            if (!navigator.onLine) {
                saveOffline(payload);
                playSuccessBeep();
                showToast('تم حفظ الفاتورة محلياً (انقطع الاتصال)');
                document.getElementById('reprintBtn').style.display = 'none';
                resetAfterSale();
                showConfetti();
            } else {
                showToast('فشل الاتصال بالخادم', true);
            }
        } finally {
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = '<i class="fas fa-check-circle"></i> إتمام البيع';
        }
    }

    function resetAfterSale() {
        clearCart();
        document.getElementById('customerName').value = '';
        document.getElementById('customerPhone').value = '';
        document.getElementById('customerEmail').value = '';
        document.getElementById('cartCustomerSearch').value = '';
        document.getElementById('cartCustomerBadge').style.display = 'none';
        document.getElementById('orderNotes').value = '';
        document.getElementById('customerBadge').style.display = 'none';
        selectedCustomerId = null;
        splitPayments = [];
        document.getElementById('splitPaymentArea').style.display = 'none';
        document.getElementById('splitPaymentToggle').classList.remove('active');
        loadRecentSales();
        updateStats();
    }

    // Show print prompt after sale
    function showPrintPrompt(saleId) {
        if (confirm('تم البيع بنجاح (رقم: ' + saleId + '). هل تريد طباعة الفاتورة الآن؟')) {
            if (lastSaleData) buildReceiptPreview(lastSaleData);
        }
    }

    // Reprint last receipt from header button
    function reprintLastReceipt() {
        if (lastSaleData) {
            buildReceiptPreview(lastSaleData);
        } else {
            showToast('لا توجد فاتورة سابقة للطباعة', true);
        }
    }

    // Cash drawer trigger
    function triggerCashDrawer() {
        const drawerUrl = localStorage.getItem('posCashDrawerUrl') || '';
        if (!drawerUrl) return;
        try {
            fetch(drawerUrl, { method: 'POST', mode: 'no-cors', signal: AbortSignal.timeout(3000) })
                .catch(() => {}); // ignore CORS/network errors silently
        } catch (e) { /* silent */ }
    }

    // ── Unified Print Settings ──
    function loadPrintSettings() {
        const defaults = {
            showLogo: true, showQR: true, showTaxNumber: true, showProductImages: true,
            showCustomerInfo: true, showContactInfo: true,
            receiptSize: '80mm', autoPrint: false, autoPrintDelay: '3',
            customHeaderTitle: 'فاتورة مبيعات', customFooterText: 'شكراً لتعاملكم معنا',
            customCartFooter: '—— تقرير السلة ——',
            primaryColor: '#db2777', receiptFontSize: '10', paperMargin: '5',
            showCartReportHeader: true, showCartReportContact: true,
            showRevenueStats: true,
        };
        try {
            const saved = JSON.parse(localStorage.getItem('posPrintSettings') || '{}');
            return { ...defaults, ...saved };
        } catch { return defaults; }
    }

    function savePrintSettings() {
        const ids = ['showLogo','showQR','showTaxNumber','showProductImages','showCustomerInfo','showContactInfo',
            'showRevenueStats','autoPrint','showCartReportHeader','showCartReportContact'];
        const settings = {};
        ids.forEach(id => settings[id] = document.getElementById(id).checked);
        settings.receiptSize = document.getElementById('receiptSize').value;
        settings.autoPrintDelay = document.getElementById('autoPrintDelay').value;
        settings.customHeaderTitle = document.getElementById('customHeaderTitle').value.trim();
        settings.customFooterText = document.getElementById('customFooterText').value.trim();
        settings.customCartFooter = document.getElementById('customCartFooter').value.trim();
        settings.primaryColor = document.getElementById('primaryColor').value;
        settings.receiptFontSize = document.getElementById('receiptFontSize').value;
        settings.paperMargin = document.getElementById('paperMargin').value;
        localStorage.setItem('posPrintSettings', JSON.stringify(settings));
        localStorage.setItem('posCashDrawerUrl', document.getElementById('cashDrawerUrl').value.trim());
        applyRevenueVisibility();
        syncReceiptSizePills(settings.receiptSize);
    }

    function openSettings() {
        const s = loadPrintSettings();
        ['showLogo','showQR','showTaxNumber','showProductImages','showCustomerInfo','showContactInfo',
            'showRevenueStats','autoPrint','showCartReportHeader','showCartReportContact']
            .forEach(id => document.getElementById(id).checked = s[id]);
        document.getElementById('receiptSize').value = s.receiptSize;
        document.getElementById('autoPrintDelay').value = s.autoPrintDelay;
        document.getElementById('customHeaderTitle').value = s.customHeaderTitle;
        document.getElementById('customFooterText').value = s.customFooterText;
        document.getElementById('customCartFooter').value = s.customCartFooter;
        document.getElementById('primaryColor').value = s.primaryColor;
        document.getElementById('receiptFontSize').value = s.receiptFontSize;
        document.getElementById('paperMargin').value = s.paperMargin;
        document.getElementById('cashDrawerUrl').value = localStorage.getItem('posCashDrawerUrl') || '';
        new bootstrap.Modal(document.getElementById('settingsModal')).show();
    }

    function resetPrintSettings() {
        localStorage.removeItem('posPrintSettings');
        showToast('تم استعادة الإعدادات الافتراضية');
        openSettings();
    }

    // ── Fullscreen toggle ──
    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(() => {});
            document.getElementById('fullscreenBtn').innerHTML = '<i class="fas fa-compress"></i>';
            document.getElementById('fullscreenBtn').title = 'إنهاء الشاشة الكاملة';
        } else {
            document.exitFullscreen();
            document.getElementById('fullscreenBtn').innerHTML = '<i class="fas fa-expand"></i>';
            document.getElementById('fullscreenBtn').title = 'شاشة كاملة';
        }
    }
    document.addEventListener('fullscreenchange', () => {
        const btn = document.getElementById('fullscreenBtn');
        if (!btn) return;
        if (document.fullscreenElement) {
            btn.innerHTML = '<i class="fas fa-compress"></i>';
            btn.title = 'إنهاء الشاشة الكاملة';
        } else {
            btn.innerHTML = '<i class="fas fa-expand"></i>';
            btn.title = 'شاشة كاملة';
        }
    });

    // ── Quick receipt size selector ──
    function syncReceiptSizePills(size) {
        document.querySelectorAll('.rs-pill').forEach(p => {
            const isActive = p.dataset.size === size;
            p.style.cssText = isActive
                ? 'padding:3px 8px;border-radius:4px;border:2px solid #db2777;background:#fdf2f8;font-size:10px;cursor:pointer;font-weight:700;transition:.1s;color:#db2777;'
                : 'padding:3px 8px;border-radius:4px;border:1px solid #e2e8f0;background:#fff;font-size:10px;cursor:pointer;font-weight:600;transition:.1s;color:#334155;';
        });
    }
    function setReceiptSize(el) {
        const size = el.dataset.size;
        syncReceiptSizePills(size);
        const select = document.getElementById('receiptSize');
        if (select) { select.value = size; }
        const s = loadPrintSettings();
        s.receiptSize = size;
        localStorage.setItem('posPrintSettings', JSON.stringify(s));
    }

    // Build receipt preview HTML
    function buildReceiptPreview(sale, autoPrintAfter) {
        const settings = loadPrintSettings();
        const pc = settings.primaryColor || '#db2777';
        const fs = parseInt(settings.receiptFontSize) || 10;
        const siteName = '{{ $siteSettings["site_name"] ?? \App\Helpers\SettingsHelper::siteName() }}';
        const siteLogo = '{{ $siteSettings["site_logo_url"] ?? \App\Helpers\SettingsHelper::siteLogo() }}';
        const sitePhone = '{{ $siteSettings["site_phone"] ?? $siteSettings["contact_phone"] ?? "" }}';
        const siteAddress = '{{ $siteSettings["address"] ?? $siteSettings["site_address"] ?? "" }}';
        const headerTitle = settings.customHeaderTitle || 'فاتورة مبيعات';
        const footerText = settings.customFooterText || 'شكراً لتعاملكم معنا';
        const taxRate = sale.tax_rate || 0.15;
        const taxEnabled = sale.tax_enabled !== undefined ? sale.tax_enabled : true;
        const taxAmount = taxEnabled ? sale.subtotal * taxRate : 0;
        const itemDiscountTotal = (sale.items || []).reduce((sum, i) => {
            const lineTotal = i.price * i.quantity;
            return sum + (i.itemDiscountType === 'percent' ? lineTotal * (i.itemDiscount / 100) : Math.min(i.itemDiscount || 0, lineTotal));
        }, 0);
        const finalTotal = sale.subtotal - sale.discount - itemDiscountTotal;

        let html = `<div class="receipt-preview-80mm" style="font-size:${fs}px;">`;
        html += `<div class="rp-header">`;
        if (settings.showLogo && siteLogo) {
            html += `<img src="${siteLogo}" style="max-width:60px;max-height:60px;margin-bottom:4px;display:block;margin-left:auto;margin-right:auto;" alt="${siteName}">`;
        }
        html += `<div style="font-size:${fs+4}px;font-weight:bold;color:${pc};">${siteName}</div>`;
        if (settings.showContactInfo) {
            if (sitePhone) html += `<div class="rp-contact" style="font-size:${Math.max(8, fs-2)}px;">📞 ${sitePhone}</div>`;
            if (siteAddress) html += `<div class="rp-contact" style="font-size:${Math.max(8, fs-2)}px;">📍 ${siteAddress}</div>`;
        }
        html += `<h3 style="font-size:${fs+2}px;margin:4px 0 2px;color:${pc};">${headerTitle}</h3>`;
        html += `<div class="rp-info" style="font-size:${Math.max(8, fs-2)}px;color:#777;">رقم: ${sale.posSaleId} | ${new Date().toLocaleString('ar-SA')}</div>`;
        html += `</div>`;

        if (settings.showCustomerInfo && sale.customerName) {
            html += `<div style="font-size:${Math.max(9, fs-1)}px;margin-bottom:4px;">`;
            html += `العميل: ${sale.customerName}`;
            if (sale.customerPhone) html += ` | ${sale.customerPhone}`;
            html += `</div>`;
        }

        html += `<div class="rp-divider"></div>`;

        sale.items.forEach(item => {
            const lineTotal = item.price * item.quantity;
            html += `<div class="rp-item" style="font-size:${Math.max(9, fs-1)}px;">`;
            if (settings.showProductImages && item.image) {
                html += `<img src="${item.image}" class="rp-item-thumb" alt="">`;
            }
            html += `<span class="rp-item-name">${item.name}</span>`;
            html += `<span>${item.quantity}×₪${item.price.toFixed(2)}</span>`;
            html += `<span style="font-weight:bold;margin-right:8px;">₪${lineTotal.toFixed(2)}</span>`;
            html += `</div>`;
        });

        html += `<div class="rp-divider"></div>`;
        html += `<div class="rp-row" style="font-size:${fs}px;"><span>المجموع الفرعي</span><span>₪${sale.subtotal.toFixed(2)}</span></div>`;
        if (sale.discount > 0) {
            html += `<div class="rp-row" style="color:#ef4444;font-size:${fs}px;"><span>الخصم</span><span>-₪${sale.discount.toFixed(2)}</span></div>`;
        }
        if (itemDiscountTotal > 0) {
            html += `<div class="rp-row" style="color:#ef4444;font-size:${fs}px;"><span>خصم المنتجات</span><span>-₪${itemDiscountTotal.toFixed(2)}</span></div>`;
        }
        if (taxEnabled && taxAmount > 0) {
            html += `<div class="rp-row" style="font-size:${fs}px;"><span>ضريبة ${(taxRate * 100).toFixed(0)}%</span><span>₪${taxAmount.toFixed(2)}</span></div>`;
        }
        html += `<div class="rp-row total" style="font-size:${fs+3}px;font-weight:bold;border-top:2px solid ${pc};padding-top:4px;margin-top:4px;color:${pc};"><span>الإجمالي</span><span>₪${(finalTotal + taxAmount).toFixed(2)}</span></div>`;

        if (settings.showQR) {
            html += `<div class="rp-qr"><div id="rpQrCode"></div></div>`;
        }

        html += `<div class="rp-footer" style="font-size:${Math.max(8, fs-2)}px;color:#888;border-top:1px dashed ${pc};padding-top:6px;margin-top:6px;">${footerText}<br>${siteName}</div>`;
        html += `</div>`;

        document.getElementById('receiptPreviewContent').innerHTML = html;
        const rpContainer = document.getElementById('receiptPreviewContainer');
        rpContainer.classList.remove('receipt-fade-in');
        void rpContainer.offsetWidth;
        rpContainer.classList.add('receipt-fade-in');

        if (settings.showQR) {
            const qrDiv = document.getElementById('rpQrCode');
            if (typeof QRCode !== 'undefined') {
                new QRCode(qrDiv, { text: sale.posSaleId, width: 50, height: 50 });
            } else {
                qrDiv.innerHTML = `<span style="font-size:8px;color:#999;">[${sale.posSaleId}]</span>`;
            }
        }

        const modal = new bootstrap.Modal(document.getElementById('printPreviewModal'));
        modal.show();

        // Auto-print countdown
        if (autoPrintAfter && document.getElementById('printCountdown')) {
            let sec = parseInt(settings.autoPrintDelay) || 3;
            if (sec < 1) { printReceipt(); return; }
            const cd = document.getElementById('printCountdown');
            cd.textContent = `🖨️ طباعة تلقائية بعد ${sec} ثوانٍ`;
            cd.classList.add('countdown-pulse');
            const timer = setInterval(() => {
                sec--;
                if (sec > 0) cd.textContent = `🖨️ طباعة تلقائية بعد ${sec} ثوانٍ`;
                else {
                    clearInterval(timer);
                    cd.textContent = '';
                    cd.classList.remove('countdown-pulse');
                    printReceipt();
                }
            }, 1000);
            document.getElementById('printPreviewModal').addEventListener('hidden.bs.modal', () => clearInterval(timer), { once: true });
        }
    }

    function printReceipt() {
        const settings = loadPrintSettings();
        const pc = settings.primaryColor || '#db2777';
        const fs = parseInt(settings.receiptFontSize) || 10;
        const margin = settings.paperMargin || '5';
        const siteName = '{{ $siteSettings["site_name"] ?? \App\Helpers\SettingsHelper::siteName() }}';
        const siteLogo = '{{ $siteSettings["site_logo_url"] ?? \App\Helpers\SettingsHelper::siteLogo() }}';
        const sitePhone = '{{ $siteSettings["site_phone"] ?? $siteSettings["contact_phone"] ?? "" }}';
        const siteAddress = '{{ $siteSettings["address"] ?? $siteSettings["site_address"] ?? "" }}';
        const headerTitle = settings.customHeaderTitle || 'فاتورة مبيعات';
        const footerText = settings.customFooterText || 'شكراً لتعاملكم معنا';
        const sale = lastSaleData;
        const taxRate = sale?.tax_rate || 0.15;
        const taxEnabled = sale?.tax_enabled !== undefined ? sale.tax_enabled : true;
        const itemDiscountTotal = (sale?.items || []).reduce((sum, i) => {
            const lineTotal = (i.price || 0) * (i.quantity || 0);
            return sum + ((i.itemDiscountType === 'percent' ? lineTotal * (i.itemDiscount / 100) : Math.min(i.itemDiscount || 0, lineTotal)));
        }, 0);
        const taxAmount = taxEnabled ? (sale?.subtotal || 0) * taxRate : 0;
        const finalTotal = (sale?.subtotal || 0) - (sale?.discount || 0) - itemDiscountTotal;

        let printHtml = `<div class="rp-header">`;
        if (settings.showLogo && siteLogo) printHtml += `<img src="${siteLogo}" class="rp-logo" alt="${siteName}">`;
        printHtml += `<div style="font-size:${fs+4}px;font-weight:bold;color:${pc};">${siteName}</div>`;
        if (settings.showContactInfo) {
            if (sitePhone) printHtml += `<div class="rp-contact">📞 ${sitePhone}</div>`;
            if (siteAddress) printHtml += `<div class="rp-contact">📍 ${siteAddress}</div>`;
        }
        printHtml += `<h3 style="font-size:${fs+2}px;margin:4px 0 2px;color:${pc};">${headerTitle}</h3>`;
        printHtml += `<div class="rp-info" style="font-size:${Math.max(8, fs-2)}px;color:#555;">رقم: ${sale?.posSaleId || ''} | ${new Date().toLocaleString('ar-SA')}</div>`;
        printHtml += `</div>`;

        if (settings.showCustomerInfo && sale?.customerName) {
            printHtml += `<div style="font-size:${Math.max(9, fs-1)}px;margin-bottom:4px;">العميل: ${sale.customerName}`;
            if (sale.customerPhone) printHtml += ` | ${sale.customerPhone}`;
            printHtml += `</div>`;
        }

        printHtml += `<div class="rp-divider"></div>`;

        if (sale?.items) {
            sale.items.forEach(item => {
                const lineTotal = (item.price || 0) * (item.quantity || 0);
                printHtml += `<div class="rp-item" style="font-size:${Math.max(9, fs-1)}px;">`;
                if (settings.showProductImages && item.image) printHtml += `<img src="${item.image}" class="rp-item-thumb" alt="">`;
                printHtml += `<span class="rp-item-name">${item.name}</span>`;
                printHtml += `<span>${item.quantity}×₪${(item.price || 0).toFixed(2)}</span>`;
                printHtml += `<span style="font-weight:bold;margin-right:8px;">₪${lineTotal.toFixed(2)}</span>`;
                printHtml += `</div>`;
            });
        }

        printHtml += `<div class="rp-divider"></div>`;
        printHtml += `<div class="rp-row" style="font-size:${fs}px;"><span>المجموع الفرعي</span><span>₪${(sale?.subtotal || 0).toFixed(2)}</span></div>`;
        if ((sale?.discount || 0) > 0) {
            printHtml += `<div class="rp-row" style="color:#ef4444;font-size:${fs}px;"><span>الخصم</span><span>-₪${(sale?.discount || 0).toFixed(2)}</span></div>`;
        }
        if (itemDiscountTotal > 0) {
            printHtml += `<div class="rp-row" style="color:#ef4444;font-size:${fs}px;"><span>خصم المنتجات</span><span>-₪${itemDiscountTotal.toFixed(2)}</span></div>`;
        }
        if (taxEnabled && taxAmount > 0) {
            printHtml += `<div class="rp-row" style="font-size:${fs}px;"><span>ضريبة ${(taxRate * 100).toFixed(0)}%</span><span>₪${taxAmount.toFixed(2)}</span></div>`;
        }
        printHtml += `<div class="rp-row total" style="font-size:${fs+3}px;font-weight:bold;border-top:2px solid ${pc};padding-top:4px;margin-top:4px;color:${pc};"><span>الإجمالي</span><span>₪${(finalTotal + taxAmount).toFixed(2)}</span></div>`;

        if (settings.showQR) {
            printHtml += `<div class="rp-qr"><div id="printQrCode"></div></div>`;
        }

        printHtml += `<div class="rp-footer" style="font-size:${Math.max(8, fs-2)}px;color:#888;border-top:1px dashed ${pc};padding-top:6px;margin-top:6px;">${footerText}<br>${siteName}</div>`;

        const win = window.open('', '_blank', 'width=400,height=600');
        win.document.write(`
            <!DOCTYPE html>
            <html lang="ar" dir="rtl">
            <head>
                <meta charset="UTF-8">
                <title>فاتورة - ${siteName}</title>
                <style>
                    @page { size: ${settings.receiptSize}; margin: ${margin}mm; }
                    body { width: ${settings.receiptSize}; margin: 0 auto; font-family: 'Courier New', Courier, monospace; font-size: ${fs}px; line-height: 1.4; padding: 3px 3px; }
                    .rp-header { text-align: center; border-bottom: 1px dashed #333; padding-bottom: 6px; margin-bottom: 6px; }
                    .rp-header .rp-logo { max-width: 60px; max-height: 60px; margin-bottom: 4px; }
                    .rp-header h3 { font-weight: bold; margin: 2px 0; }
                    .rp-header .rp-info { color: #555; }
                    .rp-header .rp-contact { color: #666; margin-top: 2px; }
                    .rp-divider { border-top: 1px dashed #333; margin: 6px 0; }
                    .rp-row { display: flex; justify-content: space-between; padding: 1px 0; }
                    .rp-item { display: flex; justify-content: space-between; align-items: center; padding: 2px 0; border-bottom: 1px dotted #ddd; }
                    .rp-item .rp-item-name { flex: 1; padding: 0 4px; }
                    .rp-item .rp-item-thumb { width: 20px; height: 20px; border-radius: 2px; object-fit: cover; }
                    .rp-footer { text-align: center; }
                    .rp-qr { text-align: center; margin: 4px 0; }
                    .rp-qr img { width: 50px; height: 50px; }
                    @media print { .no-print { display: none !important; } body * { visibility: visible; } }
                </style>
            </head>
            <body><div class="receipt-preview-80mm">${printHtml}</div></body>
            </html>
        `);
        win.document.close();
        win.onload = function() {
            if (settings.showQR && typeof QRCode !== 'undefined' && win.document.getElementById('printQrCode')) {
                new QRCode(win.document.getElementById('printQrCode'), { text: sale?.posSaleId || '', width: 50, height: 50 });
            }
        };
        setTimeout(() => { win.focus(); win.print(); }, 500);
    }

    // ========== SUSPEND / RESTORE ==========
    async function suspendCart() {
        if (cart.length === 0) {
            showToast('السلة فارغة', true);
            return;
        }

        const cartData = cart.map(i => ({
            product_id: i.product_id,
            name: i.name,
            price: i.price,
            quantity: i.quantity,
            image: i.image || '',
            sku: i.sku || '',
        }));

        const payload = {
            cart_data: cartData,
            customer_name: document.getElementById('customerName').value.trim() || null,
            customer_phone: document.getElementById('customerPhone').value.trim() || null,
            customer_email: document.getElementById('customerEmail').value.trim() || null,
            payment_method: selectedPayment,
        };

        try {
            const response = await fetch('{{ route('admin.pos.suspend') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            if (data.success) {
                showToast('تم تعليق الطلب بنجاح');
                clearCart();
                document.getElementById('customerName').value = '';
                document.getElementById('customerPhone').value = '';
                document.getElementById('customerEmail').value = '';
            } else {
                showToast(data.message || 'حدث خطأ', true);
            }
        } catch (e) {
            showToast('فشل الاتصال بالخادم', true);
        }
    }

    async function openSuspendedList() {
        const modal = new bootstrap.Modal(document.getElementById('suspendedModal'));
        modal.show();
        document.getElementById('suspendedListBody').innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-2"></i><p>جاري التحميل...</p></div>';

        try {
            const response = await fetch('{{ route('admin.pos.suspended') }}');
            const data = await response.json();
            const body = document.getElementById('suspendedListBody');

            if (!data.carts || data.carts.length === 0) {
                body.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3" style="opacity:.3;"></i><p>لا توجد طلبات معلقة</p></div>';
                return;
            }

            body.innerHTML = data.carts.map(c => `
                <div class="suspended-item">
                    <div class="si-info">
                        <div class="si-name">${c.customer_name || 'بدون اسم'}</div>
                        <div class="si-meta">${c.item_count} منتج | ${c.created_at}</div>
                        ${c.notes ? `<div class="si-meta" style="font-style:italic;">ملاحظة: ${c.notes}</div>` : ''}
                    </div>
                    <div class="si-total">₪${c.total.toFixed(2)}</div>
                    <div class="si-actions">
                        <button class="si-restore" onclick="restoreCart(${c.id})" title="استرجاع"><i class="fas fa-redo"></i></button>
                        <button class="si-delete" onclick="deleteSuspended(${c.id})" title="حذف"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `).join('');
        } catch (e) {
            document.getElementById('suspendedListBody').innerHTML = '<div class="text-center py-4 text-danger"><p>فشل تحميل الطلبات المعلقة</p></div>';
        }
    }

    async function restoreCart(id) {
        try {
            const response = await fetch('{{ route('admin.pos.suspended.restore', '') }}/' + id, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
            const data = await response.json();
            if (data.success) {
                const d = data.data;
                cart = d.cart_data.map((item, idx) => ({
                    ...item,
                    product_id: item.product_id,
                    stock: item.stock ?? 999,
                }));
                if (d.customer_name) document.getElementById('customerName').value = d.customer_name;
                if (d.customer_phone) document.getElementById('customerPhone').value = d.customer_phone;
                if (d.customer_email) document.getElementById('customerEmail').value = d.customer_email;
                if (d.payment_method) {
                    selectedPayment = d.payment_method;
                    document.querySelectorAll('.pm-btn').forEach(b => {
                        b.classList.toggle('active', b.dataset.method === d.payment_method);
                    });
                }
                renderCart();
                bootstrap.Modal.getInstance(document.getElementById('suspendedModal')).hide();
                showToast('تم استرجاع الطلب بنجاح');
                deleteSuspended(id, true);
            }
        } catch (e) {
            showToast('فشل استرجاع الطلب', true);
        }
    }

    async function deleteSuspended(id, silent = false) {
        try {
            await fetch('{{ route('admin.pos.suspended.delete', '') }}/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
            if (!silent) {
                openSuspendedList();
                showToast('تم حذف الطلب المعلق');
            }
        } catch (e) {
            if (!silent) showToast('فشل حذف الطلب', true);
        }
    }

    // Load all sales into modal (professional table)
    async function loadSalesModal() {
        const list = document.getElementById('salesModalList');
        const countEl = document.getElementById('salesModalCount');
        list.innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
        try {
            const response = await fetch('{{ route('admin.pos.recent-sales') }}');
            const data = await response.json();

            if (data.sales && data.sales.length > 0) {
                countEl.textContent = `(${data.sales.length})`;
                list.innerHTML = `
                    <div style="overflow-x:auto;">
                        <table class="sm-table">
                            <thead><tr>
                                <th>#</th>
                                <th>الفاتورة</th>
                                <th>العميل</th>
                                <th>المبلغ</th>
                                <th>طريقة الدفع</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr></thead>
                            <tbody>
                                ${data.sales.map((s, i) => `
                                    <tr>
                                        <td style="color:var(--gray-400);font-size:.7rem;">${i + 1}</td>
                                        <td><span class="sale-id-badge">${s.pos_sale_id}</span></td>
                                        <td>${s.customer_name || '<span style="color:var(--gray-400)">بدون</span>'}</td>
                                        <td><span class="sm-total">₪${s.total.toFixed(2)}</span></td>
                                        <td><span class="sm-payment">${s.payment_method === 'cash' ? 'نقداً' : s.payment_method === 'card' ? 'بطاقة' : s.payment_method === 'transfer' ? 'تحويل' : s.payment_method || '—'}</span></td>
                                        <td style="font-size:.72rem;color:var(--gray-500);">${s.created_at}</td>
                                        <td>
                                            <div style="display:flex;gap:3px;flex-wrap:nowrap;">
                                                <button class="sm-btn sm-view" onclick="event.stopPropagation();openSaleDetail('${s.pos_sale_id}')" title="عرض"><i class="fas fa-eye"></i></button>
                                                <button class="sm-btn sm-print" onclick="event.stopPropagation();reprintReceipt('${s.pos_sale_id}')" title="طباعة"><i class="fas fa-print"></i></button>
                                                <button class="sm-btn sm-edit" onclick="event.stopPropagation();openEditSale('${s.pos_sale_id}')" title="تعديل"><i class="fas fa-edit"></i></button>
                                                <button class="sm-btn sm-refund" onclick="event.stopPropagation();openRefund('${s.pos_sale_id}')" title="إرجاع"><i class="fas fa-undo-alt"></i></button>
                                                <button class="sm-btn sm-delete" onclick="event.stopPropagation();openDeleteSale('${s.pos_sale_id}')" title="إلغاء"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                countEl.textContent = '';
                list.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-inbox fa-2x mb-2" style="display:block;"></i> لا توجد مبيعات بعد</div>';
            }
            document.getElementById('salesModalSearch').value = '';
        } catch(e) {
            console.error('loadSalesModal error', e);
            list.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-exclamation-triangle fa-2x mb-2" style="display:block;"></i> فشل تحميل المبيعات</div>';
        }
    }

    function openSalesModal() {
        loadSalesModal();
        new bootstrap.Modal(document.getElementById('salesModal')).show();
    }

    // Discount modal
    function openDiscountModal() {
        document.getElementById('discountModalValue').value = document.getElementById('discountInput').value;
        document.getElementById('discountModalType').value = document.getElementById('discountType').value;
        new bootstrap.Modal(document.getElementById('discountModal')).show();
    }

    function applyDiscountFromModal() {
        const val = parseFloat(document.getElementById('discountModalValue').value) || 0;
        const type = document.getElementById('discountModalType').value;
        document.getElementById('discountInput').value = val;
        document.getElementById('discountType').value = type;
        bootstrap.Modal.getInstance(document.getElementById('discountModal')).hide();
        updateCartTotals();
        if (val > 0) {
            showToast('تم تطبيق خصم ' + (type === 'percent' ? val + '%' : '₪' + val.toFixed(2)));
        } else {
            showToast('تم إلغاء الخصم');
        }
    }

    function filterSalesModal() {
        const q = document.getElementById('salesModalSearch').value.trim().toLowerCase();
        document.querySelectorAll('#salesModalList tbody tr').forEach(el => {
            if (q === '') { el.style.display = ''; return; }
            const text = el.textContent.toLowerCase();
            el.style.display = text.includes(q) ? '' : 'none';
        });
    }

    // Load recent sales into the bar
    async function loadRecentSales() {
        try {
            const response = await fetch('{{ route('admin.pos.recent-sales') }}');
            const data = await response.json();
            const list = document.getElementById('recentSalesList');
            const countEl = document.getElementById('rsCount');

            if (data.sales && data.sales.length > 0) {
                countEl.textContent = `(${data.sales.length})`;
                list.innerHTML = data.sales.map(s => `
                    <div class="rs-item" data-sale-id="${s.pos_sale_id}" data-customer="${s.customer_name || ''}" data-total="${s.total}">
                        <i class="fas fa-receipt" style="color:var(--gray-400);"></i>
                        <span style="font-weight:600;font-size:.72rem;flex-shrink:0;" class="sale-id-text">${s.pos_sale_id}</span>
                        <span style="font-size:.65rem;color:var(--gray-500);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${s.customer_name || 'بدون عميل'}</span>
                        <span class="rs-total">₪${s.total.toFixed(2)}</span>
                        <span style="color:var(--gray-400);font-size:.6rem;">${s.created_at}</span>
                        <div class="rs-item-actions">
                            <button class="rsa-view" onclick="event.stopPropagation();openSaleDetail('${s.pos_sale_id}')"><i class="fas fa-eye"></i> عرض</button>
                            <button class="rsa-print" onclick="event.stopPropagation();reprintReceipt('${s.pos_sale_id}')"><i class="fas fa-print"></i> طباعة</button>
                            <button class="rsa-edit" onclick="event.stopPropagation();openEditSale('${s.pos_sale_id}')"><i class="fas fa-edit"></i> تعديل</button>
                            <button class="rsa-refund" onclick="event.stopPropagation();openRefund('${s.pos_sale_id}')"><i class="fas fa-undo-alt"></i> إرجاع</button>
                            <button class="rsa-delete" onclick="event.stopPropagation();openDeleteSale('${s.pos_sale_id}')"><i class="fas fa-trash"></i> إلغاء</button>
                        </div>
                    </div>
                `).join('');
            } else {
                list.innerHTML = '<div class="rs-item" style="color:var(--gray-400);"><i class="fas fa-inbox"></i> لا توجد مبيعات بعد</div>';
            }
            document.getElementById('saleSearch').value = '';
        } catch(e) { console.error('loadRecentSales error', e); }
    }

    // Search sales in bar
    function searchSales() {
        const q = document.getElementById('saleSearch').value.trim().toLowerCase();
        document.querySelectorAll('#recentSalesList .rs-item').forEach(el => {
            if (q === '') { el.style.display = 'flex'; return; }
            const id = (el.dataset.saleId || '').toLowerCase();
            const customer = (el.dataset.customer || '').toLowerCase();
            const total = (el.dataset.total || '');
            el.style.display = (id.includes(q) || customer.includes(q) || total.includes(q)) ? 'flex' : 'none';
        });
    }

    // Reprint receipt by opening in new window
    function reprintReceipt(posSaleId) {
        const url = '{{ route('admin.pos.receipt', '') }}/' + encodeURIComponent(posSaleId);
        window.open(url, '_blank', 'width=400,height=600,scrollbars=yes');
    }

    // Update stats
    async function updateStats() {
        try {
            const res = await fetch('{{ route('admin.pos.recent-sales') }}');
            const data = await res.json();
            if (data.sales) {
                const todayStr = new Date().toISOString().slice(0, 10);
                const todaySales = data.sales.filter(s => (s.created_at || '').startsWith(todayStr));
                const todayRev = todaySales.reduce((sum, s) => sum + s.total, 0);
                const totalRev = data.sales.reduce((sum, s) => sum + s.total, 0);
                document.getElementById('dsTodaySales').textContent = todaySales.length;
                document.getElementById('dsTodayRev').textContent = '₪' + todayRev.toFixed(2);
                document.getElementById('dsTotalSales').textContent = data.sales.length;
                document.getElementById('dsTotalRev').textContent = '₪' + totalRev.toFixed(2);
            }
        } catch(e) { console.error('updateStats error', e); }
    }

    // ========== SALE DETAIL ==========
    async function openSaleDetail(posSaleId) {
        const modal = new bootstrap.Modal(document.getElementById('saleDetailModal'));
        const content = document.getElementById('sddContent');
        document.getElementById('sddTitle').textContent = 'فاتورة: ' + posSaleId;
        content.innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
        modal.show();

        try {
            const resp = await fetch('{{ route('admin.pos.getSale', '') }}/' + encodeURIComponent(posSaleId), {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await resp.json();
            const s = data.sale;
            const items = s.items || [];
            const subtotal = s.subtotal || items.reduce((sum, i) => sum + (i.price * i.quantity), 0);

            let html = '';
            // Customer info
            html += `<div class="row g-2 mb-3">
                <div class="col-6"><div class="sale-detail-field"><span class="sdf-label">العميل</span><span class="sdf-value">${s.customer_name || 'بدون اسم'}</span></div></div>
                <div class="col-6"><div class="sale-detail-field"><span class="sdf-label">الهاتف</span><span class="sdf-value">${s.customer_phone || '-'}</span></div></div>
                <div class="col-6"><div class="sale-detail-field"><span class="sdf-label">طريقة الدفع</span><span class="sdf-value">${s.payment_method || '-'}</span></div></div>
                <div class="col-6"><div class="sale-detail-field"><span class="sdf-label">التاريخ</span><span class="sdf-value">${new Date(s.created_at).toLocaleString('ar-SA')}</span></div></div>
            </div>`;

            // Items
            if (items.length > 0) {
                html += `<div style="font-size:.75rem;font-weight:700;color:var(--gray-500);margin-bottom:.35rem;">المنتجات (${items.length})</div>
                <div class="sale-detail-items">`;
                items.forEach(i => {
                    html += `<div class="sale-detail-item">
                        <span class="sdi-name">${i.name}</span>
                        <span class="sdi-qty">×${i.quantity}</span>
                        <span class="sdi-price">₪${(i.price * i.quantity).toFixed(2)}</span>
                    </div>`;
                });
                html += `</div>`;
            }

            // Totals
            const discount = s.discount_amount || 0;
            html += `<div style="margin-top:.75rem;padding-top:.5rem;border-top:2px solid var(--gray-100);">
                <div class="sale-detail-field"><span class="sdf-label">المجموع الفرعي</span><span class="sdf-value">₪${subtotal.toFixed(2)}</span></div>`;
            if (discount > 0) {
                html += `<div class="sale-detail-field"><span class="sdf-label">الخصم</span><span class="sdf-value" style="color:#dc2626;">-₪${discount.toFixed(2)}</span></div>`;
            }
            html += `<div class="sale-detail-field" style="border-bottom:none;font-size:.9rem;">
                <span class="sdf-label">الإجمالي</span><span class="sdf-value" style="color:#db2777;">₪${s.order_total.toFixed(2)}</span>
            </div></div>`;

            // Notes
            if (s.notes) {
                html += `<div style="margin-top:.5rem;font-size:.7rem;color:var(--gray-500);background:var(--gray-50);padding:.35rem .5rem;border-radius:6px;">
                    <i class="fas fa-sticky-note"></i> ${s.notes}
                </div>`;
            }

            // Action buttons
            html += `<div class="sale-detail-actions">
                <button class="btn-print-sale" onclick="reprintReceipt('${posSaleId}')"><i class="fas fa-print"></i> طباعة</button>
                <button class="btn-edit-sale" onclick="bootstrap.Modal.getInstance(document.getElementById('saleDetailModal')).hide();setTimeout(()=>openEditSale('${posSaleId}'),300)"><i class="fas fa-edit"></i> تعديل</button>
                <button class="btn-refund-sale" onclick="bootstrap.Modal.getInstance(document.getElementById('saleDetailModal')).hide();setTimeout(()=>openRefund('${posSaleId}'),300)"><i class="fas fa-undo-alt"></i> إرجاع</button>
                <button class="btn-delete-sale" onclick="bootstrap.Modal.getInstance(document.getElementById('saleDetailModal')).hide();setTimeout(()=>openDeleteSale('${posSaleId}'),300)"><i class="fas fa-trash"></i> إلغاء</button>
            </div>`;

            content.innerHTML = html;
        } catch(e) {
            content.innerHTML = '<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-circle"></i> فشل تحميل تفاصيل الفاتورة</div>';
        }
    }

    // ========== EDIT SALE ==========
    let editSaleId = null;

    async function openEditSale(posSaleId) {
        editSaleId = posSaleId;
        const modal = new bootstrap.Modal(document.getElementById('editSaleModal'));
        const body = document.getElementById('editSaleBody');
        body.innerHTML = '<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
        modal.show();

        try {
            const resp = await fetch('{{ route('admin.pos.getSale', '') }}/' + encodeURIComponent(posSaleId), {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await resp.json();
            const s = data.sale;
            const items = s.items || [];

            let html = `
                <div class="mb-3">
                    <label class="form-label fw-bold small">فاتورة: ${posSaleId}</label>
                    <div id="editItemsContainer">`;
            items.forEach((item, idx) => {
                const pid = item.product_id || item.id || idx;
                html += `<div class="edit-cart-item" data-index="${idx}">
                    <span class="eci-name">${item.name}</span>
                    <div class="eci-qty"><input type="number" class="edit-qty" value="${item.quantity}" min="0" max="999" data-pid="${pid}" oninput="updateEditTotal(this)"></div>
                    <div class="eci-price"><input type="number" class="edit-price" value="${item.price}" step="0.01" min="0" data-pid="${pid}" oninput="updateEditTotal(this)"></div>
                    <span class="eci-total" id="editItemTotal_${idx}">₪${(item.price * item.quantity).toFixed(2)}</span>
                    <span class="eci-remove" onclick="this.closest('.edit-cart-item').remove();updateEditSummary();"><i class="fas fa-times-circle"></i></span>
                </div>`;
            });
            html += `</div></div>
                <div class="mb-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-bold small">العميل</label>
                            <input type="text" id="editCustomerName" class="form-control" value="${s.customer_name || ''}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">الهاتف</label>
                            <input type="text" id="editCustomerPhone" class="form-control" value="${s.customer_phone || ''}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">البريد</label>
                            <input type="email" id="editCustomerEmail" class="form-control" value="${s.customer_email || ''}">
                        </div>
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label fw-bold small">طريقة الدفع</label>
                        <select id="editPaymentMethod" class="form-select">
                            <option value="cash" ${s.payment_method === 'cash' ? 'selected' : ''}>نقداً</option>
                            <option value="card" ${s.payment_method === 'card' ? 'selected' : ''}>بطاقة</option>
                            <option value="transfer" ${s.payment_method === 'transfer' ? 'selected' : ''}>تحويل</option>
                            <option value="split" ${s.payment_method === 'split' ? 'selected' : ''}>دفع مقسم</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small">ملاحظات</label>
                        <input type="text" id="editNotes" class="form-control" value="${s.notes || ''}">
                    </div>
                </div>
                <div style="background:var(--gray-50);border-radius:8px;padding:.5rem .75rem;display:flex;justify-content:space-between;font-weight:700;">
                    <span>الإجمالي المقدر:</span>
                    <span id="editTotalDisplay" style="color:#db2777;">₪0.00</span>
                </div>`;

            body.innerHTML = html;
            document.getElementById('confirmEditBtn').disabled = false;
            document.getElementById('confirmEditBtn').innerHTML = '<i class="fas fa-save"></i> حفظ التعديلات';
            updateEditSummary();
        } catch(e) {
            body.innerHTML = '<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-circle"></i> فشل تحميل بيانات الفاتورة</div>';
        }
    }

    function updateEditTotal(input) {
        const row = input.closest('.edit-cart-item');
        const qty = parseInt(row.querySelector('.edit-qty').value) || 0;
        const price = parseFloat(row.querySelector('.edit-price').value) || 0;
        const idx = row.dataset.index;
        document.getElementById('editItemTotal_' + idx).textContent = '₪' + (qty * price).toFixed(2);
        updateEditSummary();
    }

    function updateEditSummary() {
        let total = 0;
        document.querySelectorAll('.edit-cart-item').forEach(row => {
            const qty = parseInt(row.querySelector('.edit-qty').value) || 0;
            const price = parseFloat(row.querySelector('.edit-price').value) || 0;
            total += qty * price;
        });
        document.getElementById('editTotalDisplay').textContent = '₪' + total.toFixed(2);
    }

    async function confirmEditSale() {
        const items = [];
        document.querySelectorAll('.edit-cart-item').forEach(row => {
            const pid = parseInt(row.querySelector('.edit-qty').dataset.pid);
            const qty = parseInt(row.querySelector('.edit-qty').value) || 0;
            const price = parseFloat(row.querySelector('.edit-price').value) || 0;
            if (qty > 0 && price > 0) {
                items.push({ product_id: pid, quantity: qty, price: price });
            }
        });

        if (items.length === 0) {
            showToast('يجب أن تحتوي الفاتورة على منتج واحد على الأقل', true);
            return;
        }

        const btn = document.getElementById('confirmEditBtn');
        btn.disabled = true;
        btn.innerHTML = '<div class="spinner-border spinner-border-sm"></div> جاري الحفظ...';

        try {
            const resp = await fetch('{{ route('admin.pos.editSale', '') }}/' + encodeURIComponent(editSaleId), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({
                    items,
                    customer_name: document.getElementById('editCustomerName').value.trim() || null,
                    customer_phone: document.getElementById('editCustomerPhone').value.trim() || null,
                    customer_email: document.getElementById('editCustomerEmail').value.trim() || null,
                    notes: document.getElementById('editNotes').value.trim() || null,
                    payment_method: document.getElementById('editPaymentMethod').value,
                }),
            });
            const data = await resp.json();
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('editSaleModal')).hide();
                showToast('تم تعديل الفاتورة بنجاح');
                loadRecentSales();
                updateStats();
            } else {
                showToast(data.message || 'فشل التعديل', true);
            }
        } catch(e) {
            showToast('خطأ في الاتصال', true);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> حفظ التعديلات';
        }
    }

    // ========== DELETE / VOID SALE ==========
    function openDeleteSale(posSaleId) {
        if (!confirm('إلغاء الفاتورة ' + posSaleId + '؟')) return;
        doDeleteSale(posSaleId);
    }

    async function doDeleteSale(saleId, reason) {
        try {
            const resp = await fetch('{{ route('admin.pos.deleteSale', '') }}/' + encodeURIComponent(saleId), {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ reason: reason || null }),
            });
            const data = await resp.json();
            if (data.success) {
                showToast('تم إلغاء الفاتورة ' + saleId);
                loadRecentSales();
                updateStats();
                const detailModal = bootstrap.Modal.getInstance(document.getElementById('saleDetailModal'));
                if (detailModal) detailModal.hide();
            } else {
                showToast(data.message || 'فشل الإلغاء', true);
            }
        } catch(e) {
            showToast('خطأ في الاتصال', true);
        }
    }

    // Search products
    function searchProducts() {
        const q = productSearch.value.trim();
        const categoryId = categoryFilter.value;
        const isOffers = categoryId === 'offers';
        const isBestseller = categoryId === 'bestseller';

        if (q.length > 0) {
            clearSearch.style.display = 'block';
        } else {
            clearSearch.style.display = 'none';
        }

        resetScroll();

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(async () => {
            productsLoading.classList.add('show');
            productsGrid.style.display = 'none';

            try {
                const params = new URLSearchParams();
                if (q) params.set('q', q);
                if (categoryId && categoryId !== 'offers' && categoryId !== 'bestseller') params.set('category_id', categoryId);
                if (isOffers) params.set('offers', '1');
                if (isBestseller) params.set('bestseller', '1');

                const response = await fetch('{{ route('admin.pos.products.search') }}?' + params.toString());
                const data = await response.json();

                if (data.products && data.products.length > 0) {
                    renderProducts(data.products);
                    renderSuggestions(data.products, q);
                } else {
                    const emptyCats = Array.from(document.querySelectorAll('#categoryPills .cpill:not(.cpill-offer):not(.cpill-best)')).map(b => b.dataset.cat !== '' ? `<button class="empty-cat-btn" data-cat="${escAttr(b.dataset.cat)}">${escHtml(b.textContent)}</button>` : '').join('');
                    productsGrid.innerHTML = `
                        <div class="pos-products-empty">
                            <i class="fas fa-box-open empty-icon"></i>
                            <p style="font-size:.85rem;margin-bottom:.25rem;">لا توجد منتجات مطابقة</p>
                            <p style="font-size:.7rem;margin-bottom:.6rem;">حاول البحث بكلمة أخرى أو اختر تصنيفاً</p>
                            <div class="empty-categories">${emptyCats}</div>
                        </div>
                    `;
                    hideSuggestions();
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

    // Render search suggestions dropdown
    function renderSuggestions(products, query) {
        const container = document.getElementById('searchSuggest');
        if (!query || query.length < 1) { container.classList.remove('show'); return; }
        const maxSuggest = 8;
        const shown = products.slice(0, maxSuggest);
        if (shown.length === 0) { container.classList.remove('show'); return; }
        container.innerHTML = shown.map(p => {
            const dotClass = p.track_inventory
                ? (p.stock <= 0 ? 'sd-out' : (p.stock <= 5 ? 'sd-low' : 'sd-ok'))
                : 'sd-untracked';
            return `<div class="ss-item" data-id="${p.id}" onclick="event.stopPropagation();suggestAddToCart(${p.id})">
                ${p.image
                    ? `<img src="${p.image}" class="ss-img" loading="lazy">`
                    : `<div class="ss-img-placeholder"><i class="fas fa-box"></i></div>`
                }
                <div class="ss-info">
                    <div class="ss-name">${highlightMatch(p.name, query)}</div>
                    <div class="ss-sku">${p.sku || ''}</div>
                </div>
                <span class="ss-dot ${dotClass}"></span>
                <span class="ss-price">₪${p.price.toFixed(2)}</span>
            </div>`;
        }).join('');
        container.classList.add('show');
    }

    function highlightMatch(text, query) {
        if (!query) return text;
        const idx = text.toLowerCase().indexOf(query.toLowerCase());
        if (idx === -1) return text;
        return text.slice(0, idx) + '<strong style="color:#db2777;">' + text.slice(idx, idx + query.length) + '</strong>' + text.slice(idx + query.length);
    }

    function hideSuggestions() {
        document.getElementById('searchSuggest').classList.remove('show');
    }

    function suggestAddToCart(productId) {
        hideSuggestions();
        let card = document.querySelector(`.pos-product-card[data-id="${productId}"]`);
        if (card) { addToCart(card); return; }
        const cached = window.productCache && window.productCache[productId];
        if (cached) {
            card = {
                dataset: { id: cached.id, name: cached.name, price: cached.price, stock: cached.stock, image: cached.image || '', sku: cached.sku || '', trackInventory: cached.track_inventory ? '1' : '0', discount: cached.discount_percentage || 0 },
                querySelector: () => null, getAttribute: (a) => null,
            };
            addToCart(card);
            return;
        }
        fetch('{{ route('admin.pos.products.search') }}?q=' + productId, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.products && data.products[0]) {
                const p = data.products[0];
                window.productCache = window.productCache || {};
                window.productCache[p.id] = p;
                suggestAddToCart(productId);
            }
        });
    }

    // Infinite scroll for product grid
    function resetScroll() {
        scrollPage = 1;
        hasMorePages = true;
        isLoadingMore = false;
    }

    async function loadMoreProducts() {
        if (isLoadingMore || !hasMorePages) return;
        const q = productSearch.value.trim();
        const categoryId = categoryFilter.value;
        const isOffers = categoryId === 'offers';
        const isBestseller = categoryId === 'bestseller';
        isLoadingMore = true;
        const nextPage = scrollPage + 1;
        const loader = document.getElementById('scrollLoader') || (function() {
            const div = document.createElement('div');
            div.id = 'scrollLoader';
            div.style.cssText = 'grid-column:1/-1;text-align:center;padding:.75rem;color:var(--gray-400);font-size:.75rem;';
            div.innerHTML = '<div class="spinner-pink" style="width:24px;height:24px;margin:0 auto;"></div>';
            productsGrid.appendChild(div);
            return div;
        })();
        loader.style.display = 'block';

        try {
            const params = new URLSearchParams();
            if (q) params.set('q', q);
            if (categoryId && categoryId !== 'offers' && categoryId !== 'bestseller') params.set('category_id', categoryId);
            if (isOffers) params.set('offers', '1');
            if (isBestseller) params.set('bestseller', '1');
            params.set('page', String(nextPage));

            const response = await fetch('{{ route('admin.pos.products.search') }}?' + params.toString());
            const data = await response.json();

            if (data.products && data.products.length > 0) {
                scrollPage = nextPage;
                hasMorePages = data.has_more !== false;
                data.products.forEach(p => {
                    window.productCache = window.productCache || {};
                    window.productCache[p.id] = p;
                    const inCart = cart.some(i => i.product_id === p.id);
                    let stockDot = '';
                    if (p.track_inventory) {
                        const cls = p.stock <= 0 ? 'sd-out' : (p.stock <= 5 ? 'sd-low' : 'sd-ok');
                        stockDot = `<span class="stock-dot ${cls}"></span>`;
                    } else {
                        stockDot = `<span class="stock-dot sd-untracked"></span>`;
                    }
                    const div = document.createElement('div');
                    div.className = `pos-product-card ${inCart ? 'added' : ''}`;
                    div.setAttribute('data-id', p.id);
                    div.setAttribute('data-name', p.name);
                    div.setAttribute('data-price', p.price);
                    div.setAttribute('data-stock', p.stock);
                    div.setAttribute('data-image', p.image || '');
                    div.setAttribute('data-sku', p.sku || '');
                    div.setAttribute('data-discount', p.discount_percentage || 0);
                    div.setAttribute('data-track-inventory', p.track_inventory ? '1' : '0');
                    let innerHtml = '';
                    if (p.discount_percentage > 0) innerHtml += `<span class="discount-badge">-${p.discount_percentage}%</span>`;
                    innerHtml += `<button class="fav-btn" data-id="${p.id}" onclick="event.stopPropagation();toggleFavorite(${p.id})" title="مفضلة"><i class="${inCart ? 'fas' : 'far'} fa-heart"></i></button>`;
                    if (p.image) {
                        innerHtml += `<img src="${p.image}" alt="" class="product-img" loading="lazy">`;
                    } else {
                        innerHtml += `<div class="product-img-placeholder"><i class="fas fa-box"></i></div>`;
                    }
                    innerHtml += `<div class="product-name">${p.name}</div>`;
                    innerHtml += `<div class="product-price">₪${p.price.toFixed(2)}</div>`;
                    innerHtml += `<div class="product-sku">${p.sku || ''}</div>`;
                    innerHtml += stockDot;
                    div.innerHTML = innerHtml;
                    productsGrid.appendChild(div);
                });
                setTimeout(loadQuickBar, 50);
            } else {
                hasMorePages = false;
            }
        } catch(e) {
            hasMorePages = false;
        } finally {
            isLoadingMore = false;
            if (loader) loader.style.display = 'none';
        }
    }

    // Scroll listener for infinite scroll (throttled with requestAnimationFrame)
    let scrollRafId = null;
    productsGrid.addEventListener('scroll', function() {
        if (scrollRafId) return;
        scrollRafId = requestAnimationFrame(() => {
            scrollRafId = null;
            if (this.scrollTop + this.clientHeight >= this.scrollHeight - 60) {
                loadMoreProducts();
            }
        });
    });

    // Event delegation for product cards (click=add, contextmenu=qty modal)
    productsGrid.addEventListener('click', function(e) {
        const card = e.target.closest('.pos-product-card');
        if (card && !e.target.closest('.fav-btn')) addToCart(card);
    });
    productsGrid.addEventListener('contextmenu', function(e) {
        const card = e.target.closest('.pos-product-card');
        if (card) { e.preventDefault(); openQtyModalFromCard(card); }
    });

    function renderProducts(products) {
        // Build product cache for quick access
        window.productCache = window.productCache || {};
        products.forEach(p => { window.productCache[p.id] = p; });

        // Re-sync favorite buttons after re-render
        setTimeout(loadQuickBar, 50);

        productsGrid.innerHTML = products.map(p => {
            const inCart = cart.some(i => i.product_id === p.id);
            let stockDot = '';
            if (p.track_inventory) {
                const cls = p.stock <= 0 ? 'sd-out' : (p.stock <= 5 ? 'sd-low' : 'sd-ok');
                stockDot = `<span class="stock-dot ${cls}"></span>`;
            } else {
                stockDot = `<span class="stock-dot sd-untracked"></span>`;
            }

            return `
                <div class="pos-product-card ${inCart ? 'added' : ''}"
                     data-id="${p.id}"
                     data-name="${p.name}"
                     data-price="${p.price}"
                     data-stock="${p.stock}"
                     data-image="${p.image || ''}"
                     data-sku="${p.sku || ''}"
                     data-discount="${p.discount_percentage || 0}"
                     data-track-inventory="${p.track_inventory ? '1' : '0'}">
                    ${p.discount_percentage > 0 ? `<span class="discount-badge">-${p.discount_percentage}%</span>` : ''}
                    <button class="fav-btn" data-id="${p.id}" onclick="event.stopPropagation();toggleFavorite(${p.id})" title="مفضلة">
                        <i class="far fa-heart"></i>
                    </button>
                    ${p.image
                        ? `<img src="${p.image}" alt="" class="product-img" loading="lazy">`
                        : `<div class="product-img-placeholder"><i class="fas fa-box"></i></div>`
                    }
                    <div class="product-name">${p.name}</div>
                    <div class="product-price">₪${p.price.toFixed(2)}</div>
                    <div class="product-sku">${p.sku || ''}</div>
                    ${stockDot}
                </div>
            `;
        }).join('');
    }

    // Search input
    productSearch.addEventListener('input', searchProducts);

    // Clear search
    clearSearch.addEventListener('click', function() {
        productSearch.value = '';
        clearSearch.style.display = 'none';
        hideSuggestions();
        document.querySelectorAll('.cpill').forEach(b => b.classList.remove('active'));
        document.querySelector('.cpill[data-cat=""]').classList.add('active');
        categoryFilter.value = '';
        searchProducts();
        productSearch.focus();
    });

    // Hide suggestions on escape, blur, and click outside
    productSearch.addEventListener('blur', function() {
        setTimeout(hideSuggestions, 200);
    });
    productSearch.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') hideSuggestions();
        if (e.key === 'Enter') { hideSuggestions(); productSearch.blur(); }
    });
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-box')) hideSuggestions();
        if (!e.target.closest('.item-staff-popup') && !e.target.closest('.staff-badge')) {
            document.querySelectorAll('.item-staff-popup.show').forEach(p => p.classList.remove('show'));
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Qty modal keys (take priority when modal is open)
        if (qtyModal.classList.contains('show')) {
            if (e.key === 'Enter') { confirmQty(); e.preventDefault(); }
            else if (e.key === 'Escape') { qtyModal.classList.remove('show'); e.preventDefault(); }
            else if (e.key === '+' || e.key === '=') { adjustQty(1); e.preventDefault(); }
            else if (e.key === '-') { adjustQty(-1); e.preventDefault(); }
            return;
        }

        // Ctrl+Enter / Cmd+Enter to submit
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            if (cart.length > 0) { e.preventDefault(); submitSale(); }
            return;
        }

        // Shift+F1 for barcode focus
        if (e.shiftKey && e.key === 'F1') {
            e.preventDefault();
            barcodeInput.focus();
            showToast('نمط المسح نشط - امسح الباركود الآن');
            return;
        }

        // F4 or Ctrl+K or / to focus search
        if (e.key === 'F4' || (e.ctrlKey && e.key === 'k') || (e.key === '/' && !['INPUT', 'SELECT', 'TEXTAREA'].includes(e.target.tagName))) {
            e.preventDefault();
            productSearch.focus();
            return;
        }

        // F2 for new sale / quick clear
        if (e.key === 'F2') {
            e.preventDefault();
            if (cart.length > 0) {
                if (confirm('بدء عملية بيع جديدة؟')) clearCart();
            }
            return;
        }

        // Escape to clear cart
        if (e.key === 'Escape' && cart.length > 0) {
            if (confirm('تفريغ السلة؟')) clearCart();
            return;
        }

        // F5 - refresh products
        if (e.key === 'F5' && !e.ctrlKey) {
            e.preventDefault();
            productSearch.value = '';
            document.querySelectorAll('.cpill').forEach(b => b.classList.remove('active'));
            document.querySelector('.cpill[data-cat=""]').classList.add('active');
            categoryFilter.value = '';
            searchProducts();
            showToast('تم تحديث المنتجات');
            return;
        }

        // F6 - cycle payment method
        if (e.key === 'F6') {
            e.preventDefault();
            const methods = ['cash', 'card', 'transfer'];
            const currentIndex = methods.indexOf(selectedPayment);
            const nextMethod = methods[(currentIndex + 1) % methods.length];
            document.querySelectorAll('.pm-btn').forEach(b => {
                b.classList.toggle('active', b.dataset.method === nextMethod);
            });
            selectedPayment = nextMethod;
            showToast('طريقة الدفع: ' + (nextMethod === 'cash' ? 'نقداً' : nextMethod === 'card' ? 'بطاقة' : 'تحويل'));
            return;
        }

        // F8 - suspend cart
        if (e.key === 'F8') {
            e.preventDefault();
            if (cart.length > 0) suspendCart();
            return;
        }

        // F9 - open suspended list
        if (e.key === 'F9') {
            e.preventDefault();
            openSuspendedList();
            return;
        }

        // F12 - print last receipt
        if (e.key === 'F12') {
            e.preventDefault();
            if (lastSaleData) {
                buildReceiptPreview(lastSaleData);
            } else {
                showToast('لا توجد فاتورة سابقة للطباعة', true);
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
        if (e.key.length !== 1) return;
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
                playScanBeep();
                addToCart(card);
            } else {
                showToast('المنتج غير معروض في القائمة', true);
            }
        } else {
            showToast('لم يتم العثور على المنتج', true);
        }
    }

    // Toast
    const toastIcon = posToast.querySelector('i:first-child');

    function showToast(msg, isError = false) {
        toastMessage.textContent = msg;
        posToast.className = 'pos-toast' + (isError ? ' error' : '');
        toastIcon.className = isError ? 'fas fa-exclamation-circle' : 'fas fa-check-circle';
        toastIcon.style.color = isError ? '#ef4444' : '#22c55e';
        posToast.classList.add('show');
        clearTimeout(window.toastTimeout);
        window.toastTimeout = setTimeout(hideToast, 3500);
    }

    function hideToast() {
        posToast.classList.remove('show');
    }

    // Sound feedback using Web Audio API (single reusable AudioContext)
    const beepCtx = new (window.AudioContext || window.webkitAudioContext)();

    function playBeep(freq = 800, duration = 150) {
        try {
            const osc = beepCtx.createOscillator();
            const gain = beepCtx.createGain();
            osc.connect(gain);
            gain.connect(beepCtx.destination);
            osc.frequency.value = freq;
            osc.type = 'sine';
            gain.gain.setValueAtTime(0.25, beepCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, beepCtx.currentTime + duration / 1000);
            osc.start(beepCtx.currentTime);
            osc.stop(beepCtx.currentTime + duration / 1000);
        } catch (e) { /* silent fallback */ }
    }

    function playSuccessBeep() {
        playBeep(660, 100);
        setTimeout(() => playBeep(880, 150), 120);
    }

    function playErrorBeep() {
        playBeep(300, 300);
    }

    function playScanBeep() {
        playBeep(1000, 80);
    }

    // Click outside qty modal to close
    qtyModal.addEventListener('click', function(e) {
        if (e.target === qtyModal) qtyModal.classList.remove('show');
    });



    // ========== CUSTOMER SEARCH ==========
    let selectedCustomerId = null;
    let customerSearchTimeout = null;

    function escHtml(str) { return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
    function escJs(str) { return String(str).replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"').replace(/`/g,'\\`').replace(/\${/g,'\\${').replace(/\n/g,'\\n').replace(/\r/g,'\\r'); }
    function escAttr(str) { return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/\n/g,' ').replace(/\r/g,' '); }

    document.getElementById('customerName').addEventListener('input', function() {
        clearTimeout(customerSearchTimeout);
        const val = this.value.trim();
        if (val.length < 1) {
            document.getElementById('customerSearchResults').classList.remove('show');
            document.getElementById('customerSearchResults').innerHTML = '';
            return;
        }
        customerSearchTimeout = setTimeout(() => searchCustomers(val), 300);
    });

    document.getElementById('customerName').addEventListener('blur', function() {
        setTimeout(() => document.getElementById('customerSearchResults').classList.remove('show'), 200);
    });

    document.getElementById('customerName').addEventListener('focus', function() {
        if (this.value.trim().length >= 1) {
            document.getElementById('customerSearchResults').classList.add('show');
        }
    });

    async function searchCustomers(term) {
        try {
            const resp = await fetch('{{ route('admin.pos.searchCustomers') }}?q=' + encodeURIComponent(term), {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await resp.json();
            const container = document.getElementById('customerSearchResults');
            if (!data.customers || data.customers.length === 0) {
                container.innerHTML = `<div class="csr-create" data-term="${escAttr(term)}">
                    <i class="fas fa-plus-circle"></i> إنشاء عميل جديد "${escHtml(term)}"
                </div>`;
                container.classList.add('show');
                return;
            }
            let html = '';
            data.customers.forEach(c => {
                html += `<div class="csr-item" data-customer='${escAttr(JSON.stringify({id:c.id,name:c.name,phone:c.phone||'',email:c.email||''}))}'>
                    <div class="csr-icon"><i class="fas fa-user"></i></div>
                    <div class="csr-info">
                        <div class="csr-name">${escHtml(c.name)}</div>
                        <div class="csr-meta">${escHtml(c.phone || '')} ${c.email ? '| ' + escHtml(c.email) : ''}</div>
                    </div>
                </div>`;
            });
            html += `<div class="csr-create" data-term="${escAttr(term)}">
                <i class="fas fa-plus-circle"></i> إنشاء عميل جديد "${escHtml(term)}"
            </div>`;
            container.innerHTML = html;
            container.querySelectorAll('.csr-item').forEach(el => {
                el.addEventListener('click', function() {
                    const d = JSON.parse(this.dataset.customer);
                    selectCustomer(d.id, d.name, d.phone, d.email);
                });
            });
            container.querySelectorAll('.csr-create').forEach(el => {
                el.addEventListener('click', function() {
                    openQuickCustomerCreate(this.dataset.term);
                });
            });
            container.classList.add('show');
        } catch (e) {
            console.error('Customer search error', e);
        }
    }

    function selectCustomer(id, name, phone, email) {
        selectedCustomerId = id;
        document.getElementById('customerName').value = name;
        document.getElementById('customerPhone').value = phone;
        document.getElementById('customerEmail').value = email;
        document.getElementById('customerSearchResults').classList.remove('show');
        document.getElementById('customerBadge').innerHTML = `<span class="customer-badge" style="cursor:pointer;" onclick="clearCustomer()">
            <i class="fas fa-user"></i> ${escHtml(name)} <i class="fas fa-times" style="font-size:.55rem;margin-right:2px;"></i>
        </span>`;
        document.getElementById('customerBadge').style.display = 'block';
    }

    function clearCustomer() {
        selectedCustomerId = null;
        document.getElementById('customerName').value = '';
        document.getElementById('customerPhone').value = '';
        document.getElementById('customerEmail').value = '';
        document.getElementById('customerBadge').style.display = 'none';
        document.getElementById('customerBadge').innerHTML = '';
    }

    function openQuickCustomerCreate(name) {
        const newName = prompt('اسم العميل الجديد:', name || '');
        if (!newName || newName.trim() === '') return;
        const phone = prompt('رقم الهاتف (اختياري):', '');
        createQuickCustomer(newName.trim(), phone || '');
    }

    async function createQuickCustomer(name, phone) {
        try {
            const resp = await fetch('{{ route('admin.pos.createCustomer') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ name, phone }),
            });
            const data = await resp.json();
            if (data.success) {
                selectCustomer(data.customer.id, data.customer.name, data.customer.phone, data.customer.email || '');
                showToast('تم إنشاء العميل: ' + data.customer.name);
            } else {
                showToast('فشل إنشاء العميل', true);
            }
        } catch (e) {
            showToast('خطأ في الاتصال', true);
        }
    }

    // ========== QUICK PRODUCT CREATE ==========
    function quickCreateProduct() {
        document.getElementById('qpName').value = '';
        document.getElementById('qpPrice').value = '';
        document.getElementById('qpSku').value = '';
        document.getElementById('qpBarcode').value = '';
        document.getElementById('qpCategory').value = '';
        new bootstrap.Modal(document.getElementById('quickProductModal')).show();
    }

    async function confirmQuickProduct() {
        const name = document.getElementById('qpName').value.trim();
        const price = parseFloat(document.getElementById('qpPrice').value);
        const sku = document.getElementById('qpSku').value.trim();
        const barcode = document.getElementById('qpBarcode').value.trim();
        const categoryId = document.getElementById('qpCategory').value;

        if (!name) { showToast('يرجى إدخال اسم المنتج', true); return; }
        if (isNaN(price) || price <= 0) { showToast('يرجى إدخال سعر صحيح', true); return; }

        try {
            const resp = await fetch('{{ route('admin.pos.quickProduct') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ name_ar: name, b2c_price: price, sku: sku || null, barcode: barcode || null, category_id: categoryId || null }),
            });
            const data = await resp.json();
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('quickProductModal')).hide();
                // Add directly to cart
                const dummyCard = {
                    dataset: { id: data.product.id },
                    querySelector: (s) => {
                        if (s === '.pp-name') return { textContent: data.product.name };
                        if (s === '.pp-price') return { textContent: data.product.price };
                        return { textContent: '' };
                    },
                    getAttribute: (a) => a === 'data-price' ? data.product.price : null,
                };
                window.productCache = window.productCache || {};
                window.productCache[data.product.id] = data.product;
                addToCart(dummyCard);
                showToast('تم إضافة المنتج: ' + data.product.name);

                // Reload products grid
                searchProducts();
            } else {
                showToast(data.message || 'فشل إضافة المنتج', true);
            }
        } catch (e) {
            showToast('خطأ في الاتصال', true);
        }
    }

    // ========== FAVORITES BAR ==========
    async function loadQuickBar() {
        const container = document.getElementById('quickBarItems');
        try {
            const resp = await fetch('{{ route('admin.pos.getFavorites') }}', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await resp.json();
            // Mark favorite buttons as active
            const favIds = data.products ? data.products.map(p => p.id) : [];
            document.querySelectorAll('.fav-btn').forEach(btn => {
                const pid = parseInt(btn.dataset.id);
                btn.classList.toggle('active', favIds.includes(pid));
                btn.innerHTML = favIds.includes(pid) ? '<i class="fas fa-heart"></i>' : '<i class="far fa-heart"></i>';
            });
            if (!data.products || data.products.length === 0) {
                container.innerHTML = `<span style="font-size:.7rem;color:var(--gray-400);">لا توجد منتجات مفضلة. انقر على <i class="fas fa-heart" style="color:#f43f5e;"></i> لإضافة منتج.</span>`;
                return;
            }
            let html = '';
            data.products.forEach(p => {
                const img = p.image ? `<img src="${p.image}" class="qb-img">` : `<i class="fas fa-box" style="font-size:.65rem;color:var(--gray-400);"></i>`;
                html += `<div class="qb-item" onclick="quickAddFavorite(${p.id})" title="${p.name}">
                    ${img}
                    <span>${p.name.length > 15 ? p.name.substring(0, 15) + '..' : p.name}</span>
                    <span class="qb-price">₪${parseFloat(p.price).toFixed(2)}</span>
                </div>`;
            });
            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = `<span style="font-size:.7rem;color:var(--gray-400);">خطأ في تحميل المفضلة</span>`;
        }
    }

    async function toggleFavorite(productId) {
        try {
            const resp = await fetch('{{ route('admin.pos.toggleFavorite') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ product_id: productId }),
            });
            const data = await resp.json();
            if (data.success) {
                showToast(data.added ? 'تمت الإضافة للمفضلة' : 'تمت الإزالة من المفضلة');
                loadQuickBar();
                // Update heart icon state
                document.querySelectorAll(`.fav-btn[data-id="${productId}"]`).forEach(el => {
                    el.classList.toggle('active', data.added);
                    el.innerHTML = data.added ? '<i class="fas fa-heart"></i>' : '<i class="far fa-heart"></i>';
                });
            }
        } catch (e) {
            showToast('خطأ', true);
        }
    }

    function quickAddFavorite(productId) {
        let card = document.querySelector(`.pos-product-card[data-id="${productId}"]`);
        if (card) { addToCart(card); return; }
        const cached = window.productCache && window.productCache[productId];
        if (cached) {
            if (cart.some(i => i.product_id === cached.id)) {
                const existing = cart.find(i => i.product_id === cached.id);
                existing.quantity++;
                renderCart();
                showToast('تم زيادة الكمية');
                return;
            }
            cart.push({
                product_id: cached.id, name: cached.name, price: parseFloat(cached.price),
                quantity: 1, stock: cached.stock || 999, image: cached.image || '',
                sku: cached.sku || '', track_inventory: true,
                itemId: 'ci_' + Date.now() + '_', itemDiscount: 0, itemDiscountType: 'fixed', staffId: null,
            });
            renderCart();
            playScanBeep();
            showToast('تمت الإضافة');
            return;
        }
        fetch('{{ route('admin.pos.products.search') }}?q=' + productId, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.products && data.products[0]) {
                const p = data.products[0];
                window.productCache = window.productCache || {};
                window.productCache[p.id] = p;
                quickAddFavorite(productId);
            } else {
                showToast('المنتج غير متوفر', true);
            }
        })
        .catch(() => showToast('خطأ في تحميل المنتج', true));
    }

    // ========== PRICE OVERRIDE ==========
    function togglePriceOverride(itemId) {
        const display = document.getElementById(`price-display-${itemId}`);
        const input = document.getElementById(`price-input-${itemId}`);
        if (display.style.display === 'none') {
            display.style.display = '';
            input.style.display = 'none';
        } else {
            display.style.display = 'none';
            input.style.display = 'inline-block';
            input.focus();
            input.select();
        }
    }

    function confirmPriceOverride(itemId) {
        const input = document.getElementById(`price-input-${itemId}`);
        const newPrice = parseFloat(input.value);
        if (isNaN(newPrice) || newPrice <= 0) {
            showToast('سعر غير صالح', true);
            return;
        }
        const item = cart.find(i => i.itemId === itemId);
        if (item) {
            const oldPrice = item.price;
            item.price = newPrice;
            item.priceOverridden = true;
            item.overrideNote = prompt('سبب تغيير السعر (اختياري):', 'تعديل يدوي') || 'تعديل يدوي';
            renderCart();
            highlightCartItem(itemId);
            showToast(`تم تغيير السعر من ₪${oldPrice.toFixed(2)} إلى ₪${newPrice.toFixed(2)}`);
        }
    }

    // ========== PER-ITEM DISCOUNT ==========
    function toggleItemDiscount(itemId) {
        const area = document.getElementById('itemDiscountArea-' + itemId);
        area.classList.toggle('show');
        if (area.classList.contains('show')) {
            document.getElementById('itemDiscountVal-' + itemId).focus();
        }
    }

    function confirmItemDiscount(itemId) {
        const val = parseFloat(document.getElementById(`itemDiscountVal-${itemId}`).value) || 0;
        const type = document.getElementById(`itemDiscountType-${itemId}`).value;
        const item = cart.find(i => i.itemId === itemId);
        if (item) {
            if (val <= 0) {
                item.itemDiscount = 0;
            } else {
                item.itemDiscount = val;
                item.itemDiscountType = type;
            }
            renderCart();
            highlightCartItem(itemId);
            if (val > 0) {
                showToast(`خصم ${type === 'percent' ? val + '%' : '₪' + val.toFixed(2)} على ${item.name}`);
            } else {
                showToast('تم إلغاء الخصم');
            }
        }
    }

    function clearItemDiscount(itemId) {
        const item = cart.find(i => i.itemId === itemId);
        if (item) {
            item.itemDiscount = 0;
            item.itemDiscountType = 'fixed';
            document.getElementById(`itemDiscountArea-${itemId}`).classList.remove('show');
            renderCart();
            highlightCartItem(itemId);
            showToast('تم إلغاء خصم المنتج');
        }
    }

    // ========== SPLIT PAYMENT ==========
    let splitPayments = [];

    function toggleSplitPayment() {
        const area = document.getElementById('splitPaymentArea');
        const btn = document.getElementById('splitPaymentToggle');
        if (area.style.display === 'none' || !area.style.display) {
            area.style.display = 'block';
            btn.classList.add('active');
            initSplitPayment();
        } else {
            area.style.display = 'none';
            btn.classList.remove('active');
            splitPayments = [];
        }
    }

    function initSplitPayment() {
        const total = cart.reduce((sum, i) => sum + i.price * i.quantity, 0);
        const discount = calculateDiscount(total);
        const itemDiscountTotal = cart.reduce((sum, i) => {
            const lineTotal = i.price * i.quantity;
            return sum + (i.itemDiscountType === 'percent' ? lineTotal * (i.itemDiscount / 100) : Math.min(i.itemDiscount || 0, lineTotal));
        }, 0);
        const taxAmt = taxEnabled ? total * (parseFloat(taxRateSelect.value) || 0) : 0;
        const netTotal = total - discount - itemDiscountTotal + taxAmt;
        const container = document.getElementById('splitPaymentMethods');
        const methods = [
            { key: 'cash', label: 'نقداً', icon: 'fa-money-bill-wave' },
            { key: 'card', label: 'بطاقة', icon: 'fa-credit-card' },
            { key: 'transfer', label: 'تحويل', icon: 'fa-exchange-alt' },
        ];
        let html = '';
        methods.forEach(m => {
            const existing = splitPayments.find(sp => sp.method === m.key);
            const val = existing ? existing.amount : 0;
            html += `<div style="display:flex;align-items:center;gap:.35rem;margin-bottom:.25rem;">
                <i class="fas ${m.icon}" style="font-size:.7rem;color:var(--gray-500);width:16px;"></i>
                <span style="font-size:.72rem;width:50px;">${m.label}</span>
                <input type="number" class="form-control" style="width:80px;height:28px;font-size:.72rem;" value="${val}" min="0" step="5" data-method="${m.key}" onchange="updateSplitPayment(this)">
                <span style="font-size:.68rem;color:var(--gray-400);">₪</span>
            </div>`;
        });
        container.innerHTML = html;
        updateSplitTotal(netTotal);
    }

    function updateSplitPayment(input) {
        const method = input.dataset.method;
        const amount = parseFloat(input.value) || 0;
        const existing = splitPayments.findIndex(sp => sp.method === method);
        if (existing >= 0) {
            if (amount > 0) splitPayments[existing].amount = amount;
            else splitPayments.splice(existing, 1);
        } else if (amount > 0) {
            splitPayments.push({ method, amount });
        }
        const total = cart.reduce((sum, i) => sum + i.price * i.quantity, 0);
        const discount = calculateDiscount(total);
        const itemDiscountTotal = cart.reduce((sum, i) => {
            const lineTotal = i.price * i.quantity;
            return sum + (i.itemDiscountType === 'percent' ? lineTotal * (i.itemDiscount / 100) : Math.min(i.itemDiscount || 0, lineTotal));
        }, 0);
        const taxAmt = taxEnabled ? total * (parseFloat(taxRateSelect.value) || 0) : 0;
        const netTotal = total - discount - itemDiscountTotal + taxAmt;
        updateSplitTotal(netTotal);
    }

    function updateSplitTotal(netTotal) {
        const paid = splitPayments.reduce((sum, sp) => sum + sp.amount, 0);
        const remaining = netTotal - paid;
        document.getElementById('splitPaymentTotal').innerHTML =
            `<span style="color:var(--gray-600);">المدفوع: <strong>₪${paid.toFixed(2)}</strong></span>` +
            (remaining > 0.01 ? ` <span style="color:#dc3545;">المتبقي: ₪${remaining.toFixed(2)}</span>` :
             remaining < -0.01 ? ` <span style="color:#16a34a;">الباقي: ₪${Math.abs(remaining).toFixed(2)}</span>` :
             ` <span style="color:#16a34a;font-weight:600;">✅ مغطى بالكامل</span>`);
    }

    // ========== REFUND ==========
    let refundSaleId = null;

    function openRefund(saleId) {
        refundSaleId = saleId;
        document.getElementById('refundSaleId').value = 'فاتورة: ' + saleId;
        document.getElementById('refundReason').value = '';
        const container = document.getElementById('refundItemsContainer');
        container.innerHTML = '<div class="text-center py-3 text-muted" id="refundLoading"><i class="fas fa-spinner fa-spin"></i> جاري تحميل المنتجات...</div>';
        document.getElementById('confirmRefundBtn').disabled = false;
        document.getElementById('confirmRefundBtn').innerHTML = '<i class="fas fa-check-circle"></i> تأكيد الإرجاع';

        // Fetch sale details
        fetch('{{ route('admin.pos.getSale', '') }}/' + encodeURIComponent(saleId), {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.sale && data.sale.items) {
                let html = '<label class="form-label fw-bold small">حدد الكميات للإرجاع</label>';
                data.sale.items.forEach((item, idx) => {
                    const name = item.name || item.product_name || 'منتج';
                    const qty = item.quantity || 1;
                    const price = item.price || 0;
                    html += `<div class="refund-item">
                        <span style="flex:1;font-size:.8rem;">${name} (₪${parseFloat(price).toFixed(2)})</span>
                        <span style="font-size:.7rem;color:var(--gray-400);">الحد الأقصى: ${qty}</span>
                        <input type="number" class="refund-qty" value="${qty}" min="0" max="${qty}" data-product-id="${item.product_id || item.id || idx}">
                    </div>`;
                });
                html += '<div style="margin-top:.5rem;font-size:.7rem;color:var(--gray-400);"><i class="fas fa-info-circle"></i> اضبط الكمية المراد إرجاعها لكل منتج (0 = لا تريد إرجاعه)</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-center py-3 text-danger"><i class="fas fa-exclamation-circle"></i> لا توجد بيانات للمنتجات</div>';
            }
        })
        .catch(() => {
            container.innerHTML = '<div class="text-center py-3 text-danger"><i class="fas fa-exclamation-circle"></i> فشل تحميل بيانات الفاتورة</div>';
        });

        new bootstrap.Modal(document.getElementById('refundModal')).show();
    }

    async function confirmRefund() {
        const items = [];
        document.querySelectorAll('.refund-qty').forEach(input => {
            const qty = parseInt(input.value) || 0;
            if (qty > 0) {
                items.push({ product_id: parseInt(input.dataset.productId), quantity: qty });
            }
        });

        if (items.length === 0) {
            showToast('يرجى تحديد منتج واحد على الأقل للإرجاع', true);
            return;
        }

        const btn = document.getElementById('confirmRefundBtn');
        btn.disabled = true;
        btn.innerHTML = '<div class="spinner-border spinner-border-sm"></div> جاري...';

        try {
            const resp = await fetch('{{ route('admin.pos.refund') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({
                    sale_id: refundSaleId,
                    items: items,
                    reason: document.getElementById('refundReason').value.trim() || null,
                }),
            });
            const data = await resp.json();
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('refundModal')).hide();
                showToast('تم الإرجاع بنجاح! مرجع: ' + (data.data ? data.data.pos_sale_id : ''));
                loadRecentSales();
                updateStats();
            } else {
                showToast(data.message || 'فشل الإرجاع', true);
            }
        } catch (e) {
            showToast('خطأ في الاتصال', true);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> تأكيد الإرجاع';
        }
    }

    // Confetti celebration on sale
    function showConfetti() {
        const colors = ['#db2777', '#f59e0b', '#22c55e', '#3b82f6', '#a855f7', '#ef4444', '#06b6d4', '#84cc16'];
        const canvas = document.getElementById('confettiCanvas') || (function() {
            const c = document.createElement('canvas');
            c.id = 'confettiCanvas';
            c.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:99999;';
            document.body.appendChild(c);
            return c;
        })();
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        const pieces = [];
        for (let i = 0; i < 80; i++) {
            pieces.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height - canvas.height,
                w: Math.random() * 10 + 5,
                h: Math.random() * 8 + 3,
                color: colors[Math.floor(Math.random() * colors.length)],
                vx: (Math.random() - 0.5) * 4,
                vy: Math.random() * 4 + 2,
                rot: Math.random() * 360,
                rv: (Math.random() - 0.5) * 10,
            });
        }
        let frames = 0;
        function draw() {
            if (frames > 240) { canvas.remove(); return; }
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            pieces.forEach(p => {
                p.x += p.vx;
                p.vy += 0.06;
                p.y += p.vy;
                p.rot += p.rv;
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate(p.rot * Math.PI / 180);
                ctx.globalAlpha = Math.max(0, 1 - frames / 240);
                ctx.fillStyle = p.color;
                ctx.fillRect(-p.w/2, -p.h/2, p.w, p.h);
                ctx.restore();
            });
            frames++;
            requestAnimationFrame(draw);
        }
        draw();
        setTimeout(() => { const el = document.getElementById('confettiCanvas'); if (el) el.remove(); }, 8000);
    }

    // Auto-refresh recent sales every 30s (pause when tab hidden)
    const pollInterval = setInterval(() => {
        if (!document.hidden) loadRecentSales();
    }, 30000);
    window.addEventListener('beforeunload', () => clearInterval(pollInterval));

    // Init
    loadQuickBar();
    renderCartTabs();
    applyRevenueVisibility();
    updateTaxUI();

    // Load Quick-Pick Grid

    // Online/Offline event listeners
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    if (!navigator.onLine) updateOnlineStatus();

    // Delegate click for empty-state category buttons (rendered dynamically)
    document.getElementById('productsGrid').addEventListener('click', function(e) {
        const btn = e.target.closest('.empty-cat-btn');
        if (btn && btn.dataset.cat) selectCategoryByVal(btn.dataset.cat);
    });

    // Init receipt size pills from saved settings
    (function initReceiptPills() {
        const s = loadPrintSettings();
        syncReceiptSizePills(s.receiptSize);
    })();
</script>
@endpush
