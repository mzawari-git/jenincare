@extends($layoutPath)

@section('title', ($siteSettings['site_name'] ?? 'شركة جنين للتجميل') . ' | منصة الجمال الذكية')
@section('meta_description', 'شركة جنين للتجميل - وجهتك الأولى للعناية بالبشرة والشعر. منتجات أصلية 100%، شحن لكل فلسطين، دفع عند الاستلام، ودعم احترافي.')
@section('meta_keywords', 'شركة جنين للتجميل, جيني كير, عناية بالبشرة, عناية بالشعر, منتجات تجميل, فلسطين, شحن مجاني, منتجات أصلية, جمال, ذكاء اصطناعي')

@push('scripts')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "{{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}",
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
  "name": "{{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}",
  "url": "{{ url('/') }}",
  "potentialAction": { "@type": "SearchAction", "target": "{{ url('/shop') }}?search={search_term_string}", "query-input": "required name=search_term_string" }
}
</script>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════════════════════════
     SECTION 1: HERO — Dynamic Title + Product Slideshow
     ═══════════════════════════════════════════════════════════════ --}}
@php
$allPhrases = [
    'لمسات ساحرة تبدأ بمنتجات استثنائية... اختاري الأفضل مع جنين.',
    'جودة لا تُضاهى لجمال يدوم... مستحضرات صُممت لتبرز إشراقتكِ.',
    'ألوان غنية وتركيبات آمنة، لتجربة جمال تفوق التوقعات.',
    'سر الإطلالة المثالية يبدأ من هنا... دعي الجودة تتحدث عنكِ.',
    'منتجات أصلية 100%... لأن بشرتك تستحق الأفضل دائماً.',
    'أحدث التقنيات العالمية بين يديكِ، لنتائج احترافية تبهر عملائكِ.',
    'ارتقي بمستوى خدماتكِ مع أجهزة الجيل الجديد من شركة جنين للتجميل.',
    'دقة الأداء، وسرعة النتائج... التكنولوجيا الذكية في خدمة الجمال.',
    'استثمري في نجاحكِ مع أجهزة صُممت لتدوم وتقدم الأفضل.',
    'من التصميم إلى التنفيذ... نجهز صالونكِ ليكون وجهة الفخامة الأولى.',
    'أثاث عصري ومعدات متكاملة، نبني لكِ مساحة تعكس رقي أعمالكِ.',
    'راحة لعملائكِ وتميز لمشروعكِ، مع حلول جنين الشاملة لتجهيز الصالونات.',
    'لا تساومي على أناقة مكانكِ... دعينا نصنع لكِ صالون أحلامكِ.',
    'شركة جنين للتجميل: خيار المحترفين الأول.',
    'كل ما يخص عالم الجمال والأناقة... تحت سقف واحد.',
    'شريككِ الموثوق لرحلة نجاح وتألق مستمرة.',
    'جودة نثق بها، وخدمة تلبي تطلعاتكم.',
    'اكتشفي أسرار الجمال مع أفخر الماركات العالمية الأصلية.',
    'صالونكِ المتكامل... من الفكرة إلى الواقع مع خبراء شركة جنين للتجميل.',
    'منتجات أصلية، أجهزة احترافية، تجهيز متكامل... في مكان واحد.',
    'إشراقة وردية تلفت الأنظار.. بلمسات احترافية من جنين.',
    'دعي جمالك يتألق بنعومة ورقي لا مثيل لهما.',
    'رفاهية الجمال في كل تفصيل، لتكوني الأجمل دائماً.',
    'تركيبات غنية وعصرية تبرز ملامحك بأناقة ساحرة.',
    'جاذبية تنبض بالحياة.. لأنكِ أيقونة الجمال المستمر.',
    'تكنولوجيا متطورة ترسم مستقبل صالونك باحترافية عالية.',
    'أداء استثنائي يضمن لعملائك تجربة لا تُنسى.',
    'لأن التميز هدفك.. وضعنا بين يديك أحدث أجهزة التجميل العالمية.',
    'دقة الابتكار لنتائج مبهرة تعزز ثقة عملائك يوماً بعد يوم.',
    'استثمري في القمة.. أجهزة مصممة لرواد عالم التجميل.',
    'أثاث يجمع بين الرفاهية والعملية.. لصالون ينبض بالفخامة.',
    'نصمم مساحتك برؤية عصرية تعكس هوية علامتك التجارية.',
    'راحة مطلقة وتصميم نقي يلهم كل من يزور صالونك.',
    'من الفكرة إلى التألق.. تجهيزات متكاملة لبيئة عمل إبداعية ومريحة.',
    'اجعلي صالونك تحفة فنية تتحدث عن رقي اختياراتك.',
    'شركة جنين للتجميل.. بصمتك الخاصة في عالم الأناقة والاحتراف.',
    'نجمع لك أسرار الجمال والتجهيز الاحترافي في مكان واحد.',
    'جودة تتحدث عن نفسها.. وتفاصيل دقيقة تصنع الفارق.',
    'روائع التجميل والتجهيزات.. لنجاح وتألق لا يعرف الحدود.',
    'واجهتك الأولى لكل ما يبرز الجمال ويرتقي بأعمالك إلى القمة.',
    // ═══════ NEW MARKETING PHRASES ═══════
    'اكتشفي عالم الجمال بأفضل الماركات العالمية الأصلية.',
    'بشرتكِ تستحق الأفضل... مستحضرات طبيعية بنتائج مضمونة.',
    'تألقي بثقة مع منتجات اختارها خبراء الجمال بعناية فائقة.',
    'كل لمسة تنبض بالحياة... جمالكِ يبدأ من اختياراتكِ الذكية.',
    'حولي روتين العناية اليومي إلى لحظات سحرية من الاسترخاء.',
    'منتجات صُنعت بحب، لترسم ابتسامة الثقة على وجهكِ كل يوم.',
    'لأنكِ تستحقين الأفضل... نقدم لكِ تجربة تسوق استثنائية.',
    'مع جنين، كل يوم هو يوم جمال... اكتشفي سر الإشراقة.',
    'لمسة واحدة تكفي لتغيير كل شيء... اختاري الذكاء في الجمال.',
    'نؤمن بأن الجمال الحقيقي يبدأ من الداخل... ونكمله من الخارج.',
    'تسوقي بذكاء، تألقي بثقة... جنين وجهتكِ الأولى للأناقة.',
    'مستحضرات فاخرة بأسعار تنافسية... جمالكِ لم يعد حلماً.',
    'نقلب الموازين في عالم التجميل... جودة عالمية بخدمة محلية.',
    'مع كل طلب، نعدكِ بتجربة لا تُنسى... من الاختيار حتى التوصيل.',
    'لمسة ناعمة، عطر يدوم، جمال يتجدد... جنين تفهمكِ.',
    'احصلي على إطلالة النجمات مع منتجات احترافية في منزلكِ.',
    'نبتكر لكِ الحلول... لتبدعي أنتِ في الجمال والأناقة.',
    'جمالكِ هو أولويتنا... ورضاكِ هو مكسبنا الأكبر.',
    'اختياركِ لـ جنين = اختيار للثقة والجودة والأصالة.',
    'نسافر حول العالم لنجلب لكِ أحدث ما توصل إليه علم التجميل.',
    'صالونكِ يستحق الأفضل... نجهزه لكِ بأعلى معايير الفخامة.',
    'أجهزة احترافية تضمن لكِ نتائج مذهلة في كل استخدام.',
    'نبني لكِ قصة نجاح... تبدأ بصالون متكامل وجمال لا حدود له.',
    'استثمري في جمالكِ اليوم، واجني الثناء غداً مع جنين.',
    'لمسة احترافية تُحدث فرقاً كبيراً... اكتشفي سر التألق.',
    'نحنُ لسنا مجرد متجر... نحنُ وجهتكِ الشاملة لعالم الجمال.',
    'كل منتج يروي قصة... قصة جودة وأصالة وتميز من جنين.',
    'بشرة نضرة، شعر صحي، إطلالة ساحرة... كل هذا وأكثر مع جنين.',
    'نحوّل حلم الجمال إلى واقع ملموس... مع منتجات موثوقة ومضمونة.',
    'تسوقي الآن، وابدئي رحلتكِ نحو جمال لا يقاوم.',
    'جنين... حيث تلتقي الأحلام بالحقيقة في عالم التجميل.',
    'تألقي دائماً، مع منتجات اخترناها لكِ بكل حب ودقة.',
    'اختاري الذكاء، اختاري الجودة، اختاري جنين للتجميل.',
    'نعيد تعريف الجمال في فلسطين... جودة عالمية بأيدٍ محلية.',
];

