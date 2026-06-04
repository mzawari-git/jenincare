@extends($layoutPath)

@section('title', 'حلول الجملة والتجهيز — ' . ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))
@section('meta_description', 'حلول متكاملة لتجهيز الصالونات والعيادات. أسعار جملة تنافسية، شحن مجاني للطلبات الكبيرة، دعم فني مخصص، وخط ائتماني مرن للعملاء المعتمدين في فلسطين.')
@section('meta_keywords', 'تجهيز صالونات, معدات تجميل جملة, منتجات تجميل جملة فلسطين, JeninCare B2B, توريد صالونات, معدات عيادات تجميل')

@section('content')
{{-- ═══════════════════════════════════════════════════════════════
     HERO — Corporate / Institutional Tone
     ═══════════════════════════════════════════════════════════════ --}}
<section class="pt-32 pb-20 relative overflow-hidden border-b border-white/5">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_70%_20%,rgba(var(--brand-500-rgb,255,42,133),0.06),transparent_50%)] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="lg:w-7/12 text-right">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-white/10 glass-panel mb-6">
                    <span class="w-2 h-2 rounded-full bg-accent-500 animate-pulse"></span>
                    <span class="text-xs tracking-widest text-white/80 uppercase font-bold">القسم المؤسسي — B2B</span>
                </div>
                <h1 class="text-4xl md:text-6xl font-black mb-6 text-white leading-tight">
                    شريكك الموثوق<br>
                    <span class="text-accent-500">في تجهيز مشروعك التجميلي</span>
                </h1>
                <p class="text-white-dim text-lg leading-relaxed mb-8 max-w-lg">
                    نقدم حلولاً متكاملة للصالونات والعيادات ومراكز التجميل في فلسطين. من المنتجات الاحترافية إلى الأجهزة المتقدمة، نوفر لك كل ما تحتاجه لتأسيس أو تطوير عملك بأعلى معايير الجودة والاحترافية.
                </p>
                <div class="flex gap-4 flex-wrap">
                    <a href="{{ route('register') }}" class="px-8 py-4 rounded-full font-black text-sm inline-flex items-center gap-2 shadow-neon hover:shadow-neon-strong transition-all" style="background: var(--gradient-primary); color: white;">
                        <i class="fas fa-user-plus"></i> سجل كشريك مؤسسي
                    </a>
                    <a href="{{ route('contact') }}" class="px-8 py-4 rounded-full font-bold text-sm border border-white/15 text-white hover:bg-white/5 transition-all inline-flex items-center gap-2">
                        <i class="fas fa-headset"></i> طلب استشارة مجانية
                    </a>
                </div>
                <p class="text-white/40 text-xs mt-4">التسجيل خاضع للموافقة المؤسسية — نضمن جودة شبكة الشركاء</p>
            </div>
            <div class="lg:w-5/12 flex justify-center">
                <div class="w-52 h-52 md:w-64 md:h-64 rounded-full flex items-center justify-center shadow-neon text-6xl relative" style="background: var(--gradient-hero);">
                    <i class="fas fa-building text-white opacity-80"></i>
                    <div class="absolute -top-2 -right-2 w-16 h-16 rounded-full bg-accent-500 flex items-center justify-center text-white text-sm font-black shadow-lg">
                        B2B
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     STATS BAR — Social Proof for Businesses
     ═══════════════════════════════════════════════════════════════ --}}
<section class="py-10 border-b border-white/5 bg-surface/50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div class="glass-panel rounded-2xl p-5">
                <span class="text-2xl md:text-3xl font-black text-accent-500 block mb-1">+120</span>
                <span class="text-xs text-white-dim">صالون وعيادة شريكة</span>
            </div>
            <div class="glass-panel rounded-2xl p-5">
                <span class="text-2xl md:text-3xl font-black text-brand-500 block mb-1">40%</span>
                <span class="text-xs text-white-dim">خصم جملة أقصى</span>
            </div>
            <div class="glass-panel rounded-2xl p-5">
                <span class="text-2xl md:text-3xl font-black text-white block mb-1">30 يوم</span>
                <span class="text-xs text-white-dim">آجال دفع للعملاء المعتمدين</span>
            </div>
            <div class="glass-panel rounded-2xl p-5">
                <span class="text-2xl md:text-3xl font-black text-accent-500 block mb-1">24H</span>
                <span class="text-xs text-white-dim">استجابة طلبات التجهيز</span>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     FEATURES — Corporate Language
     ═══════════════════════════════════════════════════════════════ --}}
