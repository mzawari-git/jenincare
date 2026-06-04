@extends($layoutPath)

@section('title', $product->meta_title ?: ($product->name_ar . ' - ' . ($siteSettings['site_name'] ?? 'شركة جنين للتجميل')))
@section('meta_description', $product->meta_description ?: ($product->short_description_ar ?? $product->description_ar ?? ''))

@if($product->meta_keywords)
@php
    $kws = $product->meta_keywords;
    if (is_string($kws)) { $decoded = json_decode($kws, true); if (is_array($decoded)) $kws = implode(', ', $decoded); }
@endphp
@section('meta_keywords', $kws)
@endif

@if($product->og_image) @section('og_image', $product->og_image) @endif
@if($product->slug) @section('canonical_url', url('/product/' . $product->slug)) @endif

@section('content')
{{-- Breadcrumb --}}
<section class="pt-28 pb-2 border-b border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-2 text-sm text-white-dim flex-wrap">
            <a href="{{ route('home') }}" class="hover:text-brand-500 transition-colors">الرئيسية</a>
            <i class="ph ph-caret-left text-xs"></i>
            @if($product->category)
            <a href="{{ route('shop', ['category' => $product->category->slug]) }}" class="hover:text-brand-500 transition-colors">{{ $product->category->name_ar }}</a>
            <i class="ph ph-caret-left text-xs"></i>
            @endif
            <span class="text-brand-500 font-medium">{{ Str::limit($product->name_ar, 40) }}</span>
        </nav>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-20">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        {{-- Product Images --}}
        <div>
            <div class="rounded-3xl overflow-hidden relative border border-white/5 glass-panel group">
                @if($product->main_image)
                <img src="{{ $product->optimizedImageUrl(800, 800) }}" alt="{{ $product->name_ar }}" width="800" height="800" class="w-full aspect-square object-contain transition-transform duration-500 group-hover:scale-105 filter brightness-90" id="mainProductImage" onerror="this.outerHTML='<div class=&quot;w-full aspect-square flex items-center justify-center bg-surface-alt&quot;><i class=&quot;ph ph-image text-6xl text-white/10&quot;></i></div>'">
                @else
                <div class="w-full aspect-square flex items-center justify-center bg-surface-alt"><i class="ph ph-image text-6xl text-white/10"></i></div>
                @endif
                <div class="scanner-line" style="background:var(--brand-500); box-shadow:var(--neon-glow-strong);"></div>
                @if($product->is_on_sale)
                <span class="absolute top-4 right-4 bg-red-500 text-white text-sm font-bold px-4 py-1.5 rounded-full shadow-lg z-10">-{{ $product->discount_percentage_display }}%</span>
                @endif
                @if(!$product->isInStock())
                <div class="absolute inset-0 bg-black/60 flex items-center justify-center z-10 backdrop-blur-sm">
                    <span class="glass-panel text-white px-6 py-2.5 rounded-full font-bold text-sm">نفذت الكمية</span>
                </div>
                @endif
            </div>
            @if($product->gallery_images && count($product->gallery_images) > 0)
            <div class="flex gap-3 mt-4 overflow-x-auto pb-2 hide-scroll">
                <button onclick="swapImage(this, '{{ $product->optimizedImageUrl(800, 800) }}')" class="gallery-thumb w-16 h-16 flex-shrink-0 rounded-xl overflow-hidden border-2 border-brand-500 shadow-neon">
                    <img src="{{ $product->optimizedImageUrl(100, 100) }}" width="100" height="100" class="w-full h-full object-contain">
                </button>
                @foreach($product->gallery_images as $image)
                <button onclick="swapImage(this, '{{ route('image.optimized', ['path' => $image]) }}?w=800&h=800&webp=1&q=85')" class="gallery-thumb w-16 h-16 flex-shrink-0 rounded-xl overflow-hidden border-2 border-transparent hover:border-brand-500/50 transition-all">
                    <img src="{{ route('image.optimized', ['path' => $image]) }}?w=100&h=100&webp=1&q=80" width="100" height="100" class="w-full h-full object-contain">
                </button>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Product Info --}}
        <div>
            @if($product->category)
            <span class="inline-block pill-brand text-xs font-bold mb-3">{{ $product->category->name_ar }}</span>
            @endif
            <h1 class="text-2xl lg:text-3xl font-extrabold text-white leading-tight mb-3">{{ $product->name_ar }}</h1>

            @if($product->average_rating > 0)
            <div class="flex items-center gap-2 mb-4">
                <div class="flex gap-0.5">
                    @for($i = 1; $i <= 5; $i++)
                    <i class="ph-fill ph-star text-sm {{ $i <= $product->average_rating ? 'text-amber-400' : 'text-white/10' }}"></i>
                    @endfor
                </div>
                <span class="text-xs text-white-dim">({{ $product->reviews_count }} تقييم)</span>
            </div>
            @endif

            <div class="py-5 border-b border-white/5 mb-5">
                @if($product->is_on_sale)
                <span class="text-base text-white-dim line-through ml-3">{{ number_format($product->b2c_price, 2) }} ₪</span>
                @endif
                <span class="text-3xl font-black text-brand-500 shadow-neon-text">{{ number_format($product->final_b2c_price ?? $product->b2c_price, 2) }} ₪</span>
            </div>

            @if($product->short_description_ar)
            <p class="text-white-dim leading-relaxed mb-6 text-sm">{{ $product->short_description_ar }}</p>
            @endif

            @if($product->isInStock())
            <div class="flex items-center gap-3 flex-wrap mb-6">
                <div class="flex items-center border border-white/10 rounded-xl overflow-hidden">
                    <button onclick="updateQuantity(-1)" class="px-4 py-3 hover:bg-white/5 transition-colors text-white font-bold">-</button>
                    <input type="number" id="qty" value="1" min="1" class="w-12 text-center border-x border-white/10 py-3 font-bold text-white bg-transparent focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                    <button onclick="updateQuantity(1)" class="px-4 py-3 hover:bg-white/5 transition-colors text-white font-bold">+</button>
                </div>
                <button onclick="addToCart({{ $product->id }}, null, this)" class="flex-1 min-w-[180px] py-3.5 px-8 bg-white style="color:#0f172a;" rounded-full font-bold hover:shadow-neon transition-all flex items-center justify-center gap-2">
                    <i class="ph ph-shopping-bag text-lg"></i> أضف للسلة
                </button>
                <a href="https://wa.me/{{ $siteSettings['whatsapp_number'] ?? '970591234567' }}?text={{ urlencode('السلام عليكم، مهتمة بـ: ' . $product->name_ar . ' - ' . number_format($product->final_b2c_price ?? $product->b2c_price, 2) . ' ₪') }}" target="_blank" class="py-3.5 px-6 border border-green-500/30 text-green-400 rounded-full font-bold hover:bg-green-500 hover:text-white transition-colors flex items-center gap-2 whitespace-nowrap">
                    <i class="ph ph-whatsapp-logo text-lg"></i> واتساب
                </a>
                @auth
                <button onclick="addToWishlist({{ $product->id }})" class="w-12 h-12 rounded-full border border-white/10 hover:border-brand-500 hover:text-brand-500 transition-colors flex items-center justify-center text-white-dim flex-shrink-0">
                    <i class="ph ph-heart text-xl"></i>
                </button>
                @endauth
            </div>
            @else
            <div class="bg-red-500/10 text-red-400 px-5 py-4 rounded-xl text-sm mb-6 flex items-center gap-3 border border-red-500/20">
                <i class="ph-fill ph-warning-circle text-xl"></i>
                <span>هذا المنتج غير متوفر حالياً في المخزون</span>
            </div>
            @endif

            {{-- Trust badges --}}
            <div class="grid grid-cols-2 gap-2">
                <div class="flex items-center gap-2 px-4 py-3 glass-panel rounded-xl text-sm text-white-dim">
                    <i class="ph ph-truck text-brand-500"></i> توصيل خلال {{ $product->estimated_delivery_days ?? 3 }} أيام
                </div>
                <div class="flex items-center gap-2 px-4 py-3 glass-panel rounded-xl text-sm text-white-dim">
                    <i class="ph ph-shield-check text-brand-500"></i> منتج أصلي 100%
                </div>
                @if($product->free_shipping)
                <div class="flex items-center gap-2 px-4 py-3 glass-panel rounded-xl text-sm text-white-dim">
                    <i class="ph ph-gift text-brand-500"></i> شحن مجاني
                </div>
                @endif
                <div class="flex items-center gap-2 px-4 py-3 glass-panel rounded-xl text-sm text-white-dim">
                    <i class="ph ph-arrow-counter-clockwise text-brand-500"></i> إرجاع خلال 14 يوم
                </div>
            </div>
        </div>
    </div>

    {{-- Description & Specs --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-10">
        @if($product->description_ar)
        <div class="lg:col-span-2 glass-panel rounded-2xl p-6 border-white/5">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="ph ph-text-align-right text-brand-500"></i> وصف المنتج
            </h3>
            <div class="text-white-dim leading-loose text-sm">{{ $product->description_ar }}</div>
        </div>
        @endif

        @if($product->specifications)
        <div class="glass-panel rounded-2xl p-6 border-white/5">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="ph ph-list-dashes text-brand-500"></i> المواصفات
            </h3>
            <div class="space-y-2">
                @foreach($product->specifications as $key => $value)
                <div class="flex px-3 py-2 bg-white/5 rounded-lg text-sm">
                    <span class="font-semibold text-white w-24 flex-shrink-0">{{ $key }}:</span>
                    <span class="text-white-dim">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Related Products --}}
    @if($relatedProducts->isNotEmpty())
    <section class="mt-16">
        <div class="flex items-center gap-4 mb-6">
            <h3 class="text-xl font-bold text-white">منتجات ذات صلة</h3>
            <div class="flex-1 h-px bg-white/5"></div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($relatedProducts as $related)
            <div class="group glass-panel rounded-2xl border-white/5 overflow-hidden hover:border-brand-500/20 transition-all duration-300">
                <a href="{{ route('product.show', $related->slug) }}" class="block aspect-square overflow-hidden">
                    @if($related->main_image)
                    <img src="{{ $related->optimizedImageUrl(400, 400) }}" alt="{{ $related->name_ar }}" width="400" height="400" loading="lazy" class="w-full h-full object-cover filter brightness-75 group-hover:brightness-100 group-hover:scale-105 transition-all duration-500" onerror="this.parentElement.innerHTML='<div class=&quot;w-full h-full flex items-center justify-center bg-surface-alt&quot;><i class=&quot;ph ph-image text-3xl text-white/10&quot;></i></div>'">
                    @else
                    <div class="w-full h-full flex items-center justify-center bg-surface-alt"><i class="ph ph-image text-3xl text-white/10"></i></div>
                    @endif
                </a>
                <div class="p-4">
                    <a href="{{ route('product.show', $related->slug) }}">
                        <h4 class="text-sm font-bold text-white leading-snug line-clamp-2 group-hover:text-brand-500 transition-colors">{{ $related->name_ar }}</h4>
                    </a>
                    <div class="mt-2">
                        <span class="text-base font-extrabold text-brand-500">{{ number_format($related->final_b2c_price ?? $related->b2c_price, 2) }} ₪</span>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button onclick="addToCart({{ $related->id }})" class="flex-1 py-2 bg-white style="color:#0f172a;" text-xs font-bold rounded-lg hover:bg-brand-500 hover:text-white transition-colors flex items-center justify-center gap-1">
                            <i class="ph ph-shopping-bag text-sm"></i> أضف للسلة
                        </button>
                        <a href="https://wa.me/{{ $siteSettings['whatsapp_number'] ?? '970591234567' }}?text={{ urlencode('السلام عليكم، مهتمة بـ: ' . $related->name_ar . ' - ' . number_format($related->final_b2c_price ?? $related->b2c_price, 2) . ' ₪') }}" target="_blank" class="py-2 px-2.5 border border-green-500/30 text-green-400 text-xs font-bold rounded-lg hover:bg-green-500 hover:text-white transition-all flex items-center justify-center" title="واتساب" aria-label="واتساب">
                            <i class="ph ph-whatsapp-logo"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif
