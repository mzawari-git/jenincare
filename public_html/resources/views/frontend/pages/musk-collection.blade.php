<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مجموعة المسك الفاخرة بالأرغان | الدليل الشامل - نوڤا كوزمتكس</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Cairo', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: {
                        nova: {
                            dark: '#0D0D0D',
                            gold: '#D4AF37',
                            lightGold: '#F3E5AB',
                            rose: '#B76E79',
                            gray: '#F4F4F6'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Cairo', sans-serif; scroll-behavior: smooth; }
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
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(212, 175, 55, 0.15);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
    </style>
</head>
<body class="bg-[#FAF9F6] text-gray-800 antialiased">

    <!-- Navbar -->
    <nav class="bg-[#0D0D0D]/95 backdrop-blur-md text-white p-4 sticky top-0 z-50 shadow-xl border-b border-nova-gold/20">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-2xl font-bold tracking-wider font-serif flex flex-col">
                <span class="gradient-text leading-none tracking-widest">NOVA</span>
                <span class="text-[10px] font-sans tracking-[0.25em] text-gray-400 mt-1">COSMETICS</span>
            </div>
            <div class="flex items-center gap-6 text-sm font-semibold">
                <a href="/" class="hover:text-nova-gold transition-colors hidden lg:block">الرئيسية</a>
                <a href="#overview" class="hover:text-nova-gold transition-colors hidden lg:block">نظرة عامة</a>
                <a href="#features" class="hover:text-nova-gold transition-colors hidden lg:block">سر الفعالية</a>
                <a href="#products" class="hover:text-nova-gold transition-colors">مكونات المجموعة</a>
                <a href="#customizer" class="bg-gradient-to-r from-nova-gold to-nova-lightGold text-nova-dark px-5 py-2 rounded-full font-bold shadow-md hover:brightness-110 transition-all">
                    اطلب بكجك المخصص
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header id="overview" class="hero-bg py-24 lg:py-36 overflow-hidden relative">
        <div class="absolute inset-0 opacity-20 section-pattern"></div>
        <div class="container mx-auto px-4 text-center z-10 relative">
            <span class="text-nova-gold font-bold tracking-widest text-sm mb-5 inline-flex items-center gap-2 bg-nova-gold/10 px-4 py-1.5 rounded-full border border-nova-gold/30">
                <i class="fas fa-crown"></i> مجموعة المسك والأرغان الملكية
            </span>
            <h1 class="text-4xl lg:text-7xl font-extrabold mb-6 text-white leading-tight">
                أعيدي الحيوية والبريق لشعرك
            </h1>
            <p class="text-xl lg:text-2xl text-nova-lightGold mb-8 font-light max-w-3xl mx-auto leading-relaxed">
                الاندماج الطبيعي الفاخر بين زيت الأرغان العضوي المرمم ونفحات المسك الشرقي الساحر.
            </p>
            <p class="text-gray-300 text-base lg:text-lg mb-12 max-w-2xl mx-auto leading-relaxed">
                في "نوڤا كوزمتكس"، نقدم لك طقوساً متكاملة للعناية الاحترافية بالشعر في منزلك. صُممت هذه المجموعة الخالية من المواد الضارة لتنظيف، ترطيب، وحماية شعرك بعمق مع رائحة تدوم طوال اليوم.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="#customizer" class="w-full sm:w-auto bg-gradient-to-r from-nova-gold to-nova-lightGold text-nova-dark px-8 py-4 rounded-full font-bold text-lg hover:shadow-[0_0_25px_rgba(212,175,55,0.4)] transition-all">
                    تسوّق المجموعة الآن
                </a>
                <a href="#products" class="w-full sm:w-auto border border-white/20 hover:border-nova-gold text-white px-8 py-4 rounded-full font-bold text-lg transition-colors">
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
                    <div class="w-12 h-12 bg-nova-gold/10 text-nova-gold rounded-xl flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-shield-virus"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-nova-dark">خالٍ من الكبريت والملح</h4>
                        <p class="text-xs text-gray-500 mt-1">آمن للشعر المعالج بالبروتين والكيراتين</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-4">
                    <div class="w-12 h-12 bg-nova-gold/10 text-nova-gold rounded-xl flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-nova-dark">أرغان عضوي 100%</h4>
                        <p class="text-xs text-gray-500 mt-1">تغذية مكثفة من الأعماق إلى الأطراف</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-4">
                    <div class="w-12 h-12 bg-nova-gold/10 text-nova-gold rounded-xl flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-hourglass-start"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-nova-dark">رائحة مسك ممتدة</h4>
                        <p class="text-xs text-gray-500 mt-1">ثبات عطري ساحر يدوم لأيام</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-4">
                    <div class="w-12 h-12 bg-nova-gold/10 text-nova-gold rounded-xl flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-nova-dark">جودة احترافية معتمدة</h4>
                        <p class="text-xs text-gray-500 mt-1">مُطوّر في أرقى مختبرات التجميل</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Detailed Products Section -->
    <section id="products" class="py-24 bg-nova-gray/50 section-pattern">
        <div class="container mx-auto px-4">
            <div class="text-center mb-20">
                <span class="text-nova-gold font-bold text-sm tracking-wider uppercase">روتين العناية الملكي</span>
                <h2 class="text-4xl font-extrabold text-nova-dark mt-2 mb-4">تفاصيل مكونات مجموعة مسك الفاخرة</h2>
                <div class="w-24 h-1 bg-nova-gold mx-auto"></div>
                <p class="text-gray-600 mt-4 max-w-2xl mx-auto">تعرفي على روتين الجمال المتكامل لشعر صحي، براق، ونابض بالحياة.</p>
            </div>

            <div class="space-y-24">
                
                <!-- Product 1: Shampoo -->
                <div class="flex flex-col lg:flex-row items-center gap-16 bg-white p-8 lg:p-12 rounded-3xl shadow-md border border-gray-100 relative group">
                    <div class="lg:w-2/5 flex justify-center">
                        <div class="relative w-80 h-96 rounded-2xl overflow-hidden shadow-lg border border-gray-100 flex items-center justify-center bg-white p-6 group-hover:scale-[1.02] transition-transform duration-500">
                            <img src="/uploads/musk-collection/shampoo-musk.webp" alt="شامبو مسك" class="max-h-full object-contain" onerror="this.src='https://images.unsplash.com/photo-1535585209827-a15fcdbc4c2d?auto=format&fit=crop&q=80&w=600'">
                            <span class="absolute top-4 right-4 bg-nova-dark text-nova-gold text-xs font-extrabold px-4 py-1.5 rounded-full shadow-md">الخطوة 1: التنظيف الفاخر</span>
                        </div>
                    </div>
                    <div class="lg:w-3/5">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-nova-gold/15 text-nova-gold text-xs font-bold px-3 py-1 rounded-md">جديد وحصري</span>
                            <span class="text-gray-400 text-sm font-semibold">سعة 1000 مل (حجم عملاق)</span>
                        </div>
                        <h3 class="text-3xl font-bold text-nova-dark mb-4">شامبو مسك خالي من الكبريت والملح (Sulfate & Salt Free)</h3>
                        <p class="text-gray-600 text-lg mb-6 leading-relaxed">
                            ابدئي روتينك بتنظيف مثالي وآمن تماماً. هذا الشامبو المميز مصمم خصيصاً للشعر الحساس والمعالج بالبروتين أو الكيراتين. ينظف الفروة بلطف دون سلبها الزيوت الطبيعية والمغذيات، ويحمي صبغة الشعر من البهتان مع ترطيبه بزيت الأرغان العضوي وغمره بعبير مسك مميز.
                        </p>
                        <div class="grid grid-cols-2 gap-4 mb-6 text-sm text-gray-600">
                            <div class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> خالي تماماً من الأملاح والبارابين</div>
                            <div class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> يحافظ على علاج البروتين المصبوغ</div>
                            <div class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> ينظف بلطف ويرطب ألياف الشعر</div>
                            <div class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> رغوة غنية بعبق المسك الصافي</div>
                        </div>
                        <div class="border-t border-gray-100 pt-6 flex items-center justify-between">
                            <span class="text-2xl font-black text-nova-dark">السعر : <span class="text-nova-gold">55.00 ₪</span></span>
                        </div>
                    </div>
                </div>

                <!-- Product 2: Hair Mask -->
                <div class="flex flex-col lg:flex-row-reverse items-center gap-16 bg-white p-8 lg:p-12 rounded-3xl shadow-md border border-gray-100 relative group">
                    <div class="lg:w-2/5 flex justify-center">
                        <div class="relative w-80 h-96 rounded-2xl overflow-hidden shadow-lg border border-gray-100 flex items-center justify-center bg-white p-6 group-hover:scale-[1.02] transition-transform duration-500">
                            <img src="/uploads/musk-collection/hair-mask-musk.webp" alt="حمام زيت مسك" class="max-h-full object-contain" onerror="this.src='https://images.unsplash.com/photo-1526947425960-945c6e72858f?auto=format&fit=crop&q=80&w=600'">
                            <span class="absolute top-4 right-4 bg-nova-dark text-nova-gold text-xs font-extrabold px-4 py-1.5 rounded-full shadow-md">الخطوة 2: الترميم والترطيب</span>
                        </div>
                    </div>
                    <div class="lg:w-3/5">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-nova-gold/15 text-nova-gold text-xs font-bold px-3 py-1 rounded-md">الأكثر طلباً</span>
                            <span class="text-gray-400 text-sm font-semibold">سعة 500 مل</span>
                        </div>
                        <h3 class="text-3xl font-bold text-nova-dark mb-4">حمام زيت مسك وترميم عميق (Deep Reviving)</h3>
                        <p class="text-gray-600 text-lg mb-6 leading-relaxed">
                            علاج مكثف ذو مفعول سحري مصمم خصيصاً لإعادة إحياء الشعر المنهك، التالف والمتقصف. بتركيبة غنية بالأرغان العضوي النقي، يتغلغل حمام الزيت داخل جذور الشعر لترميم الخلايا التالفة وإعادة بناء درع الرطوبة الطبيعي، مما يمنح شعرك نعومة حريرية ولمعاناً لا يقاوم.
                        </p>
                        <h4 class="font-bold text-nova-dark mb-3"><i class="fas fa-magic text-nova-gold mr-1"></i> نصائح الاستخدام الاحترافي:</h4>
                        <ul class="list-disc list-inside text-gray-600 space-y-2 mb-6 marker:text-nova-gold text-sm">
                            <li>بعد الشامبو، وزّعي كمية مناسبة بالتساوي من المنتصف وحتى الأطراف.</li>
                            <li>دلكي خصلات شعرك بلطف، ثم اتركي القناع لمدة 15 إلى 20 دقيقة.</li>
                            <li>لأفضل نتيجة، لفي شعرك بمنشفة دافئة ورطبة قبل شطفه بالماء البارد لإغلاق مسام الشعر.</li>
                        </ul>
                        <div class="border-t border-gray-100 pt-6 flex items-center justify-between">
                            <span class="text-2xl font-black text-nova-dark">السعر : <span class="text-nova-gold">50.00 ₪</span></span>
                        </div>
                    </div>
                </div>

                <!-- Product 3: Regular Serum -->
                <div class="flex flex-col lg:flex-row items-center gap-16 bg-white p-8 lg:p-12 rounded-3xl shadow-md border border-gray-100 relative group">
                    <div class="lg:w-2/5 flex justify-center">
                        <div class="relative w-80 h-96 rounded-2xl overflow-hidden shadow-lg border border-gray-100 flex items-center justify-center bg-white p-6 group-hover:scale-[1.02] transition-transform duration-500">
                            <img src="/uploads/musk-collection/serum-musk.webp" alt="سيروم مسك" class="max-h-full object-contain" onerror="this.src='https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?auto=format&fit=crop&q=80&w=600'">
                            <span class="absolute top-4 right-4 bg-nova-dark text-nova-gold text-xs font-extrabold px-4 py-1.5 rounded-full shadow-md">الخطوة 3: التغذية والنمو</span>
                        </div>
                    </div>
                    <div class="lg:w-3/5">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-nova-gold/15 text-nova-gold text-xs font-bold px-3 py-1 rounded-md">حماية قصوى</span>
                            <span class="text-gray-400 text-sm font-semibold">سعة 150 مل</span>
                        </div>
                        <h3 class="text-3xl font-bold text-nova-dark mb-4">سيروم الأرغان والمسك للعناية المتقدمة (Advanced Growth)</h3>
                        <p class="text-gray-600 text-lg mb-6 leading-relaxed">
                            سيروم يومي خفيف الوزن ذو تركيبة غنية بزيت الأرغان الأساسي. يعمل على تغليف جذع الشعرة لحمايتها من التلف والحرارة (الاستشوار ومكواة الشعر) مع تحفيز نمو الشعر الطبيعي وتقوية البصيلات. يمنع تجعد الشعر ويعالج الهيشان والتقصف فوراً دون ترك أي ملمس لزج أو ثقيل.
                        </p>
                        <div class="grid grid-cols-2 gap-4 mb-6 text-sm text-gray-600">
                            <div class="flex items-center gap-2"><i class="fas fa-shield-halved text-nova-gold"></i> حماية حرارية قصوى أثناء التصفيف</div>
                            <div class="flex items-center gap-2"><i class="fas fa-sparkles text-nova-gold"></i> يعطي لمعاناً براقاً ويفك التشابك</div>
                            <div class="flex items-center gap-2"><i class="fas fa-compress text-nova-gold"></i> يمنع ويعالج نفشة الشعر والهيشان</div>
                            <div class="flex items-center gap-2"><i class="fas fa-dna text-nova-gold"></i> يحفز نمو الشعر الصحي ويقوي الأطراف</div>
                        </div>
                        <div class="border-t border-gray-100 pt-6 flex items-center justify-between">
                            <span class="text-2xl font-black text-nova-dark">السعر : <span class="text-nova-gold">45.00 ₪</span></span>
                        </div>
                    </div>
                </div>

                <!-- Product 4: Gold Serum -->
                <div class="flex flex-col lg:flex-row-reverse items-center gap-16 bg-white p-8 lg:p-12 rounded-3xl shadow-md border border-gray-100 relative group overflow-hidden">
                    <div class="absolute top-0 right-0 w-36 h-36 bg-nova-gold/5 rounded-bl-full pointer-events-none"></div>
                    <div class="lg:w-2/5 flex justify-center">
                        <div class="relative w-80 h-96 rounded-2xl overflow-hidden shadow-lg border border-gray-100 flex items-center justify-center bg-white p-6 group-hover:scale-[1.02] transition-transform duration-500 shadow-nova-gold/10">
                            <img src="/uploads/musk-collection/gold-serum.webp" alt="سيروم الذهب" class="max-h-full object-contain" onerror="this.src='https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&q=80&w=600'">
                            <span class="absolute top-4 right-4 bg-gradient-to-r from-nova-gold to-yellow-500 text-nova-dark text-xs font-black px-4 py-1.5 rounded-full shadow-md">اللمسة الملكية الفاخرة</span>
                        </div>
                    </div>
                    <div class="lg:w-3/5">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-nova-dark text-nova-gold text-xs font-bold px-3 py-1 rounded-md"><i class="fas fa-star"></i> منتج النخبة</span>
                            <span class="text-gray-400 text-sm font-semibold">سعة 150 مل - إصدار محدود</span>
                        </div>
                        <h3 class="text-3xl font-bold text-nova-dark mb-4 flex items-center gap-2">
                            سيروم الذهب الحصري للأرغان والمسك <i class="fas fa-wand-magic-sparkles text-nova-gold"></i>
                        </h3>
                        <p class="text-gray-600 text-lg mb-6 leading-relaxed">
                            جوهرة تشكيلة المسك. سيروم النخبة المطور بجزيئات الذهب الدقيقة المغذية والمنعمة للشعر مع زيت الأرغان. صُمم لإعطاء لمعان وإضاءة ثلاثية الأبعاد لشعرك مع رائحة مسك ممتدة بالغة الثبات والجاذبية. يعمل على ملء الفراغات الدقيقة وتنعيم الطبقة الخارجية للشعرة للحصول على ملمس انسيابي مخملي كالحرير.
                        </p>
                        <h4 class="font-bold text-nova-dark mb-2">مميزات سيروم الذهب الاستثنائية:</h4>
                        <p class="text-gray-600 mb-6 text-sm">
                            الذهب والزيوت المدمجة تعمل بتناغم تام على عكس الضوء الطبيعي لتسليط الإشراقة واللمعان على مظهر شعرك، مع توفير علاج مكثف للشعر شديد التضرر والجفاف.
                        </p>
                        <div class="border-t border-gray-100 pt-6 flex items-center justify-between">
                            <span class="text-2xl font-black text-nova-dark">السعر : <span class="text-nova-gold">45.00 ₪</span></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Customizer & Interactive Calculator Section -->
    <section id="customizer" class="py-24 bg-white relative">
        <div class="container mx-auto px-4 max-w-5xl">
            <div class="text-center mb-16">
                <span class="text-nova-gold font-bold text-sm tracking-wider uppercase">حاسبة السعر وبناء البكج التفاعلية</span>
                <h2 class="text-4xl font-extrabold text-nova-dark mt-2 mb-4">ركّبي مجموعتك الخاصة بلمسة واحدة</h2>
                <div class="w-24 h-1 bg-nova-gold mx-auto"></div>
                <p class="text-gray-600 mt-4">قومي باختيار المنتجات التي ترغبين بها لمشاهدة المجموع الكلي، مع الاستمتاع بخصومات وتوفير حقيقي عند طلب البكجات الكاملة!</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                
                <!-- Selector List -->
                <div class="lg:col-span-7 space-y-4">
                    <h3 class="text-xl font-bold text-nova-dark mb-6 flex items-center gap-2"><i class="fas fa-tasks text-nova-gold"></i> حددي منتجاتك المفضلة:</h3>
                    
                    <!-- Item 1: Shampoo -->
                    <label class="flex items-center gap-4 p-4 bg-nova-gray/50 rounded-2xl border-2 border-transparent hover:border-nova-gold/40 cursor-pointer transition-all duration-300 shadow-sm block relative">
                        <input type="checkbox" id="check-shampoo" checked class="w-6 h-6 text-nova-gold border-gray-300 rounded focus:ring-nova-gold" onchange="calculateTotal()">
                        <img src="/uploads/musk-collection/shampoo-musk.webp" alt="شامبو مسك" class="w-16 h-16 object-contain bg-white rounded-lg p-1 border border-gray-100" onerror="this.src='https://images.unsplash.com/photo-1535585209827-a15fcdbc4c2d?auto=format&fit=crop&q=80&w=200'">
                        <div class="flex-grow">
                            <h4 class="font-bold text-nova-dark text-base">شامبو مسك الطبيعي (1000 مل)</h4>
                            <p class="text-xs text-gray-500">حجم عملاق خالٍ من الأملاح</p>
                        </div>
                        <div class="text-left">
                            <span class="font-bold text-lg text-nova-gold">55.00 ₪</span>
                        </div>
                    </label>

                    <!-- Item 2: Hair Mask -->
                    <label class="flex items-center gap-4 p-4 bg-nova-gray/50 rounded-2xl border-2 border-transparent hover:border-nova-gold/40 cursor-pointer transition-all duration-300 shadow-sm block relative">
                        <input type="checkbox" id="check-mask" checked class="w-6 h-6 text-nova-gold border-gray-300 rounded focus:ring-nova-gold" onchange="calculateTotal()">
                        <img src="/uploads/musk-collection/hair-mask-musk.webp" alt="حمام زيت مسك" class="w-16 h-16 object-contain bg-white rounded-lg p-1 border border-gray-100" onerror="this.src='https://images.unsplash.com/photo-1526947425960-945c6e72858f?auto=format&fit=crop&q=80&w=200'">
                        <div class="flex-grow">
                            <h4 class="font-bold text-nova-dark text-base">حمام زيت مسك وترميم (500 مل)</h4>
                            <p class="text-xs text-gray-500">مغذي ومعالج بالأرغان العضوي</p>
                        </div>
                        <div class="text-left">
                            <span class="font-bold text-lg text-nova-gold">50.00 ₪</span>
                        </div>
                    </label>

                    <!-- Item 3: Serum -->
                    <label class="flex items-center gap-4 p-4 bg-nova-gray/50 rounded-2xl border-2 border-transparent hover:border-nova-gold/40 cursor-pointer transition-all duration-300 shadow-sm block relative">
                        <input type="checkbox" id="check-serum" checked class="w-6 h-6 text-nova-gold border-gray-300 rounded focus:ring-nova-gold" onchange="calculateTotal()">
                        <img src="/uploads/musk-collection/serum-musk.webp" alt="سيروم مسك" class="w-16 h-16 object-contain bg-white rounded-lg p-1 border border-gray-100" onerror="this.src='https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?auto=format&fit=crop&q=80&w=200'">
                        <div class="flex-grow">
                            <h4 class="font-bold text-nova-dark text-base">سيروم العناية بالمسك (150 مل)</h4>
                            <p class="text-xs text-gray-500">حماية يومية فائقة ومنع للتطاير</p>
                        </div>
                        <div class="text-left">
                            <span class="font-bold text-lg text-nova-gold">45.00 ₪</span>
                        </div>
                    </label>

                    <!-- Item 4: Gold Serum -->
                    <label class="flex items-center gap-4 p-4 bg-nova-gray/50 rounded-2xl border-2 border-transparent hover:border-nova-gold/40 cursor-pointer transition-all duration-300 shadow-sm block relative">
                        <input type="checkbox" id="check-gold" checked class="w-6 h-6 text-nova-gold border-gray-300 rounded focus:ring-nova-gold" onchange="calculateTotal()">
                        <img src="/uploads/musk-collection/gold-serum.webp" alt="سيروم الذهب" class="w-16 h-16 object-contain bg-white rounded-lg p-1 border border-gray-100" onerror="this.src='https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&q=80&w=200'">
                        <div class="flex-grow">
                            <h4 class="font-bold text-nova-dark text-base">سيروم الذهب بالمسك والأرغان (150 مل)</h4>
                            <p class="text-xs text-gray-500">لمعان ملكي وثبات عطري متناهي</p>
                        </div>
                        <div class="text-left">
                            <span class="font-bold text-lg text-nova-gold">45.00 ₪</span>
                        </div>
                    </label>
                </div>

                <!-- Receipt & Order Panel -->
                <div class="lg:col-span-5 bg-nova-dark text-white rounded-3xl p-8 shadow-2xl border border-nova-gold/20 relative">
                    <div class="absolute top-0 left-0 w-24 h-24 bg-nova-gold/10 rounded-br-full pointer-events-none"></div>
                    <h3 class="text-2xl font-bold mb-6 pb-4 border-b border-gray-800 text-nova-lightGold flex items-center gap-2">
                        <i class="fas fa-receipt text-nova-gold"></i> ملخص طلبك
                    </h3>
                    
                    <div class="space-y-4 mb-8 text-sm" id="receipt-items">
                        <!-- Dynamic items will be added here via JS -->
                    </div>

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
                            <span class="text-3xl font-black text-nova-gold" id="total-price">170.00 ₪</span>
                        </div>
                        <div class="text-xs text-nova-lightGold mt-2 text-center font-bold" id="bundle-badge">
                            🎉 تهانينا! حصلتِ على العرض الكامل (شامبو هدية بسعر رمزي جداً)
                        </div>
                    </div>

                    <!-- Custom Warning Box for empty selections -->
                    <div id="selection-warning" class="hidden bg-red-900/30 border border-red-500/50 text-red-200 text-xs p-3 rounded-xl mb-4 text-center">
                        <i class="fas fa-exclamation-triangle mr-1"></i> يرجى اختيار منتج واحد على الأقل لإتمام عملية الطلب.
                    </div>

                    <div class="space-y-4">
                        <!-- الخيار الأول: الطلب المباشر من الموقع -->
                        <button id="direct-order-btn" onclick="openOrderModal()" class="w-full bg-gradient-to-r from-nova-gold to-nova-lightGold text-nova-dark py-4 rounded-xl font-bold text-lg transition-all flex items-center justify-center gap-3 shadow-[0_4px_15px_rgba(212,175,55,0.3)] hover:scale-[1.01] active:scale-[0.99]">
                            <i class="fas fa-shopping-bag"></i> الطلب المباشر من الموقع
                        </button>
                        
                        <!-- الخيار الثاني: الواتساب -->
                        <a id="whatsapp-order-btn" href="#" target="_blank" class="w-full bg-green-600 hover:bg-green-700 text-white py-4 rounded-xl font-bold text-lg transition-colors flex items-center justify-center gap-3 shadow-[0_4px_15px_rgba(34,197,94,0.3)]">
                            <i class="fab fa-whatsapp text-2xl"></i> اطلب عبر الواتساب
                        </a>
                        
                        <!-- الخيار الثالث: الاتصال الهاتفي -->
                        <a href="tel:972569030203" class="w-full bg-transparent border-2 border-nova-gold/60 hover:border-nova-gold text-nova-gold py-4 rounded-xl font-bold text-lg transition-colors flex items-center justify-center gap-3">
                            <i class="fas fa-phone-alt"></i> أو اتصل لطلبك فوراً
                        </a>
                    </div>
                    <p class="text-center text-xs text-gray-500 mt-6">توصيل آمن وسريع لكافة مناطق الضفة والقدس والداخل.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- Interactive Direct Order Modal -->
    <div id="order-modal" class="fixed inset-0 bg-black/85 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white text-gray-800 rounded-3xl max-w-md w-full p-8 border border-nova-gold/20 relative animate-fade-in shadow-2xl">
            <!-- Close Button -->
            <button onclick="closeOrderModal()" class="absolute top-4 left-4 text-gray-400 hover:text-nova-dark transition-colors p-2">
                <i class="fas fa-times text-xl"></i>
            </button>
            
            <!-- Modal Body (Form) -->
            <div id="modal-form-body">
                <h3 class="text-2xl font-bold text-nova-dark mb-2 text-center">إتمام الطلب المباشر</h3>
                <p class="text-gray-500 text-sm text-center mb-6">يرجى ملء البيانات التالية لتأكيد طلبك من نوڤا كوزمتكس</p>

                <form id="direct-order-form" onsubmit="submitDirectOrder(event)" class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">الاسم الكامل *</label>
                        <input type="text" required id="order-name" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-nova-gold focus:ring-1 focus:ring-nova-gold outline-none text-sm" placeholder="أدخلي اسمك الكامل">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">رقم الهاتف الجوال *</label>
                        <input type="tel" required id="order-phone" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-nova-gold focus:ring-1 focus:ring-nova-gold outline-none text-sm text-right" dir="ltr" placeholder="059XXXXXXX أو 056XXXXXXX">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">المدينة / العنوان بالتفصيل *</label>
                        <input type="text" required id="order-address" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-nova-gold focus:ring-1 focus:ring-nova-gold outline-none text-sm" placeholder="مثال: رام الله - حي الماصيون - عمارة الهدى">
                    </div>
                    
                    <div class="bg-nova-gray p-4 rounded-xl border border-gray-100 text-xs text-gray-600 mt-2">
                        <div class="font-bold text-nova-dark mb-1">ملخص الفاتورة:</div>
                        <div id="modal-summary-items" class="space-y-1 mb-2">
                            <!-- Selected items list -->
                        </div>
                        <div class="flex justify-between font-bold text-nova-dark text-sm border-t border-gray-200 pt-2">
                            <span>المجموع النهائي:</span>
                            <span id="modal-summary-total" class="text-nova-gold">0.00 ₪</span>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-nova-dark hover:bg-nova-dark/90 text-white py-4 rounded-xl font-bold text-lg transition-colors flex items-center justify-center gap-2 mt-4 shadow-lg">
                        <i class="fas fa-check-circle"></i> تأكيد وإرسال الطلب
                    </button>
                </form>
            </div>

            <!-- Success Screen -->
            <div id="order-success-screen" class="hidden text-center py-8">
                <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl animate-bounce">
                    <i class="fas fa-check"></i>
                </div>
                <h4 class="text-2xl font-bold text-nova-dark mb-3">تم استلام طلبك بنجاح!</h4>
                <p class="text-gray-600 text-sm mb-6 leading-relaxed">شکراً لثقتك بـ <strong class="text-nova-dark">نوڤا كوزمتكس</strong>. سيقوم طاقم خدمة العملاء بالتواصل معك هاتفياً خلال 24 ساعة لتأكيد عنوان الشحن والتسليم.</p>
                <button onclick="closeOrderModal()" class="bg-gradient-to-r from-nova-gold to-nova-lightGold text-nova-dark px-8 py-3 rounded-full font-bold text-sm hover:brightness-110 transition-all shadow-md">
                    حسناً، العودة للموقع
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-nova-dark text-white pt-16 pb-8 border-t-2 border-nova-gold/20">
        <div class="container mx-auto px-4 text-center">
            <div class="text-3xl font-bold tracking-widest font-serif mb-6">
                <span class="gradient-text">NOVA</span>
                <span class="text-sm font-sans block text-gray-400 -mt-1 tracking-[0.25em]">COSMETICS</span>
            </div>
            <p class="text-gray-400 max-w-lg mx-auto mb-8 text-sm">
                وجهتك المتكاملة لعالم الجمال الفاخر. نوفر أفضل المنتجات الموثوقة للعناية والتجميل لتعزيز ثقتك وجمالك الطبيعي بأعلى درجات الفخامة والفعالية.
            </p>
            <div class="flex flex-col sm:flex-row justify-center items-center gap-6 mb-12 text-sm text-gray-400">
                <span class="flex items-center gap-2"><i class="fas fa-map-marker-alt text-nova-gold"></i> فلسطين، جنين</span>
                <span class="flex items-center gap-2"><i class="fas fa-phone text-nova-gold"></i> رقم الهاتف: <span dir="ltr">+972569030203</span></span>
            </div>
            <div class="border-t border-gray-900 pt-8 text-gray-500 text-xs">
                <p>جميع الحقوق محفوظة ? 2026 نوڤا كوزمتكس (Nova Cosmetics)</p>
            </div>
        </div>
    </footer>

    <!-- Interactive script to handle real-time bundle pricing and discount strategy -->
    <script>
        function calculateTotal() {
            const hasShampoo = document.getElementById('check-shampoo').checked;
            const hasMask = document.getElementById('check-mask').checked;
            const hasSerum = document.getElementById('check-serum').checked;
            const hasGold = document.getElementById('check-gold').checked;

            const receiptContainer = document.getElementById('receipt-items');
            receiptContainer.innerHTML = '';

            let rawTotal = 0;
            let activeItems = [];

            // Add selected items to list and raw price calculation
            if (hasShampoo) {
                rawTotal += 55;
                activeItems.push({ name: 'شامبو مسك الطبيعي (1000 مل)', price: 55 });
                receiptContainer.innerHTML += `<div class="flex justify-between"><span>• شامبو مسك الطبيعي (1000 مل)</span><span>55.00 ₪</span></div>`;
            }
            if (hasMask) {
                rawTotal += 50;
                activeItems.push({ name: 'حمام زيت مسك وترميم (500 مل)', price: 50 });
                receiptContainer.innerHTML += `<div class="flex justify-between"><span>• حمام زيت مسك وترميم (500 مل)</span><span>50.00 ₪</span></div>`;
            }
            if (hasSerum) {
                rawTotal += 45;
                activeItems.push({ name: 'سيروم العناية بالمسك (150 مل)', price: 45 });
                receiptContainer.innerHTML += `<div class="flex justify-between"><span>• سيروم العناية بالمسك (150 مل)</span><span>45.00 ₪</span></div>`;
            }
            if (hasGold) {
                rawTotal += 45;
                activeItems.push({ name: 'سيروم الذهب بالمسك بالأرغان', price: 45 });
                receiptContainer.innerHTML += `<div class="flex justify-between"><span>• سيروم الذهب بالمسك بالأرغان</span><span>45.00 ₪</span></div>`;
            }

            const warningBox = document.getElementById('selection-warning');
            const directOrderBtn = document.getElementById('direct-order-btn');
            const whatsappOrderBtn = document.getElementById('whatsapp-order-btn');

            if (activeItems.length === 0) {
                receiptContainer.innerHTML = `<div class="text-center text-gray-500 py-4">الرجاء اختيار منتج واحد على الأقل للمتابعة.</div>`;
                document.getElementById('original-price').innerText = '0.00 ₪';
                document.getElementById('discount-amount').innerText = '0.00 ₪';
                document.getElementById('total-price').innerText = '0.00 ₪';
                document.getElementById('bundle-badge').innerText = 'الرجاء اختيار المنتجات';
                
                // Show warning box and disable buttons style-wise
                warningBox.classList.remove('hidden');
                directOrderBtn.classList.add('opacity-50', 'cursor-not-allowed');
                whatsappOrderBtn.classList.add('opacity-50', 'cursor-not-allowed');
                
                updateWhatsAppButton([], 0);
                return;
            }

            // Hide warning box and restore buttons
            warningBox.classList.add('hidden');
            directOrderBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            whatsappOrderBtn.classList.remove('opacity-50', 'cursor-not-allowed');

            let finalTotal = rawTotal;
            let discount = 0;
            let badgeText = '';

            // Smart Bundle Pricing Logic
            if (hasShampoo && hasMask && hasSerum && hasGold) {
                finalTotal = 210.00;
                discount = rawTotal - finalTotal;
                badgeText = '🎉 عرض ملكي متكامل! وفرتِ ' + discount + ' ₪ وحصلتِ على المجموعة الرباعية الكاملة!';
            } 
            else if (!hasShampoo && hasMask && hasSerum && hasGold) {
                finalTotal = 170.00;
                discount = rawTotal - finalTotal;
                badgeText = '✨ البكج الثلاثي الأيقوني! وفرتِ ' + discount + ' ₪ على منتجات العناية والترميم الحصرية!';
            }
            else if (activeItems.length >= 3) {
                finalTotal = rawTotal - 15;
                discount = 15;
                badgeText = '🎁 عرض رائع! خصم بقيمة 15 ₪ لشراء 3 منتجات مخصصة!';
            }
            else if (activeItems.length === 2) {
                finalTotal = rawTotal - 5;
                discount = 5;
                badgeText = '💫 خصم ثنائي بقيمة 5 ₪ مضاف لمشترياتك الآن!';
            } else {
                finalTotal = rawTotal;
                discount = 0;
                badgeText = 'استمتعي بالمنتج المختار الفاخر من نوڤا كوزمتكس ✨';
            }

            // Update UI elements
            document.getElementById('original-price').innerText = rawTotal.toFixed(2) + ' ₪';
            document.getElementById('discount-amount').innerText = '-' + discount.toFixed(2) + ' ₪';
            document.getElementById('total-price').innerText = finalTotal.toFixed(2) + ' ₪';
            document.getElementById('bundle-badge').innerText = badgeText;

            // Generate customized WhatsApp URL
            updateWhatsAppButton(activeItems, finalTotal);
        }

        function updateWhatsAppButton(items, finalPrice) {
            const phoneNumber = '+972569030203';
            if (items.length === 0) {
                document.getElementById('whatsapp-order-btn').href = "#";
                return;
            }

            let message = 'مرحباً "نوڤا كوزمتكس" 🌟، أرغب في طلب البكج المخصص الذي قمت بتركيبه عبر موقعكم للمجموعة الملكية:\n\n';
            items.forEach((item, index) => {
                message += `${index + 1}. ${item.name}\n`;
            });
            message += `\n💰 السعر الإجمالي النهائي: ${finalPrice.toFixed(2)} شيكل.\n📍 يرجى تزويدي بتفاصيل التوصيل وموعد الشحن. شكراً لكم!`;
            
            const encodedMessage = encodeURIComponent(message);
            document.getElementById('whatsapp-order-btn').href = `https://wa.me/${phoneNumber}?text=${encodedMessage}`;
        }

        // Modal Logic
        function openOrderModal() {
            // Guard against no active items
            const hasShampoo = document.getElementById('check-shampoo').checked;
            const hasMask = document.getElementById('check-mask').checked;
            const hasSerum = document.getElementById('check-serum').checked;
            const hasGold = document.getElementById('check-gold').checked;

            if (!hasShampoo && !hasMask && !hasSerum && !hasGold) {
                document.getElementById('selection-warning').classList.remove('hidden');
                document.getElementById('customizer').scrollIntoView({ behavior: 'smooth' });
                return;
            }

            // Generate summary inside Modal
            let summaryHtml = '';
            if (hasShampoo) summaryHtml += `<div class="flex justify-between"><span>• شامبو مسك (1000 مل)</span><span>55.00 ₪</span></div>`;
            if (hasMask) summaryHtml += `<div class="flex justify-between"><span>• حمام زيت مسك (500 مل)</span><span>50.00 ₪</span></div>`;
            if (hasSerum) summaryHtml += `<div class="flex justify-between"><span>• سيروم العناية (150 مل)</span><span>45.00 ₪</span></div>`;
            if (hasGold) summaryHtml += `<div class="flex justify-between"><span>• سيروم الذهب الفاخر (150 مل)</span><span>45.00 ₪</span></div>`;

            document.getElementById('modal-summary-items').innerHTML = summaryHtml;
            document.getElementById('modal-summary-total').innerText = document.getElementById('total-price').innerText;

            // Reset view state
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

        function submitDirectOrder(event) {
            event.preventDefault();
            
            // Collect Form Values (Could be sent to a back-end or Google sheet if integrated)
            const name = document.getElementById('order-name').value;
            const phone = document.getElementById('order-phone').value;
            const address = document.getElementById('order-address').value;

            // Show Success Screen
            document.getElementById('modal-form-body').classList.add('hidden');
            document.getElementById('order-success-screen').classList.remove('hidden');

            // Reset inputs
            document.getElementById('order-name').value = '';
            document.getElementById('order-phone').value = '';
            document.getElementById('order-address').value = '';
        }

        // Run calculation once on page load to initialize the dynamic items
        window.onload = function() {
            calculateTotal();
        }
    </script>

</body>
</html>