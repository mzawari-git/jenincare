@extends($layoutPath)

@section('title', ($siteSettings['site_name'] ?? 'JeniCare') . ' | منصة الجمال الذكية')
@section('meta_description', 'JeniCare - الجيل الجديد من العناية بالبشرة. بروتوكولات علاجية تتكيف مع بيئتك. منتجات أصلية، شحن لكل فلسطين.')
@section('meta_keywords', 'JeniCare, جيني كير, عناية بالبشرة, عناية بالشعر, منتجات تجميل, فلسطين, شحن مجاني, منتجات أصلية, جمال, ذكاء اصطناعي')

@push('scripts')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "{{ $siteSettings['site_name'] ?? 'JeniCare' }}",
  "url": "{{ url('/') }}",
  "logo": "{{ asset('assets/images/logo.png') }}",
  "description": "منصة العناية بالبشرة الذكية - منتجات أصلية 100%",
  "address": { "@type": "PostalAddress", "addressLocality": "رام الله", "addressCountry": "PS" },
  "contactPoint": { "@type": "ContactPoint", "telephone": "{{ $siteSettings['site_phone'] ?? '+972 56 903 0203' }}", "contactType": "customer service" },
  "sameAs": ["{{ $siteSettings['facebook_url'] ?? '#' }}", "{{ $siteSettings['instagram_url'] ?? '#' }}"]
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "{{ $siteSettings['site_name'] ?? 'JeniCare' }}",
  "url": "{{ url('/') }}",
  "potentialAction": { "@type": "SearchAction", "target": "{{ url('/shop') }}?search={search_term_string}", "query-input": "required name=search_term_string" }
}
</script>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 1: HERO — Slideshow with Cycling Text
     ═══════════════════════════════════════════════════════════════ --}}
@php
$allPhrases = [
    'لمسات ساحرة تبدأ بمنتجات استثنائية... اختاري الأفضل مع جنين.',
    'جودة لا تُضاهى لجمال يدوم... مستحضرات صُممت لتبرز إشراقتكِ.',
    'ألوان غنية وتركيبات آمنة، لتجربة جمال تفوق التوقعات.',
    'سر الإطلالة المثالية يبدأ من هنا... دعي الجودة تتحدث عنكِ.',
    'منتجات أصلية 100%... لأن بشرتك تستحق الأفضل دائماً.',
    'أحدث التقنيات العالمية بين يديكِ، لنتائج احترافية تبهر عملائكِ.',
    'ارتقي بمستوى خدماتكِ مع أجهزة الجيل الجديد من جنين للتجميل.',
    'دقة الأداء، وسرعة النتائج... التكنولوجيا الذكية في خدمة الجمال.',
    'استثمري في نجاحكِ مع أجهزة صُممت لتدوم وتقدم الأفضل.',
    'من التصميم إلى التنفيذ... نجهز صالونكِ ليكون وجهة الفخامة الأولى.',
    'أثاث عصري ومعدات متكاملة، نبني لكِ مساحة تعكس رقي أعمالكِ.',
    'راحة لعملائكِ وتميز لمشروعكِ، مع حلول جنين الشاملة لتجهيز الصالونات.',
    'لا تساومي على أناقة مكانكِ... دعينا نصنع لكِ صالون أحلامكِ.',
    'جنين للتجميل: خيار المحترفين الأول.',
    'كل ما يخص عالم الجمال والأناقة... تحت سقف واحد.',
    'شريككِ الموثوق لرحلة نجاح وتألق مستمرة.',
    'جودة نثق بها، وخدمة تلبي تطلعاتكم.',
    'اكتشفي أسرار الجمال مع أفخر الماركات العالمية الأصلية.',
    'صالونكِ المتكامل... من الفكرة إلى الواقع مع خبراء جنين للتجميل.',
    'منتجات أصلية، أجهزة احترافية، تجهيز متكامل... في مكان واحد.',
];
shuffle($allPhrases);
$phraseCount = count($allPhrases);

$slideshowCats = $categories->filter(fn($c) => $c->products_count > 0)->shuffle()->take(5);
@endphp

