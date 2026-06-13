@extends($layoutPath)

@section('title', 'مجموعة المسك الفاخرة بالأرغان | الدليل الشامل - نوڤا كوزمتكس')
@section('meta_description', 'مجموعة المسك الفاخرة بالأرغان للعناية بالشعر من نوڤا كوزمتكس - شامبو خالٍ من الكبريت، حمام زيت مرطب، سيروم مغذي، وسيروم الذهب الفاخر.')

@push('styles')
<style>
    .gradient-text {
        background: linear-gradient(45deg, #D4AF37, #F3E5AB, #D4AF37);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .hero-bg {
        background-color: #0D0D0D;
        background-image: radial-gradient(circle at top right, rgba(212, 175, 55, 0.18), transparent 45%),
                          radial-gradient(circle at bottom left, rgba(183, 110, 121, 0.12), transparent 45%);
    }
    .section-pattern {
        background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M15 0L17.8 12.2L30 15L17.8 17.8L15 30L12.2 17.8L0 15L12.2 12.2Z' fill='%23d4af37' fill-opacity='0.03'/%3E%3C/svg%3E");
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes twinkle {
        0%, 100% { opacity: 0.3; transform: scale(0.9); }
        50% { opacity: 1; transform: scale(1.1); }
    }

    #loading-overlay {
        position: fixed;
        inset: 0;
        z-index: 99999;
        background-color: #0D0D0D;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: opacity 0.6s ease, visibility 0.6s ease;
    }
    #loading-overlay.hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }
    .loader-ring {
        width: 80px; height: 80px;
        border-radius: 50%;
        border: 4px solid rgba(212, 175, 55, 0.1);
        border-top-color: #D4AF37;
        animation: spin 0.9s linear infinite;
    }
    .loader-bar-track {
        width: 200px; height: 3px;
        background: rgba(255,255,255,0.08);
        border-radius: 10px; overflow: hidden;
    }
    .loader-bar-fill {
        height: 100%; width: 0%;
        background: linear-gradient(90deg, #D4AF37, #F3E5AB);
        border-radius: 10px; transition: width 0.3s ease;
    }
    #musk-content { opacity: 0; transition: opacity 0.8s ease; }
    #musk-content.visible { opacity: 1; }
</style>
@endpush

@section('content')

<!-- Loading Overlay -->
<div id="loading-overlay">
    <div class="loader-ring mb-6"></div>
    <div class="text-2xl font-bold tracking-widest mb-4">
        <span class="gradient-text">NOVA</span>
    </div>
    <div class="text-[#D4AF37] text-lg mb-4 flex gap-2">
        <span style="animation:twinkle 1.5s ease-in-out infinite;"><i class="fas fa-star"></i></span>
        <span style="animation:twinkle 1.5s ease-in-out infinite;animation-delay:0.3s;"><i class="fas fa-star"></i></span>
        <span style="animation:twinkle 1.5s ease-in-out infinite;animation-delay:0.6s;"><i class="fas fa-star"></i></span>
    </div>
    <div class="loader-bar-track mb-4">
        <div class="loader-bar-fill" id="loader-bar-fill"></div>
    </div>
    <p class="text-gray-500 text-sm font-light tracking-wide" id="loading-text">جاري تحميل المجموعة الملكية...</p>
</div>

<div id="musk-content">

<!-- Hero Section -->
<header id="overview" class="hero-bg py-24 lg:py-36 overflow-hidden relative">
    <div class="absolute inset-0 opacity-20 section-pattern"></div>
    <div class="container mx-auto px-4 text-center z-10 relative">
        <span class="text-[#D4AF37] font-bold tracking-widest text-sm mb-5 inline-flex items-center gap-2 bg-[#D4AF37]/10 px-4 py-1.5 rounded-full border border-[#D4AF37]/30">
            <i class="fas fa-crown"></i> مجموعة المسك والأرغان الملكية
        </span>
        <h1 class="text-4xl lg:text-7xl font-extrabold mb-6 text-white leading-tight">
            أعيدي الحيوية والبريق لشعرك
        </h1>
        <p class="text-xl lg:text-2xl text-[#F3E5AB] mb-8 font-light max-w-3xl mx-auto leading-relaxed">
            الاندماج الطبيعي الفاخر بين زيت الأرغان العضوي المرمم ونفحات المسك الشرقي الساحر.
        </p>
        <p class="text-gray-300 text-base lg:text-lg mb-12 max-w-2xl mx-auto leading-relaxed">
            في "نوڤا كوزمتكس"، نقدم لك طقوساً متكاملة للعناية الاحترافية بالشعر في منزلك. صُممت هذه المجموعة الخالية من المواد الضارة لتنظيف، ترطيب، وحماية شعرك بعمق مع رائحة تدوم طوال اليوم.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a href="#customizer" class="w-full sm:w-auto bg-gradient-to-r from-[#D4AF37] to-[#F3E5AB] text-[#0D0D0D] px-8 py-4 rounded-full font-bold text-lg hover:shadow-[0_0_25px_rgba(212,175,55,0.4)] transition-all">
                تسوّق المجموعة الآن
            </a>
            <a href="#products" class="w-full sm:w-auto border border-white/20 hover:border-[#D4AF37] text-white px-8 py-4 rounded-full font-bold text-lg transition-colors">
                اكتشف المكونات الأربعة
            </a>
        </div>
    </div>
</header>

<!-- Brand Values Section -->
<section id="features" class="py-16 bg-white border-b border-gray-100">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="flex items-center gap-4 p-4">
                <div class="w-12 h-12 bg-[#D4AF37]/10 text-[#D4AF37] rounded-xl flex items-center justify-center text-xl shrink-0"><i class="fas fa-shield-virus"></i></div>
                <div><h4 class="font-bold text-[#0D0D0D]">خالٍ من الكبريت والملح</h4><p class="text-xs text-gray-500 mt-1">آمن للشعر المعالج بالبروتين والكيراتين</p></div>
            </div>
            <div class="flex items-center gap-4 p-4">
                <div class="w-12 h-12 bg-[#D4AF37]/10 text-[#D4AF37] rounded-xl flex items-center justify-center text-xl shrink-0"><i class="fas fa-seedling"></i></div>
                <div><h4 class="font-bold text-[#0D0D0D]">أرغان عضوي 100%</h4><p class="text-xs text-gray-500 mt-1">تغذية مكثفة من الأعماق إلى الأطراف</p></div>
            </div>
            <div class="flex items-center gap-4 p-4">
                <div class="w-12 h-12 bg-[#D4AF37]/10 text-[#D4AF37] rounded-xl flex items-center justify-center text-xl shrink-0"><i class="fas fa-hourglass-start"></i></div>
                <div><h4 class="font-bold text-[#0D0D0D]">رائحة مسك ممتدة</h4><p class="text-xs text-gray-500 mt-1">ثبات عطري ساحر يدوم لأيام</p></div>
            </div>
            <div class="flex items-center gap-4 p-4">
                <div class="w-12 h-12 bg-[#D4AF37]/10 text-[#D4AF37] rounded-xl flex items-center justify-center text-xl shrink-0"><i class="fas fa-check-double"></i></div>
                <div><h4 class="font-bold text-[#0D0D0D]">جودة احترافية معتمدة</h4><p class="text-xs text-gray-500 mt-1">مُطوّر في أرقى مختبرات التجميل</p></div>
            </div>
        </div>
    </div>
</section>

<!-- Detailed Products Section -->
<section id="products" class="py-24 bg-[#F4F4F6]/50 section-pattern">
    <div class="container mx-auto px-4">
        <div class="text-center mb-20">
            <span class="text-[#D4AF37] font-bold text-sm tracking-wider uppercase">روتين العناية الملكي</span>
            <h2 class="text-4xl font-extrabold text-[#0D0D0D] mt-2 mb-4">تفاصيل مكونات مجموعة مسك الفاخرة</h2>
            <div class="w-24 h-1 bg-[#D4AF37] mx-auto"></div>
            <p class="text-gray-600 mt-4 max-w-2xl mx-auto">تعرفي على روتين الجمال المتكامل لشعر صحي، براق، ونابض بالحياة.</p>
        </div>

        <div class="space-y-24">
            <!-- Product cards (same content as before but using inline colors instead of custom classes) -->
            @php
                $products = [
                    [
                        'img' => 'شامبو مسك.webp',
                        'fallback' => 'https://images.unsplash.com/photo-1535585209827-a15fcdbc4c2d?auto=format&fit=crop&q=80&w=600',
                        'badge' => 'الخطوة 1: التنظيف الفاخر',
                        'badgeClass' => 'bg-[#0D0D0D] text-[#D4AF37]',
                        'tag' => 'جديد وحصري',
                        'size' => 'سعة 1000 مل (حجم عملاق)',
                        'title' => 'شامبو مسك خالي من الكبريت والملح (Sulfate & Salt Free)',
                        'desc' => 'ابدئي روتينك بتنظيف مثالي وآمن تماماً. هذا الشامبو المميز مصمم خصيصاً للشعر الحساس والمعالج بالبروتين أو الكيراتين. ينظف الفروة بلطف دون سلبها الزيوت الطبيعية والمغذيات، ويحمي صبغة الشعر من البهتان مع ترطيبه بزيت الأرغان العضوي وغمره بعبير مسك مميز.',
                        'features' => ['خالي تماماً من الأملاح والبارابين', 'يحافظ على علاج البروتين المصبوغ', 'ينظف بلطف ويرطب ألياف الشعر', 'رغوة غنية بعبق المسك الصافي'],
                        'price' => '55.00 ₪',
                        'reverse' => false
                    ],
                    [
                        'img' => 'حمام زيت مسك.webp',
                        'fallback' => 'https://images.unsplash.com/photo-1526947425960-945c6e72858f?auto=format&fit=crop&q=80&w=600',
                        'badge' => 'الخطوة 2: الترميم والترطيب',
                        'badgeClass' => 'bg-[#0D0D0D] text-[#D4AF37]',
                        'tag' => 'الأكثر طلباً',
                        'size' => 'سعة 500 مل',
                        'title' => 'حمام زيت مسك وترميم عميق (Deep Reviving)',
                        'desc' => 'علاج مكثف ذو مفعول سحري مصمم خصيصاً لإعادة إحياء الشعر المنهك، التالف والمتقصف. بتركيبة غنية بالأرغان العضوي النقي، يتغلغل حمام الزيت داخل جذور الشعر لترميم الخلايا التالفة وإعادة بناء درع الرطوبة الطبيعي، مما يمنح شعرك نعومة حريرية ولمعاناً لا يقاوم.',
                        'features' => [],
                        'price' => '50.00 ₪',
                        'reverse' => true,
                        'tips' => ['بعد الشامبو، وزّعي كمية مناسبة بالتساوي من المنتصف وحتى الأطراف.', 'دلكي خصلات شعرك بلطف، ثم اتركي القناع لمدة 15 إلى 20 دقيقة.', 'لأفضل نتيجة، لفي شعرك بمنشفة دافئة ورطبة قبل شطفه بالماء البارد لإغلاق مسام الشعر.']
                    ],
                    [
                        'img' => 'سيروم.webp',
                        'fallback' => 'https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?auto=format&fit=crop&q=80&w=600',
                        'badge' => 'الخطوة 3: التغذية والنمو',
                        'badgeClass' => 'bg-[#0D0D0D] text-[#D4AF37]',
                        'tag' => 'حماية قصوى',
                        'size' => 'سعة 150 مل',
                        'title' => 'سيروم الأرغان والمسك للعناية المتقدمة (Advanced Growth)',
                        'desc' => 'سيروم يومي خفيف الوزن ذو تركيبة غنية بزيت الأرغان الأساسي. يعمل على تغليف جذع الشعرة لحمايتها من التلف والحرارة (الاستشوار ومكواة الشعر) مع تحفيز نمو الشعر الطبيعي وتقوية البصيلات. يمنع تجعد الشعر ويعالج الهيشان والتقصف فوراً دون ترك أي ملمس لزج أو ثقيل.',
                        'features' => ['حماية حرارية قصوى أثناء التصفيف', 'يعطي لمعاناً براقاً ويفك التشابك', 'يمنع ويعالج نفشة الشعر والهيشان', 'يحفز نمو الشعر الصحي ويقوي الأطراف'],
                        'price' => '45.00 ₪',
                        'reverse' => false
                    ],
                    [
                        'img' => 'سيروم الذهب.webp',
                        'fallback' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&q=80&w=600',
                        'badge' => 'اللمسة الملكية الفاخرة',
                        'badgeClass' => 'bg-gradient-to-r from-[#D4AF37] to-yellow-500 text-[#0D0D0D]',
                        'tag' => '<i class="fas fa-star"></i> منتج النخبة',
                        'tagClass' => 'bg-[#0D0D0D] text-[#D4AF37]',
                        'size' => 'سعة 150 مل - إصدار محدود',
                        'title' => 'سيروم الذهب الحصري للأرغان والمسك <i class="fas fa-wand-magic-sparkles text-[#D4AF37]"></i>',
                        'desc' => 'جوهرة تشكيلة المسك. سيروم النخبة المطور بجزيئات الذهب الدقيقة المغذية والمنعمة للشعر مع زيت الأرغان. صُمم لإعطاء لمعان وإضاءة ثلاثية الأبعاد لشعرك مع رائحة مسك ممتدة بالغة الثبات والجاذبية. يعمل على ملء الفراغات الدقيقة وتنعيم الطبقة الخارجية للشعرة للحصول على ملمس انسيابي مخملي كالحرير.',
                        'extra' => 'الذهب والزيوت المدمجة تعمل بتناغم تام على عكس الضوء الطبيعي لتسليط الإشراقة واللمعان على مظهر شعرك، مع توفير علاج مكثف للشعر شديد التضرر والجفاف.',
                        'features' => [],
                        'price' => '45.00 ₪',
                        'reverse' => true
                    ]
                ];
            @endphp

            @foreach($products as $i => $p)
                @php
                    $flexDir = ($p['reverse'] ?? false) ? 'lg:flex-row-reverse' : 'lg:flex-row';
                @endphp
                <div class="flex flex-col {{ $flexDir }} items-center gap-16 bg-white p-8 lg:p-12 rounded-3xl shadow-md border border-gray-100 relative group">
                    <div class="lg:w-2/5 flex justify-center">
                        <div class="relative w-80 h-96 rounded-2xl overflow-hidden shadow-lg border border-gray-100 flex items-center justify-center bg-white p-6 group-hover:scale-[1.02] transition-transform duration-500">
                            <img src="{{ $p['img'] }}" alt="{{ $p['title'] }}" class="max-h-full object-contain" onerror="this.src='{{ $p['fallback'] }}'">
                            <span class="absolute top-4 right-4 {{ $p['badgeClass'] }} text-xs font-extrabold px-4 py-1.5 rounded-full shadow-md">{{ $p['badge'] }}</span>
                        </div>
                    </div>
                    <div class="lg:w-3/5">
                        <div class="flex items-center gap-2 mb-4">
                            @if(isset($p['tagClass']))
                                <span class="{{ $p['tagClass'] }} text-xs font-bold px-3 py-1 rounded-md">{!! $p['tag'] !!}</span>
                            @else
                                <span class="bg-[#D4AF37]/15 text-[#D4AF37] text-xs font-bold px-3 py-1 rounded-md">{{ $p['tag'] }}</span>
                            @endif
                            <span class="text-gray-400 text-sm font-semibold">{{ $p['size'] }}</span>
                        </div>
                        <h3 class="text-3xl font-bold text-[#0D0D0D] mb-4">{!! $p['title'] !!}</h3>
                        <p class="text-gray-600 text-lg mb-6 leading-relaxed">{{ $p['desc'] }}</p>
                        
                        @if(!empty($p['features']))
                        <div class="grid grid-cols-2 gap-4 mb-6 text-sm text-gray-600">
                            @foreach($p['features'] as $f)
                            <div class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> {{ $f }}</div>
                            @endforeach
                        </div>
                        @endif

                        @if(!empty($p['tips']))
                        <h4 class="font-bold text-[#0D0D0D] mb-3"><i class="fas fa-magic text-[#D4AF37] mr-1"></i> نصائح الاستخدام الاحترافي:</h4>
                        <ul class="list-disc list-inside text-gray-600 space-y-2 mb-6 marker:text-[#D4AF37] text-sm">
                            @foreach($p['tips'] as $tip)
                            <li>{{ $tip }}</li>
                            @endforeach
                        </ul>
                        @endif

                        @if(!empty($p['extra']))
                        <h4 class="font-bold text-[#0D0D0D] mb-2">مميزات سيروم الذهب الاستثنائية:</h4>
                        <p class="text-gray-600 mb-6 text-sm">{{ $p['extra'] }}</p>
                        @endif

                        <div class="border-t border-gray-100 pt-6 flex items-center justify-between">
                            <span class="text-2xl font-black text-[#0D0D0D]">السعر : <span class="text-[#D4AF37]">{{ $p['price'] }}</span></span>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</section>

<!-- Customizer Section -->
<section id="customizer" class="py-24 bg-white relative">
    <div class="container mx-auto px-4 max-w-5xl">
        <div class="text-center mb-16">
            <span class="text-[#D4AF37] font-bold text-sm tracking-wider uppercase">حاسبة السعر وبناء البكج التفاعلية</span>
            <h2 class="text-4xl font-extrabold text-[#0D0D0D] mt-2 mb-4">ركّبي مجموعتك الخاصة بلمسة واحدة</h2>
            <div class="w-24 h-1 bg-[#D4AF37] mx-auto"></div>
            <p class="text-gray-600 mt-4">قومي باختيار المنتجات التي ترغبين بها لمشاهدة المجموع الكلي، مع الاستمتاع بخصومات وتوفير حقيقي عند طلب البكجات الكاملة!</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            <!-- Selector List -->
            <div class="lg:col-span-7 space-y-4">
                <h3 class="text-xl font-bold text-[#0D0D0D] mb-6 flex items-center gap-2"><i class="fas fa-tasks text-[#D4AF37]"></i> حددي منتجاتك المفضلة:</h3>
                
                @php
                    $items = [
                        ['id' => 'check-shampoo', 'img' => 'شامبو مسك.webp', 'name' => 'شامبو مسك الطبيعي (1000 مل)', 'sub' => 'حجم عملاق خالٍ من الأملاح', 'price' => 55.00, 'fallback' => 'https://images.unsplash.com/photo-1535585209827-a15fcdbc4c2d?auto=format&fit=crop&q=80&w=200'],
                        ['id' => 'check-mask', 'img' => 'حمام زيت مسك.webp', 'name' => 'حمام زيت مسك وترميم (500 مل)', 'sub' => 'مغذي ومعالج بالأرغان العضوي', 'price' => 50.00, 'fallback' => 'https://images.unsplash.com/photo-1526947425960-945c6e72858f?auto=format&fit=crop&q=80&w=200'],
                        ['id' => 'check-serum', 'img' => 'سيروم.webp', 'name' => 'سيروم العناية بالمسك (150 مل)', 'sub' => 'حماية يومية فائقة ومنع للتطاير', 'price' => 45.00, 'fallback' => 'https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?auto=format&fit=crop&q=80&w=200'],
                        ['id' => 'check-gold', 'img' => 'سيروم الذهب.webp', 'name' => 'سيروم الذهب بالمسك والأرغان (150 مل)', 'sub' => 'لمعان ملكي وثبات عطري متناهي', 'price' => 45.00, 'fallback' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&q=80&w=200'],
                    ];
                @endphp

                @foreach($items as $item)
                <label class="flex items-center gap-4 p-4 bg-[#F4F4F6]/50 rounded-2xl border-2 border-transparent hover:border-[#D4AF37]/40 cursor-pointer transition-all duration-300 shadow-sm block relative">
                    <input type="checkbox" id="{{ $item['id'] }}" checked class="w-6 h-6 text-[#D4AF37] border-gray-300 rounded focus:ring-[#D4AF37]">
                    <img src="{{ $item['img'] }}" alt="{{ $item['name'] }}" class="w-16 h-16 object-contain bg-white rounded-lg p-1 border border-gray-100" onerror="this.src='{{ $item['fallback'] }}'">
                    <div class="flex-grow">
                        <h4 class="font-bold text-[#0D0D0D] text-base">{{ $item['name'] }}</h4>
                        <p class="text-xs text-gray-500">{{ $item['sub'] }}</p>
                    </div>
                    <div class="text-left">
                        <span class="font-bold text-lg text-[#D4AF37]">{{ number_format($item['price'], 2) }} ₪</span>
                    </div>
                </label>
                @endforeach
            </div>

            <!-- Receipt Panel -->
            <div class="lg:col-span-5 bg-[#0D0D0D] text-white rounded-3xl p-8 shadow-2xl border border-[#D4AF37]/20 relative">
                <div class="absolute top-0 left-0 w-24 h-24 bg-[#D4AF37]/10 rounded-br-full pointer-events-none"></div>
                <h3 class="text-2xl font-bold mb-6 pb-4 border-b border-gray-800 text-[#F3E5AB] flex items-center gap-2">
                    <i class="fas fa-receipt text-[#D4AF37]"></i> ملخص طلبك
                </h3>
                
                <div class="space-y-4 mb-8 text-sm" id="receipt-items"></div>

                <div class="border-t border-gray-800 pt-6 mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-400">السعر الإجمالي الأصلي</span>
                        <span class="line-through text-gray-500" id="original-price">195.00 ₪</span>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-400">قيمة التوفير والخصم</span>
                        <span class="text-green-400 font-bold" id="discount-amount">-25.00 ₪</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-white">السعر الإجمالي النهائي</span>
                        <span class="text-3xl font-black text-[#D4AF37]" id="total-price">170.00 ₪</span>
                    </div>
                    <div class="text-xs text-[#F3E5AB] mt-2 text-center font-bold" id="bundle-badge">
                        🎉 تهانينا! حصلتِ على العرض الكامل
                    </div>
                </div>

                <div id="selection-warning" class="hidden bg-red-900/30 border border-red-500/50 text-red-200 text-xs p-3 rounded-xl mb-4 text-center">
                    <i class="fas fa-exclamation-triangle mr-1"></i> يرجى اختيار منتج واحد على الأقل لإتمام عملية الطلب.
                </div>

                <div class="space-y-4">
                    <button id="direct-order-btn" onclick="openOrderModal()" class="w-full bg-gradient-to-r from-[#D4AF37] to-[#F3E5AB] text-[#0D0D0D] py-4 rounded-xl font-bold text-lg transition-all flex items-center justify-center gap-3 shadow-[0_4px_15px_rgba(212,175,55,0.3)] hover:scale-[1.01]">
                        <i class="fas fa-shopping-bag"></i> الطلب المباشر من الموقع
                    </button>
                    <a id="whatsapp-order-btn" href="#" target="_blank" class="w-full bg-green-600 hover:bg-green-700 text-white py-4 rounded-xl font-bold text-lg transition-colors flex items-center justify-center gap-3">
                        <i class="fab fa-whatsapp text-2xl"></i> اطلب عبر الواتساب
                    </a>
                    <a href="tel:972569030203" class="w-full bg-transparent border-2 border-[#D4AF37]/60 hover:border-[#D4AF37] text-[#D4AF37] py-4 rounded-xl font-bold text-lg transition-colors flex items-center justify-center gap-3">
                        <i class="fas fa-phone-alt"></i> أو اتصل لطلبك فوراً
                    </a>
                </div>
                <p class="text-center text-xs text-gray-500 mt-6">توصيل آمن وسريع لكافة مناطق الضفة والقدس والداخل.</p>
            </div>
        </div>
    </div>
</section>

<!-- Order Modal -->
<div id="order-modal" class="fixed inset-0 bg-black/85 backdrop-blur-sm z-[99999] hidden items-center justify-center p-4">
    <div class="bg-white text-gray-800 rounded-3xl max-w-md w-full p-8 border border-[#D4AF37]/20 relative animate-fade-in shadow-2xl">
        <button onclick="closeOrderModal()" class="absolute top-4 left-4 text-gray-400 hover:text-[#0D0D0D] transition-colors p-2">
            <i class="fas fa-times text-xl"></i>
        </button>
        <div id="modal-form-body">
            <h3 class="text-2xl font-bold text-[#0D0D0D] mb-2 text-center">إتمام الطلب المباشر</h3>
            <p class="text-gray-500 text-sm text-center mb-6">يرجى ملء البيانات التالية لتأكيد طلبك من نوڤا كوزمتكس</p>
            <form id="direct-order-form" onsubmit="submitDirectOrder(event)" class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">الاسم الكامل *</label>
                    <input type="text" required id="order-name" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37] outline-none text-sm" placeholder="أدخلي اسمك الكامل">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">رقم الهاتف الجوال *</label>
                    <input type="tel" required id="order-phone" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37] outline-none text-sm text-right" dir="ltr" placeholder="059XXXXXXX أو 056XXXXXXX">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">المدينة / العنوان بالتفصيل *</label>
                    <input type="text" required id="order-address" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37] outline-none text-sm" placeholder="مثال: رام الله - حي الماصيون - عمارة الهدى">
                </div>
                <div class="bg-[#F4F4F6] p-4 rounded-xl border border-gray-100 text-xs text-gray-600 mt-2">
                    <div class="font-bold text-[#0D0D0D] mb-1">ملخص الفاتورة:</div>
                    <div id="modal-summary-items" class="space-y-1 mb-2"></div>
                    <div class="flex justify-between font-bold text-[#0D0D0D] text-sm border-t border-gray-200 pt-2">
                        <span>المجموع النهائي:</span>
                        <span id="modal-summary-total" class="text-[#D4AF37]">0.00 ₪</span>
                    </div>
                </div>
                <button type="submit" class="w-full bg-[#0D0D0D] hover:bg-[#0D0D0D]/90 text-white py-4 rounded-xl font-bold text-lg transition-colors flex items-center justify-center gap-2 mt-4 shadow-lg">
                    <i class="fas fa-check-circle"></i> تأكيد وإرسال الطلب
                </button>
            </form>
        </div>
        <div id="order-success-screen" class="hidden text-center py-8">
            <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl animate-bounce">
                <i class="fas fa-check"></i>
            </div>
            <h4 class="text-2xl font-bold text-[#0D0D0D] mb-3">تم استلام طلبك بنجاح!</h4>
            <p class="text-gray-600 text-sm mb-6 leading-relaxed">شكراً لثقتك بـ <strong class="text-[#0D0D0D]">نوڤا كوزمتكس</strong>. سيقوم طاقم خدمة العملاء بالتواصل معك هاتفياً خلال 24 ساعة لتأكيد عنوان الشحن والتسليم.</p>
            <button onclick="closeOrderModal()" class="bg-gradient-to-r from-[#D4AF37] to-[#F3E5AB] text-[#0D0D0D] px-8 py-3 rounded-full font-bold text-sm hover:brightness-110 transition-all shadow-md">
                حسناً، العودة للموقع
            </button>
        </div>
    </div>
