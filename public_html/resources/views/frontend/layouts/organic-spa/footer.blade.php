{{-- ═══════════════════════════════════════════════════════════════
     PROFESSIONAL FOOTER — Organic Spa Theme
     ═══════════════════════════════════════════════════════════════ --}}

{{-- Newsletter --}}
<section class="py-16 relative overflow-hidden" style="border-top:2px solid var(--glass-border);">
    <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
        <h2 class="text-2xl md:text-3xl font-black mb-3" style="color:var(--brand-500);">انضمي إلى حديقة {{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}</h2>
        <p class="text-ink-dim mb-8 max-w-lg mx-auto text-sm">احصلي على خصم 10% على طلبك الأول، وكوني أول من يعرف عن العروض الحصرية والمنتجات الجديدة.</p>
        <form class="max-w-md mx-auto relative flex items-center" id="newsletterFormV3">
            @csrf
            <input type="email" name="email" placeholder="بريدك الإلكتروني" required class="w-full bg-white/5 border border-white/10 text-white py-3.5 px-6 pl-28 rounded-full focus:outline-none focus:border-brand-500 transition-all text-sm placeholder:text-white/30">
            <button type="submit" class="absolute left-2 px-6 py-2 rounded-full font-bold text-sm text-white" style="background:var(--gradient-primary);">اشتراك</button>
        </form>
        <p id="newsletterMsgV3" class="text-sm mt-3"></p>
    </div>
</section>

{{-- Trust Badges --}}
<section class="py-12" style="border-top:2px solid var(--glass-border);">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="flex flex-col items-center text-center p-5 rounded-2xl border" style="border-color:var(--glass-border);background:var(--glass-bg);">
                <div class="w-12 h-12 rounded-full flex items-center justify-center mb-3" style="background:var(--brand-500);"><i class="fa-solid fa-shield-check text-xl text-white"></i></div>
                <h3 class="font-bold text-white text-sm mb-1">أصلي 100%</h3>
                <p class="text-ink-dim text-xs">منتجات أصلية ومستوردة</p>
            </div>
            <div class="flex flex-col items-center text-center p-5 rounded-2xl border" style="border-color:var(--glass-border);background:var(--glass-bg);">
                <div class="w-12 h-12 rounded-full flex items-center justify-center mb-3" style="background:var(--brand-600);"><i class="fa-solid fa-truck-fast text-xl text-white"></i></div>
                <h3 class="font-bold text-white text-sm mb-1">شحن لكل فلسطين</h3>
                <p class="text-ink-dim text-xs">توصيل لجميع المناطق</p>
            </div>
            <div class="flex flex-col items-center text-center p-5 rounded-2xl border" style="border-color:var(--glass-border);background:var(--glass-bg);">
                <div class="w-12 h-12 rounded-full flex items-center justify-center mb-3" style="background:var(--brand-500);"><i class="fa-solid fa-lock text-xl text-white"></i></div>
                <h3 class="font-bold text-white text-sm mb-1">دفع آمن</h3>
                <p class="text-ink-dim text-xs">الدفع عند الاستلام</p>
            </div>
            <div class="flex flex-col items-center text-center p-5 rounded-2xl border" style="border-color:var(--glass-border);background:var(--glass-bg);">
                <div class="w-12 h-12 rounded-full flex items-center justify-center mb-3" style="background:var(--brand-600);"><i class="fa-solid fa-headset text-xl text-white"></i></div>
                <h3 class="font-bold text-white text-sm mb-1">دعم فني</h3>
                <p class="text-ink-dim text-xs">9 ص - 10 م يومياً</p>
            </div>
        </div>
    </div>
</section>

{{-- Main Footer --}}
<footer class="pt-16 pb-8 relative overflow-hidden" style="border-top:2px solid var(--glass-border);">
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 mb-12">

            {{-- Brand --}}
            <div class="lg:col-span-4 text-right">
                <div class="flex items-center gap-3 mb-5 justify-end">
                    @if(!empty($siteSettings['site_logo_url']))
                        <img src="{{ $siteSettings['site_logo_url'] }}" alt="{{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}" class="h-10 w-auto object-contain" style="max-height:40px;">
                    @else
                        <span class="text-2xl font-extrabold text-white">{{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}<span style="color:var(--brand-500);">.</span></span>
                    @endif
                </div>
                <p class="text-ink-dim text-sm leading-relaxed mb-6 max-w-sm ml-auto">
                    {{ $siteSettings['site_description'] ?? 'وجهتك الفاخرة لمنتجات العناية الطبيعية.' }}
                </p>
                <div class="flex gap-3 justify-end">
                    @if(!empty($siteSettings['facebook_url']))<a href="{{ $siteSettings['facebook_url'] }}" target="_blank" class="w-9 h-9 rounded-full border flex items-center justify-center text-white/40 hover:text-white transition-all" style="border-color:var(--glass-border);" aria-label="فيسبوك"><i class="ph-fill ph-facebook-logo text-lg"></i></a>@endif
                    @if(!empty($siteSettings['instagram_url']))<a href="{{ $siteSettings['instagram_url'] }}" target="_blank" class="w-9 h-9 rounded-full border flex items-center justify-center text-white/40 hover:text-white transition-all" style="border-color:var(--glass-border);" aria-label="إنستغرام"><i class="ph-fill ph-instagram-logo text-lg"></i></a>@endif
                    @if(!empty($siteSettings['whatsapp_number']))<a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings['whatsapp_number']) }}" target="_blank" class="w-9 h-9 rounded-full border flex items-center justify-center text-white/40 hover:text-white transition-all" style="border-color:var(--glass-border);" aria-label="واتساب"><i class="ph-fill ph-whatsapp-logo text-lg"></i></a>@endif
                    @if(!empty($siteSettings['tiktok_url']))<a href="{{ $siteSettings['tiktok_url'] }}" target="_blank" class="w-9 h-9 rounded-full border flex items-center justify-center text-white/40 hover:text-white transition-all" style="border-color:var(--glass-border);" aria-label="تيك توك"><i class="ph-fill ph-tiktok-logo text-lg"></i></a>@endif
                </div>
            </div>

            {{-- Shop --}}
            <div class="lg:col-span-2 text-right">
                <h5 class="font-bold text-white mb-5 text-sm">المتجر</h5>
                <ul class="space-y-3 text-ink-dim text-sm">
                    <li><a href="{{ route('shop') }}" class="hover:text-brand-500 transition-colors">جميع المنتجات</a></li>
                    @php $ftCats = $headerCategories ?? \App\Models\Category::active()->withCount('products')->having('products_count','>',0)->orderBy('sort_order')->get(); @endphp
                    @foreach($ftCats->take(5) as $fc)
                        @php $al = preg_replace('/\s{2,}/',' ',trim(preg_replace('/[a-zA-Z&\-\(\)]+/','',$fc->name_ar))); $al = !empty($al)?$al:$fc->name_ar; @endphp
                        <li><a href="{{ route('shop',['category'=>$fc->slug]) }}" class="hover:text-brand-500 transition-colors">{{ $al }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Support --}}
            <div class="lg:col-span-2 text-right">
                <h5 class="font-bold text-white mb-5 text-sm">خدمة العملاء</h5>
                <ul class="space-y-3 text-ink-dim text-sm">
                    <li><a href="{{ route('shipping-policy') }}" class="hover:text-brand-500 transition-colors">الشحن والتوصيل</a></li>
                    <li><a href="{{ route('return-policy') }}" class="hover:text-brand-500 transition-colors">سياسة الإرجاع</a></li>
                    <li><a href="{{ route('faq') }}" class="hover:text-brand-500 transition-colors">الأسئلة الشائعة</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-brand-500 transition-colors">الشروط والأحكام</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-brand-500 transition-colors">حماية البيانات</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-brand-500 transition-colors">تواصل معنا</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div class="lg:col-span-4 text-right">
                <h5 class="font-bold text-white mb-5 text-sm">تواصل معنا</h5>
                <ul class="space-y-4 text-ink-dim text-sm">
                    <li class="flex items-center gap-3 justify-end">
                        <span>{{ $siteSettings['site_address'] ?? 'فلسطين، رام الله' }}</span>
                        <i class="ph-fill ph-map-pin" style="color:var(--brand-500);"></i>
                    </li>
                    <li class="flex items-center gap-3 justify-end" dir="ltr">
                        <span>{{ $siteSettings['site_phone'] ?? '+972 56 903 0203' }}</span>
                        <i class="ph-fill ph-phone" style="color:var(--brand-500);"></i>
                    </li>
                    <li class="flex items-center gap-3 justify-end">
                        <span>{{ $siteSettings['site_email'] ?? 'hello@jenincare.com' }}</span>
                        <i class="ph-fill ph-envelope" style="color:var(--brand-500);"></i>
                    </li>
                    <li class="flex items-center gap-3 justify-end">
                        <span>يومياً 9:00 ص - 10:00 م</span>
                        <i class="ph-fill ph-clock" style="color:var(--brand-500);"></i>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Copyright --}}
        <div class="pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-ink-dim" style="border-top:2px solid var(--glass-border);">
            <p>&copy; {{ date('Y') }} {{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}. جميع الحقوق محفوظة.</p>
            <div class="flex gap-2 text-xs">
                <span class="px-3 py-1 rounded-full" style="border:1px solid var(--glass-border);">الدفع عند الاستلام</span>
                @if(($siteSettings['payment_jawwal_enabled'] ?? '0') == '1')<span class="px-3 py-1 rounded-full" style="color:var(--brand-500);border:1px solid var(--glass-border);">جوال باي</span>@endif
            </div>
        </div>
    </div>
</footer>

<script>
document.getElementById('newsletterFormV3')?.addEventListener('submit',async function(e){e.preventDefault();const btn=this.querySelector('button'),msg=document.getElementById('newsletterMsgV3'),email=this.querySelector('input[name="email"]').value.trim();if(!email)return;btn.disabled=true;btn.innerHTML='جاري...';try{const r=await fetch((window.basePath||'')+'/newsletter/subscribe',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':this.querySelector('input[name="_token"]').value,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({email})});const d=await r.json();d.success?(msg.innerHTML='<span class="text-green-400">'+d.message+'</span>',this.querySelector('input').value='',window.showNotification&&showNotification('success',d.message)):(msg.innerHTML='<span class="text-red-400">'+(d.message||'خطأ')+'</span>');}catch(e){msg.innerHTML='<span class="text-red-400">خطأ في الاتصال</span>';}btn.disabled=false;btn.innerHTML='اشتراك';setTimeout(()=>{msg.innerHTML=''},5000);});
</script>