<section id="hero" class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop"
             class="w-full h-full object-cover opacity-15 mix-blend-luminosity"
             alt="" aria-hidden="true" loading="eager" fetchpriority="high">
        <div class="absolute inset-0 bg-gradient-to-b from-surface/60 via-surface/88 to-surface"></div>
    </div>

    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 pt-24 pb-16">
        <div class="flex flex-col lg:flex-row items-center gap-10 lg:gap-16">

            {{-- Right: Static Title + Cycling Text --}}
            <div class="w-full lg:w-[45%] text-right">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-brand-500/20 bg-brand-500/5 mb-6">
                    <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse shadow-neon"></span>
                    <span class="text-xs tracking-widest text-brand-500 uppercase font-bold">جنين للتجميل</span>
                </div>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black mb-6 leading-tight tracking-tight">
                    <span class="text-ink">منتجات أصلية</span><br>
                    <span class="gradient-text bg-[length:200%_auto]">نتائج مبهرة.</span>
                </h1>

                <div id="heroPhraseContainer" class="relative h-20 md:h-16 mb-8 overflow-hidden">
                    @foreach($allPhrases as $i => $phrase)
                    <p class="hero-phrase text-lg md:text-xl text-ink-dim max-w-lg font-light leading-relaxed absolute w-full text-right"
                       style="top:0;right:0;opacity:{{ $i === 0 ? '1' : '0' }};transform:translateY({{ $i === 0 ? '0' : '16px' }});transition:opacity 0.7s ease,transform 0.7s ease;"
                       data-phrase="{{ $i }}">{{ $phrase }}</p>
                    @endforeach
                </div>

                <div class="flex items-center gap-6 flex-wrap">
                    <a href="{{ route('shop') }}" class="group relative px-8 py-4 rounded-full overflow-hidden font-black tracking-wide inline-flex items-center gap-2 shadow-neon hover:shadow-neon-strong transition-all" style="background:var(--gradient-primary);color:#fff;">
                        <span class="relative z-10">تسوقي الآن <i class="fa-solid fa-arrow-left mr-2"></i></span>
                    </a>
                    <a href="{{ route('shop') }}" class="text-ink-dim hover:text-brand-500 border-b border-ink-dim/20 pb-1 hover:border-brand-500 transition-all font-bold text-sm">
                        جميع المنتجات
                    </a>
                </div>

                {{-- Phrase counter dots --}}
                <div class="flex gap-1.5 mt-8 justify-end">
                    @for($i = 0; $i < min($phraseCount, 6); $i++)
                    <span class="phrase-dot block w-1.5 h-1.5 rounded-full transition-all duration-300 {{ $i === 0 ? 'bg-brand-500 w-5' : 'bg-ink-dim/20' }}"></span>
                    @endfor
                    <span class="text-[10px] text-ink-dim/40 ml-2">{{ $phraseCount }}+</span>
                </div>
            </div>

            {{-- Left: Product Slideshow --}}
            <div class="w-full lg:w-[55%] relative flex justify-center">
                <div class="relative w-full max-w-lg">
                    @foreach($slideshowCats as $index => $cat)
                    @php
                    $catProducts = \App\Models\Product::where('category_id', $cat->id)->where('status', 'active')->inRandomOrder()->take(2)->get();
                    $main = $catProducts->first();
                    if (!$main) { $main = $featuredProducts->first(); $catProducts = $featuredProducts->take(2); }
                    if (!$main) continue;
                    @endphp
                    <div class="hero-slide glass-panel rounded-3xl overflow-hidden p-3 {{ $index === 0 ? '' : 'hidden' }}" data-slide="{{ $index }}">
                        <a href="{{ route('product.show', $main->slug) }}" class="block relative rounded-2xl overflow-hidden h-[340px] md:h-[400px] bg-surface-alt group">
                            @if($main->main_image_url)
                            <img src="{{ $main->main_image_url }}" alt="{{ $main->name_ar }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                            @else
                            <div class="w-full h-full flex items-center justify-center"><i class="fa-solid fa-flask text-5xl text-ink-dim/15"></i></div>
                            @endif
                            <div class="absolute bottom-0 left-0 right-0 p-5 bg-gradient-to-t from-surface/95 via-surface/70 to-transparent">
                                <span class="inline-block px-2.5 py-1 rounded-full bg-brand-500 text-white text-[11px] font-bold mb-2">{{ $cat->display_name ?? $cat->name_ar }}</span>
                                <h3 class="text-lg font-black text-white mb-1">{{ $main->name_ar }}</h3>
                                <div class="flex items-center justify-between">
                                    <span class="text-brand-400 font-black">{{ number_format($main->final_b2c_price ?? $main->b2c_price, 0) }} ₪</span>
                                    <span class="text-white/50 text-xs flex items-center gap-1">تفاصيل <i class="fa-solid fa-arrow-left"></i></span>
                                </div>
                            </div>
                        </a>
                        @if($catProducts->count() > 1)
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            @foreach($catProducts as $sub)
                            <a href="{{ route('product.show', $sub->slug) }}" class="glass-panel rounded-xl overflow-hidden hover:-translate-y-1 transition-all duration-300">
                                <div class="h-16 bg-surface-alt">
                                    @if($sub->main_image_url)<img src="{{ $sub->main_image_url }}" alt="" class="w-full h-full object-cover" loading="lazy">@endif
                                </div>
                                <div class="p-2 text-center">
                                    <p class="text-[11px] font-bold text-ink truncate">{{ $sub->name_ar }}</p>
                                    <span class="text-brand-500 font-bold text-[11px]">{{ number_format($sub->final_b2c_price ?? $sub->b2c_price, 0) }} ₪</span>
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 flex flex-col items-center gap-2 opacity-30">
        <span class="text-[10px] uppercase tracking-[0.3em] text-ink-dim">مرر</span>
        <div class="w-px h-10 bg-gradient-to-b from-ink-dim/30 to-transparent"></div>
    </div>