// Hero two-line headlines (independent from product slides)
$heroHeadlines = [
    ['line1' => 'منتجات أصلية 100%', 'line2' => 'جمال لا يُقاوم.'],
    ['line1' => 'بشرتكِ تستحق الأفضل', 'line2' => 'اختاري من جنين.'],
    ['line1' => 'أحدث الماركات العالمية', 'line2' => 'بين يديكِ الآن.'],
    ['line1' => 'صالونكِ المثالي', 'line2' => 'فخامة متناهية.'],
    ['line1' => 'تقنيات متطورة', 'line2' => 'نتائج احترافية.'],
    ['line1' => 'تجهيزات متكاملة', 'line2' => 'لأفضل صالون.'],
    ['line1' => 'جودة عالمية', 'line2' => 'خدمة محلية.'],
    ['line1' => 'اكتشفي سر الإشراقة', 'line2' => 'مع منتجات جنين.'],
    ['line1' => 'أجهزة احترافية', 'line2' => 'لمستقبل صالونكِ.'],
    ['line1' => 'مستحضرات طبيعية', 'line2' => 'بنتائج مضمونة.'],
    ['line1' => 'تألقي بثقة', 'line2' => 'كل يوم مع جنين.'],
    ['line1' => 'شحن سريع', 'line2' => 'لكل فلسطين.'],
    ['line1' => 'دفع عند الاستلام', 'line2' => 'ثقة وأمان.'],
    ['line1' => 'أفضل الأسعار', 'line2' => 'لأجود المنتجات.'],
    ['line1' => 'خبراء الجمال', 'line2' => 'يختارون لكِ.'],
    ['line1' => 'روتين العناية', 'line2' => 'يتحول إلى سحر.'],
    ['line1' => 'كل منتج يروي', 'line2' => 'قصة تميز.'],
    ['line1' => 'استثمري في جمالكِ', 'line2' => 'واجني الثناء.'],
    ['line1' => 'لمسة واحدة تكفي', 'line2' => 'لتغيير كل شيء.'],
    ['line1' => 'جنين... وجهتكِ', 'line2' => 'لعالم الجمال.'],
    ['line1' => 'إطلالة النجمات', 'line2' => 'في منزلكِ.'],
    ['line1' => 'نبني لكِ قصة نجاح', 'line2' => 'من الصفر إلى القمة.'],
    ['line1' => 'منتجات اخترناها', 'line2' => 'بكل حب ودقة.'],
    ['line1' => 'نعيد تعريف الجمال', 'line2' => 'في فلسطين.'],
    // ═════ NEW HIGH-IMPACT HEADLINES ═════
    ['line1' => 'ماركات عالمية أصلية', 'line2' => 'بأسعار تنافسية.'],
    ['line1' => 'توصيل خلال 24 ساعة', 'line2' => 'لكل مدن فلسطين.'],
    ['line1' => 'دعم فني على مدار اليوم', 'line2' => 'نحنُ هنا لمساعدتكِ.'],
    ['line1' => 'أكثر من 800 منتج', 'line2' => 'في متجركِ المفضل.'],
    ['line1' => 'ثقة 15,000 عميلة', 'line2' => 'لا تخطئي الاختيار.'],
    ['line1' => 'منتجات مختارة بعناية', 'line2' => 'لأنكِ تستحقين.'],
    ['line1' => 'أجهزة صالونات احترافية', 'line2' => 'للنجاح والتميز.'],
    ['line1' => 'تجميل احترافي', 'line2' => 'يبدأ من اختياركِ.'],
    ['line1' => 'لمسة جنين', 'line2' => 'تفرق معكِ دائماً.'],
    ['line1' => 'جودة لا تُضاهى', 'line2' => 'وأصالة تدوم.'],
    ['line1' => 'أنقي المنتجات', 'line2' => 'من مصادر موثوقة.'],
    ['line1' => 'تسوقي بذكاء', 'line2' => 'وتألقي بثقة.'],
    ['line1' => 'كل ما تحتاجينه', 'line2' => 'للعناية والجمال.'],
    ['line1' => 'صالونكِ يستحق الأفضل', 'line2' => 'ونحنُ نقدمه لكِ.'],
    ['line1' => 'تجربة تسوق فريدة', 'line2' => 'مع جنين للتجميل.'],
    ['line1' => 'ثقة وجودة', 'line2' => 'في كل طلب.'],
    ['line1' => 'جمالكِ.. مسؤوليتنا', 'line2' => 'ونحنُ نحب ذلك.'],
];

