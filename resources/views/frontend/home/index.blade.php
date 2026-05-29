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
     SECTION 1: HERO — Dark Sci-Fi Immersive Experience
     ═══════════════════════════════════════════════════════════════ --}}
<section id="hero" class="relative min-h-screen flex items-center justify-center overflow-hidden">

    {{-- Background Image + Gradient Overlay --}}
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop"
             class="w-full h-full object-cover opacity-30 mix-blend-luminosity filter contrast-125"
             alt="" aria-hidden="true" loading="eager" fetchpriority="high">
        <div class="absolute inset-0 bg-gradient-to-b from-surface/50 via-surface/85 to-surface"></div>
    </div>

    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 pt-28 pb-16">
        @if($slides->isNotEmpty())
            @php $slide = $slides->first(); @endphp
            <div class="flex flex-col lg:flex-row items-center justify-between gap-12">

                {{-- Right: Text Content (RTL) --}}
                <div class="w-full lg:w-1/2 text-right">
                    {{-- System Status Badge --}}
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-white/10 glass-panel mb-6">
                        <span class="w-2 h-2 rounded-full bg-brand-500 shadow-neon animate-pulse"></span>
                        <span class="text-xs tracking-widest text-white/80 uppercase font-bold">
                            {{ $slide->badge_text_ar ?: 'V 4.0 Online' }}
                        </span>
                    </div>

                    {{-- Main Heading --}}
                    <h1 class="text-5xl md:text-7xl lg:text-8xl font-black mb-6 leading-none tracking-tighter">
                        @if($slide->title_ar)
                            <span class="text-white">{!! nl2br(e($slide->title_ar)) !!}</span>
                        @else
                            <span class="text-white">جمالك</span><br>
                            <span class="gradient-text bg-[length:200%_auto]">المُحوسب.</span>
                        @endif
                    </h1>

                    {{-- Description --}}
                    <p class="text-lg md:text-xl text-ink-dim mb-10 max-w-lg font-light leading-relaxed">
                        {{ $slide->description_ar ?: 'اكتشفي أرقى منتجات العناية بالبشرة والشعر من الماركات العالمية الأصلية. مختارة بعناية لتبرز جمالك الطبيعي.' }}
                    </p>

                    {{-- CTA Buttons --}}
                    <div class="flex items-center gap-6 flex-wrap">
                        <a href="{{ $slide->button_url ?? route('shop') }}"
                           class="group relative px-8 py-4 rounded-full overflow-hidden bg-white text-surface font-black tracking-wide inline-flex items-center gap-2">
                            <span class="relative z-10">{{ $slide->button_text_ar ?: 'تسوقي الآن' }} <i class="fa-solid fa-arrow-left mr-2"></i></span>
                            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500" style="background: var(--gradient-primary);"></div>
                            <span class="relative z-10 opacity-0 group-hover:opacity-100 absolute inset-0 flex items-center justify-center text-white transition-opacity duration-500">{{ $slide->button_text_ar ?: 'تسوقي الآن' }} <i class="fa-solid fa-arrow-left mr-2"></i></span>
                        </a>
                        <a href="{{ route('shop') }}?sort=newest"
                           class="text-white/60 hover:text-white border-b border-white/15 pb-1 hover:border-brand-500 transition-all font-bold text-sm">
                            استكشاف المنتجات
                        </a>
                    </div>
                </div>

                {{-- Left: Hologram / Scanner Visual --}}
                <div class="w-full lg:w-1/2 relative flex justify-center mt-8 lg:mt-0">
                    <div class="relative w-[280px] h-[400px] md:w-[380px] md:h-[520px] rounded-[2.5rem] border border-white/5 glass-panel overflow-hidden group">

                        {{-- Scanner Line Animation --}}
                        <div class="scanner-line"></div>

                        {{-- Main Image --}}
                        @if($slide->image_url)
                            <img src="{{ $slide->image_url }}" alt="{{ $slide->title_ar ?? '' }}"
                                 class="w-full h-full object-cover filter grayscale sepia-[.1] hue-rotate-[170deg] opacity-60 group-hover:opacity-90 transition-opacity duration-700"
                                 loading="eager">
                        @elseif($slide->product && $slide->product->main_image_url)
                            <img src="{{ $slide->product->main_image_url }}" alt="{{ $slide->product->name_ar }}"
                                 class="w-full h-full object-cover filter grayscale sepia-[.1] hue-rotate-[170deg] opacity-60 group-hover:opacity-90 transition-opacity duration-700"
                                 loading="eager">
                        @elseif($featuredProducts->isNotEmpty())
                            <img src="{{ $featuredProducts->first()->main_image_url }}" alt=""
                                 class="w-full h-full object-cover filter grayscale sepia-[.1] hue-rotate-[170deg] opacity-60 group-hover:opacity-90 transition-opacity duration-700"
                                 loading="eager">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fa-solid fa-flask text-6xl text-white/10"></i>
                            </div>
                        @endif

                        {{-- Grid Overlay --}}
                        <div class="absolute inset-0 bg-grid opacity-40 pointer-events-none"></div>

                        {{-- Floating HUD Nodes --}}
                        <div class="absolute top-[20%] right-4 bg-black/80 border border-brand-500/30 text-brand-500 text-[10px] px-3 py-1.5 rounded-full font-mono animate-float-slow backdrop-blur-md">
                            <i class="fa-solid fa-droplet mr-1"></i> تحليل البشرة: نشط
                        </div>
                        <div class="absolute bottom-[25%] left-4 bg-black/80 border border-accent-500/30 text-accent-500 text-[10px] px-3 py-1.5 rounded-full font-mono animate-float-fast backdrop-blur-md">
                            <i class="fa-solid fa-shield-halved mr-1"></i> أصلي 100%
                        </div>
                        <div class="absolute top-[55%] left-2 md:left-6 bg-black/80 border border-white/10 text-white/60 text-[10px] px-3 py-1.5 rounded-full font-mono animate-float-slow backdrop-blur-md" style="animation-delay: 2s;">
                            <i class="fa-solid fa-box mr-1"></i> شحن سريع
                        </div>

                        {{-- Center Target Ping --}}
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-14 h-14 border border-white/10 rounded-full flex items-center justify-center">
                            <div class="w-1.5 h-1.5 bg-brand-500 rounded-full animate-ping-neon shadow-neon-strong"></div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Fallback: No slides --}}
            <div class="flex flex-col lg:flex-row items-center justify-between gap-12">
                <div class="w-full lg:w-1/2 text-right">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-white/10 glass-panel mb-6">
                        <span class="w-2 h-2 rounded-full bg-brand-500 shadow-neon animate-pulse"></span>
                        <span class="text-xs tracking-widest text-white/80 uppercase font-bold">V 4.0 Online</span>
                    </div>
                    <h1 class="text-5xl md:text-7xl lg:text-8xl font-black mb-6 leading-none tracking-tighter">
                        <span class="text-white">جمالك</span><br>
                        <span class="gradient-text bg-[length:200%_auto]">المُحوسب.</span>
                    </h1>
                    <p class="text-lg md:text-xl text-ink-dim mb-10 max-w-lg font-light leading-relaxed">
                        اكتشفي أرقى منتجات العناية بالبشرة والشعر من الماركات العالمية الأصلية. مختارة بعناية لتبرز جمالك الطبيعي.
                    </p>
                    <div class="flex items-center gap-6 flex-wrap">
                        <a href="{{ route('shop') }}"
                           class="group relative px-8 py-4 rounded-full overflow-hidden bg-white text-surface font-black tracking-wide inline-flex items-center gap-2">
                            <span class="relative z-10">تسوقي الآن <i class="fa-solid fa-arrow-left mr-2"></i></span>
                            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500" style="background: var(--gradient-primary);"></div>
                            <span class="relative z-10 opacity-0 group-hover:opacity-100 absolute inset-0 flex items-center justify-center text-white transition-opacity duration-500">تسوقي الآن <i class="fa-solid fa-arrow-left mr-2"></i></span>
                        </a>
                        <a href="{{ route('shop') }}?sort=newest"
                           class="text-white/60 hover:text-white border-b border-white/15 pb-1 hover:border-brand-500 transition-all font-bold text-sm">
                            استكشاف المنتجات
                        </a>
                    </div>
                </div>
                <div class="w-full lg:w-1/2 relative flex justify-center mt-8 lg:mt-0">
                    <div class="relative w-[280px] h-[400px] md:w-[380px] md:h-[520px] rounded-[2.5rem] border border-white/5 glass-panel overflow-hidden group">
                        <div class="scanner-line"></div>
                        @if($featuredProducts->isNotEmpty() && $featuredProducts->first()->main_image_url)
                            <img src="{{ $featuredProducts->first()->main_image_url }}" alt=""
                                 class="w-full h-full object-cover filter grayscale sepia-[.1] hue-rotate-[170deg] opacity-60 group-hover:opacity-90 transition-opacity duration-700"
                                 loading="eager">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fa-solid fa-flask text-6xl text-white/10"></i>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-grid opacity-40 pointer-events-none"></div>
                        <div class="absolute top-[20%] right-4 bg-black/80 border border-brand-500/30 text-brand-500 text-[10px] px-3 py-1.5 rounded-full font-mono animate-float-slow backdrop-blur-md">
                            <i class="fa-solid fa-droplet mr-1"></i> تحليل البشرة: نشط
                        </div>
                        <div class="absolute bottom-[25%] left-4 bg-black/80 border border-accent-500/30 text-accent-500 text-[10px] px-3 py-1.5 rounded-full font-mono animate-float-fast backdrop-blur-md">
                            <i class="fa-solid fa-shield-halved mr-1"></i> أصلي 100%
                        </div>
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-14 h-14 border border-white/10 rounded-full flex items-center justify-center">
                            <div class="w-1.5 h-1.5 bg-brand-500 rounded-full animate-ping-neon shadow-neon-strong"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Scroll Indicator --}}
    <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 flex flex-col items-center gap-2 opacity-40">
        <span class="text-[10px] uppercase tracking-[0.3em] text-white/50">مرر</span>
        <div class="w-px h-12 bg-gradient-to-b from-white/30 to-transparent"></div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 2: Trust Bar — Social Proof & Quick Stats
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
                <h3 class="font-black text-lg mb-3 text-white">منتجات أصلية مضمونة</h3>
                <p class="text-ink-dim text-sm leading-relaxed">جميع منتجاتنا أصلية 100% ومستوردة من مصادر موثوقة ومعتمدة دولياً. نضمن لكِ الجودة والأصالة في كل طلب.</p>
            </div>
            <div class="glass-panel rounded-2xl p-7 text-right hover:-translate-y-2 transition-all duration-500 group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 group-hover:bg-brand-500/20 transition-colors">
                    <i class="fa-solid fa-truck-fast text-xl text-brand-500"></i>
                </div>
                <h3 class="font-black text-lg mb-3 text-white">توصيل لكل فلسطين</h3>
                <p class="text-ink-dim text-sm leading-relaxed">نوصل طلبك لباب بيتك في الضفة الغربية، القدس، والداخل المحتل. شحن سريع وتتبع مباشر لشحنتك حتى الاستلام.</p>
            </div>
            <div class="glass-panel rounded-2xl p-7 text-right hover:-translate-y-2 transition-all duration-500 group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 group-hover:bg-brand-500/20 transition-colors">
                    <i class="fa-solid fa-tags text-xl text-brand-500"></i>
                </div>
                <h3 class="font-black text-lg mb-3 text-white">أفضل الأسعار والعروض</h3>
                <p class="text-ink-dim text-sm leading-relaxed">أسعار تنافسية مع عروض حصرية وخصومات يومية. الدفع عند الاستلام متاح لراحتك وأمانك التام.</p>
            </div>
            <div class="glass-panel rounded-2xl p-7 text-right hover:-translate-y-2 transition-all duration-500 group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 group-hover:bg-brand-500/20 transition-colors">
                    <i class="fa-solid fa-headset text-xl text-brand-500"></i>
                </div>
                <h3 class="font-black text-lg mb-3 text-white">دعم احترافي متواصل</h3>
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
                    <span class="bg-black/50 backdrop-blur px-3 py-1 rounded-full text-xs border border-white/10 text-white/70">منتجات مميزة</span>
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
     SECTION 5: Categories Grid
     ═══════════════════════════════════════════════════════════════ --}}