</section>

<script>
(function() {
    var phrases = document.querySelectorAll('.hero-phrase');
    var dots = document.querySelectorAll('.phrase-dot');
    var slides = document.querySelectorAll('.hero-slide');
    var totalP = phrases.length;
    var totalS = slides.length;
    var currentP = 0;
    var currentS = 0;
    var pInterval, sInterval;

    function showPhrase(idx) {
        phrases.forEach(function(p, i) {
            p.style.opacity = i === idx ? '1' : '0';
            p.style.transform = i === idx ? 'translateY(0)' : 'translateY(16px)';
        });
        dots.forEach(function(d, i) {
            d.className = i === idx ? 'phrase-dot block w-5 h-1.5 rounded-full bg-brand-500 transition-all duration-300' : 'phrase-dot block w-1.5 h-1.5 rounded-full bg-ink-dim/20 transition-all duration-300';
        });
        currentP = idx;
    }

    function showSlide(idx) {
        slides.forEach(function(s) { s.classList.add('hidden'); });
        var s = document.querySelector('.hero-slide[data-slide="' + idx + '"]');
        if (s) s.classList.remove('hidden');
        currentS = idx;
    }

    function nextPhrase() { showPhrase((currentP + 1) % totalP); }
    function nextSlide() { showSlide((currentS + 1) % totalS); }

    pInterval = setInterval(nextPhrase, 4000);
    if (totalS > 1) sInterval = setInterval(nextSlide, 6000);

    document.querySelectorAll('.hero-slide').forEach(function(s) {
        s.addEventListener('mouseenter', function() { clearInterval(sInterval); });
        s.addEventListener('mouseleave', function() { if (totalS > 1) sInterval = setInterval(nextSlide, 6000); });
    });
})();
</script>

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 2: Categories — Compact Professional Grid
     ═══════════════════════════════════════════════════════════════ --}}
@if($categories->isNotEmpty())
@php $topCategories = $categories->take(6); @endphp
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
            <div class="text-right md:text-right w-full md:w-auto">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-brand-500/20 bg-brand-500/5 mb-4">
                    <span class="text-xs text-brand-500 font-bold tracking-widest uppercase">أقسام المتجر</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-black mb-2">تسوقي حسب <span class="gradient-text bg-[length:200%_auto]">القسم</span></h2>
                <p class="text-ink-dim text-sm md:text-base max-w-lg">اكتشفي منتجات أصلية من أفضل الماركات العالمية في جميع أقسام التجميل والعناية.</p>
            </div>
            <a href="{{ route('shop') }}" class="shrink-0 inline-flex items-center gap-2 text-brand-500 font-bold text-sm hover:gap-3 transition-all">
                جميع الأقسام ({{ $categories->count() }}) <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($topCategories as $cat)
            <a href="{{ route('shop', ['category' => $cat->slug]) }}"
               class="group flex flex-col items-center glass-panel rounded-2xl p-5 text-center transition-all duration-500 hover:-translate-y-1.5 hover:shadow-lg hover:border-brand-500/30">
                <div class="w-20 h-20 rounded-2xl overflow-hidden mb-4 bg-surface-alt flex-shrink-0">
                    @if($cat->sample_image)
                    <img src="{{ $cat->sample_image }}" alt="{{ $cat->display_name ?? $cat->name_ar }}"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                    @else
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fa-solid fa-tag text-2xl text-ink-dim/20"></i>
                    </div>
                    @endif
                </div>
                <h3 class="font-black text-sm mb-1.5 text-ink group-hover:text-brand-500 transition-colors duration-300 leading-tight">{{ $cat->display_name ?? $cat->name_ar }}</h3>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-brand-500/10 text-brand-500 text-[11px] font-bold">
                    {{ $cat->products_count }} منتج
                </span>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 3: Trust Bar — Social Proof & Quick Stats
     ═══════════════════════════════════════════════════════════════ --}}