// Product slides with matching titles
$slidesData = [];
$slideProductIds = [];
$catIds = $categories->filter(fn($c) => $c->products_count > 0)->shuffle()->take(8);
foreach ($catIds as $cat) {
    $p = \App\Models\Product::where('category_id', $cat->id)->where('status', 'active')->inRandomOrder()->first();
    if (!$p) continue;
    $slideProductIds[$cat->id] = $p->id;
    $catName = $cat->display_name ?? $cat->name_ar;
    // استخدام مصطلحات آمنة لتجاوز فلاتر المنصات الإعلانية - تجنب كلمة "ليزر"
    $safeDeviceTerms = ['جهاز', 'أجهزة', 'تقنية', 'تكنولوجيا', 'نبض', 'ضوئي', 'متقدم', ' advanced', 'device', 'technology'];
    $isDevices = false;
    foreach ($safeDeviceTerms as $term) {
        if (str_contains($catName, $term)) { $isDevices = true; break; }
    }
    $isSalon = str_contains($catName, 'صالون') || str_contains($catName, 'تجهيز');
    $slidesData[] = [
        'product' => $p,
        'category' => $cat,
        'title_line1' => $isDevices ? 'تقنيات متطورة' : ($isSalon ? 'صالونك المثالي' : 'منتجات أصلية'),
        'title_line2' => $isDevices ? 'نتائج احترافية.' : ($isSalon ? 'فخامة متناهية.' : 'جمال لا يُقاوم.'),
        'color' => $isDevices ? '#06b6d4' : ($isSalon ? '#d4af37' : '#ec4899'),
    ];
}
if (empty($slidesData) && $featuredProducts->isNotEmpty()) {
    $slidesData[] = [
        'product' => $featuredProducts->first(),
        'category' => null,
        'title_line1' => 'منتجات أصلية',
        'title_line2' => 'نتائج مبهرة.',
        'color' => '#ec4899',
    ];
}
// Pre-fetch sub-products in one batch to avoid N+1
$subProductsCache = [];
if (!empty($slideProductIds)) {
    $allSubProducts = \App\Models\Product::whereIn('category_id', array_keys($slideProductIds))
        ->where('status', 'active')
        ->whereNotIn('id', array_values($slideProductIds))
        ->inRandomOrder()
        ->get()
        ->groupBy('category_id');
    foreach ($allSubProducts as $cid => $prods) {
        $subProductsCache[$cid] = $prods->take(2);
    }
}
@endphp