</div>

</div>

@push('scripts')
<script>
function calculateTotal() {
    const checks = ['check-shampoo', 'check-mask', 'check-serum', 'check-gold'];
    const prices = { 'check-shampoo': 55, 'check-mask': 50, 'check-serum': 45, 'check-gold': 45 };
    const names = {
        'check-shampoo': { name: 'شامبو مسك الطبيعي (1000 مل)', display: 'شامبو مسك الطبيعي (1000 مل)' },
        'check-mask': { name: 'حمام زيت مسك وترميم (500 مل)', display: 'حمام زيت مسك وترميم (500 مل)' },
        'check-serum': { name: 'سيروم العناية بالمسك (150 مل)', display: 'سيروم العناية بالمسك (150 مل)' },
        'check-gold': { name: 'سيروم الذهب بالمسك بالأرغان', display: 'سيروم الذهب بالمسك بالأرغان' }
    };

    const receiptContainer = document.getElementById('receipt-items');
    receiptContainer.innerHTML = '';
    let rawTotal = 0;
    let activeItems = [];

    checks.forEach(id => {
        const el = document.getElementById(id);
        if (el && el.checked) {
            rawTotal += prices[id];
            activeItems.push({ name: names[id].name, price: prices[id] });
            receiptContainer.innerHTML += `<div class="flex justify-between"><span>• ${names[id].display}</span><span>${prices[id].toFixed(2)} ₪</span></div>`;
        }
    });

    const warningBox = document.getElementById('selection-warning');
    const directOrderBtn = document.getElementById('direct-order-btn');
    const whatsappOrderBtn = document.getElementById('whatsapp-order-btn');

    if (activeItems.length === 0) {
        receiptContainer.innerHTML = '<div class="text-center text-gray-500 py-4">الرجاء اختيار منتج واحد على الأقل للمتابعة.</div>';
        document.getElementById('original-price').innerText = '0.00 ₪';
        document.getElementById('discount-amount').innerText = '0.00 ₪';
        document.getElementById('total-price').innerText = '0.00 ₪';
        document.getElementById('bundle-badge').innerText = 'الرجاء اختيار المنتجات';
        warningBox.classList.remove('hidden');
        directOrderBtn.classList.add('opacity-50', 'cursor-not-allowed');
        whatsappOrderBtn.classList.add('opacity-50', 'cursor-not-allowed');
        updateWhatsAppButton([], 0);
        return;
    }

    warningBox.classList.add('hidden');
    directOrderBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    whatsappOrderBtn.classList.remove('opacity-50', 'cursor-not-allowed');

    const hasShampoo = document.getElementById('check-shampoo')?.checked;
    const hasMask = document.getElementById('check-mask')?.checked;
    const hasSerum = document.getElementById('check-serum')?.checked;
    const hasGold = document.getElementById('check-gold')?.checked;

    let finalTotal = rawTotal, discount = 0, badgeText = '';

    if (hasShampoo && hasMask && hasSerum && hasGold) {
        finalTotal = 210; discount = rawTotal - finalTotal;
        badgeText = '🎉 عرض ملكي متكامل! وفرتِ ' + discount + ' ₪ وحصلتِ على المجموعة الرباعية الكاملة!';
    } else if (!hasShampoo && hasMask && hasSerum && hasGold) {
        finalTotal = 170; discount = rawTotal - finalTotal;
        badgeText = '✨ البكج الثلاثي الأيقوني! وفرتِ ' + discount + ' ₪ على منتجات العناية والترميم الحصرية!';
    } else if (activeItems.length >= 3) {
        finalTotal = rawTotal - 15; discount = 15;
        badgeText = '🎁 عرض رائع! خصم بقيمة 15 ₪ لشراء 3 منتجات مخصصة!';
    } else if (activeItems.length === 2) {
        finalTotal = rawTotal - 5; discount = 5;
        badgeText = '💫 خصم ثنائي بقيمة 5 ₪ مضاف لمشترياتك الآن!';
    } else {
        finalTotal = rawTotal; discount = 0;
        badgeText = 'استمتعي بالمنتج المختار الفاخر من نوڤا كوزمتكس ✨';
    }

    document.getElementById('original-price').innerText = rawTotal.toFixed(2) + ' ₪';
    document.getElementById('discount-amount').innerText = '-' + discount.toFixed(2) + ' ₪';
    document.getElementById('total-price').innerText = finalTotal.toFixed(2) + ' ₪';
    document.getElementById('bundle-badge').innerText = badgeText;
    updateWhatsAppButton(activeItems, finalTotal);
}

