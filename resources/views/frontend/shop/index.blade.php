@extends($layoutPath)

@section('title', 'المتجر - ' . ($siteSettings['site_name'] ?? 'JeniCare'))
@section('meta_description', 'تسوق أفضل منتجات العناية بالشعر والبشرة من JeniCare. توصيل سريع لجميع أنحاء فلسطين.')

@section('content')
{{-- Page Header --}}
<section class="pt-32 pb-10 relative border-b border-white/5">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_30%_0%,rgba(var(--brand-500-rgb,255,42,133),0.06),transparent_60%)] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-sm text-white-dim mb-3">
                    <a href="{{ route('home') }}" class="hover:text-brand-500 transition-colors">الرئيسية</a>
                    <i class="ph ph-caret-left text-xs"></i>
                    <span class="text-white font-medium">المتجر</span>
                </nav>
                <h1 class="text-3xl font-extrabold text-white">المتجر</h1>
                <p class="text-white-dim mt-1">تصفحي مجموعتنا المتنوعة من منتجات العناية</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <div class="flex items-center gap-2 glass-panel rounded-full px-4 py-2 border-white/5">
                    <i class="ph-fill ph-package text-brand-500"></i>
                    <span class="text-sm font-bold text-white">{{ $products->total() }}</span>
                    <span class="text-xs text-white-dim">منتج</span>
                </div>
                <div class="flex items-center gap-2 glass-panel rounded-full px-4 py-2 border-white/5">
                    <i class="ph-fill ph-squares-four text-accent-500"></i>
                    <span class="text-sm font-bold text-white">{{ $categories->count() }}</span>
                    <span class="text-xs text-white-dim">قسم</span>
                </div>
                <div class="flex items-center gap-2 glass-panel rounded-full px-4 py-2 border-white/5">
                    <i class="ph-fill ph-truck text-green-400"></i>
                    <span class="text-xs text-white-dim">شحن مجاني فوق {{ $siteSettings['free_shipping_min'] ?? '200' }} ₪</span>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-20">
    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Sidebar Filters --}}
        @if(isset($categories))
        <aside class="lg:w-64 flex-shrink-0">
            <div class="glass-panel rounded-2xl p-5 lg:sticky lg:top-32 border-white/5">
                <h3 class="text-sm font-bold text-white mb-4 pb-3 border-b border-white/5 flex items-center gap-2">
                    <i class="ph ph-funnel text-brand-500"></i> الأقسام
                </h3>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('shop') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm transition-all duration-200 {{ !request('category') ? 'bg-brand-500 text-white font-bold shadow-neon' : 'text-white-dim hover:bg-white/5 hover:text-white' }}">
                            <span>الكل</span>
                            <span class="text-[11px] {{ !request('category') ? 'bg-white/20' : 'bg-white/5' }} px-2 py-0.5 rounded-full">{{ $products->total() }}</span>
                        </a>
                    </li>
                    @foreach($categories as $category)
                    @php
                        $arName = preg_replace('/\s{2,}/', ' ', trim(preg_replace('/[a-zA-Z&\-\(\)]+/', '', $category->name_ar)));
                        $arName = !empty($arName) ? $arName : $category->name_ar;
                    @endphp
                    <li>
                        <a href="{{ route('shop', ['category' => $category->slug]) }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm transition-all duration-200 {{ request('category') == $category->slug ? 'bg-brand-500 text-white font-bold shadow-neon' : 'text-white-dim hover:bg-white/5 hover:text-white' }}">
                            <span>{{ $arName }}</span>
                            <span class="text-[11px] {{ request('category') == $category->slug ? 'bg-white/20' : 'bg-white/5' }} px-2 py-0.5 rounded-full">{{ $category->products_count ?? 0 }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>

                <h3 class="text-sm font-bold text-white mt-6 mb-4 pb-3 border-b border-white/5 flex items-center gap-2">
                    <i class="ph ph-currency-circle-dollar text-brand-500"></i> السعر
                </h3>
                <form action="{{ route('shop') }}" method="GET">
                    @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                    @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                    <div class="flex gap-2">
                        <input type="number" name="min_price" placeholder="من" value="{{ request('min_price') }}" class="flex-1 w-full bg-white/5 border border-white/10 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 transition-all placeholder:text-white-dim">
                        <input type="number" name="max_price" placeholder="إلى" value="{{ request('max_price') }}" class="flex-1 w-full bg-white/5 border border-white/10 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 transition-all placeholder:text-white-dim">
                        <button type="submit" class="text-white px-3 rounded-lg hover:shadow-neon transition-all" style="background: var(--gradient-primary);"><i class="ph ph-magnifying-glass"></i></button>
                    </div>
                </form>
            </div>
        </aside>
        @endif

        {{-- Products Area --}}
        <div class="flex-1 min-w-0">
            {{-- Toolbar --}}
            <div class="flex justify-between items-center flex-wrap gap-3 mb-6 glass-panel rounded-2xl px-5 py-3 border-white/5 sticky top-[80px] z-20">
                <p class="text-sm text-white-dim flex items-center gap-2">
                    <i class="ph-fill ph-package text-brand-500"></i>
                    <strong class="text-white">{{ $products->total() }}</strong> منتج
                    @if(request('category') && isset($selectedCategory))
                    <span>في <strong class="text-brand-500">{{ $selectedCategory->name_ar }}</strong></span>
                    @endif
                </p>
                <div class="flex items-center gap-3">
                    <div class="flex border border-white/10 rounded-lg overflow-hidden">
                        <button id="viewGrid" onclick="setViewMode('grid')" title="عرض شبكي" class="w-9 h-9 flex items-center justify-center bg-white text-surface transition-all">
                            <i class="ph ph-squares-four"></i>
                        </button>
                        <button id="viewList" onclick="setViewMode('list')" title="عرض قائمة" class="w-9 h-9 flex items-center justify-center bg-transparent text-white-dim transition-all">
                            <i class="ph ph-list"></i>
                        </button>
                    </div>
                    <select onchange="window.location.href=this.value" class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-brand-500 cursor-pointer [&>option]:bg-surface-alt [&>option]:text-white">
                        <option value="{{ route('shop', array_merge(request()->except('sort'), ['sort' => 'newest'])) }}" {{ request('sort') == 'newest' || !request('sort') ? 'selected' : '' }}>الأحدث</option>
                        <option value="{{ route('shop', array_merge(request()->except('sort'), ['sort' => 'price_low'])) }}" {{ request('sort') == 'price_low' ? 'selected' : '' }}>السعر: من الأقل</option>
                        <option value="{{ route('shop', array_merge(request()->except('sort'), ['sort' => 'price_high'])) }}" {{ request('sort') == 'price_high' ? 'selected' : '' }}>السعر: من الأعلى</option>
                        <option value="{{ route('shop', array_merge(request()->except('sort'), ['sort' => 'popular'])) }}" {{ request('sort') == 'popular' ? 'selected' : '' }}>الأكثر مبيعاً</option>
                    </select>
                </div>
            </div>

            @if($products->isEmpty())
            <div class="text-center py-20">
                <div class="w-24 h-24 rounded-3xl glass-panel flex items-center justify-center mx-auto mb-6">
                    <i class="ph ph-package text-5xl text-white-dim"></i>
                </div>
                <h3 class="text-2xl font-extrabold text-white mb-3">لا توجد منتجات</h3>
                <p class="text-white-dim mb-8 max-w-md mx-auto leading-relaxed">لم نجد منتجات تطابق بحثك. جربي تصفية مختلفة أو تصفحي جميع الأقسام.</p>
                <a href="{{ route('shop') }}" class="inline-flex items-center gap-3 px-8 py-3.5 text-white rounded-full font-bold transition-all duration-300 shadow-neon hover:shadow-neon-strong" style="background: var(--gradient-primary);">
                    <i class="ph ph-arrow-left"></i> عرض كل المنتجات
                </a>
            </div>
            @else

            {{-- Grid View --}}
            <div id="productsGrid" class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($products as $product)
                <div class="product-grid-item group glass-panel rounded-2xl border-white/5 overflow-hidden hover:border-brand-500/20 transition-all duration-300">
                    <a href="{{ route('product.show', $product->slug) }}" class="block relative aspect-square overflow-hidden">
                        @if($product->main_image_url)
                        <img src="{{ $product->main_image_url }}" alt="{{ $product->name_ar }}" loading="lazy" class="w-full h-full object-cover filter brightness-75 group-hover:brightness-100 group-hover:scale-105 transition-all duration-500" onerror="this.parentElement.innerHTML='<div class=&quot;w-full h-full flex items-center justify-center bg-surface-alt&quot;><i class=&quot;ph ph-image text-4xl text-white/10&quot;></i></div>'">
                        @else
                        <div class="w-full h-full flex items-center justify-center bg-surface-alt"><i class="ph ph-image text-4xl text-white/10"></i></div>
                        @endif
                        @if($product->is_on_sale)
                        <span class="absolute top-3 right-3 bg-red-500 text-white text-[11px] font-bold px-2.5 py-1 rounded-full">خصم {{ $product->discount_percentage_display ?? '' }}%</span>
                        @elseif($product->is_new)
                        <span class="absolute top-3 right-3 pill-brand text-[11px] font-bold px-2.5 py-1 rounded-full">جديد</span>
                        @endif
                        @php $qty = $product->stock_quantity ?? 0; @endphp
                        @if($qty <= 0)
                        <span class="absolute top-3 left-3 bg-red-500/20 text-red-400 text-[10px] font-bold px-2 py-0.5 rounded-full">نفذ</span>
                        @elseif($qty <= 10)
                        <span class="absolute top-3 left-3 bg-amber-500/20 text-amber-400 text-[10px] font-bold px-2 py-0.5 rounded-full">تبقى {{ $qty }}</span>
                        @endif
                    </a>
                    <div class="p-4">
                        @if($product->category)
                        <span class="text-[11px] text-brand-500 font-semibold">{{ $product->category->name_ar }}</span>
                        @endif
                        <a href="{{ route('product.show', $product->slug) }}" class="block mt-1">
                            <h3 class="text-sm font-bold text-white leading-snug line-clamp-2 group-hover:text-brand-500 transition-colors">{{ $product->name_ar }}</h3>
                        </a>
                        <div class="flex items-center gap-2 mt-3">
                            @if($product->is_on_sale)
                            <span class="text-xs text-white-dim line-through">{{ number_format($product->b2c_price, 2) }} ₪</span>
                            @endif
                            <span class="text-base font-extrabold text-brand-500">{{ number_format($product->final_b2c_price ?? $product->b2c_price, 2) }} ₪</span>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <button onclick="addToCart({{ $product->id }})" class="flex-1 py-2.5 bg-white text-surface text-sm font-bold rounded-xl hover:bg-brand-500 hover:text-white transition-all flex items-center justify-center gap-1.5">
                                <i class="ph ph-shopping-bag"></i> أضف للسلة
                            </button>
                            <a href="https://wa.me/{{ $siteSettings['whatsapp_number'] ?? '970591234567' }}?text={{ urlencode('السلام عليكم، مهتمة بـ: ' . $product->name_ar . ' - ' . number_format($product->final_b2c_price ?? $product->b2c_price, 2) . ' ₪') }}" target="_blank" class="py-2.5 px-3 border border-green-500/30 text-green-400 text-sm font-bold rounded-xl hover:bg-green-500 hover:text-white transition-all flex items-center justify-center" title="تواصل واتساب" aria-label="تواصل واتساب">
                                <i class="ph ph-whatsapp-logo text-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- List View (hidden default) --}}
            <div id="productsList" class="hidden space-y-3">
                @foreach($products as $product)
                <div class="product-list-item glass-panel rounded-2xl border-white/5 p-4 flex gap-4 items-center hover:border-brand-500/20 transition-all duration-300">
                    <a href="{{ route('product.show', $product->slug) }}" class="flex-shrink-0">
                        @if($product->main_image_url)
                        <img src="{{ $product->main_image_url }}" alt="{{ $product->name_ar }}" loading="lazy" class="w-24 h-24 rounded-xl object-cover" onerror="this.parentElement.innerHTML='<div class=&quot;w-24 h-24 rounded-xl bg-white/5 flex items-center justify-center&quot;><i class=&quot;ph ph-image text-2xl text-white/10&quot;></i></div>'">
                        @else
                        <div class="w-24 h-24 rounded-xl bg-white/5 flex items-center justify-center"><i class="ph ph-image text-2xl text-white/10"></i></div>
                        @endif
                    </a>
                    <div class="flex-1 min-w-0">
                        @if($product->category)
                        <span class="text-[11px] text-brand-500 font-semibold">{{ $product->category->name_ar }}</span>
                        @endif
                        <a href="{{ route('product.show', $product->slug) }}">
                            <h3 class="text-sm font-bold text-white mt-0.5 hover:text-brand-500 transition-colors">{{ $product->name_ar }}</h3>
                        </a>
                        <div class="flex items-center gap-2 mt-2">
                            @if($product->is_on_sale)
                            <span class="text-xs text-white-dim line-through">{{ number_format($product->b2c_price, 2) }} ₪</span>
                            @endif
                            <span class="text-base font-extrabold text-brand-500">{{ number_format($product->final_b2c_price ?? $product->b2c_price, 2) }} ₪</span>
                            @php $qty = $product->stock_quantity ?? 0; @endphp
                            @if($qty <= 0)
                            <span class="bg-red-500/20 text-red-400 text-[10px] font-bold px-2 py-0.5 rounded-full">نفذ المخزون</span>
                            @elseif($qty <= 10)
                            <span class="bg-amber-500/20 text-amber-400 text-[10px] font-bold px-2 py-0.5 rounded-full">تبقى {{ $qty }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 flex-shrink-0">
                        <button onclick="addToCart({{ $product->id }})" class="px-5 py-2.5 bg-white text-surface text-sm font-bold rounded-xl hover:bg-brand-500 hover:text-white transition-colors flex items-center gap-2 whitespace-nowrap">
                            <i class="ph ph-shopping-bag"></i> أضف للسلة
                        </button>
                        <a href="https://wa.me/{{ $siteSettings['whatsapp_number'] ?? '970591234567' }}?text={{ urlencode('السلام عليكم، مهتمة بـ: ' . $product->name_ar . ' - ' . number_format($product->final_b2c_price ?? $product->b2c_price, 2) . ' ₪') }}" target="_blank" class="px-5 py-2.5 border border-green-500/30 text-green-400 text-sm font-bold rounded-xl hover:bg-green-500 hover:text-white transition-colors text-center whitespace-nowrap">
                            <i class="ph ph-whatsapp-logo"></i> واتساب
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="flex justify-center mt-10">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function setViewMode(mode) {
    const gridBtn = document.getElementById('viewGrid');
    const listBtn = document.getElementById('viewList');
    const grid = document.getElementById('productsGrid');
    const list = document.getElementById('productsList');

    if (mode === 'grid') {
        grid.classList.remove('hidden'); grid.style.display = '';
        list.classList.add('hidden');
        gridBtn.style.background = 'var(--ink)'; gridBtn.style.color = 'var(--surface)';
        listBtn.style.background = 'transparent'; listBtn.style.color = 'var(--ink-dim)';
    } else {
        grid.classList.add('hidden'); grid.style.display = 'none';
        list.classList.remove('hidden');
        listBtn.style.background = 'var(--ink)'; listBtn.style.color = 'var(--surface)';
        gridBtn.style.background = 'transparent'; gridBtn.style.color = 'var(--ink-dim)';
    }
    localStorage.setItem('shopViewMode', mode);
}
(function() { const saved = localStorage.getItem('shopViewMode'); if (saved === 'list') setViewMode('list'); })();
</script>

<style>
@media (max-width: 768px) {
    .product-list-item { flex-direction: column !important; text-align: center; }
    .product-list-item > a img { width: 120px !important; height: 120px !important; }
    .product-list-item > div:last-child { flex-direction: row !important; width: 100%; }
}
</style>
@endsection