@if($categories->isNotEmpty())
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-16 text-right">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-brand-500/20 bg-brand-500/5 mb-6">
                <span class="text-xs text-brand-500 font-bold tracking-widest uppercase">تصفحي الأقسام</span>
            </div>
            <h2 class="text-3xl md:text-5xl font-black mb-4">تسوقي حسب <span class="gradient-text bg-[length:200%_auto]">القسم</span></h2>
            <p class="text-ink-dim max-w-xl text-lg font-light">اكتشفي مجموعتنا الكاملة من منتجات التجميل والعناية بالبشرة والشعر. جميع المنتجات أصلية ومضمونة من أفضل الماركات العالمية.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($categories as $cat)
            <a href="{{ route('shop', ['category' => $cat->slug]) }}"
               class="category-card glass-panel rounded-[2rem] overflow-hidden group relative h-[340px] block transition-all duration-700 hover:-translate-y-2 hover:border-brand-500/20">
                {{-- Gradient overlay --}}
                <div class="absolute inset-0 bg-gradient-to-t from-surface/98 via-surface/30 to-transparent z-10"></div>
                {{-- Top accent bar --}}
                <div class="absolute top-0 right-8 left-8 h-[2px] rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500 z-20" style="background: var(--gradient-primary);"></div>
                @if($cat->sample_image)
                <img src="{{ $cat->sample_image }}" alt="{{ $cat->display_name ?? $cat->name_ar }}"
                     class="absolute inset-0 w-full h-full object-cover filter brightness-50 group-hover:brightness-75 group-hover:scale-110 transition-all duration-1000"
                     loading="lazy">
                @else
                <div class="absolute inset-0 flex items-center justify-center">
                    <i class="fa-solid fa-tag text-8xl text-white/5"></i>
                </div>
                @endif
                <div class="absolute bottom-8 right-8 left-8 z-20 text-right">
                    <h3 class="text-2xl font-black mb-2 text-white group-hover:text-brand-500 transition-colors duration-300">{{ $cat->display_name ?? $cat->name_ar }}</h3>
                    <div class="flex items-center justify-between">
                        <span class="pill-brand text-xs">{{ $cat->products_count }} منتج</span>
                        @if($cat->min_price)
                        <span class="text-ink-dim text-xs">من {{ number_format($cat->min_price, 0) }} ₪</span>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 6: More Products — Horizontal Scroll
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
     SECTION 7: Protocols / CTA Banner
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