<section class="py-12 border-b border-white/5">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div class="glass-panel rounded-2xl p-6">
                <span class="text-3xl md:text-4xl font-black gradient-text bg-[length:200%_auto] block mb-2">+{{ \App\Models\Product::count() }}</span>
                <span class="text-sm text-ink-muted">منتج أصلي</span>
            </div>
            <div class="glass-panel rounded-2xl p-6">
                <span class="text-3xl md:text-4xl font-black text-white block mb-2">15,000+</span>
                <span class="text-sm text-ink-muted">عميلة سعيدة</span>
            </div>
            <div class="glass-panel rounded-2xl p-6">
                <span class="text-3xl md:text-4xl font-black text-white block mb-2">4.9</span>
                <span class="text-sm text-ink-muted">تقييم العملاء</span>
            </div>
            <div class="glass-panel rounded-2xl p-6">
                <span class="text-3xl md:text-4xl font-black text-white block mb-2">24H</span>
                <span class="text-sm text-ink-muted">توصيل سريع</span>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 3: Brand USP — Optimized for Facebook & Google Ads
     ═══════════════════════════════════════════════════════════════ --}}
<section class="py-16 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_30%_50%,rgba(var(--brand-500-rgb,255,42,133),0.04),transparent_60%)] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="text-center mb-14">
            <h2 class="text-3xl md:text-5xl font-black mb-4">أفضل وجهتك <span class="gradient-text bg-[length:200%_auto]">للعناية بالبشرة والشعر</span></h2>
            <p class="text-ink-dim max-w-3xl mx-auto text-lg font-light leading-relaxed">متجر الكتروني متخصص في منتجات التجميل والعناية بالبشرة، نوفر لكِ ماركات عالمية أصلية بأسعار تنافسية، مع شحن سريع لجميع مدن فلسطين. اكتشفي عروضنا الحصرية وخدمة العملاء المميزة.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="glass-panel rounded-2xl p-7 text-right hover:-translate-y-2 transition-all duration-500 group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 group-hover:bg-brand-500/20 transition-colors">
                    <i class="fa-solid fa-certificate text-xl text-brand-500"></i>
                </div>
                <h3 class="font-black text-lg mb-3 text-ink">منتجات أصلية مضمونة</h3>
                <p class="text-ink-dim text-sm leading-relaxed">جميع منتجاتنا أصلية 100% ومستوردة من مصادر موثوقة ومعتمدة دولياً. نضمن لكِ الجودة والأصالة في كل طلب.</p>
            </div>
            <div class="glass-panel rounded-2xl p-7 text-right hover:-translate-y-2 transition-all duration-500 group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 group-hover:bg-brand-500/20 transition-colors">
                    <i class="fa-solid fa-truck-fast text-xl text-brand-500"></i>
                </div>
                <h3 class="font-black text-lg mb-3 text-ink">توصيل لكل فلسطين</h3>
                <p class="text-ink-dim text-sm leading-relaxed">نوصل طلبك لباب بيتك في الضفة الغربية، القدس، والداخل المحتل. شحن سريع وتتبع مباشر لشحنتك حتى الاستلام.</p>
            </div>
            <div class="glass-panel rounded-2xl p-7 text-right hover:-translate-y-2 transition-all duration-500 group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 group-hover:bg-brand-500/20 transition-colors">
                    <i class="fa-solid fa-tags text-xl text-brand-500"></i>
                </div>
                <h3 class="font-black text-lg mb-3 text-ink">أفضل الأسعار والعروض</h3>
                <p class="text-ink-dim text-sm leading-relaxed">أسعار تنافسية مع عروض حصرية وخصومات يومية. الدفع عند الاستلام متاح لراحتك وأمانك التام.</p>
            </div>
            <div class="glass-panel rounded-2xl p-7 text-right hover:-translate-y-2 transition-all duration-500 group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 group-hover:bg-brand-500/20 transition-colors">
                    <i class="fa-solid fa-headset text-xl text-brand-500"></i>
                </div>
                <h3 class="font-black text-lg mb-3 text-ink">دعم احترافي متواصل</h3>
                <p class="text-ink-dim text-sm leading-relaxed">فريق خدمة عملاء محترف جاهز لمساعدتك يومياً من 9 صباحاً حتى 10 مساءً عبر الواتساب. استفسري وسنرد فوراً.</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 4: Why JeniCare? — Premium Value Cards
     ═══════════════════════════════════════════════════════════════ --}}