<section id="hero" class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop"
             class="w-full h-full object-cover opacity-10 mix-blend-luminosity"
             alt="" aria-hidden="true" loading="eager" fetchpriority="high">
        <div class="absolute inset-0 bg-gradient-to-b from-[#1a0533] via-[#2d0a5c]/95 to-[#0f172a]/90"></div>
    </div>

    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 pt-4 md:pt-8 pb-20 md:pb-28">
        <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-12">

            <div class="w-full lg:w-[45%] text-center">

                {{-- Logo — clean, no border --}}
                <div class="mb-5 flex justify-center">
                    @if(!empty($siteSettings['site_logo_url']))
                    <img src="{{ $siteSettings['site_logo_url'] }}" alt="جنين للتجميل" class="h-8 sm:h-12 md:h-20 lg:h-28 w-auto object-contain drop-shadow-lg" style="max-height:112px;max-width:280px;">
                    @else
                    <span class="text-xl sm:text-2xl md:text-3xl tracking-wider text-white font-black" style="letter-spacing:0.12em;">جنين للتجميل</span>
                    @endif
                </div>

                {{-- ═══════════════════════════════════════════
                     PREMIUM HERO CARD — Animated gradient + floating elements
                     ═══════════════════════════════════════════ --}}
                <div id="heroCard" class="relative mb-6 select-none">
                    <div class="relative overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] p-6 sm:p-8 md:p-10" style="background:linear-gradient(145deg,rgba(60,20,60,0.55) 0%,rgba(40,15,45,0.45) 40%,rgba(var(--brand-500-rgb,255,42,133),0.18) 100%);border:1.5px solid rgba(236,72,153,0.25);backdrop-filter:blur(24px);box-shadow:0 24px 80px rgba(0,0,0,0.4),inset 0 1px 0 rgba(255,255,255,0.08),0 0 120px rgba(var(--brand-500-rgb,255,42,133),0.15);">

                        {{-- Ambient glow orbs --}}
                        <div class="absolute -top-28 -right-28 w-56 h-56 rounded-full opacity-20 pointer-events-none" style="background:radial-gradient(circle,var(--brand-500),transparent 70%);filter:blur(60px);animation:glowPulse 5s ease-in-out infinite;"></div>
                        <div class="absolute -bottom-28 -left-28 w-56 h-56 rounded-full opacity-12 pointer-events-none" style="background:radial-gradient(circle,#06b6d4,transparent 70%);filter:blur(60px);animation:glowPulse 6s ease-in-out infinite 1s;"></div>

                        {{-- Animated border shimmer --}}
                        <div class="absolute inset-0 rounded-[2rem] sm:rounded-[2.5rem] pointer-events-none" style="background:linear-gradient(135deg,transparent 40%,rgba(255,255,255,0.04) 50%,transparent 60%);background-size:200% 200%;animation:borderShimmer 5s ease-in-out infinite;"></div>

                        {{-- Floating decorative elements --}}
                        <div class="absolute top-4 right-6 text-white/10 pointer-events-none animate-bounce" style="animation-duration:3s;"><i class="ph-fill ph-sparkle text-xl"></i></div>
                        <div class="absolute bottom-8 left-6 text-white/10 pointer-events-none animate-bounce" style="animation-duration:4s;animation-delay:1s;"><i class="ph-fill ph-star text-lg"></i></div>
                        <div class="absolute top-1/2 right-3 text-white/5 pointer-events-none animate-pulse"><i class="ph-fill ph-diamond text-xs"></i></div>

                        {{-- Premium badge --}}
                        <div class="flex justify-center mb-5 relative z-10">
                            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-[10px] font-black tracking-[0.15em] uppercase" style="background:rgba(var(--brand-500-rgb,255,42,133),0.12);color:var(--brand-500);border:1px solid rgba(var(--brand-500-rgb,255,42,133),0.2);box-shadow:0 0 30px rgba(var(--brand-500-rgb,255,42,133),0.1),inset 0 1px 0 rgba(255,255,255,0.1);">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-60"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
                                </span>
                                عروض حصرية
                            </span>
                        </div>

                        {{-- Rotating Headline with scale animation --}}
                        <div class="relative overflow-hidden mb-3" style="height:100px;">
                            @foreach($heroHeadlines as $i => $headline)
                            <div class="hero-headline absolute w-full text-center px-2" style="top:0;left:0;opacity:{{ $i === 0 ? '1' : '0' }};transform:translateY({{ $i === 0 ? '0' : '20px' }}) scale({{ $i === 0 ? '1' : '0.95' }});transition:opacity 0.7s cubic-bezier(0.4,0,0.2,1),transform 0.7s cubic-bezier(0.4,0,0.2,1);pointer-events:{{ $i === 0 ? 'auto' : 'none' }};" data-headline="{{ $i }}">
                                <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-[2.75rem] font-black leading-[1.12] tracking-tight">
                                    <span class="block hero-line-1" style="background:linear-gradient(135deg,#fff 30%,#f0abfc 70%,#fff 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;background-size:200% auto;animation:shineText 4s linear infinite;">{{ $headline['line1'] }}</span>
                                    <span class="block mt-1.5" style="color:rgba(255,255,255,0.88);text-shadow:0 0 25px rgba(255,255,255,0.15),0 3px 8px rgba(0,0,0,0.3);">{{ $headline['line2'] }}</span>
                                </h1>
                            </div>
                            @endforeach
                        </div>

                        {{-- Elegant divider --}}
                        <div class="flex items-center gap-3 justify-center mb-4 relative z-10">
                            <div class="h-px flex-1 max-w-[50px]" style="background:linear-gradient(to left,transparent,rgba(255,255,255,0.25));"></div>
                            <div class="w-7 h-7 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);">
                                <i class="ph-fill ph-sparkle text-brand-500/70 text-xs"></i>
                            </div>
                            <div class="h-px flex-1 max-w-[50px]" style="background:linear-gradient(to right,transparent,rgba(255,255,255,0.25));"></div>
                        </div>

                        {{-- Rotating Marketing Phrase --}}
                        <div class="relative overflow-hidden" style="height:50px;">
                            @foreach($allPhrases as $i => $phrase)
                            <p class="hero-phrase absolute w-full text-center text-sm sm:text-base md:text-lg font-semibold leading-relaxed px-4"
                               style="top:0;left:0;color:rgba(255,255,255,0.6);opacity:{{ $i === 0 ? '1' : '0' }};transform:translateY({{ $i === 0 ? '0' : '10px' }});transition:opacity 0.6s ease,transform 0.6s ease;pointer-events:{{ $i === 0 ? 'auto' : 'none' }};"
                               data-phrase="{{ $i }}">{{ $phrase }}</p>
                            @endforeach
                        </div>

                        {{-- Progress dots with count --}}
                        <div class="flex items-center justify-center gap-2 mt-5 relative z-10">
                            <span class="text-[10px] font-bold text-white/30" id="heroCounter">1 / {{ count($heroHeadlines) }}</span>
                            <div class="flex gap-1.5">
                                @foreach($heroHeadlines as $i => $h)
                                <span class="hero-dot block h-1 rounded-full transition-all duration-500 {{ $i === 0 ? 'bg-gradient-to-r from-brand-400 to-brand-600 w-5' : 'bg-white/15 w-1' }}"></span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CTA Buttons — مرنة وغير إلزامية لتجنب Engagement-Bait على المنصات الإعلانية --}}
                <div class="flex flex-col items-center gap-3 mb-8">
                    <a href="{{ route('shop') }}" class="w-full sm:w-72 px-8 py-4 rounded-full font-black text-sm tracking-wide inline-flex items-center justify-center gap-2 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-0.5" style="background:#ffffff;color:#0f172a;">
                        اكتشفي المنتجات <i class="fa-solid fa-arrow-left mr-1"></i>
                    </a>
                    <a href="{{ route('shop') }}" class="text-white/60 hover:text-white transition-colors font-medium text-sm">
                        تصفحي المتجر — المتابعة اختيارية
                    </a>
                </div>
            </div>

            {{-- Product Slides --}}
            <div class="w-full lg:w-[55%] relative flex justify-center">
                <div class="relative w-full max-w-lg">
                    @foreach($slidesData as $index => $slide)
                    @php $main = $slide['product']; $cat = $slide['category']; @endphp
                    <div class="hero-slide rounded-3xl overflow-hidden p-3 {{ $index === 0 ? '' : 'hidden' }}" data-slide="{{ $index }}" style="background:rgba(255,255,255,0.08);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.15);">
                        <a href="{{ route('product.show', $main->slug) }}" class="block relative rounded-2xl overflow-hidden bg-surface-alt group" style="height:280px;">
                            @if($main->main_image_url)
                            <img src="{{ $main->optimizedImageUrl(800) }}" alt="{{ $main->name_ar }}" width="800" height="380"
                                 class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-700" loading="{{ $index === 0 ? 'eager' : 'lazy' }}"{{ $index === 0 ? ' fetchpriority="high"' : '' }}>
                            @else
                            <div class="w-full h-full flex items-center justify-center"><i class="fa-solid fa-flask text-5xl text-ink-dim/15"></i></div>
