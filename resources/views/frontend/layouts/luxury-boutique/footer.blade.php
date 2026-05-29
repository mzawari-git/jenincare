{{-- Luxury Boutique Footer: Ornate, centered, elegant --}}
<footer class="pt-20 pb-12 relative" style="border-top:2px solid var(--glass-border);">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <div class="mb-10">
            <h4 class="text-2xl font-black mb-2 text-white">{{ $siteSettings['site_name'] ?? 'JeniCare' }}<span class="text-brand-500">.</span></h4>
            <div class="luxury-divider mb-4"></div>
            <p class="text-ink-dim text-sm max-w-md mx-auto">{{ $siteSettings['site_description'] ?? 'وجهتك الفاخرة لمنتجات العناية.' }}</p>
        </div>

        <form id="newsletterFormV3" class="max-w-md mx-auto relative flex items-center mb-8">
            @csrf
            <input type="email" name="email" placeholder="انضمي إلى قائمتنا البريدية" required class="w-full bg-transparent border-2 border-white/10 text-white py-3 px-5 pl-28 rounded-full text-sm focus:outline-none focus:border-brand-500 placeholder:text-ink-dim">
            <button type="submit" class="absolute left-2 px-6 py-1.5 rounded-full font-bold text-xs text-white" style="background:var(--gradient-primary);">اشتراك</button>
        </form>
        <p id="newsletterMsgV3" class="text-xs mb-8"></p>

        <div class="flex justify-center gap-6 mb-8 flex-wrap text-sm text-ink-dim">
            <a href="{{ route('shop') }}" class="hover:text-brand-500 transition-colors">المتجر</a>
            <a href="{{ route('b2b') }}" class="hover:text-brand-500 transition-colors">للأعمال</a>
            <a href="{{ route('shipping-policy') }}" class="hover:text-brand-500 transition-colors">الشحن</a>
            <a href="{{ route('return-policy') }}" class="hover:text-brand-500 transition-colors">الإرجاع</a>
            <a href="{{ route('faq') }}" class="hover:text-brand-500 transition-colors">الأسئلة</a>
            <a href="{{ route('contact') }}" class="hover:text-brand-500 transition-colors">تواصل</a>
        </div>

        <div class="flex justify-center gap-4 mb-8">
            @if(!empty($siteSettings['facebook_url']))<a href="{{ $siteSettings['facebook_url'] }}" target="_blank" class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-ink-dim hover:text-white hover:border-brand-500 transition-all" style="border-color:var(--glass-border);" aria-label="فيسبوك"><i class="ph-fill ph-facebook-logo text-lg"></i></a>@endif
            @if(!empty($siteSettings['instagram_url']))<a href="{{ $siteSettings['instagram_url'] }}" target="_blank" class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-ink-dim hover:text-white hover:border-brand-500 transition-all" style="border-color:var(--glass-border);" aria-label="إنستغرام"><i class="ph-fill ph-instagram-logo text-lg"></i></a>@endif
            @if(!empty($siteSettings['twitter_url']))<a href="{{ $siteSettings['twitter_url'] }}" target="_blank" class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-ink-dim hover:text-white hover:border-brand-500 transition-all" style="border-color:var(--glass-border);" aria-label="تويتر"><i class="ph-fill ph-twitter-logo text-lg"></i></a>@endif
            @if(!empty($siteSettings['tiktok_url']))<a href="{{ $siteSettings['tiktok_url'] }}" target="_blank" class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-ink-dim hover:text-white hover:border-brand-500 transition-all" style="border-color:var(--glass-border);" aria-label="تيك توك"><i class="ph-fill ph-tiktok-logo text-lg"></i></a>@endif
            @if(!empty($siteSettings['linkedin_url']))<a href="{{ $siteSettings['linkedin_url'] }}" target="_blank" class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-ink-dim hover:text-white hover:border-brand-500 transition-all" style="border-color:var(--glass-border);" aria-label="لينكد إن"><i class="ph-fill ph-linkedin-logo text-lg"></i></a>@endif
            @if(!empty($siteSettings['youtube_url']))<a href="{{ $siteSettings['youtube_url'] }}" target="_blank" class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-ink-dim hover:text-white hover:border-brand-500 transition-all" style="border-color:var(--glass-border);" aria-label="يوتيوب"><i class="ph-fill ph-youtube-logo text-lg"></i></a>@endif
            @if(!empty($siteSettings['whatsapp_number']))<a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$siteSettings['whatsapp_number']) }}" target="_blank" class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-ink-dim hover:text-white hover:border-brand-500 transition-all" style="border-color:var(--glass-border);" aria-label="واتساب"><i class="ph-fill ph-whatsapp-logo text-lg"></i></a>@endif
        </div>

        <p class="text-ink-dim text-xs">&copy; {{ date('Y') }} {{ $siteSettings['site_name'] ?? 'JeniCare' }}. جميع الحقوق محفوظة.</p>
    </div>
</footer>

<script>
document.getElementById('newsletterFormV3')?.addEventListener('submit',async function(e){e.preventDefault();const btn=this.querySelector('button'),msg=document.getElementById('newsletterMsgV3'),email=this.querySelector('input[name="email"]').value.trim();if(!email)return;btn.disabled=true;btn.textContent='...';try{const r=await fetch((window.basePath||'')+'/newsletter/subscribe',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':this.querySelector('input[name="_token"]').value,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({email})});const d=await r.json();d.success?(msg.innerHTML='<span class="text-green-400">'+d.message+'</span>',this.querySelector('input').value=''):(msg.innerHTML='<span class="text-red-400">'+(d.message||'خطأ')+'</span>');}catch(e){msg.innerHTML='<span class="text-red-400">خطأ</span>';}btn.disabled=false;btn.textContent='اشتراك';setTimeout(()=>{msg.innerHTML=''},5000);});
</script>