<section class="py-24 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_50%_50%,rgba(var(--brand-500-rgb,255,42,133),0.03),transparent_70%)] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="mb-16 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-brand-500/20 bg-brand-500/5 mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                <span class="text-xs text-brand-500 font-bold tracking-widest uppercase">لماذا تختارينا</span>
            </div>
            <h2 class="text-3xl md:text-5xl font-black mb-4">لماذا <span class="gradient-text bg-[length:200%_auto]">JeniCare</span><span class="text-brand-500">.</span></h2>
            <p class="text-ink-dim max-w-xl mx-auto text-lg font-light">متجر العناية بالبشرة الأول في فلسطين. نوفر لكِ تجربة تسوق آمنة وموثوقة مع منتجات أصلية وخدمة عملاء استثنائية.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
            @php
                $valueCards = [
                    ['num' => '01', 'icon' => 'fa-solid fa-shield-check', 'title' => 'منتجات أصلية 100%', 'desc' => 'نضمن لكِ أصالة كل منتج من مصادر موثوقة ومعتمدة دولياً. لا تقلقي بشأن جودة المنتجات - نحن نتعامل فقط مع الماركات العالمية الأصلية.'],
                    ['num' => '02', 'icon' => 'fa-solid fa-truck-fast', 'title' => 'شحن سريع لكل فلسطين', 'desc' => 'توصيل لجميع المناطق من جنين إلى رام الله والخليل وغزة، مع تتبع مباشر لشحنتك حتى باب منزلك. اطلبي اليوم واستلمي خلال 24-48 ساعة.'],
                    ['num' => '03', 'icon' => 'fa-solid fa-headset', 'title' => 'دعم يومي احترافي', 'desc' => 'فريق متخصص جاهز لمساعدتك من 9 صباحاً حتى 10 مساءً عبر الواتساب. استشارات مجانية لاختيار المنتج المناسب لنوع بشرتك.'],
                ];
            @endphp
            @foreach($valueCards as $card)
            <div class="value-card glass-panel rounded-[2rem] p-8 text-right group relative overflow-hidden transition-all duration-500">
                {{-- Top accent line --}}
                <div class="absolute top-0 right-8 left-8 h-[3px] rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500" style="background: var(--gradient-primary);"></div>
                {{-- Background glow --}}
                <div class="absolute -left-10 -bottom-10 w-40 h-40 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-700" style="background: radial-gradient(circle, var(--brand-500) 0%, transparent 70%); filter: blur(50px);"></div>
                {{-- Number badge --}}
                <div class="absolute top-6 left-6 text-6xl font-black opacity-[0.04] group-hover:opacity-[0.08] transition-opacity duration-500 select-none" style="color: var(--brand-500);">{{ $card['num'] }}</div>
                {{-- Icon --}}
                <div class="relative z-10 w-16 h-16 rounded-2xl bg-brand-500/10 flex items-center justify-center mb-6 group-hover:bg-brand-500/20 group-hover:scale-110 transition-all duration-500 shadow-neon">
                    <i class="{{ $card['icon'] }} text-2xl" style="color: var(--brand-500);"></i>
                </div>
                {{-- Content --}}
                <div class="relative z-10">
                    <h3 class="text-xl font-black mb-3" style="color: var(--ink);">{{ $card['title'] }}</h3>
                    <p class="text-ink-dim text-sm leading-relaxed">{{ $card['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<style>
.value-card:hover { transform: translateY(-6px); border-color: rgba(255,42,133,0.15); box-shadow: 0 12px 40px rgba(0,0,0,0.3), var(--neon-glow); }
</style>

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 3: Product Lab — Asymmetric Grid
     ═══════════════════════════════════════════════════════════════ --}}
<section id="products" class="py-20 relative">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_30%_50%,rgba(var(--brand-500-rgb,255,42,133),0.04),transparent_60%)] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="mb-16 text-right">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-brand-500/20 bg-brand-500/5 mb-6">
                <span class="text-xs text-brand-500 font-bold tracking-widest uppercase">مختبر الجمال</span>
            </div>
            <h2 class="text-3xl md:text-5xl font-black mb-4">منتجات مختارة <span class="gradient-text bg-[length:200%_auto]">بعناية فائقة</span></h2>
            <p class="text-ink-dim max-w-xl text-lg font-light">كل منتج في متجرنا تم انتقاؤه بعناية من أفضل الماركات العالمية ليكون جزءاً من روتين عنايتك الشخصي. منتجات أصلية، نتائج مضمونة.</p>
        </div>

        @if($featuredProducts->isNotEmpty() || $newProducts->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-8">

            @php $bigProduct = $featuredProducts->first(); @endphp
            @if($bigProduct)
            {{-- Large Featured Product Card (col-span-8) --}}
            <div class="md:col-span-7 lg:col-span-8 group relative rounded-[2rem] overflow-hidden glass-panel border border-white/5 h-[450px] cursor-pointer"
                 onclick="window.location='{{ route('product.show', $bigProduct->slug) }}'">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-transparent z-10"></div>
                @if($bigProduct->main_image_url)
                <img src="{{ $bigProduct->main_image_url }}" alt="{{ $bigProduct->name_ar }}"
                     class="absolute inset-0 w-full h-full object-cover filter grayscale mix-blend-luminosity group-hover:scale-105 transition-transform duration-700"
                     loading="lazy">
                @else
                <div class="absolute inset-0 flex items-center justify-center text-white/10"><i class="fa-solid fa-flask text-8xl"></i></div>
                @endif

                <div class="absolute top-6 right-6 z-20 flex gap-2">
                    <span class="bg-black/50 backdrop-blur px-3 py-1 rounded-full text-xs border border-white/10 text-ink/70">منتجات مميزة</span>
                    <span class="pill-brand backdrop-blur text-xs">الأكثر مبيعاً</span>
                </div>

                <div class="absolute bottom-8 right-8 z-20 text-right">
                    <h3 class="text-3xl font-black mb-2 text-white">{{ $bigProduct->name_ar }}</h3>
                    @if($bigProduct->brand)
                    <p class="text-ink-dim text-sm mb-3">{{ $bigProduct->brand->name }}</p>
                    @endif
                    <div class="flex items-center justify-end gap-4">
                        <span class="text-2xl font-bold text-brand-500">{{ number_format($bigProduct->b2c_price, 0) }} ₪</span>
                        <button onclick="event.stopPropagation(); addToCart({{ $bigProduct->id }})"
                                class="w-12 h-12 rounded-full bg-white text-surface flex items-center justify-center hover:shadow-neon transition-all"
                                aria-label="إضافة للسلة">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            @php $secondProduct = $newProducts->first() ?? $featuredProducts->skip(1)->first(); @endphp
            @if($secondProduct)
            {{-- Tall Product Card (col-span-4) --}}
            <div class="md:col-span-5 lg:col-span-4 group relative rounded-[2rem] overflow-hidden glass-panel border border-white/5 h-[450px] cursor-pointer"
                 onclick="window.location='{{ route('product.show', $secondProduct->slug) }}'">
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent z-10"></div>
                <div class="absolute inset-0 bg-accent-500/5 mix-blend-overlay z-10 group-hover:bg-accent-500/10 transition-colors"></div>
                @if($secondProduct->main_image_url)
                <img src="{{ $secondProduct->main_image_url }}" alt="{{ $secondProduct->name_ar }}"
                     class="absolute inset-0 w-full h-full object-cover filter contrast-125 group-hover:scale-105 transition-transform duration-700"
                     loading="lazy">
                @else
                <div class="absolute inset-0 flex items-center justify-center text-white/10"><i class="fa-solid fa-droplet text-8xl"></i></div>
                @endif

                <div class="absolute top-6 right-6 z-20">
                    <span class="pill-accent backdrop-blur text-xs">وصل حديثاً</span>
                </div>

                <div class="absolute bottom-8 right-8 z-20 text-right">
                    <h3 class="text-xl font-black mb-1 text-white">{{ $secondProduct->name_ar }}</h3>
                    @if($secondProduct->brand)
                    <p class="text-ink-dim text-xs mb-4">{{ $secondProduct->brand->name }}</p>
                    @endif
                    <div class="flex items-center justify-between">
                        <button onclick="event.stopPropagation(); addToCart({{ $secondProduct->id }})"
                                class="text-xs font-bold uppercase tracking-wider border-b border-white/30 hover:text-brand-500 hover:border-brand-500 transition-colors pb-1 text-white/70">
                            تفاصيل
                        </button>
                        <span class="font-bold text-white">{{ number_format($secondProduct->b2c_price, 0) }} ₪</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Info/Value Card (col-span-5) --}}
            <div class="md:col-span-5 rounded-[2rem] glass-panel border border-white/5 p-10 flex flex-col justify-between relative overflow-hidden group cursor-default">
                <div class="absolute -left-20 -top-20 w-64 h-64 bg-brand-500/8 rounded-full blur-3xl group-hover:bg-brand-500/12 transition-colors"></div>
                {{-- Top accent bar --}}
                <div class="absolute top-0 right-8 left-8 h-[2px] rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500" style="background: var(--gradient-primary);"></div>
                <div class="relative z-10">
                    <div class="w-14 h-14 rounded-2xl bg-accent-500/10 flex items-center justify-center mb-6 shadow-accent-neon">
                        <i class="fa-solid fa-microchip text-2xl text-accent-500"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-4" style="color: var(--ink);">روتين عناية<br>مصمم خصيصاً لكِ.</h3>
                    <p class="text-ink-dim text-sm leading-relaxed">
                        نختار لكِ أفضل المنتجات المناسبة لنوع بشرتك واحتياجاتك. تصفحي مجموعتنا المميزة من منتجات العناية بالبشرة والشعر، وتمتعي بتجربة تسوق فريدة مع شحن سريع ودفع آمن.
                    </p>
                </div>
                <div class="mt-8">
                    <a href="{{ route('shop') }}" class="text-accent-500 font-bold flex items-center gap-2 hover:gap-4 transition-all group/link">
                        تصفحي المتجر <i class="fa-solid fa-arrow-left text-sm group-hover/link:-translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>

            @php $thirdProduct = $featuredProducts->skip(1)->first() ?? $newProducts->skip(1)->first() ?? $featuredProducts->skip(2)->first(); @endphp
            @if($thirdProduct)
            {{-- Wide Bottom Card (col-span-7) --}}
            <div class="md:col-span-7 group relative rounded-[2rem] overflow-hidden glass-panel border border-white/5 h-[300px] cursor-pointer"
                 onclick="window.location='{{ route('product.show', $thirdProduct->slug) }}'">
                <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/40 to-transparent z-10"></div>
                @if($thirdProduct->main_image_url)
                <img src="{{ $thirdProduct->main_image_url }}" alt="{{ $thirdProduct->name_ar }}"
                     class="absolute inset-0 w-full h-full object-cover filter grayscale group-hover:grayscale-0 transition-all duration-1000"
                     loading="lazy">
                @else
                <div class="absolute inset-0 flex items-center justify-center text-white/10"><i class="fa-solid fa-box-open text-8xl"></i></div>
                @endif

                <div class="absolute top-1/2 transform -translate-y-1/2 right-12 z-20 text-right max-w-sm">
                    <h3 class="text-2xl font-black mb-2 text-white">{{ $thirdProduct->name_ar }}</h3>
                    @if($thirdProduct->brand)
                    <p class="text-ink-dim text-xs mb-5">{{ $thirdProduct->brand->name }}</p>
                    @endif
                    <button onclick="event.stopPropagation(); addToCart({{ $thirdProduct->id }})"
                            class="px-6 py-2.5 bg-white text-surface rounded-full font-bold transition-all text-sm hover:shadow-neon hover:scale-105 inline-flex items-center gap-2">
                        <i class="fa-solid fa-plus text-xs"></i> إضافة للمختبر — {{ number_format($thirdProduct->b2c_price, 0) }} ₪
                    </button>
                </div>
            </div>
            @endif

        </div>
        @else
        <div class="text-center py-20 text-ink-dim">
            <i class="fa-solid fa-flask text-5xl mb-6 opacity-20"></i>
            <p class="text-lg">لم يتم إضافة منتجات بعد.</p>
        </div>
        @endif
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 4: Tech Marquee Ticker
     ═══════════════════════════════════════════════════════════════ --}}