@endif
                            <div class="absolute bottom-0 left-0 right-0 p-5 bg-gradient-to-t from-surface/95 via-surface/70 to-transparent">
                                @if($cat)<span class="inline-block px-2.5 py-1 rounded-full text-white text-[11px] font-bold mb-2" style="background:{{ $slide['color'] }};">{{ $cat->display_name ?? $cat->name_ar }}</span>@endif
                                <h3 class="text-lg font-black text-white mb-1">{{ $main->name_ar }}</h3>
                                <span class="font-black text-2xl md:text-3xl" style="color:#ffffff;text-shadow:0 0 12px rgba(255,255,255,0.3),0 0 2px rgba(255,255,255,0.5);">{{ number_format($main->final_b2c_price ?? $main->b2c_price, 0) }} ₪</span>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     SECTION: Categories — BringUs Style (Circular image, white card, pink border)
     ═══════════════════════════════════════════════════════════════ --}}
@if($categories->isNotEmpty())
<section class="categories-section py-12 relative z-20" style="background:#fafafa;">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-black mb-2" style="color:#1a1a1a;">تسوقي حسب <span style="color:#ec4899;">القسم</span></h2>
            <p class="max-w-2xl mx-auto text-sm md:text-base" style="color:#888;">اكتشفي منتجات أصلية من أفضل الماركات العالمية في جميع أقسام التجميل والعناية</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 md:gap-4">
            @foreach($categories as $cat)
            @php
                $arName = $cat->display_name ?? $cat->name_ar;
                $enName = $cat->name_en ?? '';
            @endphp
            <a href="{{ route('shop', ['category' => $cat->slug]) }}"
               class="group flex flex-col items-center text-center rounded-2xl p-3 md:p-4 transition-all duration-300 hover:shadow-md"
               style="background:#fff;border:1.5px solid #E8D5E0;">
                {{-- Circular image --}}
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-full overflow-hidden flex-shrink-0 mb-3" style="border:2px solid #f3e8f3;background:#fdf2f8;">
                    @if($cat->image)
                    <img src="{{ asset($cat->image) }}" alt="{{ $arName }}"
                         class="w-full h-full object-cover" loading="lazy">
                    @else
                    <div class="w-full h-full flex items-center justify-center" style="background:#fce7f3;">
                        <i class="fa-solid fa-tag text-lg" style="color:#ec4899;"></i>
                    </div>
                    @endif
                </div>
                {{-- Arabic name --}}
                <h3 class="font-bold text-xs md:text-sm mb-0.5 leading-tight" style="color:#1a1a1a;">
                    {{ $arName }}
                </h3>
                {{-- English name --}}
                @if($enName)
                <span class="text-[10px] md:text-[11px]" style="color:#999;">{{ $enName }}</span>
                @endif
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- WhatsApp FAB --}}
@if(!empty($siteSettings['whatsapp_number']))
<a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$siteSettings['whatsapp_number']) }}" target="_blank" rel="noopener"
   class="fixed z-[999] flex items-center justify-center shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1 hover:scale-105"
   style="bottom:24px;right:20px;width:56px;height:56px;background:#25D366;border-radius:50%;" aria-label="واتساب">
    <i class="ph-fill ph-whatsapp-logo text-white text-2xl"></i>
