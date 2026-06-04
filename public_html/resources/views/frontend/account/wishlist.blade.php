@extends($layoutPath)

@section('title', 'المفضلة - ' . ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))

@section('content')
<section class="pt-32 pb-8 bg-gradient-to-b border-b border-white/5 text-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-white mb-2">المفضلة</h1>
        <p class="text-white-dim">المنتجات التي أضفتها إلى قائمة أمنياتك</p>
    </div>
</section>

<div class="container" style="padding:0 16px 60px;">
    <div class="row g-4">
        <div class="col-lg-3">
            @include('frontend.account.sidebar')
        </div>

        <div class="col-lg-9">
            @if($wishlists->isEmpty())
            <div class="glass-panel rounded-2xl  overflow-hidden">
                <div class="text-center py-20">
                    <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-brand-50 to-pink-50 flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <i class="ph ph-heart text-5xl text-brand-400"></i>
                    </div>
                    <h3 class="text-2xl font-extrabold text-white mb-3">قائمة أمنياتك فارغة</h3>
                    <p class="text-white-dim mb-8 max-w-sm mx-auto leading-relaxed">تصفحي منتجاتنا وأضيفي ما يعجبك إلى المفضلة لتشتريه لاحقاً.</p>
                    <a href="{{ route('shop') }}" class="inline-flex items-center gap-3 px-10 py-3.5 bg-white style="color:#0f172a;" rounded-full font-bold hover:bg-brand-500 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="ph ph-arrow-left"></i> تصفحي المتجر
                    </a>
                </div>
            </div>
            @else
            <div class="row g-4">
                @foreach($wishlists as $wishlist)
                @php $product = $wishlist->product; @endphp
                <div class="col-md-6 col-lg-4">
                    <div style="background:var(--glass-bg);border-radius:16px;border:1px solid var(--glass-border);overflow:hidden;transition:all .3s;" onmouseover="this.style.boxShadow='0 8px 32px rgba(0,0,0,.1)';this.style.transform='translateY(-3px)'" onmouseout="this.style.boxShadow='none';this.style.transform='none'">
                        <a href="{{ route('product.show', $product->slug) }}" style="display:block;aspect-ratio:1/1;overflow:hidden;background:var(--surface-alt);">
                            @if($product->main_image)
                            <img src="{{ $product->optimizedImageUrl(500, 500) }}" alt="{{ $product->name_ar }}" width="500" height="500" style="width:100%;height:100%;object-fit:cover;transition:transform .4s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                            @else
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--surface-alt);">
                                <i class="fas fa-bottle" style="font-size:3rem;color:var(--gray-300);"></i>
                            </div>
                            @endif
                        </a>
                        <div style="padding:16px;">
                            <a href="{{ route('product.show', $product->slug) }}" style="text-decoration:none;">
                                <h3 style="font-size:.9rem;font-weight:600;color:var(--ink);margin-bottom:6px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $product->name_ar }}</h3>
                            </a>
                            <div style="font-weight:700;color:var(--brand-500);font-size:.95rem;margin-bottom:12px;">
                                {{ number_format($product->final_b2c_price ?? $product->b2c_price, 2) }} ₪
                            </div>
                            <button onclick="addToCart({{ $product->id }})" style="width:100%;padding:10px;background:linear-gradient(135deg,var(--brand-500),var(--brand-500));color:#fff;border:none;border-radius:50px;font-weight:600;font-size:.85rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;transition:all .3s;box-shadow:0 2px 8px rgba(219,39,119,0.15);" onmouseover="this.style.boxShadow='0 4px 16px rgba(219,39,119,0.25)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(219,39,119,0.15)'">
                                <i class="fas fa-cart-plus"></i> أضف للسلة
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