<div class="py-10 border-y border-white/5 overflow-hidden flex whitespace-nowrap opacity-40 hover:opacity-70 transition-opacity">
    <div class="animate-marquee-rtl flex items-center gap-16 font-mono text-xs tracking-[0.2em] uppercase text-white/50">
        <span><i class="fa-solid fa-asterisk text-brand-500 text-[8px] mr-2"></i> منتجات أصلية 100%</span>
        <i class="fa-solid fa-circle text-[4px] text-brand-500"></i>
        <span>شحن سريع لكل فلسطين</span>
        <i class="fa-solid fa-circle text-[4px] text-accent-500"></i>
        <span>أفضل ماركات التجميل العالمية</span>
        <i class="fa-solid fa-circle text-[4px] text-brand-500"></i>
        <span>الدفع عند الاستلام</span>
        <i class="fa-solid fa-circle text-[4px] text-accent-500"></i>
        <span>دعم احترافي يومي</span>
        <i class="fa-solid fa-circle text-[4px] text-brand-500"></i>
        <span>عروض وخصومات حصرية</span>
        <i class="fa-solid fa-circle text-[4px] text-accent-500"></i>
        <span>توصيل لجميع المناطق</span>
        <i class="fa-solid fa-circle text-[4px] text-brand-500"></i>
        <span>منتجات أصلية 100%</span>
        <i class="fa-solid fa-circle text-[4px] text-accent-500"></i>
        <span>شحن سريع لكل فلسطين</span>
        <i class="fa-solid fa-circle text-[4px] text-brand-500"></i>
        <span>أفضل ماركات التجميل العالمية</span>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 5: More Products — Horizontal Scroll
     ═══════════════════════════════════════════════════════════════ --}}