</div>

{{-- Sticky Mobile Add to Cart --}}
@if($product->isInStock())
<div class="fixed bottom-0 left-0 right-0 z-40 lg:hidden p-3" style="background:rgba(5,5,5,0.95); border-top:1px solid var(--glass-border); backdrop-filter:blur(16px);">
    <div class="flex items-center gap-3">
        <button onclick="addToCart({{ $product->id }}, null, this)" class="flex-1 py-3.5 bg-white style="color:#0f172a;" rounded-full font-bold text-sm hover:bg-brand-500 hover:text-white transition-colors flex items-center justify-center gap-2">
            <i class="ph ph-shopping-bag text-lg"></i> أضف للسلة
        </button>
        <a href="https://wa.me/{{ $siteSettings['whatsapp_number'] ?? '970591234567' }}?text={{ urlencode('السلام عليكم، مهتمة بـ: ' . $product->name_ar . ' - ' . number_format($product->final_b2c_price ?? $product->b2c_price, 2) . ' ₪') }}" target="_blank" class="py-3.5 px-4 border border-green-500/30 text-green-400 rounded-full font-bold text-sm hover:bg-green-500 hover:text-white transition-colors flex items-center gap-1 flex-shrink-0">
            <i class="ph ph-whatsapp-logo text-lg"></i>
        </a>
    </div>
</div>
<div class="lg:hidden h-20"></div>
@endif

<script>
function swapImage(thumb, src) {
    document.getElementById('mainProductImage').src = src;
    document.querySelectorAll('.gallery-thumb').forEach(t => {
        t.classList.remove('border-brand-500', 'shadow-neon'); t.classList.add('border-transparent');
    });
    thumb.classList.add('border-brand-500', 'shadow-neon'); thumb.classList.remove('border-transparent');
}

function updateQuantity(delta) {
    const qtyEl = document.getElementById('qty');
    let val = parseInt(qtyEl.value) + delta;
    if (val < 1) val = 1;
    qtyEl.value = val;
}
</script>
@endsection
