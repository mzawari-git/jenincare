<!DOCTYPE html>
<html lang="ar" dir="rtl" @if($isLightTheme ?? false) data-theme-mode="light" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', ($siteSettings['site_description'] ?? 'شركة جنين للتجميل - المنصة الرائدة لتقنيات العناية بالبشرة'))">
    <meta name="keywords" content="@yield('meta_keywords', 'شركة جنين للتجميل, تجميل, عناية, بشرة')">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">
    <meta property="og:type" content="website"><meta property="og:title" content="@yield('title', ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))"><meta property="og:description" content="@yield('meta_description', '')"><meta property="og:url" content="{{ url()->current() }}"><meta property="og:image" content="@yield('og_image', asset('assets/images/og-image.webp'))">
    <meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="@yield('title', ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))"><meta name="twitter:description" content="@yield('meta_description', '')"><meta name="twitter:image" content="@yield('og_image', asset('assets/images/og-image.webp'))">
    <title>@yield('title', ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))</title>

    @if(!empty($siteSettings['site_favicon_url']))<link rel="icon" href="{{ $siteSettings['site_favicon_url'] }}">@else<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">@endif

    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link id="googleFontsLink" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">

    <script src="https://unpkg.com/@phosphor-icons/web" defer></script>
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="manifest" href="{{ asset('manifest.json') }}"><meta name="theme-color" content="var(--surface, #050505)"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="stylesheet" href="{{ asset('css/themes/' . $activeTheme . '.css') }}">
    <link rel="stylesheet" href="{{ asset('css/light-mode.css') }}">
    <script>(function(){var m=localStorage.getItem('شركة جنين للتجميل_mode');if(!m){var c=document.cookie.match('شركة جنين للتجميل_mode=([^;]+)');m=c?c[1]:null;}if(m==='dark'){document.documentElement.removeAttribute('data-theme-mode');}else{document.documentElement.setAttribute('data-theme-mode','light');}})();</script>

    @php $tracking = app(\App\Services\AdvertisingTrackingService::class); @endphp

    <style>
        /* ── Luxury Boutique: Classic centered elegance, gold frames, ornate ── */
        * { transition: all 0.35s cubic-bezier(0.22, 1, 0.36, 1); }
        ::selection { background: var(--brand-500); color: #000; }

        .luxury-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 2px solid var(--glass-border);
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.4);
            position: relative;
        }
        .luxury-card::before {
            content: '';
            position: absolute; inset: 6px;
            border: 1px solid rgba(255,255,255,0.04);
            border-radius: 0.75rem;
            pointer-events: none;
            z-index: 1;
        }
        .luxury-card:hover {
            border-color: var(--brand-500);
            box-shadow: 0 8px 40px rgba(0,0,0,0.5), var(--neon-glow);
        }

        .luxury-frame {
            border: 2px solid var(--glass-border);
            border-radius: 1.25rem;
            padding: 2px;
            background: linear-gradient(135deg, var(--glass-border), transparent 40%, var(--glass-border));
        }

        .luxury-divider {
            width: 60px;
            height: 2px;
            background: var(--brand-500);
            margin: 0 auto;
            border-radius: 1px;
        }

        .floating-social-v3 {
            position: fixed; left: 20px; top: 50%; transform: translateY(-50%); z-index: 999; display: flex; flex-direction: column; gap: 8px;
        }
        .floating-social-v3 a {
            width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.1rem; transition: all .3s; text-decoration: none !important; border: 1px solid rgba(255,255,255,.1); box-shadow: 0 2px 8px rgba(0,0,0,.3);
        }
        .floating-social-v3 a[data-platform="facebook"] { background: #1877F2; border-color: #1877F2; }
        .floating-social-v3 a[data-platform="instagram"] { background: linear-gradient(135deg, #833AB4, #E4405F, #FCAF45); border-color: #E4405F; }
        .floating-social-v3 a[data-platform="twitter"] { background: #0F1419; border-color: #0F1419; }
        .floating-social-v3 a[data-platform="tiktok"] { background: #000000; border-color: #000000; }
        .floating-social-v3 a[data-platform="linkedin"] { background: #0A66C2; border-color: #0A66C2; }
        .floating-social-v3 a[data-platform="youtube"] { background: #FF0000; border-color: #FF0000; }
        .floating-social-v3 a[data-platform="whatsapp"] { background: #25D366; border-color: #25D366; }
        .floating-social-v3 a:hover { transform: scale(1.15); }
        @media (max-width:768px) { .floating-social-v3 { left: 8px; bottom: 80px; top: auto; transform: none; } }
    </style>
    @stack('styles')
    <script>if('serviceWorker' in navigator&&window.location.hostname!=='localhost'){navigator.serviceWorker.getRegistrations().then(function(r){r.forEach(function(x){x.unregister()})}).then(function(){navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(function(){})})}</script>
    <script>window.basePath="{{ rtrim(url('/'), '/') }}";</script>
</head>
<body class="antialiased" style="background-color:var(--surface);color:var(--ink);">
    <a href="#main-content" class="skip-link">الانتقال إلى المحتوى الرئيسي</a>
    @if($tracking->isEnabled()) {!! $tracking->getBrowserPixelNoscript() !!} @endif

    @include('frontend.layouts.luxury-boutique.header')
    <main id="main-content" style="min-height:60vh;">
        @yield('content')
    </main>
    @include('frontend.layouts.luxury-boutique.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="{{ asset('js/behavioral-analysis.js') }}" defer></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @if($tracking->isEnabled()) {!! $tracking->getBrowserPixelScript() !!} @endif
    @stack('scripts')

    <div class="modal fade" id="quickViewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content luxury-card" style="color:var(--ink);">
                <div class="modal-header" style="border:none;"><button type="button" class="btn-close btn-close-white ms-0 me-auto" data-bs-dismiss="modal"></button></div>
                <div class="modal-body" id="quickViewBody" style="padding:0 20px 24px;"><div class="text-center py-4"><div class="spinner-border" style="color:var(--brand-500);"></div></div></div>
            </div>
        </div>
    </div>
    <div id="searchDropdown" style="display:none;position:absolute;background:var(--surface-alt);border:2px solid var(--glass-border);border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.4);z-index:9999;max-height:400px;overflow-y:auto;"></div>

    <script>
    let st; const si=document.querySelectorAll('#searchOverlayV3 input[name="search"]'),sd=document.getElementById('searchDropdown');
    if(si.length&&sd){si.forEach(function(i){i.setAttribute('autocomplete','off');i.addEventListener('input',function(){clearTimeout(st);const q=this.value.trim();if(q.length<2){sd.style.display='none';return;}st=setTimeout(()=>{fetch((window.basePath||'')+'/api/search?q='+encodeURIComponent(q)).then(r=>r.json()).then(d=>{if(!d.length){sd.style.display='none';return;}sd.innerHTML=d.map(p=>'<a href="'+p.url+'" style="display:flex;align-items:center;gap:10px;padding:10px 14px;text-decoration:none;color:var(--ink);border-bottom:1px solid var(--glass-border);" onmouseover="this.style.background=\'rgba(255,255,255,.03)\'" onmouseout="this.style.background=\'\'">'+(p.image?'<img src="'+p.image+'" style="width:42px;height:42px;border-radius:8px;object-fit:cover;">':'')+'<div><div style="font-weight:600;font-size:.8rem;">'+p.name+'</div><div style="font-size:.75rem;color:var(--brand-500);font-weight:700;">'+p.price+'</div></div></a>').join('');sd.style.display='block';const r=this.getBoundingClientRect();sd.style.top=(r.bottom+window.scrollY)+'px';sd.style.left=r.left+'px';sd.style.width=r.width+'px';});},250);});});document.addEventListener('click',e=>{let in_=false;si.forEach(i=>{if(i.contains(e.target))in_=true;});if(!in_&&!sd.contains(e.target))sd.style.display='none';});}
    async function quickView(id){const m=new bootstrap.Modal(document.getElementById('quickViewModal')),b=document.getElementById('quickViewBody');b.innerHTML='<div class="text-center py-4"><div class="spinner-border" style="color:var(--brand-500);"></div></div>';m.show();try{const r=await fetch((window.basePath||'')+'/api/product/'+id+'/quickview'),p=await r.json();b.innerHTML='<div class="row g-4"><div class="col-md-5">'+(p.image?'<img src="'+p.image+'" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:12px;">':'<div style="width:100%;aspect-ratio:1/1;background:var(--surface);border-radius:12px;display:flex;align-items:center;justify-content:center;"><i class="ph ph-package" style="font-size:3rem;color:#333;"></i></div>')+'</div><div class="col-md-7"><h4 style="font-weight:800;">'+p.name+'</h4><div style="font-size:1.3rem;font-weight:800;color:var(--brand-500);">'+p.price+' ₪</div><div class="d-flex gap-2 mt-3"><button onclick="addToCart('+p.id+')" class="btn" style="background:var(--gradient-primary);color:#fff;border-radius:12px;font-weight:700;">أضف للسلة</button><a href="'+p.url+'" class="btn luxury-card" style="color:var(--ink);font-weight:700;">تفاصيل</a></div></div></div>';}catch(e){b.innerHTML='<div class="text-center py-4 text-danger">تعذر تحميل المنتج</div>';}}
    async function toggleWishlist(id,btn){try{const r=await fetch((window.basePath||'')+'/wishlist/toggle',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({product_id:id})});const d=await r.json();if(d.success){const i=btn.querySelector('i');i.className=d.action==='added'?'fas fa-heart':'far fa-heart';btn.style.color=d.action==='added'?'#EF4444':'var(--ink-dim)';showNotification('success',d.message);}else if(r.status===401)window.location='/login';}catch(e){}}
    </script>

    <div class="floating-social-v3">
        @if(!empty($siteSettings['facebook_url']))<a href="{{ $siteSettings['facebook_url'] }}" data-platform="facebook" style="background:#1877F2;" target="_blank" aria-label="فيسبوك"><i class="ph-fill ph-facebook-logo"></i></a>@endif
        @if(!empty($siteSettings['instagram_url']))<a href="{{ $siteSettings['instagram_url'] }}" data-platform="instagram" style="background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);" target="_blank" aria-label="إنستغرام"><i class="ph-fill ph-instagram-logo"></i></a>@endif
        @if(!empty($siteSettings['twitter_url']))<a href="{{ $siteSettings['twitter_url'] }}" data-platform="twitter" style="background:#1DA1F2;" target="_blank" aria-label="تويتر"><i class="ph-fill ph-twitter-logo"></i></a>@endif
        @if(!empty($siteSettings['tiktok_url']))<a href="{{ $siteSettings['tiktok_url'] }}" data-platform="tiktok" style="background:#000;" target="_blank" aria-label="تيك توك"><i class="ph-fill ph-tiktok-logo"></i></a>@endif
        @if(!empty($siteSettings['linkedin_url']))<a href="{{ $siteSettings['linkedin_url'] }}" data-platform="linkedin" style="background:#0A66C2;" target="_blank" aria-label="لينكد إن"><i class="ph-fill ph-linkedin-logo"></i></a>@endif
        @if(!empty($siteSettings['youtube_url']))<a href="{{ $siteSettings['youtube_url'] }}" data-platform="youtube" style="background:#FF0000;" target="_blank" aria-label="يوتيوب"><i class="ph-fill ph-youtube-logo"></i></a>@endif
        @if(!empty($siteSettings['whatsapp_number']))<a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$siteSettings['whatsapp_number']) }}" data-platform="whatsapp" style="background:#25D366;" target="_blank" aria-label="واتساب"><i class="ph-fill ph-whatsapp-logo"></i></a>@endif
    </div>

    <style>.skip-link{position:absolute;top:-40px;left:0;background:var(--brand-500);color:var(--surface);padding:8px 16px;z-index:100;border-radius:0 0 8px 0;font-weight:700;}.skip-link:focus{top:0;}</style>
@include('frontend.layouts.partials.theme-switcher')
</body>
</html>