@if($newProducts->isNotEmpty() || $featuredProducts->isNotEmpty())
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-12 flex items-end justify-between">
            <div class="text-right">
                <h2 class="text-3xl md:text-4xl font-black mb-2">وصل حديثاً</h2>
                <p class="text-ink-dim text-sm">أحدث المنتجات الأصلية في مختبر الجمال - شحن سريع وتوصيل لكل فلسطين</p>
            </div>
            <a href="{{ route('shop') }}?sort=newest" class="text-brand-500 font-bold text-sm hover:gap-3 flex items-center gap-1 transition-all">
                عرض الكل <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
        </div>

        <div class="flex gap-6 overflow-x-auto hide-scroll pb-4" style="scroll-snap-type: x mandatory;">
            @php $scrollProducts = $newProducts->isNotEmpty() ? $newProducts : $featuredProducts; @endphp
            @foreach($scrollProducts->take(8) as $product)
            <a href="{{ route('product.show', $product->slug) }}"
               class="flex-shrink-0 w-[260px] glass-panel rounded-2xl overflow-hidden group border border-white/5 block transition-all duration-500 hover:-translate-y-2 hover:border-brand-500/30" style="scroll-snap-align: start;">
                <div class="relative h-[260px] overflow-hidden">
                    @if($product->main_image_url)
                    <img src="{{ $product->main_image_url }}" alt="{{ $product->name_ar }}"
                         class="w-full h-full object-cover filter brightness-75 group-hover:brightness-100 group-hover:scale-110 transition-all duration-700"
                         loading="lazy">
                    @else
                    <div class="w-full h-full flex items-center justify-center bg-surface-alt">
                        <i class="fa-solid fa-box text-4xl text-white/10"></i>
                    </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-surface/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="absolute top-3 right-3">
                        <span class="pill-brand text-[10px] px-2 py-0.5">جديد</span>
                    </div>
                    <div class="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0">
                        <span class="bg-white text-surface text-[10px] font-bold px-3 py-1.5 rounded-full flex items-center gap-1">
                            <i class="fa-solid fa-bag-shopping text-[9px]"></i> تسوق الآن
                        </span>
                    </div>
                </div>
                <div class="p-5 text-right">
                    <h3 class="font-bold text-sm mb-1 line-clamp-1" style="color: var(--ink);">{{ $product->name_ar }}</h3>
                    @if($product->brand)
                    <p class="text-ink-dim text-xs mb-3">{{ $product->brand->name }}</p>
                    @endif
                    <span class="text-brand-500 font-black text-lg">{{ number_format($product->b2c_price, 0) }} ₪</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 6: Protocols / CTA Banner
     ═══════════════════════════════════════════════════════════════ --}}