function updateWhatsAppButton(items, finalPrice) {
    const phoneNumber = '970567088284';
    if (items.length === 0) { document.getElementById('whatsapp-order-btn').href = '#'; return; }
    let message = 'مرحباً "نوڤا كوزمتكس" 🌟، أرغب في طلب البكج المخصص الذي قمت بتركيبه عبر موقعكم للمجموعة الملكية:\n\n';
    items.forEach((item, i) => { message += (i + 1) + '. ' + item.name + '\n'; });
    message += '\n💰 السعر الإجمالي النهائي: ' + finalPrice.toFixed(2) + ' شيكل.\n📍 يرجى تزويدي بتفاصيل التوصيل وموعد الشحن. شكراً لكم!';
    document.getElementById('whatsapp-order-btn').href = 'https://wa.me/' + phoneNumber + '?text=' + encodeURIComponent(message);
}

function openOrderModal() {
    const checks = ['check-shampoo', 'check-mask', 'check-serum', 'check-gold'];
    let hasAny = false, summaryHtml = '';
    const labels = { 'check-shampoo': 'شامبو مسك (1000 مل)', 'check-mask': 'حمام زيت مسك (500 مل)', 'check-serum': 'سيروم العناية (150 مل)', 'check-gold': 'سيروم الذهب الفاخر (150 مل)' };
    const prices = { 'check-shampoo': '55.00 ₪', 'check-mask': '50.00 ₪', 'check-serum': '45.00 ₪', 'check-gold': '45.00 ₪' };

    checks.forEach(id => {
        const el = document.getElementById(id);
        if (el && el.checked) {
            hasAny = true;
            summaryHtml += '<div class="flex justify-between"><span>• ' + labels[id] + '</span><span>' + prices[id] + '</span></div>';
        }
    });

    if (!hasAny) {
        document.getElementById('selection-warning').classList.remove('hidden');
        document.getElementById('customizer').scrollIntoView({ behavior: 'smooth' });
        return;
    }

    document.getElementById('modal-summary-items').innerHTML = summaryHtml;
    document.getElementById('modal-summary-total').innerText = document.getElementById('total-price').innerText;
    document.getElementById('modal-form-body').classList.remove('hidden');
    document.getElementById('order-success-screen').classList.add('hidden');
    const modal = document.getElementById('order-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeOrderModal() {
    const modal = document.getElementById('order-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function submitDirectOrder(e) {
    e.preventDefault();
    document.getElementById('modal-form-body').classList.add('hidden');
    document.getElementById('order-success-screen').classList.remove('hidden');
    document.getElementById('order-name').value = '';
    document.getElementById('order-phone').value = '';
    document.getElementById('order-address').value = '';
}

// Loading page
window.addEventListener('load', function() {
    const barFill = document.getElementById('loader-bar-fill');
    const loadingText = document.getElementById('loading-text');
    const overlay = document.getElementById('loading-overlay');
    const content = document.getElementById('musk-content');

    const messages = ['جاري تحميل المجموعة الملكية...', 'تجهيز أجود مكونات العناية...', 'إضافة لمسات المسك الفاخرة...', 'تجربة الجمال في الطريق إليكِ...'];

    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 18 + 5;
        if (progress > 100) progress = 100;
        barFill.style.width = progress + '%';
        loadingText.textContent = messages[Math.min(Math.floor(progress / 30), messages.length - 1)];
        if (progress >= 100) {
            clearInterval(interval);
            setTimeout(() => { overlay.classList.add('hidden'); content.classList.add('visible'); }, 400);
        }
    }, 250);

    calculateTotal();
    checks().forEach(id => {
        document.getElementById(id)?.addEventListener('change', calculateTotal);
    });
});

function checks() { return ['check-shampoo', 'check-mask', 'check-serum', 'check-gold']; }

// Re-attach listeners after Alpine/DOM ready
document.addEventListener('DOMContentLoaded', function() {
    checks().forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', calculateTotal);
    });
});
</script>
@endpush
@endsection