</a>
@endif

{{-- Hide theme switcher on mobile; keep floating social visible --}}
@push('styles')
<style>
    @media (max-width:1024px) {
        #themeSwitcher { display: none !important; }
        .floating-social-v3 { opacity: 0.7; }
    }
    @media (min-width:1025px) {
        .floating-social-v3 { opacity: 0.4; transition: opacity 0.3s; }
        .floating-social-v3:hover { opacity: 1; }
        #themeSwitcher { opacity: 0.4; transition: opacity 0.3s; }
        #themeSwitcher:hover { opacity: 1; }
    }
</style>
@endpush

<script>
(function() {
    // Unified rotator: headline + phrase + dot change together
    var headlines = document.querySelectorAll('.hero-headline');
    var phrases = document.querySelectorAll('.hero-phrase');
    var heroDots = document.querySelectorAll('.hero-dot');
    var totalH = headlines.length;
    var totalP = phrases.length;
    var currentIdx = 0, interval;

    function showSlide(idx) {
        // Headlines with scale
        headlines.forEach(function(h, i) {
            var active = i === idx;
            h.style.opacity = active ? '1' : '0';
            h.style.transform = active ? 'translateY(0) scale(1)' : 'translateY(20px) scale(0.95)';
            h.style.pointerEvents = active ? 'auto' : 'none';
        });
        // Phrases (cycle through independently mapped to headline index)
        var phraseIdx = idx % totalP;
        phrases.forEach(function(p, i) {
            p.style.opacity = i === phraseIdx ? '1' : '0';
            p.style.transform = i === phraseIdx ? 'translateY(0)' : 'translateY(10px)';
        });
        // Dots with gradient
        heroDots.forEach(function(d, i) {
            d.className = i === idx
                ? 'hero-dot block h-1 rounded-full bg-gradient-to-r from-brand-400 to-brand-600 w-5 transition-all duration-500'
                : 'hero-dot block h-1 rounded-full bg-white/15 w-1 transition-all duration-500';
        });
        // Counter
        var counter = document.getElementById('heroCounter');
        if (counter) counter.textContent = (idx + 1) + ' / ' + totalH;
        currentIdx = idx;
    }

    function next() { showSlide((currentIdx + 1) % totalH); }
    interval = setInterval(next, 4000);

    // Pause on hover
    var heroCard = document.getElementById('heroCard');
    if (heroCard) {
        heroCard.addEventListener('mouseenter', function() { clearInterval(interval); });
        heroCard.addEventListener('mouseleave', function() { interval = setInterval(next, 4000); });
    }

    // Product slides rotator (independent)
    var slides = document.querySelectorAll('.hero-slide');
    var totalS = slides.length;
    var currentS = 0, sInterval;

    function showProductSlide(idx) {
        slides.forEach(function(s) { s.classList.add('hidden'); });
        var s = document.querySelector('.hero-slide[data-slide="' + idx + '"]');
        if (s) s.classList.remove('hidden');
        currentS = idx;
    }
    function nextProductSlide() { showProductSlide((currentS + 1) % totalS); }
    if (totalS > 1) sInterval = setInterval(nextProductSlide, 6000);

    slides.forEach(function(s) {
        s.addEventListener('mouseenter', function() { clearInterval(sInterval); });
        s.addEventListener('mouseleave', function() { if (totalS > 1) sInterval = setInterval(nextProductSlide, 6000); });
    });
})();
</script>

<style>
    @keyframes borderShimmer {
        0% { background-position: 200% 200%; }
        100% { background-position: -200% -200%; }
    }
    @keyframes glowPulse {
        0%, 100% { opacity: 0.15; transform: scale(1); }
        50% { opacity: 0.25; transform: scale(1.1); }
    }
    @keyframes shineText {
        0% { background-position: 200% center; }
        100% { background-position: -200% center; }
    }
    .value-card:hover { transform: translateY(-6px); border-color: rgba(255,42,133,0.15); box-shadow: 0 12px 40px rgba(0,0,0,0.3), var(--neon-glow); }
    @media (max-width: 767px) {
        .home-sections { display: flex; flex-direction: column; }
    }
    @media (min-width: 768px) {
        .home-sections { display: flex; flex-direction: column; }
        .home-sections .products-section { order: 2; }
        .home-sections .categories-section { order: 1; }
    }
</style>

<div class="home-sections">

{{-- ═══════════════════════════════════════════════════════════════
     SECTION: Products FIRST on mobile
     ═══════════════════════════════════════════════════════════════ --}}
