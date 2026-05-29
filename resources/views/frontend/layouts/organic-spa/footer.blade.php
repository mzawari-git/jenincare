{{-- Organic Spa Footer: Flowing curves, plant motifs, soft green gradients --}}
<section class="py-20 relative overflow-hidden border-t-2" style="border-color: var(--glass-border);">
    <div class="wave-divider" style="margin-top:-80px;">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M0,60 C300,0 600,120 900,60 C1050,30 1150,50 1200,60 L1200,120 L0,120 Z" fill="var(--brand-500)" opacity="0.04"></path></svg>
    </div>
    <div class="max-w-4xl mx-auto px-4 text-center relative z-10 pt-4">
        <div class="w-16 h-16 mx-auto mb-6 rounded-full flex items-center justify-center" style="background: var(--gradient-primary);">
            <i class="fa-solid fa-leaf text-2xl text-white"></i>
        </div>
        <h2 class="text-3xl md:text-4xl font-black mb-4" style="color: var(--brand-500);">انضمي إلى حديقة {{ $siteSettings['site_name'] ?? 'JeniCare' }}</h2>
        <p class="text-ink-dim mb-8 font-light max-w-lg mx-auto">احصلي على خصم 10% على طلبك الأول، وكوني أول من يعرف عن العروض الحصرية.</p>
        <form class="max-w-md mx-auto relative flex items-center" id="newsletterFormV3">
            @csrf
            <input type="email" name="email" placeholder="بريدك الإلكتروني" required class="w-full bg-white/5 border-2 border-white/10 text-white py-4 px-6 pl-28 rounded-full focus:outline-none focus:border-brand-500 transition-all text-sm placeholder:text-white/30">
            <button type="submit" class="absolute left-2 px-6 py-2 rounded-full font-bold text-sm transition-all text-white" style="background: var(--gradient-primary);">اشتراك</button>
        </form>
        <p id="newsletterMsgV3" class="text-sm mt-3"></p>
    </div>
</section>

{{-- Trust Badges --}}
<section class="py-16 border-t-2" style="border-color: var(--glass-border);">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="flex flex-col items-center text-center p-6 spa-card"><div class="w-14 h-14 rounded-full flex items-center justify-center mb-3" style="background:var(--brand-500);"><i class="fa-solid fa-shield-check text-2xl text-white"></i></div><h3 class="font-bold text-white text-sm">أصلي 100%</h3><p class="text-ink-dim text-xs">منتجات أصلية ومستوردة</p></div>
            <div class="flex flex-col items-center text-center p-6 spa-card"><div class="w-14 h-14 rounded-full flex items-center justify-center mb-3" style="background:var(--brand-600);"><i class="fa-solid fa-truck-fast text-2xl text-white"></i></div><h3 class="font-bold text-white text-sm">شحن لكل فلسطين</h3><p class="text-ink-dim text-xs">توصيل لجميع المناطق</p></div>
            <div class="flex flex-col items-center text-center p-6 spa-card"><div class="w-14 h-14 rounded-full flex items-center justify-center mb-3" style="background:var(--brand-500);"><i class="fa-solid fa-lock text-2xl text-white"></i></div><h3 class="font-bold text-white text-sm">دفع آمن</h3><p class="text-ink-dim text-xs">الدفع عند الاستلام</p></div>
            <div class="flex flex-col items-center text-center p-6 spa-card"><div class="w-14 h-14 rounded-full flex items-center justify-center mb-3" style="background:var(--brand-600);"><i class="fa-solid fa-headset text-2xl text-white"></i></div><h3 class="font-bold text-white text-sm">دعم فني</h3><p class="text-ink-dim text-xs">9 ص - 10 م يومياً</p></div>
        </div>
    </div>
</section>

