<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مجموعة المنتجات الحصرية | شركة جنين للتجميل</title>
    <meta name="description" content="اكتشفي مجموعتنا الحصرية من المنتجات الأصلية للعناية بالبشرة والشعر. عروض مميزة، شحن سريع لكل فلسطين.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Cairo', 'sans-serif'] },
                    colors: {
                        brand: { 50: '#FDF2F8', 100: '#FCE7F3', 200: '#FBCFE8', 300: '#F9A8D4', 400: '#F472B6', 500: '#EC4899', 600: '#DB2777', 700: '#BE185D', 800: '#9D174D', 900: '#831843' },
                        glass: { DEFAULT: 'rgba(255,255,255,0.08)', border: 'rgba(255,255,255,0.12)' }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-slow': 'float 8s ease-in-out infinite',
                        'glow': 'glow 3s ease-in-out infinite',
                        'shimmer': 'shimmer 3s linear infinite',
                        'fade-up': 'fadeUp 0.6s ease-out forwards',
                        'scale-in': 'scaleIn 0.4s ease-out forwards',
                        'slide-left': 'slideLeft 0.5s ease-out forwards',
                        'slide-right': 'slideRight 0.5s ease-out forwards',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Cairo', sans-serif; background: #0a0a0f; color: #fafafa; scroll-behavior: smooth; }
        .gradient-text { background: linear-gradient(135deg, #EC4899, #F472B6, #EC4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; background-size: 200% auto; animation: shimmer 4s linear infinite; }
        .hero-glow { background: radial-gradient(ellipse at 50% 0%, rgba(236,72,153,0.15) 0%, transparent 60%); }
        .card-glow { box-shadow: 0 0 40px rgba(236,72,153,0.08); }
        .glass-panel { background: rgba(255,255,255,0.04); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); }
        .glass-panel:hover { border-color: rgba(236,72,153,0.3); }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        @keyframes glow { 0%, 100% { opacity: 0.4; } 50% { opacity: 0.8; } }
        @keyframes shimmer { 0% { background-position: 200% center; } 100% { background-position: -200% center; } }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes scaleIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        @keyframes slideLeft { from { opacity: 0; transform: translateX(40px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes slideRight { from { opacity: 0; transform: translateX(-40px); } to { opacity: 1; transform: translateX(0); } }
        .scroll-hidden::-webkit-scrollbar { display: none; }
        .scroll-hidden { -ms-overflow-style: none; scrollbar-width: none; }
        .product-card:hover .product-img { transform: scale(1.08); }
        .product-img { transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1); }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .nav-blur { background: rgba(10,10,15,0.85); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); }
        @media (max-width: 768px) { .hero-title { font-size: 2.2rem !important; } }
    </style>
</head>
<body>

    {{-- ═══════════════════════════════════════════
         NAVBAR
         ═══════════════════════════════════════════ --}}
    <nav class="nav-blur fixed top-0 w-full z-50 border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shadow-lg">
                    <i class="ph-fill ph-sparkle text-white text-sm"></i>
                </div>
                <span class="font-black text-lg tracking-tight">جنين <span class="text-brand-500">للتجميل</span></span>
            </a>
            <div class="hidden md:flex items-center gap-6 text-sm font-semibold">
                <a href="{{ url('/') }}" class="text-white/60 hover:text-white transition-colors">الرئيسية</a>
                <a href="#products" class="text-white/60 hover:text-white transition-colors">المنتجات</a>
                <a href="#features" class="text-white/60 hover:text-white transition-colors">المميزات</a>
                <a href="#order" class="text-white/60 hover:text-white transition-colors">اطلبي الآن</a>
                <a href="{{ route('shop') }}" class="bg-gradient-to-r from-brand-500 to-brand-700 text-white px-5 py-2 rounded-full font-bold text-sm hover:shadow-[0_0_25px_rgba(236,72,153,0.4)] transition-all">
                    <i class="ph ph-storefront"></i> المتجر
                </a>
            </div>
            <button onclick="toggleMobile()" class="md:hidden text-white/70 text-xl">
                <i class="ph ph-list" id="mobileIcon"></i>
            </button>
        </div>
    </nav>

    {{-- Mobile Menu --}}
    <div id="mobileMenu" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-black/70" onclick="toggleMobile()"></div>
        <div class="absolute top-0 right-0 w-72 h-full bg-[#0a0a0f] border-l border-white/5 p-6 transform transition-transform duration-300">
            <div class="flex justify-between items-center mb-8">
                <span class="font-black">القائمة</span>
                <button onclick="toggleMobile()" class="text-white/50 text-xl"><i class="ph ph-x"></i></button>
            </div>
            <div class="space-y-3">
                <a href="{{ url('/') }}" class="block py-3 px-4 rounded-xl text-white/70 hover:bg-white/5 transition-colors font-semibold">الرئيسية</a>
                <a href="#products" class="block py-3 px-4 rounded-xl text-white/70 hover:bg-white/5 transition-colors font-semibold">المنتجات</a>
                <a href="#features" class="block py-3 px-4 rounded-xl text-white/70 hover:bg-white/5 transition-colors font-semibold">المميزات</a>
                <a href="#order" class="block py-3 px-4 rounded-xl text-white/70 hover:bg-white/5 transition-colors font-semibold">اطلبي الآن</a>
                <a href="{{ route('shop') }}" class="block py-3 px-4 rounded-xl bg-gradient-to-r from-brand-500 to-brand-700 text-white font-bold text-center mt-4">المتجر</a>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         HERO SECTION
         ═══════════════════════════════════════════ --}}
    <header class="relative min-h-screen flex items-center overflow-hidden pt-16">
        <div class="absolute inset-0 hero-glow"></div>
        <div class="absolute top-20 left-10 w-72 h-72 rounded-full bg-brand-500/5 blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 rounded-full bg-brand-600/5 blur-[120px] animate-pulse" style="animation-delay:2s;"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 w-full">
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-16">
                <div class="w-full lg:w-1/2 text-center lg:text-right">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-400 text-xs font-bold tracking-wider mb-6 animate-fade-up">
                        <i class="ph-fill ph-gift text-sm"></i>
                        <span>مجموعة حصرية</span>
                        <i class="ph-fill ph-sparkle text-sm"></i>
                    </div>
                    <h1 class="hero-title text-4xl md:text-5xl lg:text-6xl font-black leading-[1.1] mb-6">
                        <span class="block gradient-text">اكتشفي</span>
                        <span class="block text-white">منتجات العناية</span>
                        <span class="block text-white/80">الأصلية <span class="gradient-text">لجمالك</span></span>
                    </h1>
                    <p class="text-white/50 text-lg max-w-xl mx-auto lg:mx-0 leading-relaxed mb-8">
                        تشكيلة مختارة بعناية من أفضل منتجات التجميل والعناية بالبشرة والشعر. 
                        منتجات أصلية 100%، شحن سريع لكل فلسطين، وأسعار تنافسية لا تقبل المنافسة.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="#products" class="bg-gradient-to-r from-brand-500 to-brand-700 text-white px-8 py-4 rounded-full font-bold text-lg hover:shadow-[0_0_30px_rgba(236,72,153,0.5)] transition-all inline-flex items-center justify-center gap-2">
                            <i class="ph-fill ph-eye"></i> تسوقي الآن
                        </a>
                        <a href="#order" class="border border-white/20 hover:border-brand-500/50 text-white/80 px-8 py-4 rounded-full font-bold text-lg transition-all inline-flex items-center justify-center gap-2">
                            <i class="ph-fill ph-whatsapp-logo text-green-400"></i> اطلبي عبر واتساب
                        </a>
                    </div>
                    <div class="flex items-center gap-6 mt-10 justify-center lg:justify-start text-white/40 text-sm">
                        <span class="flex items-center gap-1.5"><i class="ph-fill ph-shield-check text-brand-400"></i> أصلية 100%</span>
                        <span class="flex items-center gap-1.5"><i class="ph-fill ph-truck text-brand-400"></i> شحن سريع</span>
                        <span class="flex items-center gap-1.5"><i class="ph-fill ph-credit-card text-brand-400"></i> دفع آمن</span>
                    </div>
                </div>

                <div class="w-full lg:w-1/2 flex justify-center">
                    <div class="relative animate-float">
                        <div class="w-72 h-72 md:w-96 md:h-96 rounded-full bg-gradient-to-br from-brand-500/20 via-brand-600/10 to-transparent blur-3xl absolute -inset-10"></div>
                        <div class="relative glass-panel rounded-[3rem] p-8 md:p-12 text-center">
                            <div class="absolute -top-4 -right-4 w-12 h-12 rounded-full bg-brand-500/20 flex items-center justify-center animate-glow">
                                <i class="ph-fill ph-gift text-brand-400 text-xl"></i>
                            </div>
                            <i class="ph-fill ph-crown text-6xl text-brand-400/80 mb-6 block"></i>
                            <h3 class="text-3xl md:text-4xl font-black mb-3">{{ $products->count() }}+</h3>
                            <p class="text-white/60">منتج أصلي فاخر</p>
                            <div class="mt-6 flex flex-wrap gap-3 justify-center">
                                @foreach($categories->take(4) as $cat)
                                <span class="px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs text-white/60">{{ $cat->name_ar }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce text-white/20">
            <i class="ph ph-caret-double-down text-2xl"></i>
        </div>
    </header>

    {{-- ═══════════════════════════════════════════
         TRUST BAR
         ═══════════════════════════════════════════ --}}
    <section class="py-12 border-y border-white/5">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center p-4">
                    <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mx-auto mb-3">
                        <i class="ph-fill ph-package text-brand-400 text-xl"></i>
                    </div>
                    <span class="text-2xl font-black gradient-text">{{ $products->count() }}+</span>
                    <p class="text-white/50 text-sm mt-1">منتج أصلي</p>
                </div>
                <div class="text-center p-4">
                    <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mx-auto mb-3">
                        <i class="ph-fill ph-users-three text-brand-400 text-xl"></i>
                    </div>
                    <span class="text-2xl font-black text-white">15,000+</span>
                    <p class="text-white/50 text-sm mt-1">عميلة سعيدة</p>
                </div>
                <div class="text-center p-4">
                    <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mx-auto mb-3">
                        <i class="ph-fill ph-star text-brand-400 text-xl"></i>
                    </div>
                    <span class="text-2xl font-black text-white">4.9</span>
                    <p class="text-white/50 text-sm mt-1">تقييم العملاء</p>
                </div>
                <div class="text-center p-4">
                    <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mx-auto mb-3">
                        <i class="ph-fill ph-clock-countdown text-brand-400 text-xl"></i>
                    </div>
                    <span class="text-2xl font-black text-white">24H</span>
                    <p class="text-white/50 text-sm mt-1">توصيل سريع</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
         FEATURES SECTION
         ═══════════════════════════════════════════ --}}
    <section id="features" class="py-20 relative">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_30%_50%,rgba(236,72,153,0.04),transparent_60%)]"></div>
        <div class="max-w-7xl mx-auto px-4 relative z-10">
            <div class="text-center mb-16">
                <span class="text-brand-400 font-bold text-sm tracking-widest uppercase">لماذا نحن</span>
                <h2 class="text-3xl md:text-5xl font-black mt-3 mb-4">مميزات <span class="gradient-text">التسوق معنا</span></h2>
                <p class="text-white/50 max-w-2xl mx-auto">نقدم لك تجربة تسوق فريدة تجمع بين الجودة، الأصالة، والخدمة المتميزة.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="glass-panel rounded-3xl p-8 text-center hover:-translate-y-2 transition-all duration-500 group">
                    <div class="w-14 h-14 rounded-2xl bg-brand-500/10 flex items-center justify-center mx-auto mb-5 group-hover:bg-brand-500/20 transition-colors">
                        <i class="ph-fill ph-shield-check text-2xl text-brand-400"></i>
                    </div>
                    <h3 class="text-xl font-black mb-3">منتجات أصلية 100%</h3>
                    <p class="text-white/50 text-sm leading-relaxed">جميع منتجاتنا أصلية ومستوردة من مصادر موثوقة ومعتمدة دولياً. نضمن لك الجودة والأصالة.</p>
                </div>
                <div class="glass-panel rounded-3xl p-8 text-center hover:-translate-y-2 transition-all duration-500 group">
                    <div class="w-14 h-14 rounded-2xl bg-brand-500/10 flex items-center justify-center mx-auto mb-5 group-hover:bg-brand-500/20 transition-colors">
                        <i class="ph-fill ph-truck text-2xl text-brand-400"></i>
                    </div>
                    <h3 class="text-xl font-black mb-3">توصيل لكل فلسطين</h3>
                    <p class="text-white/50 text-sm leading-relaxed">شحن سريع لكل مدن الضفة والقدس والداخل المحتل. تتبع مباشر لشحنتك حتى باب منزلك.</p>
                </div>
                <div class="glass-panel rounded-3xl p-8 text-center hover:-translate-y-2 transition-all duration-500 group">
                    <div class="w-14 h-14 rounded-2xl bg-brand-500/10 flex items-center justify-center mx-auto mb-5 group-hover:bg-brand-500/20 transition-colors">
                        <i class="ph-fill ph-headset text-2xl text-brand-400"></i>
                    </div>
                    <h3 class="text-xl font-black mb-3">دعم احترافي متواصل</h3>
                    <p class="text-white/50 text-sm leading-relaxed">فريق خدمة عملاء محترف جاهز لمساعدتك يومياً عبر الواتساب من 9 صباحاً حتى 10 مساءً.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
         PRODUCTS SECTION
         ═══════════════════════════════════════════ --}}
    <section id="products" class="py-20 relative">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_70%_50%,rgba(236,72,153,0.03),transparent_60%)]"></div>
        <div class="max-w-7xl mx-auto px-4 relative z-10">
            <div class="text-center mb-16">
                <span class="text-brand-400 font-bold text-sm tracking-widest uppercase">تسوقي الآن</span>
                <h2 class="text-3xl md:text-5xl font-black mt-3 mb-4">منتجات <span class="gradient-text">مختارة بعناية</span></h2>
                <p class="text-white/50 max-w-2xl mx-auto">كل منتج تم انتقاؤه بعناية ليكون جزءاً من روتين عنايتك الشخصي.</p>
            </div>

            @if($products->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                @foreach($products as $product)
                <div class="product-card glass-panel rounded-[2rem] overflow-hidden group card-glow transition-all duration-500 hover:-translate-y-2 hover:border-brand-500/30">
                    <div class="relative h-72 overflow-hidden bg-white/5">
                        @if($product->main_image_url)
                        <img src="{{ $product->main_image_url }}" alt="{{ $product->name_ar }}"
                             class="product-img w-full h-full object-contain p-6"
                             loading="lazy">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <i class="ph-fill ph-package text-6xl text-white/10"></i>
                        </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0f] via-transparent to-transparent"></div>
                        @if($product->is_on_sale)
                        <span class="absolute top-4 right-4 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs font-black px-3 py-1 rounded-full shadow-lg">
                            خصم %{{ $product->discount_percentage_display }}
                        </span>
                        @endif
                        @if($product->is_new)
                        <span class="absolute top-4 left-4 bg-gradient-to-r from-brand-500 to-brand-600 text-white text-xs font-black px-3 py-1 rounded-full shadow-lg">
                            جديد
                        </span>
                        @endif
                    </div>
                    <div class="p-6 text-right">
                        @if($product->category)
                        <a href="{{ route('shop', ['category' => $product->category->slug]) }}" class="text-brand-400 text-xs font-bold tracking-wider hover:underline">
                            {{ $product->category->name_ar }}
                        </a>
                        @endif
                        <h3 class="text-lg font-black mt-1 mb-2 line-clamp-2">{{ $product->name_ar }}</h3>
                        @if($product->brand)
                        <p class="text-white/40 text-sm mb-3">{{ $product->brand->name }}</p>
                        @endif
                        <p class="text-white/50 text-sm leading-relaxed line-clamp-2 mb-4">
                            {{ $product->short_description_ar ?? $product->description_ar ?? '' }}
                        </p>
                        <div class="flex items-center justify-between pt-4 border-t border-white/5">
                            <div>
                                @if($product->is_on_sale)
                                <span class="text-white/40 text-sm line-through ml-2">{{ number_format($product->b2c_price, 0) }} ₪</span>
                                @endif
                                <span class="text-2xl font-black gradient-text">{{ number_format($product->final_b2c_price, 0) }} ₪</span>
                            </div>
                            <a href="{{ route('product.show', $product->slug) }}" class="w-11 h-11 rounded-full bg-brand-500/20 flex items-center justify-center hover:bg-brand-500/40 transition-all">
                                <i class="ph ph-arrow-left text-brand-400 text-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-20">
                <i class="ph-fill ph-package text-6xl text-white/10 mb-6 block"></i>
                <p class="text-white/40 text-lg">لم يتم إضافة منتجات لهذه الصفحة بعد.</p>
                <p class="text-white/30 text-sm mt-2">يقوم الإدارة بإضافة المنتجات قريباً.</p>
            </div>
            @endif

            @if($products->isNotEmpty())
            <div class="text-center mt-12">
                <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 border border-white/20 hover:border-brand-500/50 text-white/80 px-8 py-4 rounded-full font-bold text-base transition-all">
                    <i class="ph ph-storefront"></i> تصفحي جميع المنتجات في المتجر
                    <i class="ph ph-arrow-left"></i>
                </a>
            </div>
            @endif
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
         PRODUCT DETAILS (ALTERNATING LAYOUT)
         ═══════════════════════════════════════════ --}}
    @if($products->isNotEmpty())
    <section class="py-20 relative border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <span class="text-brand-400 font-bold text-sm tracking-widest uppercase">تفاصيل المنتجات</span>
                <h2 class="text-3xl md:text-5xl font-black mt-3 mb-4">تعرفي على <span class="gradient-text">منتجاتنا</span></h2>
                <p class="text-white/50 max-w-2xl mx-auto">نقدم لك تشكيلة متكاملة من منتجات العناية بالبشرة والشعر.</p>
            </div>

            <div class="space-y-16">
                @foreach($products as $index => $product)
                @php $isReversed = $index % 2 !== 0; @endphp
                <div class="flex flex-col {{ $isReversed ? 'lg:flex-row-reverse' : 'lg:flex-row' }} items-center gap-8 lg:gap-16 glass-panel rounded-[2rem] p-6 md:p-10" style="animation: scaleIn 0.5s ease-out forwards; animation-delay: {{ $index * 0.1 }}s; opacity: 0;">
                    <div class="lg:w-2/5 flex justify-center">
                        <div class="relative w-full max-w-xs aspect-square rounded-2xl overflow-hidden bg-white/5 flex items-center justify-center p-8 group-hover:scale-105 transition-transform duration-500">
                            @if($product->main_image_url)
                            <img src="{{ $product->main_image_url }}" alt="{{ $product->name_ar }}" class="w-full h-full object-contain">
                            @else
                            <i class="ph-fill ph-package text-6xl text-white/10"></i>
                            @endif
                        </div>
                    </div>
                    <div class="lg:w-3/5 text-right">
                        @if($product->category)
                        <span class="text-brand-400 text-sm font-bold tracking-wider">{{ $product->category->name_ar }}</span>
                        @endif
                        <h3 class="text-2xl md:text-3xl font-black mt-2 mb-3">{{ $product->name_ar }}</h3>
                        @if($product->name_en)
                        <p class="text-white/30 text-sm mb-3">{{ $product->name_en }}</p>
                        @endif
                        <div class="h-px bg-gradient-to-r from-brand-500/50 to-transparent w-24 mb-4"></div>
                        <p class="text-white/60 leading-relaxed mb-6">
                            {{ $product->description_ar ?? 'منتج أصلي فاخر للعناية بالبشرة والشعر.' }}
                        </p>
                        <div class="flex items-center gap-4 mb-6 text-sm text-white/50">
                            @if($product->stock_quantity > 0)
                            <span class="flex items-center gap-1.5">
                                <i class="ph-fill ph-check-circle text-green-400"></i> متوفر
                            </span>
                            @else
                            <span class="flex items-center gap-1.5">
                                <i class="ph-fill ph-x-circle text-red-400"></i> غير متوفر
                            </span>
                            @endif
                            @if($product->brand)
                            <span class="flex items-center gap-1.5">
                                <i class="ph-fill ph-certificate text-brand-400"></i> {{ $product->brand->name }}
                            </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between pt-4 border-t border-white/5">
                            <div class="flex items-center gap-4">
                                <span class="text-3xl font-black gradient-text">{{ number_format($product->final_b2c_price, 0) }} ₪</span>
                                @if($product->is_on_sale)
                                <span class="text-white/30 line-through">{{ number_format($product->b2c_price, 0) }} ₪</span>
                                @endif
                            </div>
                            <a href="{{ route('product.show', $product->slug) }}" class="bg-gradient-to-r from-brand-500 to-brand-700 text-white px-6 py-3 rounded-full font-bold text-sm hover:shadow-[0_0_20px_rgba(236,72,153,0.4)] transition-all inline-flex items-center gap-2">
                                <i class="ph ph-shopping-bag"></i> تسوقي
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════════════════════════════════════════
         ORDER SECTION / CTA
         ═══════════════════════════════════════════ --}}
    <section id="order" class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(236,72,153,0.08),transparent_70%)]"></div>
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
            <div class="glass-panel rounded-[3rem] p-10 md:p-16 border border-white/5">
                <div class="w-16 h-16 rounded-2xl bg-brand-500/10 flex items-center justify-center mx-auto mb-6">
                    <i class="ph-fill ph-gift text-3xl text-brand-400"></i>
                </div>
                <span class="text-brand-400 font-bold text-sm tracking-widest uppercase">ابدئي رحلتك الآن</span>
                <h2 class="text-3xl md:text-5xl font-black mt-4 mb-4">
                    مستعدة <span class="gradient-text">لاكتشاف</span> الجمال؟
                </h2>
                <p class="text-white/50 max-w-xl mx-auto mb-8 leading-relaxed">
                    اطلبي الآن واستمتعي بتشكيلتنا الحصرية من المنتجات الأصلية مع شحن سريع 
                    وخدمة عملاء متميزة. فريقنا جاهز للإجابة على جميع استفساراتك.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings['whatsapp_number'] ?? '972569030203') }}" target="_blank" class="bg-green-500 hover:bg-green-600 text-white px-8 py-4 rounded-full font-bold text-lg transition-all inline-flex items-center justify-center gap-3 shadow-lg">
                        <i class="ph-fill ph-whatsapp-logo text-2xl"></i> اطلبي عبر واتساب
                    </a>
                    <a href="{{ route('shop') }}" class="border border-white/20 hover:border-brand-500/50 text-white/80 px-8 py-4 rounded-full font-bold text-lg transition-all inline-flex items-center justify-center gap-2">
                        <i class="ph ph-storefront"></i> تصفحي المتجر
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
         MARQUEE TICKER
         ═══════════════════════════════════════════ --}}
    <div class="py-8 border-y border-white/5 overflow-hidden whitespace-nowrap">
        <div class="flex items-center gap-12 text-xs tracking-widest uppercase text-white/30 animate-marquee">
            <span><i class="ph-fill ph-gift text-brand-400 mr-2"></i> منتجات أصلية 100%</span>
            <i class="ph-fill ph-circle text-[6px] text-brand-500"></i>
            <span>شحن سريع لكل فلسطين</span>
            <i class="ph-fill ph-circle text-[6px] text-brand-500"></i>
            <span>أفضل ماركات التجميل العالمية</span>
            <i class="ph-fill ph-circle text-[6px] text-brand-500"></i>
            <span>الدفع عند الاستلام</span>
            <i class="ph-fill ph-circle text-[6px] text-brand-500"></i>
            <span>دعم احترافي يومي</span>
            <i class="ph-fill ph-circle text-[6px] text-brand-500"></i>
            <span>عروض وخصومات حصرية</span>
            <i class="ph-fill ph-circle text-[6px] text-brand-500"></i>
            <span>منتجات أصلية 100%</span>
            <i class="ph-fill ph-circle text-[6px] text-brand-500"></i>
            <span>شحن سريع لكل فلسطين</span>
            <i class="ph-fill ph-circle text-[6px] text-brand-500"></i>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         FOOTER
         ═══════════════════════════════════════════ --}}
    <footer class="py-12 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center">
                        <i class="ph-fill ph-sparkle text-white text-sm"></i>
                    </div>
                    <span class="font-black">جنين <span class="text-brand-500">للتجميل</span></span>
                </div>
                <div class="flex gap-6 text-sm text-white/40">
                    <a href="{{ url('/') }}" class="hover:text-white transition-colors">الرئيسية</a>
                    <a href="{{ route('shop') }}" class="hover:text-white transition-colors">المتجر</a>
                    <a href="{{ route('contact') }}" class="hover:text-white transition-colors">اتصل بنا</a>
                </div>
                <div class="flex gap-3">
                    @if(!empty($siteSettings['facebook_url']))
                    <a href="{{ $siteSettings['facebook_url'] }}" target="_blank" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-brand-500/20 transition-colors">
                        <i class="ph-fill ph-facebook-logo text-white/60"></i>
                    </a>
                    @endif
                    @if(!empty($siteSettings['instagram_url']))
                    <a href="{{ $siteSettings['instagram_url'] }}" target="_blank" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-brand-500/20 transition-colors">
                        <i class="ph-fill ph-instagram-logo text-white/60"></i>
                    </a>
                    @endif
                    @if(!empty($siteSettings['whatsapp_number']))
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings['whatsapp_number']) }}" target="_blank" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-green-500/20 transition-colors">
                        <i class="ph-fill ph-whatsapp-logo text-white/60"></i>
                    </a>
                    @endif
                </div>
            </div>
            <div class="text-center text-white/20 text-xs mt-8 pt-8 border-t border-white/5">
                <p>جميع الحقوق محفوظة &copy; {{ date('Y') }} شركة جنين للتجميل</p>
            </div>
        </div>
    </footer>

    {{-- ═══════════════════════════════════════════
         SCRIPTS
         ═══════════════════════════════════════════ --}}
    <style>
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .animate-marquee {
            animation: marquee 30s linear infinite;
            display: inline-flex;
        }
        .animate-marquee:hover {
            animation-play-state: paused;
        }
    </style>

    <script>
        function toggleMobile() {
            const menu = document.getElementById('mobileMenu');
            const icon = document.getElementById('mobileIcon');
            menu.classList.toggle('hidden');
            icon.className = menu.classList.contains('hidden') ? 'ph ph-list' : 'ph ph-x';
        }

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                document.getElementById('mobileMenu')?.classList.add('hidden');
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('mobileMenu')?.classList.add('hidden');
            }
        });

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.glass-panel').forEach(el => {
            if (!el.closest('header')) {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
                observer.observe(el);
            }
        });
    </script>
</body>
</html>