<section id="products" class="products-section py-20 relative">

    {{-- Mobile: visual separator --}}
    <div class="md:hidden -mt-20 mb-8 text-center">
        <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full font-black text-sm shadow-lg" style="background:var(--gradient-primary);color:#fff;">
            <i class="fa-solid fa-star text-xs"></i> منتجاتنا المميزة <i class="fa-solid fa-star text-xs"></i>
        </div>
    </div>

    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_30%_50%,rgba(var(--brand-500-rgb,255,42,133),0.04),transparent_60%)] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="mb-16 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-brand-500/20 bg-brand-500/5 mb-6">
                <span class="text-xs text-brand-500 font-bold tracking-widest uppercase">مختبر الجمال</span>
            </div>
            <h2 class="text-3xl md:text-5xl font-black mb-4">منتجات مختارة <span class="gradient-text bg-[length:200%_auto]">بعناية فائقة</span></h2>
            <p class="text-ink-dim max-w-2xl mx-auto text-lg font-light">كل منتج في متجرنا تم انتقاؤه بعناية من أفضل الماركات العالمية ليكون جزءاً من روتين عنايتك الشخصي. منتجات أصلية، نتائج مضمونة.</p>
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
                <img src="{{ $bigProduct->optimizedImageUrl(800, 450) }}" alt="{{ $bigProduct->name_ar }}" width="800" height="450"
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
                                class="w-12 h-12 rounded-full bg-white style="color:#0f172a;" flex items-center justify-center hover:shadow-neon transition-all"
                                aria-label="إضافة للسلة">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            @php $secondProduct = $newProducts->first() ?? $featuredProducts->skip(1)->first(); @endphp
            @if($secondProduct)
            <div class="md:col-span-5 lg:col-span-4 group relative rounded-[2rem] overflow-hidden glass-panel border border-white/5 h-[450px] cursor-pointer"
                 onclick="window.location='{{ route('product.show', $secondProduct->slug) }}'">
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent z-10"></div>
                <div class="absolute inset-0 bg-accent-500/5 mix-blend-overlay z-10 group-hover:bg-accent-500/10 transition-colors"></div>
                @if($secondProduct->main_image_url)
                <img src="{{ $secondProduct->optimizedImageUrl(600, 450) }}" alt="{{ $secondProduct->name_ar }}" width="600" height="450"
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
                <div class="absolute top-0 right-8 left-8 h-[2px] rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500" style="background: var(--gradient-primary);"></div>
                <div class="relative z-10 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-accent-500/10 flex items-center justify-center mb-6 shadow-accent-neon mx-auto">
                        <i class="fa-solid fa-microchip text-2xl text-accent-500"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-4" style="color: var(--ink);">روتين عناية<br>مصمم خصيصاً لكِ.</h3>
                    <p class="text-ink-dim text-sm leading-relaxed">
                        نختار لكِ أفضل المنتجات المناسبة لنوع بشرتك واحتياجاتك. تصفحي مجموعتنا المميزة من منتجات العناية بالبشرة والشعر، وتمتعي بتجربة تسوق فريدة مع شحن سريع ودفع آمن.
                    </p>
                </div>
                <div class="mt-8 text-center">
                    <a href="{{ route('shop') }}" class="text-accent-500 font-bold flex items-center justify-center gap-2 hover:gap-4 transition-all group/link">
                        تصفحي المتجر <i class="fa-solid fa-arrow-left text-sm group-hover/link:-translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>

            @php $thirdProduct = $featuredProducts->skip(1)->first() ?? $newProducts->skip(1)->first() ?? $featuredProducts->skip(2)->first(); @endphp
            @if($thirdProduct)
            <div class="md:col-span-7 group relative rounded-[2rem] overflow-hidden glass-panel border border-white/5 h-[300px] cursor-pointer"
                 onclick="window.location='{{ route('product.show', $thirdProduct->slug) }}'">
                <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/40 to-transparent z-10"></div>
                @if($thirdProduct->main_image_url)
                <img src="{{ $thirdProduct->optimizedImageUrl(600, 300) }}" alt="{{ $thirdProduct->name_ar }}" width="600" height="300"
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
                            class="px-6 py-2.5 bg-white style="color:#0f172a;" rounded-full font-bold transition-all text-sm hover:shadow-neon hover:scale-105 inline-flex items-center gap-2">
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
     SECTION 3: Trust Bar — Social Proof & Quick Stats
     ═══════════════════════════════════════════════════════════════ --}}
