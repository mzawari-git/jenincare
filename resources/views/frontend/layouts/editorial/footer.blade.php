{{-- Editorial Footer: Thin, clean, rules, no decoration --}}
<footer class="py-16 px-4" style="border-top:1px solid rgba(255,255,255,0.06);">
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12">
            <div>
                <h4 class="text-xs font-bold uppercase tracking-widest text-ink-dim mb-5">النشرة</h4>
                <p class="text-ink-dim text-sm leading-relaxed mb-4">{{ $siteSettings['site_description'] ?? 'وجهتك الفاخرة لمنتجات العناية.' }}</p>
                <form id="newsletterFormV3" class="flex gap-2">
                    @csrf
                    <input type="email" name="email" placeholder="بريدك الإلكتروني" required class="flex-1 bg-transparent border-b border-white/10 text-white py-2 text-sm focus:outline-none focus:border-brand-500 placeholder:text-ink-dim">
                    <button type="submit" class="text-brand-500 font-bold text-sm hover:text-white transition-colors">اشتراك</button>
                </form>
                <p id="newsletterMsgV3" class="text-xs mt-2"></p>
            </div>
            <div>
                <h4 class="text-xs font-bold uppercase tracking-widest text-ink-dim mb-5">المتجر</h4>
                <ul class="space-y-2 text-sm text-ink-dim">
                    <li><a href="{{ route('shop') }}" class="hover:text-white transition-colors">جميع المنتجات</a></li>
                    <li><a href="{{ route('b2b') }}" class="hover:text-white transition-colors">للأعمال</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-bold uppercase tracking-widest text-ink-dim mb-5">معلومات</h4>
                <ul class="space-y-2 text-sm text-ink-dim">
                    <li><a href="{{ route('shipping-policy') }}" class="hover:text-white transition-colors">الشحن</a></li>
                    <li><a href="{{ route('return-policy') }}" class="hover:text-white transition-colors">الإرجاع</a></li>
                    <li><a href="{{ route('faq') }}" class="hover:text-white transition-colors">الأسئلة الشائعة</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-white transition-colors">الشروط</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-white transition-colors">الخصوصية</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white transition-colors">تواصل</a></li>
                </ul>
            </div>
        </div>
        <div class="pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-ink-dim" style="border-top:1px solid rgba(255,255,255,0.06);">
            <p>&copy; {{ date('Y') }} {{ $siteSettings['site_name'] ?? 'JeniCare' }}.</p>
            <div class="flex gap-4">
                @if(!empty($siteSettings['facebook_url']))<a href="{{ $siteSettings['facebook_url'] }}" target="_blank" class="hover:text-white transition-colors">فيسبوك</a>@endif
                @if(!empty($siteSettings['instagram_url']))<a href="{{ $siteSettings['instagram_url'] }}" target="_blank" class="hover:text-white transition-colors">إنستغرام</a>@endif
                @if(!empty($siteSettings['twitter_url']))<a href="{{ $siteSettings['twitter_url'] }}" target="_blank" class="hover:text-white transition-colors">تويتر</a>@endif
                @if(!empty($siteSettings['tiktok_url']))<a href="{{ $siteSettings['tiktok_url'] }}" target="_blank" class="hover:text-white transition-colors">تيك توك</a>@endif
                @if(!empty($siteSettings['linkedin_url']))<a href="{{ $siteSettings['linkedin_url'] }}" target="_blank" class="hover:text-white transition-colors">لينكد إن</a>@endif
                @if(!empty($siteSettings['youtube_url']))<a href="{{ $siteSettings['youtube_url'] }}" target="_blank" class="hover:text-white transition-colors">يوتيوب</a>@endif
                @if(!empty($siteSettings['whatsapp_number']))<a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$siteSettings['whatsapp_number']) }}" target="_blank" class="hover:text-white transition-colors">واتساب</a>@endif
            </div>
        </div>
    </div>
</footer>

<script>
document.getElementById('newsletterFormV3')?.addEventListener('submit',async function(e){e.preventDefault();const btn=this.querySelector('button'),msg=document.getElementById('newsletterMsgV3'),email=this.querySelector('input[name="email"]').value.trim();if(!email)return;btn.disabled=true;btn.textContent='...';try{const r=await fetch((window.basePath||'')+'/newsletter/subscribe',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':this.querySelector('input[name="_token"]').value,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({email})});const d=await r.json();d.success?(msg.innerHTML='<span class="text-green-400">'+d.message+'</span>',this.querySelector('input').value=''):(msg.innerHTML='<span class="text-red-400">'+(d.message||'خطأ')+'</span>');}catch(e){msg.innerHTML='<span class="text-red-400">خطأ</span>';}btn.disabled=false;btn.textContent='اشتراك';setTimeout(()=>{msg.innerHTML=''},5000);});
</script>
