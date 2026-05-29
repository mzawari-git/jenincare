{{-- ═══ Newsletter Section ═══ --}}
<section class="py-24 relative overflow-hidden border-t border-white/5">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(var(--brand-500-rgb,255,42,133),0.05),transparent_70%)] pointer-events-none"></div>
    <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
        <div class="w-16 h-16 mx-auto mb-6 rounded-full flex items-center justify-center shadow-neon" style="background: var(--gradient-primary);">
            <i class="fa-solid fa-envelope-open text-2xl text-white"></i>
        </div>
        <h2 class="text-3xl md:text-4xl font-black mb-4">انضمي إلى نادي <span class="text-brand-500">{{ $siteSettings['site_name'] ?? 'JeniCare' }}</span></h2>
        <p class="text-ink-muted mb-8 font-light max-w-lg mx-auto">احصلي على خصم 10% على طلبك الأول، وكوني أول من يعرف عن العروض الحصرية والمنتجات الجديدة.</p>

        <form class="max-w-md mx-auto relative flex items-center" id="newsletterFormV3">
            @csrf
            <input type="email" name="email" placeholder="عنوان بريدك الإلكتروني" required class="w-full bg-white/5 border border-white/10 text-white py-4 px-6 pl-28 rounded-full focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all text-sm placeholder:text-white/30">
            <button type="submit" class="absolute left-2 px-6 py-2 rounded-full font-bold text-sm transition-all shadow-neon hover:shadow-neon-strong" style="background: var(--gradient-primary); color: #fff;">
                اشتراك
            </button>
        </form>
        <p id="newsletterMsgV3" class="text-sm mt-3"></p>
    </div>
</section>

{{-- ═══ Trust Badges Section ═══ --}}
<section class="py-16 border-t border-white/5">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
            <div class="flex flex-col items-center text-center p-6 glass-panel rounded-2xl">
                <div class="w-14 h-14 bg-brand-500/10 rounded-full flex items-center justify-center mb-3 shadow-neon">
                    <i class="ph-fill ph-seal-check text-2xl text-brand-500"></i>
                </div>
                <h3 class="font-bold text-white text-sm mb-1">منتجات أصلية 100%</h3>
                <p class="text-ink-dim text-xs">جميع منتجاتنا أصلية ومستوردة</p>
            </div>
            <div class="flex flex-col items-center text-center p-6 glass-panel rounded-2xl">
                <div class="w-14 h-14 bg-accent-500/10 rounded-full flex items-center justify-center mb-3 shadow-accent-neon">
                    <i class="ph-fill ph-truck text-2xl text-accent-500"></i>
                </div>
                <h3 class="font-bold text-white text-sm mb-1">شحن لكل فلسطين</h3>
                <p class="text-ink-dim text-xs">توصيل لجميع المناطق</p>
            </div>
            <div class="flex flex-col items-center text-center p-6 glass-panel rounded-2xl">
                <div class="w-14 h-14 bg-brand-500/10 rounded-full flex items-center justify-center mb-3 shadow-neon">
                    <i class="ph-fill ph-shield-check text-2xl text-brand-500"></i>
                </div>
                <h3 class="font-bold text-white text-sm mb-1">دفع آمن</h3>
                <p class="text-ink-dim text-xs">الدفع عند الاستلام متاح</p>
            </div>
            <div class="flex flex-col items-center text-center p-6 glass-panel rounded-2xl">
                <div class="w-14 h-14 bg-accent-500/10 rounded-full flex items-center justify-center mb-3 shadow-accent-neon">
                    <i class="ph-fill ph-headset text-2xl text-accent-500"></i>
                </div>
                <h3 class="font-bold text-white text-sm mb-1">دعم فني</h3>
                <p class="text-ink-dim text-xs">متاح يومياً من 9 ص - 10 م</p>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="flex justify-center items-center gap-6 mt-12 pt-8 border-t border-white/5">
            <div class="flex items-center gap-3">
                <div class="flex -space-x-2 space-x-reverse">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold border-2 border-surface" style="background: var(--gradient-primary);">ج</div>
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold border-2 border-surface" style="background: var(--gradient-hero);">م</div>
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold border-2 border-surface" style="background: var(--gradient-primary);">+</div>
                </div>
                <div class="text-sm">
                    <span class="font-bold text-white">+{{ \App\Models\Product::count() }}</span>
                    <span class="text-ink-dim">منتج متنوع</span>
                </div>
            </div>
            <div class="hidden md:block w-px h-8 bg-white/10"></div>
            <div class="hidden md:flex items-center gap-2 text-sm">
                <i class="fa-solid fa-star text-yellow-400"></i>
                <span class="font-bold text-white">4.8</span>
                <span class="text-ink-dim">تقييم العملاء</span>
            </div>
        </div>
    </div>
