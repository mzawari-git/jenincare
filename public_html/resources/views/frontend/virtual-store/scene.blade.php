@extends($layoutPath ?? 'frontend.layouts.organic-spa.app')

@section('title', $scene->name_ar)
@section('meta_description', $scene->description_ar)

@section('content')
<div class="virtual-store-container" x-data="virtualStore()">
    <div class="position-relative" style="height: 100vh; width: 100%;">
        <div id="panorama" style="width: 100%; height: 100%;"></div>

        <!-- Top toolbar -->
        <div class="position-absolute top-0 start-0 end-0 p-3" style="z-index: 10; background: linear-gradient(180deg, rgba(0,0,0,0.6) 0%, transparent 100%);">
            <div class="container d-flex align-items-center justify-content-between">
                <a href="{{ route('virtual-store.index') }}" class="btn btn-sm btn-light rounded-pill px-3">
                    <i class="fas fa-arrow-right ms-1"></i>
                    العودة للمتجر
                </a>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-map ms-1"></i>
                        {{ $scene->name_ar }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @foreach($scenes as $s)
                        <li>
                            <a class="dropdown-item {{ $s->id === $scene->id ? 'active' : '' }}"
                               href="{{ route('virtual-store.scene', $s->slug) }}">
                                @if($s->thumbnail)
                                <img src="{{ $s->thumbnail }}" alt="" style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px;" class="ms-2">
                                @endif
                                {{ $s->name_ar }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Scene info -->
        <div class="position-absolute bottom-0 start-0 end-0 p-3" style="z-index: 10; background: linear-gradient(0deg, rgba(0,0,0,0.6) 0%, transparent 100%);">
            <div class="container">
                <div class="d-flex align-items-end justify-content-between text-white">
                    <div>
                        <h4 class="mb-1">{{ $scene->name_ar }}</h4>
                        @if($scene->name_en)
                        <small class="text-white-50">{{ $scene->name_en }}</small>
                        @endif
                        @if($scene->description_ar)
                        <p class="mb-0 small text-white-50 mt-1">{{ $scene->description_ar }}</p>
                        @endif
                    </div>
                    <div class="text-end">
                        @if($scene->section)
                        <span class="badge bg-pink ms-1">{{ $scene->section }}</span>
                        @endif
                        @if($scene->aisle)
                        <span class="badge bg-dark ms-1">{{ $scene->aisle }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Product popup card -->
        <div class="position-absolute" style="z-index: 20; display: none;" id="product-popup"
             :style="{ display: showProduct ? 'block' : 'none', top: productPopupY + 'px', left: productPopupX + 'px' }">
            <div class="card shadow-lg border-0" style="width: 260px; border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex mb-2">
                        <img :src="product.image" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;" class="ms-2">
                        <div class="flex-grow-1">
                            <h6 class="mb-1" x-text="product.name"></h6>
                            <div class="d-flex align-items-center">
                                <span class="fw-bold text-pink ms-1" x-text="product.price"></span>
                                <span class="text-muted small">ريال</span>
                            </div>
                            <small class="text-danger" x-show="product.isOnSale" x-cloak>خصم</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-pink btn-sm flex-grow-1" @click="addToCart(product.id, $event)">
                            <i class="fas fa-cart-plus ms-1"></i>
                            أضف للسلة
                        </button>
                        <a :href="product.url" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css" />
<style>
[x-cloak] { display: none !important; }
.btn-pink {
    background-color: #e91e63;
    border-color: #e91e63;
    color: white;
}
.btn-pink:hover {
    background-color: #c2185b;
    border-color: #c2185b;
    color: white;
}
.bg-pink { background-color: #e91e63 !important; }
.text-pink { color: #e91e63; }
.virtual-store-container {
    margin-top: -1px;
    background: #000;
}
#panorama {
    cursor: grab;
}
#panorama:active {
    cursor: grabbing;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
<script src="{{ asset('js/virtual-store.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hotspots = @json($scene->hotspots->map(function($h) {
        return [
            'id' => $h->id,
            'pitch' => (float)$h->pitch,
            'yaw' => (float)$h->yaw,
            'label' => $h->label_ar ?: ($h->product?->name ?? ''),
            'product_id' => $h->product_id,
            'product_name' => $h->product?->name ?? '',
            'product_price' => $h->product?->getCurrentPrice() ?? 0,
            'product_image' => $h->product?->main_image_url ?? '',
            'product_slug' => $h->product?->slug ?? '',
            'is_on_sale' => $h->product?->is_on_sale ?? false,
        ];
    }));

    const connections = @json($scene->connectionsFrom->map(function($c) {
        return [
            'id' => $c->id,
            'to_scene_id' => $c->to_scene_id,
            'to_scene_slug' => $c->toScene?->slug ?? '',
            'to_scene_name' => $c->toScene?->name_ar ?? '',
            'direction' => $c->direction,
            'label_ar' => $c->label_ar,
        ];
    }));

    window.initVirtualStore(@json($scene->image_path), hotspots, connections);
});
</script>
@endpush
