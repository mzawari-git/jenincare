@extends($layoutPath)

@section('title', 'للأعمال - ' . ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))
@section('meta_description', 'شركة جنين للتجميل للأعمال - حلول متكاملة للصوالين والعيادات والمشتركين التجاريين. أسعار جملة، شحن مجاني، ودعم فني.')

@section('content')
{{-- Hero --}}
<section class="pt-32 pb-16 relative overflow-hidden border-b border-white/5">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_70%_20%,rgba(var(--brand-500-rgb,255,42,133),0.06),transparent_50%)] pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="flex flex-col lg:flex-row items-center gap-10">
            <div class="lg:w-7/12 text-right">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-white/10 glass-panel mb-6">
                    <span class="w-2 h-2 rounded-full bg-accent-500 animate-pulse"></span>
                    <span class="text-xs tracking-widest text-white/80 uppercase font-bold">بروتوكول الأعمال</span>
                </div>
                <h1 class="text-4xl md:text-6xl font-black mb-4 text-white">
                    {{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }} <span class="text-accent-500">للأعمال</span>
                </h1>
                <p class="text-white-dim text-lg leading-relaxed mb-8 max-w-lg">
                    حلول متكاملة للصوالين والعيادات والمشتركين التجاريين. أسعار جملة تنافسية، شحن مجاني للطلبات الكبيرة، ودعم فني مخصص.
                </p>
                <div class="flex gap-4 flex-wrap">
                    <a href="{{ route('register') }}" class="px-8 py-4 rounded-full font-black text-sm inline-flex items-center gap-2 shadow-neon hover:shadow-neon-strong transition-all" style="background: var(--gradient-primary); color: white;">
                        <i class="fas fa-user-plus"></i> سجل كشريك تجاري
                    </a>
                    <a href="{{ route('contact') }}" class="px-8 py-4 rounded-full font-bold text-sm border border-white/15 text-white hover:bg-white/5 transition-all inline-flex items-center gap-2">
                        <i class="fas fa-headset"></i> تواصل معنا
                    </a>
                </div>
            </div>
            <div class="lg:w-5/12 flex justify-center">
                <div class="w-48 h-48 md:w-56 md:h-56 rounded-full flex items-center justify-center shadow-neon text-6xl" style="background: var(--gradient-hero);">
                    <i class="fas fa-building text-white opacity-80"></i>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Features --}}
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-4xl font-black text-white mb-3">مميزات برنامج <span class="text-accent-500">B2B</span></h2>
            <p class="text-white-dim">كل ما تحتاجه لتطوير عملك في مجال العناية بالشعر والبشرة</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
            @php
            $b2bFeatures = [
                ['icon' => 'fas fa-percentage', 'color' => 'brand', 'title' => 'أسعار الجملة', 'desc' => 'خصومات تصل إلى 40% على الطلبات الكبيرة مع نظام تسعير تدريجي'],
                ['icon' => 'fas fa-truck-fast', 'color' => 'accent', 'title' => 'شحن مجاني', 'desc' => 'شحن مجاني للطلبات التي تتجاوز 500 ₪ مع توصيل لجميع محافظات فلسطين'],
                ['icon' => 'fas fa-credit-card', 'color' => 'accent', 'title' => 'الدفع بالآجل', 'desc' => 'نظام ائتماني مرن مع إمكانية الدفع خلال 30 يوماً للعملاء المعتمدين'],
                ['icon' => 'fas fa-headset', 'color' => 'brand', 'title' => 'دعم مخصص', 'desc' => 'مدير حساب مخصص يتابع طلباتك ويقدم لك الاستشارات والدعم'],
                ['icon' => 'fas fa-file-invoice', 'color' => 'accent', 'title' => 'فواتير ضريبية', 'desc' => 'فواتير رسمية معتمدة لجميع الطلبات لتسهيل المحاسبة والضرائب'],
                ['icon' => 'fas fa-boxes', 'color' => 'brand', 'title' => 'طلبات مخصصة', 'desc' => 'نظام RFQ لتقديم طلبات عروض أسعار للمنتجات والكميات المخصصة'],
            ];
            @endphp
            @foreach($b2bFeatures as $feat)
            <div class="glass-panel rounded-2xl p-6 text-center border-white/5 hover:border-brand-500/20 transition-all duration-300 group">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center mx-auto mb-4 text-xl {{ $feat['color'] == 'brand' ? 'bg-brand-500/10 text-brand-500' : 'bg-accent-500/10 text-accent-500' }}">
                    <i class="{{ $feat['icon'] }}"></i>
                </div>
                <h3 class="text-base font-bold text-white mb-2">{{ $feat['title'] }}</h3>
                <p class="text-white-dim text-sm leading-relaxed">{{ $feat['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-20 relative overflow-hidden">
    <div class="absolute inset-0" style="background: var(--gradient-primary); opacity: 0.05;"></div>
    <div class="max-w-3xl mx-auto px-4 text-center relative z-10">
        <h2 class="text-2xl md:text-4xl font-black text-white mb-4">جاهز لتطوير أعمالك؟</h2>
        <p class="text-white-dim text-lg mb-8">انضم إلى شركائنا واستمتع بأسعار خاصة ومزايا حصرية</p>
        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-10 py-4 bg-white text-surface rounded-full font-black text-sm hover:shadow-neon transition-all">
            <i class="fas fa-rocket"></i> ابدأ الآن
        </a>
    </div>
</section>

{{-- Who Can Join --}}
<section class="py-20 border-t border-white/5">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-4xl font-black text-white mb-3">من يمكنه <span class="text-brand-500">الانضمام</span>؟</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @php
            $whoCan = [
                ['emoji' => '💇', 'title' => 'صوالين التجميل', 'desc' => 'احصل على منتجات العناية المهنية بأسعار خاصة'],
                ['emoji' => '🏥', 'title' => 'العيادات الجلدية', 'desc' => 'منتجات طبية وتجميلية معتمدة لعيادات البشرة'],
                ['emoji' => '🧖', 'title' => 'منتجعات السبأ', 'desc' => 'حلول متكاملة للعناية في المنتجعات الصحية'],
                ['emoji' => '🏪', 'title' => 'تجار التجزئة', 'desc' => 'أسعار جملة وهامش ربح ممتاز للموزعين'],
            ];
            @endphp
            @foreach($whoCan as $w)
            <div class="glass-panel rounded-2xl p-6 text-center border-white/5">
                <div class="text-4xl mb-4">{{ $w['emoji'] }}</div>
                <h4 class="text-sm font-bold text-white mb-1">{{ $w['title'] }}</h4>
                <p class="text-white-dim text-xs leading-relaxed">{{ $w['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