<section class="py-24 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(var(--brand-500-rgb,255,42,133),0.06),transparent_70%)]"></div>
    <div class="max-w-5xl mx-auto px-4 text-center relative z-10">
        <div class="glass-panel rounded-[3rem] p-12 md:p-16 border border-white/5">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-brand-500/20 bg-brand-500/5 mb-8">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                <span class="text-xs text-brand-500 font-bold tracking-widest uppercase">ابدئي رحلتك الآن</span>
            </div>
            <h2 class="text-3xl md:text-5xl font-black mb-6">
                مستعدة لاكتشاف<br>
                <span class="gradient-text bg-[length:200%_auto]">روتينك المثالي؟</span>
            </h2>
            <p class="text-ink-dim text-lg mb-10 max-w-2xl mx-auto font-light">
                انضمي إلى آلاف العميلات السعيدات وابدئي رحلة العناية ببشرتك مع أفضل المنتجات الأصلية. شحن سريع، دفع آمن، ودعم احترافي على مدار الأسبوع.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('shop') }}"
                   class="px-10 py-4 rounded-full font-black text-sm tracking-wide inline-flex items-center justify-center gap-2 shadow-neon hover:shadow-neon-strong transition-all"
                   style="background: var(--gradient-primary); color: white;">
                    تسوقي الآن <i class="fa-solid fa-arrow-left"></i>
                </a>
                <a href="{{ route('b2b') }}"
                   class="px-10 py-4 rounded-full font-bold text-sm border border-white/15 text-white hover:bg-white/5 transition-all inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-crown text-accent-500"></i> طلبات الجملة والصالونات
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