<section class="py-24">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-14">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-brand-500/20 bg-brand-500/5 mb-6">
                <span class="text-xs text-brand-500 font-bold tracking-widest uppercase">مزايا الشريك المؤسسي</span>
            </div>
            <h2 class="text-3xl md:text-5xl font-black text-white mb-4">لماذا يختار المحترفون <span class="text-accent-500">جنين للتجميل</span>؟</h2>
            <p class="text-white-dim max-w-2xl mx-auto">برنامج شراكة مؤسسي مصمم لدعم نمو مشاريع التجميل في فلسطين بأعلى معايير الاحترافية والشفافية.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
            @php
            $b2bFeatures = [
                ['icon' => 'fas fa-percentage', 'color' => 'brand', 'title' => 'أسعار جملة تنافسية', 'desc' => 'نظام تسعير تدريجي يصل إلى 40% خصم على الطلبات الكبيرة، مع عروض حصرية شهرية للشركاء المعتمدين.'],
                ['icon' => 'fas fa-truck-fast', 'color' => 'accent', 'title' => 'شحن مجاني مؤسسي', 'desc' => 'توصيل مجاني لجميع محافظات فلسطين على الطلبات التي تتجاوز 500 ₪، مع إمكانية التوصيل المجدول للكميات الكبيرة.'],
                ['icon' => 'fas fa-credit-card', 'color' => 'accent', 'title' => 'حلول دفع مرنة', 'desc' => 'خط ائتماني يتيح الدفع خلال 30 يوماً للعملاء المعتمدين، بالإضافة إلى الدفع الآجل والتقسيط على المشاريع الكبيرة.'],
                ['icon' => 'fas fa-headset', 'color' => 'brand', 'title' => 'مدير حساب مخصص', 'desc' => 'مسؤول مبيعات مؤسسي يرافقك من مرحلة الاستشارة حتى التجهيز النهائي، مع دعم فني متواصل على مدار أيام العمل.'],
                ['icon' => 'fas fa-file-invoice-dollar', 'color' => 'accent', 'title' => 'فواتير ضريبية معتمدة', 'desc' => 'فواتير رسمية معتمدة لجميع الطلبات، مع إمكانية إصدار تقارير شهرية ورب kvartal لتسهيل إجراءات المحاسبة والضرائب.'],
                ['icon' => 'fas fa-clipboard-list', 'color' => 'brand', 'title' => 'نظام RFQ احترافي', 'desc' => 'نظام طلب عروض أسعار متكامل (Request for Quote) للمنتجات والكميات المخصصة، مع الرد خلال 24 ساعة عمل.'],
            ];
            @endphp
            @foreach($b2bFeatures as $feat)
            <div class="glass-panel rounded-2xl p-7 text-center border-white/5 hover:border-brand-500/20 transition-all duration-300 group">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center mx-auto mb-5 text-xl {{ $feat['color'] == 'brand' ? 'bg-brand-500/10 text-brand-500' : 'bg-accent-500/10 text-accent-500' }}">
                    <i class="{{ $feat['icon'] }}"></i>
                </div>
                <h3 class="text-base font-bold text-white mb-3">{{ $feat['title'] }}</h3>
                <p class="text-white-dim text-sm leading-relaxed">{{ $feat['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     WHO CAN JOIN — Target Segments
     ═══════════════════════════════════════════════════════════════ --}}
<section class="py-24 border-t border-white/5 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_30%_50%,rgba(var(--brand-500-rgb,255,42,133),0.04),transparent_60%)] pointer-events-none"></div>
    <div class="max-w-5xl mx-auto px-4 relative z-10">
        <div class="text-center mb-14">
            <h2 class="text-3xl md:text-4xl font-black text-white mb-4">من يمكنه <span class="text-brand-500">الانضمام</span> لبرنامج الشراكة؟</h2>
            <p class="text-white-dim max-w-xl mx-auto">نرحب بالشراكات المؤسسية مع جميع الجهات العاملة في قطاع التجميل والعناية الصحية في فلسطين.</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            @php
            $whoCan = [
                ['emoji' => '💇', 'title' => 'صالونات التجميل', 'desc' => 'منتجات العناية الاحترافية بأسعار خاصة للكميات'],
                ['emoji' => '🏥', 'title' => 'عيادات الجلدية', 'desc' => 'أجهزة متقدمة ومستحضرات طبية معتمدة للعيادات'],
                ['emoji' => '🧖', 'title' => 'مراكز السبا', 'desc' => 'حلول متكاملة للعناية في المنتجعات والمراكز الصحية'],
                ['emoji' => '🏪', 'title' => 'تجار الجملة', 'desc' => 'هامش ربح ممتاز وشروط توريد ميسرة للموزعين'],
            ];
            @endphp
            @foreach($whoCan as $w)
            <div class="glass-panel rounded-2xl p-6 text-center border-white/5 hover:-translate-y-1 transition-all duration-300">
                <div class="text-4xl mb-4">{{ $w['emoji'] }}</div>
                <h4 class="text-sm font-bold text-white mb-2">{{ $w['title'] }}</h4>
                <p class="text-white-dim text-xs leading-relaxed">{{ $w['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     PROCESS — How to Become a Partner
     ═══════════════════════════════════════════════════════════════ --}}
<section class="py-24 border-t border-white/5">
    <div class="max-w-5xl mx-auto px-4">
        <div class="text-center mb-14">
            <h2 class="text-3xl md:text-4xl font-black text-white mb-4">خطوات <span class="text-accent-500">الشراكة</span></h2>
            <p class="text-white-dim max-w-xl mx-auto">إجراءات بسيطة وشفافة للانضمام لبرنامج الشركاء المؤسسيين.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @php
            $steps = [
                ['num' => '01', 'title' => 'تقديم الطلب', 'desc' => 'سجل بيانات مشروعك التجاري عبر النموذج المؤسسي، وارفق المستندات المطلوبة (رخصة تجارية أو سجل شركة).'],
                ['num' => '02', 'title' => 'المراجعة والموافقة', 'desc' => 'يقوم فريقنا المختص بمراجعة الطلب خلال 48 ساعة عمل، والتواصل معك لاستكمال الإجراءات وتحديد فئة الشراكة.'],
                ['num' => '03', 'title' => 'البدء بالتعاون', 'desc' => 'بعد الموافقة، تحصل على حساب شراكة مؤسسي، مدير حساب مخصص، وإمكانية الوصول لأسعار الجملة والطلبات المخصصة.'],
            ];
            @endphp
            @foreach($steps as $step)
            <div class="relative text-center">
                <div class="w-16 h-16 rounded-full bg-brand-500/10 border border-brand-500/20 flex items-center justify-center mx-auto mb-6">
                    <span class="text-xl font-black text-brand-500">{{ $step['num'] }}</span>
                </div>
                <h3 class="text-lg font-bold text-white mb-3">{{ $step['title'] }}</h3>
                <p class="text-white-dim text-sm leading-relaxed">{{ $step['desc'] }}</p>
                @if(!$loop->last)
                <div class="hidden md:block absolute top-8 left-0 w-full h-px bg-white/5" style="transform: translateX(60%);"></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════
     CTA — Final Corporate Call-to-Action
     ═══════════════════════════════════════════════════════════════ --}}
<section class="py-24 relative overflow-hidden">
    <div class="absolute inset-0" style="background: var(--gradient-primary); opacity: 0.05;"></div>
    <div class="max-w-3xl mx-auto px-4 text-center relative z-10">
        <h2 class="text-3xl md:text-5xl font-black text-white mb-6">جاهز لتطوير مشروعك؟</h2>
        <p class="text-white-dim text-lg mb-10 leading-relaxed">انضم إلى شبكة شركائنا المؤسسيين واستفد من حلول تجهيز متكاملة، أسعار تنافسية، ودعم احترافي يضمن نمو عملك بثقة.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-10 py-4 bg-white text-surface rounded-full font-black text-sm hover:shadow-neon transition-all">
                <i class="fas fa-rocket"></i> تقدم بطلب الشراكة الآن
            </a>
            <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 px-10 py-4 border border-white/15 text-white rounded-full font-bold text-sm hover:bg-white/5 transition-all">
                <i class="fas fa-envelope"></i> طلب عرض أسعار
            </a>
        </div>
        <p class="text-white/30 text-xs mt-6">المتابعة اختيارية — نحترم خصوصيتك ووقتك</p>
    </div>
</section>
@endsection