{{-- Main Footer --}}
<footer class="pt-20 pb-12 relative overflow-hidden border-t-2" style="border-color: var(--glass-border);">
    <div class="max-w-7xl mx-auto px-4 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 mb-12">
            <div class="lg:col-span-5 text-right">
                <div class="flex items-center gap-3 mb-6 justify-end">
                    @if(!empty($siteSettings['site_logo_url']))<img src="{{ $siteSettings['site_logo_url'] }}" alt="{{ $siteSettings['site_name'] ?? 'JeniCare' }}" class="h-10 w-auto object-contain">@else<span class="text-3xl font-extrabold text-white">{{ $siteSettings['site_name'] ?? 'JeniCare' }}<span class="text-brand-500">.</span></span>@endif
                </div>
                <p class="text-ink-dim text-sm leading-relaxed mb-6 max-w-md ml-auto">{{ $siteSettings['site_description'] ?? 'وجهتك الفاخرة لمنتجات العناية الطبيعية.' }}</p>
                <div class="flex gap-4 justify-end">
                    @if(!empty($siteSettings['facebook_url']))<a href="{{ $siteSettings['facebook_url'] }}" target="_blank" class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-white/50 hover:text-white transition-all" style="border-color:var(--glass-border);" aria-label="فيسبوك"><i class="ph-fill ph-facebook-logo text-xl"></i></a>@endif
                    @if(!empty($siteSettings['instagram_url']))<a href="{{ $siteSettings['instagram_url'] }}" target="_blank" class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-white/50 hover:text-white transition-all" style="border-color:var(--glass-border);" aria-label="إنستغرام"><i class="ph-fill ph-instagram-logo text-xl"></i></a>@endif
                    @if(!empty($siteSettings['twitter_url']))<a href="{{ $siteSettings['twitter_url'] }}" target="_blank" class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-white/50 hover:text-white transition-all" style="border-color:var(--glass-border);" aria-label="تويتر"><i class="ph-fill ph-twitter-logo text-xl"></i></a>@endif
                    @if(!empty($siteSettings['tiktok_url']))<a href="{{ $siteSettings['tiktok_url'] }}" target="_blank" class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-white/50 hover:text-white transition-all" style="border-color:var(--glass-border);" aria-label="تيك توك"><i class="ph-fill ph-tiktok-logo text-xl"></i></a>@endif
                    @if(!empty($siteSettings['linkedin_url']))<a href="{{ $siteSettings['linkedin_url'] }}" target="_blank" class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-white/50 hover:text-white transition-all" style="border-color:var(--glass-border);" aria-label="لينكد إن"><i class="ph-fill ph-linkedin-logo text-xl"></i></a>@endif
                    @if(!empty($siteSettings['youtube_url']))<a href="{{ $siteSettings['youtube_url'] }}" target="_blank" class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-white/50 hover:text-white transition-all" style="border-color:var(--glass-border);" aria-label="يوتيوب"><i class="ph-fill ph-youtube-logo text-xl"></i></a>@endif
                    @if(!empty($siteSettings['whatsapp_number']))<a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings['whatsapp_number']) }}" target="_blank" class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-white/50 hover:text-white transition-all" style="border-color:var(--glass-border);" aria-label="واتساب"><i class="ph-fill ph-whatsapp-logo text-xl"></i></a>@endif
                </div>
            </div>
            <div class="lg:col-span-7">
                <div class="grid grid-cols-2 gap-8 text-right">
                    <div><h5 class="font-bold text-white mb-5 text-sm">المتجر</h5><ul class="space-y-3 text-ink-dim text-sm"><li><a href="{{ route('shop') }}" class="hover:text-brand-500 transition-colors">جميع المنتجات</a></li>@php $ftCats = $headerCategories ?? \App\Models\Category::active()->withCount('products')->having('products_count','>',0)->orderBy('sort_order')->get(); @endphp @foreach($ftCats->take(4) as $fc) @php $al = preg_replace('/\s{2,}/',' ',trim(preg_replace('/[a-zA-Z&\-\(\)]+/','',$fc->name_ar))); $al = !empty($al)?$al:$fc->name_ar; @endphp <li><a href="{{ route('shop',['category'=>$fc->slug]) }}" class="hover:text-brand-500 transition-colors">{{ $al }}</a></li> @endforeach <li><a href="{{ route('b2b') }}" class="hover:text-brand-500 transition-colors">للأعمال</a></li></ul></div>
                    <div><h5 class="font-bold text-white mb-5 text-sm">المساعدة</h5><ul class="space-y-3 text-ink-dim text-sm"><li><a href="{{ route('shipping-policy') }}" class="hover:text-brand-500 transition-colors">الشحن</a></li><li><a href="{{ route('return-policy') }}" class="hover:text-brand-500 transition-colors">الإرجاع</a></li><li><a href="{{ route('faq') }}" class="hover:text-brand-500 transition-colors">الأسئلة الشائعة</a></li><li><a href="{{ route('terms') }}" class="hover:text-brand-500 transition-colors">الشروط</a></li><li><a href="{{ route('privacy') }}" class="hover:text-brand-500 transition-colors">الخصوصية</a></li><li><a href="{{ route('contact') }}" class="hover:text-brand-500 transition-colors">تواصل</a></li></ul></div>
                </div>
            </div>
        </div>
        <div class="pt-8 border-t-2 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-ink-dim" style="border-color:var(--glass-border);">
            <p>&copy; {{ date('Y') }} {{ $siteSettings['site_name'] ?? 'JeniCare' }}. جميع الحقوق محفوظة.</p>
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