</section>

{{-- ═══ Main Footer (Terminal Aesthetic) ═══ --}}
<footer class="pt-28 pb-16 relative overflow-hidden border-t border-white/5">
    {{-- Geometric Grid Background --}}
    <div class="absolute inset-0 bg-grid [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] z-0 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-12 mb-16">

            {{-- Brand + Terminal Block --}}
            <div class="lg:col-span-5 text-right">
                <div class="flex items-center gap-3 mb-6 justify-end">
                    @if(!empty($siteSettings['site_logo_url']))
                        <img src="{{ $siteSettings['site_logo_url'] }}" alt="{{ $siteSettings['site_name'] ?? 'JeniCare' }}" class="h-10 w-auto object-contain brightness-0 invert">
                    @else
                        <span class="text-3xl font-extrabold tracking-tight text-white">{{ $siteSettings['site_name'] ?? 'JeniCare' }}<span class="text-brand-500">.</span></span>
                    @endif
                </div>
                <p class="text-ink-dim text-sm leading-relaxed mb-8 font-light max-w-md ml-auto">
                    {{ $siteSettings['site_description'] ?? 'وجهتك الفاخرة لمنتجات العناية بالبشرة والشعر والمستلزمات الطبية التجميلية في فلسطين. جودة أصلية، ثقة، وجمال.' }}
                </p>

                {{-- Terminal Status Block --}}
                <div class="terminal-block w-max ml-auto text-left mb-8" dir="ltr">
                    <p>> SYSTEM.INIT()</p>
                    <p>> LOADING_PRODUCTS... <span class="text-green-400">OK</span></p>
                    <p>> ACTIVE_THEME_{{ strtoupper($siteSettings['site_theme'] ?? 'rose') }}... <span class="text-green-400">OK</span></p>
                    <p>> SECURE_CONNECTION... <span class="text-green-400">OK</span></p>
                    <p class="text-brand-500 animate-pulse">_</p>
                </div>

                {{-- Social Icons --}}
                <div class="flex gap-4 justify-end">
                    @if(!empty($siteSettings['instagram_url']))
                    <a href="{{ $siteSettings['instagram_url'] }}" target="_blank" class="w-10 h-10 rounded-full border border-white/15 flex items-center justify-center text-white/50 hover:bg-brand-500 hover:border-brand-500 hover:text-white transition-all" title="إنستغرام" aria-label="إنستغرام"><i class="ph-fill ph-instagram-logo text-xl"></i></a>
                    @endif
                    @if(!empty($siteSettings['facebook_url']))
                    <a href="{{ $siteSettings['facebook_url'] }}" target="_blank" class="w-10 h-10 rounded-full border border-white/15 flex items-center justify-center text-white/50 hover:bg-[#1877F2] hover:border-[#1877F2] hover:text-white transition-all" title="فيسبوك" aria-label="فيسبوك"><i class="ph-fill ph-facebook-logo text-xl"></i></a>
                    @endif
                    @if(!empty($siteSettings['whatsapp_number']))
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings['whatsapp_number']) }}" target="_blank" class="w-10 h-10 rounded-full border border-white/15 flex items-center justify-center text-white/50 hover:bg-[#25D366] hover:border-[#25D366] hover:text-white transition-all" title="واتساب" aria-label="واتساب"><i class="ph-fill ph-whatsapp-logo text-xl"></i></a>
                    @endif
                    @if(!empty($siteSettings['tiktok_url']))
                    <a href="{{ $siteSettings['tiktok_url'] }}" target="_blank" class="w-10 h-10 rounded-full border border-white/15 flex items-center justify-center text-white/50 hover:bg-white hover:border-white hover:text-black transition-all" title="تيك توك" aria-label="تيك توك"><i class="ph-fill ph-tiktok-logo text-xl"></i></a>
                    @endif
                    @if(!empty($siteSettings['twitter_url']))
                    <a href="{{ $siteSettings['twitter_url'] }}" target="_blank" class="w-10 h-10 rounded-full border border-white/15 flex items-center justify-center text-white/50 hover:bg-[#1DA1F2] hover:border-[#1DA1F2] hover:text-white transition-all" title="تويتر" aria-label="تويتر"><i class="ph-fill ph-twitter-logo text-xl"></i></a>
                    @endif
                    @if(!empty($siteSettings['linkedin_url']))
                    <a href="{{ $siteSettings['linkedin_url'] }}" target="_blank" class="w-10 h-10 rounded-full border border-white/15 flex items-center justify-center text-white/50 hover:bg-[#0A66C2] hover:border-[#0A66C2] hover:text-white transition-all" title="لينكد إن" aria-label="لينكد إن"><i class="ph-fill ph-linkedin-logo text-xl"></i></a>
                    @endif
                    @if(!empty($siteSettings['youtube_url']))
                    <a href="{{ $siteSettings['youtube_url'] }}" target="_blank" class="w-10 h-10 rounded-full border border-white/15 flex items-center justify-center text-white/50 hover:bg-[#FF0000] hover:border-[#FF0000] hover:text-white transition-all" title="يوتيوب" aria-label="يوتيوب"><i class="ph-fill ph-youtube-logo text-xl"></i></a>
                    @endif
                </div>
            </div>

            {{-- Right Side Links --}}
            <div class="lg:col-span-7">
                <div class="grid grid-cols-2 gap-8 text-right">
                    {{-- Shop Column --}}
                    <div>
                        <h5 class="font-bold text-white mb-6 uppercase tracking-widest text-[10px]">المتجر</h5>
                        <ul class="space-y-4 text-ink-dim text-sm font-light">
                            <li><a href="{{ route('shop') }}" class="hover:text-brand-500 transition-colors">جميع المنتجات</a></li>
                            @php
                                $footerCats = $headerCategories ?? \App\Models\Category::active()->withCount('products')->having('products_count', '>', 0)->orderBy('sort_order')->get();
                            @endphp
                            @foreach($footerCats->take(4) as $fcat)
                                @php
                                    $arLbl = $fcat->ar_label ?? preg_replace('/\s{2,}/', ' ', trim(preg_replace('/[a-zA-Z&\-\(\)]+/', '', $fcat->name_ar)));
                                    $arLbl = !empty($arLbl) ? $arLbl : $fcat->name_ar;
                                @endphp
                                <li><a href="{{ route('shop', ['category' => $fcat->slug]) }}" class="hover:text-brand-500 transition-colors">{{ $arLbl }}</a></li>
                            @endforeach
                            <li><a href="{{ route('b2b') }}" class="hover:text-accent-500 transition-colors">للأعمال (طلب جملة)</a></li>
                        </ul>
                    </div>

                    {{-- Help Column --}}
                    <div>
                        <h5 class="font-bold text-white mb-6 uppercase tracking-widest text-[10px]">الدعم والحماية</h5>
                        <ul class="space-y-4 text-ink-dim text-sm font-light">
                            <li><a href="{{ route('shipping-policy') }}" class="hover:text-brand-500 transition-colors">الشحن والتوصيل</a></li>
                            <li><a href="{{ route('return-policy') }}" class="hover:text-brand-500 transition-colors">سياسة الإرجاع</a></li>
                            <li><a href="{{ route('faq') }}" class="hover:text-brand-500 transition-colors">الأسئلة الشائعة</a></li>
                            <li><a href="{{ route('terms') }}" class="hover:text-brand-500 transition-colors">الشروط والأحكام</a></li>
                            <li><a href="{{ route('privacy') }}" class="hover:text-brand-500 transition-colors">حماية البيانات</a></li>
                            <li><a href="{{ route('contact') }}" class="hover:text-brand-500 transition-colors">تواصل معنا</a></li>
                        </ul>
                    </div>
                </div>

                {{-- Contact Info --}}
                <div class="mt-10 pt-8 border-t border-white/5 flex flex-wrap gap-6 text-sm text-ink-dim font-light justify-end">
                    <span class="flex items-center gap-2"><i class="ph-fill ph-map-pin text-brand-500"></i> {{ $siteSettings['site_address'] ?? 'فلسطين، رام الله' }}</span>
                    <span class="flex items-center gap-2" dir="ltr"><i class="ph-fill ph-phone text-brand-500"></i> {{ $siteSettings['site_phone'] ?? '+972 56 903 0203' }}</span>
                    <span class="flex items-center gap-2"><i class="ph-fill ph-envelope text-brand-500"></i> {{ $siteSettings['site_email'] ?? 'hello@jenicare.com' }}</span>
                </div>
            </div>
        </div>

        {{-- Copyright Bar --}}
        <div class="pt-8 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-ink-dim text-sm font-light">
                &copy; {{ date('Y') }} {{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}. جميع الحقوق محفوظة.
            </p>
            <div class="flex gap-2 text-xs">
                <span class="bg-white/5 border border-white/10 px-3 py-1.5 rounded-full text-ink-dim">الدفع عند الاستلام</span>
                @if(($siteSettings['payment_bank_enabled'] ?? '0') == '1')
                <span class="bg-white/5 border border-white/10 px-3 py-1.5 rounded-full text-ink-dim">تحويل بنكي</span>
                @endif
                @if(($siteSettings['payment_jawwal_enabled'] ?? '0') == '1')
                <span class="bg-brand-500/10 border border-brand-500/20 px-3 py-1.5 rounded-full text-brand-500">جوال باي</span>
                @endif
                @if(($siteSettings['payment_reflect_enabled'] ?? '0') == '1')
                <span class="bg-accent-500/10 border border-accent-500/20 px-3 py-1.5 rounded-full text-accent-500">Reflect</span>
                @endif
            </div>
        </div>
    </div>
</footer>

<script>
document.getElementById('newsletterFormV3')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const msgEl = document.getElementById('newsletterMsgV3');
    const email = this.querySelector('input[name="email"]').value.trim();
    if (!email) return;
    btn.disabled = true;
    btn.innerHTML = 'جاري...';
    const basePath = window.basePath || '';
    try {
        const r = await fetch(basePath + '/newsletter/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.querySelector('input[name="_token"]').value,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ email })
        });
        const d = await r.json();
        if (d.success) {
            msgEl.innerHTML = '<span class="text-green-400 font-medium">' + d.message + '</span>';
            this.querySelector('input[name="email"]').value = '';
            if (window.showNotification) showNotification('success', d.message);
        } else {
            msgEl.innerHTML = '<span class="text-red-400">' + (d.message || 'حدث خطأ') + '</span>';
        }
    } catch(e) {
        msgEl.innerHTML = '<span class="text-red-400">حدث خطأ في الاتصال</span>';
    }
    btn.disabled = false;
    btn.innerHTML = 'اشتراك';
    setTimeout(() => { msgEl.innerHTML = ''; }, 5000);
});
</script>