<section class="py-12 border-b border-ink/10">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div class="glass-panel rounded-2xl p-6">
                <span class="text-3xl md:text-4xl font-black gradient-text bg-[length:200%_auto] block mb-2">+{{ \App\Models\Product::count() }}</span>
                <span class="text-sm text-ink-muted">منتج أصلي</span>
            </div>
            <div class="glass-panel rounded-2xl p-6">
                <span class="text-3xl md:text-4xl font-black text-ink block mb-2">15,000+</span>
                <span class="text-sm text-ink-muted">عميلة سعيدة</span>
            </div>
            <div class="glass-panel rounded-2xl p-6">
                <span class="text-3xl md:text-4xl font-black text-ink block mb-2">4.9</span>
                <span class="text-sm text-ink-muted">تقييم العملاء</span>
            </div>
            <div class="glass-panel rounded-2xl p-6">
                <span class="text-3xl md:text-4xl font-black text-ink block mb-2">24H</span>
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
            <div class="glass-panel rounded-2xl p-7 text-center hover:-translate-y-2 transition-all duration-500 group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 mx-auto group-hover:bg-brand-500/20 transition-colors">
                    <i class="fa-solid fa-certificate text-xl text-brand-500"></i>
                </div>
                <h3 class="font-black text-lg mb-3 text-ink">منتجات أصلية مضمونة</h3>
                <p class="text-ink-dim text-sm leading-relaxed">جميع منتجاتنا أصلية 100% ومستوردة من مصادر موثوقة ومعتمدة دولياً. نضمن لكِ الجودة والأصالة في كل طلب.</p>
            </div>
            <div class="glass-panel rounded-2xl p-7 text-center hover:-translate-y-2 transition-all duration-500 group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 mx-auto group-hover:bg-brand-500/20 transition-colors">
                    <i class="fa-solid fa-truck-fast text-xl text-brand-500"></i>
                </div>
                <h3 class="font-black text-lg mb-3 text-ink">توصيل لكل فلسطين</h3>
                <p class="text-ink-dim text-sm leading-relaxed">نوصل طلبك لباب بيتك في الضفة الغربية، القدس، والداخل المحتل. شحن سريع وتتبع مباشر لشحنتك حتى الاستلام.</p>
            </div>
            <div class="glass-panel rounded-2xl p-7 text-center hover:-translate-y-2 transition-all duration-500 group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 mx-auto group-hover:bg-brand-500/20 transition-colors">
                    <i class="fa-solid fa-tags text-xl text-brand-500"></i>
                </div>
                <h3 class="font-black text-lg mb-3 text-ink">أفضل الأسعار والعروض</h3>
                <p class="text-ink-dim text-sm leading-relaxed">أسعار تنافسية مع عروض حصرية وخصومات يومية. الدفع عند الاستلام متاح لراحتك وأمانك التام.</p>
            </div>
            <div class="glass-panel rounded-2xl p-7 text-center hover:-translate-y-2 transition-all duration-500 group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 mx-auto group-hover:bg-brand-500/20 transition-colors">
                    <i class="fa-solid fa-headset text-xl text-brand-500"></i>
                </div>
                <h3 class="font-black text-lg mb-3 text-ink">دعم احترافي متواصل</h3>
                <p class="text-ink-dim text-sm leading-relaxed">فريق خدمة عملاء محترف جاهز لمساعدتك يومياً من 9 صباحاً حتى 10 مساءً عبر الواتساب. استفسري وسنرد فوراً.</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
      SECTION 4: Why شركة جنين للتجميل? — Premium Value Cards
      ═══════════════════════════════════════════════════════════════ --}}
<section class="py-24 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_50%_50%,rgba(var(--brand-500-rgb,255,42,133),0.03),transparent_70%)] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="mb-16 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-brand-500/20 bg-brand-500/5 mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                <span class="text-xs text-brand-500 font-bold tracking-widest uppercase">لماذا تختارينا</span>
            </div>
            <h2 class="text-3xl md:text-5xl font-black mb-4">لماذا <span class="gradient-text bg-[length:200%_auto]">شركة جنين للتجميل</span><span class="text-brand-500">.</span></h2>
            <p class="text-ink-dim max-w-2xl mx-auto text-lg font-light">متجر العناية بالبشرة الأول في فلسطين. نوفر لكِ تجربة تسوق آمنة وموثوقة مع منتجات أصلية وخدمة عملاء استثنائية.</p>
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
            <div class="value-card glass-panel rounded-[2rem] p-8 text-center group relative overflow-hidden transition-all duration-500">
                {{-- Top accent line --}}
                <div class="absolute top-0 right-8 left-8 h-[3px] rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500" style="background: var(--gradient-primary);"></div>
                {{-- Background glow --}}
                <div class="absolute -left-10 -bottom-10 w-40 h-40 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-700" style="background: radial-gradient(circle, var(--brand-500) 0%, transparent 70%); filter: blur(50px);"></div>
                {{-- Number badge --}}
                <div class="absolute top-6 left-6 text-6xl font-black opacity-[0.04] group-hover:opacity-[0.08] transition-opacity duration-500 select-none" style="color: var(--brand-500);">{{ $card['num'] }}</div>
                {{-- Icon --}}
                <div class="relative z-10 w-16 h-16 rounded-2xl bg-brand-500/10 flex items-center justify-center mb-6 group-hover:bg-brand-500/20 group-hover:scale-110 transition-all duration-500 shadow-neon mx-auto">
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

</div>

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
                    <img src="{{ $product->optimizedImageUrl(400, 400) }}" alt="{{ $product->name_ar }}" width="400" height="400"
                         class="w-full h-full object-contain filter brightness-75 group-hover:brightness-100 group-hover:scale-110 transition-all duration-700"
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
                        <span class="bg-white style="color:#0f172a;" text-[10px] font-bold px-3 py-1.5 rounded-full flex items-center gap-1">
                            <i class="fa-solid fa-bag-shopping text-[9px]"></i> اكتشفي المزيد
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
                    تصفحي المنتجات <i class="fa-solid fa-arrow-left"></i>
                </a>
                <a href="{{ route('b2b') }}"
                   class="px-10 py-4 rounded-full font-bold text-sm border border-white/15 text-white hover:bg-white/5 transition-all inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-crown text-accent-500"></i> حلول الجملة والصالونات
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
