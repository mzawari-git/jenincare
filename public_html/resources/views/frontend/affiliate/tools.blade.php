@extends('frontend.layouts.editorial.app')

@section('title', 'أدوات التسويق | اربحي مع كل منتج | شركة جنين للتجميل')

@section('content')
<section style="background:#ffffff;min-height:100vh;padding:6rem 1rem 4rem;">
    <div style="max-width:1200px;margin:0 auto;">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
            <div>
                <h1 style="font-size:clamp(1.5rem,4vw,2rem);font-weight:900;color:#0f172a;margin-bottom:.2rem;">أدوات التسويق</h1>
                <p style="color:#64748b;font-size:.85rem;">كل ما تحتاجينه لنشر رابطك وزيادة أرباحك</p>
            </div>
            <a href="{{ route('affiliate.dashboard') }}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border-radius:9999px;font-size:.85rem;font-weight:700;color:#be185d;border:2px solid #fbcfe8;text-decoration:none;">لوحة التحكم</a>
        </div>

        {{-- Quick Referral Link --}}
        <div style="background:#fdf2f8;border:1px solid #fbcfe8;border-radius:1.25rem;padding:1.25rem 1.5rem;margin-bottom:2rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <div style="font-size:.7rem;font-weight:700;color:#be185d;margin-bottom:.35rem;">رابط التسويق العام</div>
                <code style="font-size:.85rem;color:#9d174d;word-break:break-all;direction:ltr;text-align:left;display:block;">{{ $affiliate->referral_link }}</code>
            </div>
            <button onclick="copyText(this, '{{ $affiliate->referral_link }}')" style="padding:.55rem 1.5rem;border-radius:9999px;font-size:.8rem;font-weight:700;color:#fff;background:linear-gradient(135deg,#ec4899,#be185d);border:none;cursor:pointer;white-space:nowrap;">نسخ الرابط</button>
        </div>

        {{-- PRODUCT SHOWCASE --}}
        <div style="margin-bottom:2rem;">
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:.75rem;background:#fce7f3;">
                    <i class="ph ph-package" style="color:#ec4899;"></i>
                </span>
                <div>
                    <h2 style="font-size:1.25rem;font-weight:900;color:#0f172a;">روّجي لمنتجات محددة</h2>
                    <p style="color:#64748b;font-size:.8rem;">اختاري أي منتج واحصلي على رابط تسويقي خاص به برمز الإحالة</p>
                </div>
            </div>

            {{-- Category Filter Pills --}}
            <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.5rem;">
                <button onclick="filterProducts('all')" class="cat-filter active" data-cat="all" style="padding:.4rem 1rem;border-radius:9999px;font-size:.75rem;font-weight:700;border:2px solid #ec4899;background:#ec4899;color:#fff;cursor:pointer;">الكل</button>
                @foreach($categories->take(8) as $cat)
                <button onclick="filterProducts('{{ $cat->id }}')" class="cat-filter" data-cat="{{ $cat->id }}" style="padding:.4rem 1rem;border-radius:9999px;font-size:.75rem;font-weight:700;border:1px solid #e2e8f0;background:#fff;color:#475569;cursor:pointer;transition:all .2s;">{{ $cat->display_name ?? $cat->name_ar }}</button>
                @endforeach
            </div>

            {{-- Products Grid --}}
            <div id="productsGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
                @foreach($products as $product)
                <div class="product-card" data-category="{{ $product->category_id }}" style="background:#fff;border:1px solid #e2e8f0;border-radius:1rem;overflow:hidden;transition:all .2s;">
                    <div style="position:relative;background:#f8fafc;height:180px;overflow:hidden;">
                        @if($product->main_image_url)
                        <img src="{{ $product->optimizedImageUrl(400, 400) }}" alt="{{ $product->name_ar }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#cbd5e1;">
                            <i class="ph ph-package" style="font-size:3rem;"></i>
                        </div>
                        @endif
                        <div style="position:absolute;bottom:0;left:0;right:0;padding:.5rem;background:linear-gradient(to top,rgba(0,0,0,.6),transparent);display:flex;justify-content:space-between;align-items:flex-end;">
                            <span style="font-weight:900;color:#fff;font-size:.85rem;">{{ number_format($product->final_b2c_price ?? $product->b2c_price, 0) }} ₪</span>
                            @if($product->category)
                            <span style="font-size:.6rem;font-weight:700;color:#fce7f3;background:rgba(236,72,153,.3);padding:.15rem .5rem;border-radius:9999px;">{{ $product->category->display_name ?? $product->category->name_ar }}</span>
                            @endif
                        </div>
                    </div>
                    <div style="padding:.85rem;">
                        <h4 style="font-size:.8rem;font-weight:700;color:#0f172a;margin-bottom:.5rem;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $product->name_ar }}</h4>
                        <button onclick="copyProductLink(this, '{{ route('product.show', $product->slug) }}?ref={{ $affiliate->referral_code }}')" style="width:100%;padding:.5rem;border-radius:.5rem;font-size:.7rem;font-weight:700;color:#be185d;background:#fdf2f8;border:1px solid #fbcfe8;cursor:pointer;transition:all .2s;">
                            <i class="ph ph-link" style="margin-left:.25rem;"></i> نسخ رابط المنتج
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Text Templates --}}
        <div style="margin-bottom:2rem;">
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:.75rem;background:#e0f2fe;">
                    <i class="ph ph-chat-circle-text" style="color:#0891b2;"></i>
                </span>
                <div>
                    <h2 style="font-size:1.25rem;font-weight:900;color:#0f172a;">نصوص جاهزة للمشاركة</h2>
                    <p style="color:#64748b;font-size:.8rem;">انسخي والصقي مباشرة مع الرابط</p>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem;">
                @foreach([
                    'اكتشفي أجمل منتجات التجميل والعناية الأصلية من جنين كير - توصيل لجميع مناطق فلسطين 🇵🇸',
                    'صالونكِ يستاهل الأفضل… أحدث أجهزة التجميل ومستحضرات أصلية 100% ✨',
                    'رفاهية الجمال في كل تفصيل.. تسوقي الآن من جنين كير وتمتعي بالتوصيل السريع 💕',
                    'شركة جنين للتجميل.. كل ما تحتاجينه لجمالك وصالونك في مكان واحد 💎',
                    'منتجات أصلية، أجهزة احترافية، تجهيز متكامل.. بانتظارك في جنين كير 🛍️',
                ] as $text)
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:1rem;">
                    <p style="color:#334155;font-size:.8rem;line-height:1.7;margin-bottom:.75rem;">{{ $text }}</p>
                    <button onclick="copyText(this, '{{ e($text) }} {{ $affiliate->referral_link }}')" style="font-size:.7rem;font-weight:700;color:#0891b2;background:none;border:none;cursor:pointer;">نسخ النص مع الرابط</button>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Tips --}}
        <div>
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:.75rem;background:#fef3c7;">
                    <i class="ph ph-lightbulb" style="color:#b45309;"></i>
                </span>
                <div>
                    <h2 style="font-size:1.25rem;font-weight:900;color:#0f172a;">نصائح ذهبية</h2>
                    <p style="color:#64748b;font-size:.8rem;">لزيادة أرباحك من التسويق</p>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.75rem;">
                @foreach([
                    ['1','واتساب أولاً','أرسلي رابط منتج محدد مع صورة للمجموعات. المنتج المرئي يبيع أكثر.'],
                    ['2','إنستغرام','ضعي رابط المنتج في البايو + رابط متجرك في كل ستوري.'],
                    ['3','تيك توك','فيديو 15 ثانية عن المنتج + رابطك في أول تعليق = مبيعات.'],
                    ['4','كوني محددة','روجي لمنتج واحد في كل منشور. التركيز يضاعف المبيعات.'],
                ] as $tip)
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:.85rem;display:flex;gap:.75rem;">
                    <span style="font-size:1.5rem;font-weight:900;color:#b45309;flex-shrink:0;">{{ $tip[0] }}</span>
                    <div>
                        <h4 style="color:#0f172a;font-weight:700;font-size:.8rem;margin-bottom:.25rem;">{{ $tip[1] }}</h4>
                        <p style="color:#64748b;font-size:.7rem;line-height:1.5;">{{ $tip[2] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</section>

<style>
.cat-filter.active { background:#ec4899 !important; color:#fff !important; border-color:#ec4899 !important; }
.product-card:hover { border-color:#f9a8d4; box-shadow:0 2px 12px rgba(236,72,153,.08); transform:translateY(-2px); }
.product-card button:hover { background:#ec4899 !important; color:#fff !important; border-color:#ec4899 !important; }
</style>

<script>
function copyText(btn, text) {
    navigator.clipboard.writeText(text).then(() => {
        var orig = btn.textContent;
        btn.textContent = 'تم النسخ!';
        setTimeout(() => { btn.textContent = orig; }, 2000);
    });
}
function copyProductLink(btn, url) {
    navigator.clipboard.writeText(url).then(() => {
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="ph ph-check" style="margin-left:.25rem;"></i> تم نسخ الرابط';
        btn.style.background = '#16a34a'; btn.style.color = '#fff'; btn.style.borderColor = '#16a34a';
        setTimeout(() => { btn.innerHTML = orig; btn.style.background = '#fdf2f8'; btn.style.color = '#be185d'; btn.style.borderColor = '#fbcfe8'; }, 2000);
    });
}
function filterProducts(catId) {
    document.querySelectorAll('.cat-filter').forEach(b => b.classList.remove('active'));
    document.querySelector('.cat-filter[data-cat="'+catId+'"]').classList.add('active');
    document.querySelectorAll('.product-card').forEach(card => {
        card.style.display = (catId === 'all' || card.dataset.category === catId) ? '' : 'none';
    });
}
</script>
@endsection
