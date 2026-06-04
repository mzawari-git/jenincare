<!DOCTYPE html>
<html lang="ar" dir="rtl" @if($isLightTheme ?? false) data-theme-mode="light" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', ($siteSettings['site_description'] ?? 'شركة جنين للتجميل - المنصة الرائدة لتقنيات العناية بالبشرة المتقدمة وحلول الجمال الاحترافية'))">
    <meta name="keywords" content="@yield('meta_keywords', 'شركة جنين للتجميل, تجميل, عناية, بشرة, منتجات تجميل, العناية بالبشرة, مكياج')">

    <link rel="canonical" href="@yield('canonical_url', url()->current())">

    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', ($siteSettings['site_name'] ?? 'شركة جنين للتجميل') . ' | ' . ($siteSettings['site_description'] ?? ''))">
    <meta property="og:description" content="@yield('meta_description', $siteSettings['site_description'] ?? '')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('assets/images/og-image.webp'))">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', ($siteSettings['site_name'] ?? 'شركة جنين للتجميل') . ' | ' . ($siteSettings['site_description'] ?? ''))">
    <meta name="twitter:description" content="@yield('meta_description', '')">
    <meta name="twitter:image" content="@yield('og_image', asset('assets/images/og-image.webp'))">

    <title>@yield('title', ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))</title>

    @if(!empty($siteSettings['site_favicon_url']))
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $siteSettings['site_favicon_url'] }}">
        <link rel="apple-touch-icon" href="{{ $siteSettings['site_favicon_url'] }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link id="googleFontsLink" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

    <script src="https://unpkg.com/@phosphor-icons/web" defer></script>
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v=4">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="manifest" href="{{ asset('manifest.json') }}?v=4"><meta name="theme-color" content="var(--surface, #050a08)">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="شركة جنين للتجميل">
    <meta name="application-name" content="شركة جنين للتجميل">
    <meta name="msapplication-TileColor" content="#050a08">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <link rel="stylesheet" href="{{ asset('css/themes/' . $activeTheme . '.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/light-mode.css') }}?v=4">
    <script>(function(){var m=localStorage.getItem('شركة جنين للتجميل_mode');if(!m){var c=document.cookie.match('شركة جنين للتجميل_mode=([^;]+)');m=c?c[1]:null;}if(m==='dark'){document.documentElement.removeAttribute('data-theme-mode');}else{document.documentElement.setAttribute('data-theme-mode','light');}})();</script>

    @php $tracking = app(\App\Services\AdvertisingTrackingService::class); @endphp

    <style>
        /* ── Organic Spa: Floating Leaf Particles ── */
        @keyframes leafDrift {
            0% { transform: translate(0, 0) rotate(0deg); opacity: 0; }
            10% { opacity: 0.12; }
            90% { opacity: 0.08; }
            100% { transform: translate(-120px, -120px) rotate(60deg); opacity: 0; }
        }
        @keyframes organicPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.03); }
        }
        @keyframes waveFlow {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .leaf-particle {
            position: fixed;
            pointer-events: none;
            z-index: 0;
            animation: leafDrift 15s linear infinite;
            color: var(--brand-500);
            opacity: 0.06;
            font-size: 2rem;
        }

        .spa-card {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur));
            -webkit-backdrop-filter: blur(var(--glass-blur));
            border: 2px solid var(--glass-border);
            border-radius: var(--card-radius);
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            transition: all 0.5s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .spa-card:hover {
            transform: translateY(-6px) scale(1.01);
            border-color: var(--brand-500);
            box-shadow: 0 16px 48px rgba(0,0,0,0.4), var(--neon-glow);
        }

        .wave-divider {
            position: relative;
            height: 80px;
            overflow: hidden;
        }
        .wave-divider svg {
            position: absolute;
            bottom: 0;
            width: 200%;
            height: 80px;
            animation: waveFlow 8s linear infinite;
        }

        .floating-social-v3 {
            position: fixed; left: 20px; top: 50%; transform: translateY(-50%); z-index: 999; display: flex; flex-direction: column; gap: 10px;
        }
        .floating-social-v3 a {
            width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.2rem; transition: all .3s; box-shadow: 0 4px 12px rgba(0,0,0,.3); text-decoration: none !important; border: 1px solid rgba(255,255,255,.1);
        }
        .floating-social-v3 a[data-platform="facebook"] { background: #1877F2; border-color: #1877F2; }
        .floating-social-v3 a[data-platform="instagram"] { background: linear-gradient(135deg, #833AB4, #E4405F, #FCAF45); border-color: #E4405F; }
        .floating-social-v3 a[data-platform="twitter"] { background: #0F1419; border-color: #0F1419; }
        .floating-social-v3 a[data-platform="tiktok"] { background: #000000; border-color: #000000; }
        .floating-social-v3 a[data-platform="linkedin"] { background: #0A66C2; border-color: #0A66C2; }
        .floating-social-v3 a[data-platform="youtube"] { background: #FF0000; border-color: #FF0000; }
        .floating-social-v3 a[data-platform="whatsapp"] { background: #25D366; border-color: #25D366; }
        .floating-social-v3 a:hover { transform: scale(1.15) translateX(5px); box-shadow: 0 6px 24px rgba(0,0,0,.4); }
        @media (max-width:768px) { .floating-social-v3 { left: 10px; bottom: 100px; top: auto; transform: none; } .floating-social-v3 a { width: 38px; height: 38px; font-size: 1rem; } }
    </style>

    @stack('styles')

    <script>if('serviceWorker' in navigator&&window.location.hostname!=='localhost'){navigator.serviceWorker.getRegistrations().then(function(r){r.forEach(function(x){x.unregister()})}).then(function(){navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(function(){})})}</script>
    </script>
    <script>window.basePath="{{ rtrim(url('/'), '/') }}";</script>
    <style>html body{background-color:#09090b !important;color:#fafafa !important;}</style>
</head>
<body class="antialiased" style="background-color: var(--surface) !important; color: var(--ink);">

    {{-- Floating Leaf Particles --}}
    <div class="leaf-particle" style="top:15%; right:10%; animation-delay:0s;">🍃</div>
    <div class="leaf-particle" style="top:40%; left:15%; animation-delay:4s; font-size:1.5rem;">🌿</div>
    <div class="leaf-particle" style="top:60%; right:20%; animation-delay:8s; font-size:1.2rem;">🍂</div>
    <div class="leaf-particle" style="top:80%; left:25%; animation-delay:12s;">🌱</div>

    <a href="#main-content" class="skip-link">الانتقال إلى المحتوى الرئيسي</a>
    @if($tracking->isEnabled()) {!! $tracking->getBrowserPixelNoscript() !!} @endif

    @include('frontend.layouts.organic-spa.header')

    <div class="header-spacer"></div>
    <main id="main-content" class="main-content-v3">
        @yield('content')
    </main>

    @include('frontend.layouts.organic-spa.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="{{ asset('js/behavioral-analysis.js') }}?v=4" defer></script>
    <script src="{{ asset('js/app.js') }}?v=4"></script>
    @if($tracking->isEnabled()) {!! $tracking->getBrowserPixelScript() !!} @endif
    @stack('scripts')

    <div class="modal fade" id="quickViewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content spa-card" style="color:var(--ink);">
                <div class="modal-header" style="border:none;padding:16px 20px;">
                    <button type="button" class="btn-close btn-close-white ms-0 me-auto" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="quickViewBody" style="padding:0 20px 24px;">
                    <div class="text-center py-4"><div class="spinner-border" style="color:var(--brand-500);"></div></div>
                </div>
            </div>
        </div>
    </div>

    <div id="searchDropdown" style="display:none;position:absolute;top:100%;right:0;left:0;border-radius:16px;box-shadow:0 12px 40px rgba(0,0,0,.4);z-index:9999;max-height:420px;overflow-y:auto;margin-top:4px;background:var(--surface-alt);border:2px solid var(--glass-border);"></div>

    <script>
    // Search, QuickView, Wishlist — same as Cyber Lab
    let searchTimer;
    const searchInputs = document.querySelectorAll('#searchOverlayV3 input[name="search"], #mobileMenuV3 input[name="search"]');
    const searchDropdown = document.getElementById('searchDropdown');
    if (searchInputs.length && searchDropdown) {
        searchInputs.forEach(function(si) {
            si.setAttribute('autocomplete', 'off');
            si.addEventListener('input', function() {
                clearTimeout(searchTimer);
                const q = this.value.trim();
                if (q.length < 2) { searchDropdown.style.display = 'none'; return; }
                searchTimer = setTimeout(() => {
                    const basePath = window.basePath || '';
                    fetch(basePath + '/api/search?q=' + encodeURIComponent(q)).then(r => r.json()).then(data => {
                        if (!data.length) { searchDropdown.style.display = 'none'; return; }
                        searchDropdown.innerHTML = data.map(p => '<a href="'+p.url+'" style="display:flex;align-items:center;gap:12px;padding:10px 14px;text-decoration:none;color:var(--ink);transition:background .15s;border-bottom:1px solid var(--glass-border);" onmouseover="this.style.background=\'rgba(255,255,255,0.05)\'" onmouseout="this.style.background=\'\'">'+(p.image?'<img src="'+p.image+'" style="width:44px;height:44px;border-radius:12px;object-fit:cover;">':'<div style="width:44px;height:44px;border-radius:12px;background:#1a1a1a;"></div>')+'<div style="flex:1;min-width:0;"><div style="font-weight:600;font-size:.85rem;">'+p.name+'</div><div style="font-size:.8rem;font-weight:700;color:var(--brand-500);">'+p.price+'</div></div></a>').join('');
                        searchDropdown.style.display = 'block';
                        const rect = this.getBoundingClientRect();
                        searchDropdown.style.top = (rect.bottom + window.scrollY) + 'px';
                        searchDropdown.style.left = rect.left + 'px'; searchDropdown.style.right = 'auto'; searchDropdown.style.width = rect.width + 'px';
                    });
                }, 300);
            });
        });
        document.addEventListener('click', e => { let inside = false; searchInputs.forEach(inp => { if (inp.contains(e.target)) inside = true; }); if (!inside && !searchDropdown.contains(e.target)) searchDropdown.style.display = 'none'; });
    }

    async function quickView(productId) {
        const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
        const body = document.getElementById('quickViewBody');
        body.innerHTML = '<div class="text-center py-4"><div class="spinner-border" style="color:var(--brand-500);"></div></div>';
        modal.show();
        try {
            const r = await fetch((window.basePath||'') + '/api/product/' + productId + '/quickview');
            const p = await r.json();
            body.innerHTML = '<div class="row g-4"><div class="col-md-5">'+(p.image?'<img src="'+p.image+'" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:16px;">':'<div style="width:100%;aspect-ratio:1/1;background:var(--surface-alt);border-radius:16px;display:flex;align-items:center;justify-content:center;"><i class="ph ph-package" style="font-size:3rem;color:#333;"></i></div>')+'</div><div class="col-md-7">'+(p.category?'<div style="font-size:.75rem;color:var(--brand-500);font-weight:600;margin-bottom:4px;">'+p.category+'</div>':'')+'<h4 style="font-weight:800;margin-bottom:8px;color:var(--ink);">'+p.name+'</h4><div style="font-size:1.3rem;font-weight:800;color:var(--brand-500);margin-bottom:8px;">'+p.price+' ₪</div><div style="background:'+(p.stock.includes('نفذ')?'rgba(220,38,38,.2)':p.stock.includes('تبقى')?'rgba(217,119,6,.2)':'rgba(22,163,74,.2)')+';color:'+(p.stock.includes('نفذ')?'#EF4444':p.stock.includes('تبقى')?'#F59E0B':'#22C55E')+';padding:4px 12px;border-radius:50px;display:inline-block;font-size:.8rem;font-weight:600;margin-bottom:12px;">'+p.stock+'</div>'+(p.description?'<p style="color:var(--ink-muted);font-size:.9rem;line-height:1.7;margin-bottom:16px;">'+p.description+'</p>':'')+'<div class="d-flex gap-2 flex-wrap"><button onclick="addToCart('+p.id+')" class="btn" style="background:var(--ink);color:var(--surface);border-radius:16px;font-weight:700;">أضف للسلة</button><a href="https://wa.me/{{ $siteSettings['whatsapp_number'] ?? '970591234567' }}?text='+encodeURIComponent('السلام عليكم: '+p.name+' - '+p.price)+'" target="_blank" class="btn" style="border:2px solid #25D366;color:#25D366;border-radius:16px;font-weight:700;">واتساب</a><a href="'+p.url+'" class="btn" style="border:2px solid var(--glass-border);color:var(--ink);border-radius:16px;font-weight:700;">تفاصيل</a></div></div></div>';
        } catch(e) { body.innerHTML = '<div class="text-center py-4 text-red-400">تعذر تحميل المنتج</div>'; }
    }

    async function toggleWishlist(productId, btn) {
        try {
            const r = await fetch((window.basePath||'') + '/wishlist/toggle', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}, body:JSON.stringify({product_id:productId}) });
            const d = await r.json();
            if (d.success) { const icon = btn.querySelector('i'); if (d.action==='added') { icon.className='fas fa-heart'; btn.style.color='#EF4444'; } else { icon.className='far fa-heart'; btn.style.color='var(--ink-dim)'; } showNotification('success', d.message); }
            else if (r.status===401) window.location='/login';
        } catch(e) {}
    }

    (function() { const pl = document.querySelector('a[href*="/product/"]'); if (!pl) return; const slug = pl.getAttribute('href').split('/product/')[1]; if (!slug) return; let v = JSON.parse(localStorage.getItem('recentlyViewed')||'[]'); v = v.filter(p=>p.slug!==slug); const nm = document.querySelector('h1')?.textContent?.trim()||document.title; const im = document.querySelector('.product-detail img[src], img.object-cover')?.src||''; v.unshift({slug,nm:nm,img:im,price:''}); if(v.length>8)v=v.slice(0,8); localStorage.setItem('recentlyViewed',JSON.stringify(v)); })();
    </script>

    {{-- Floating Social Sidebar --}}
    <div class="floating-social-v3">
        @if(!empty($siteSettings['facebook_url']))<a href="{{ $siteSettings['facebook_url'] }}" data-platform="facebook" style="background:#1877F2;" target="_blank" aria-label="فيسبوك"><i class="ph-fill ph-facebook-logo"></i></a>@endif
        @if(!empty($siteSettings['instagram_url']))<a href="{{ $siteSettings['instagram_url'] }}" data-platform="instagram" style="background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);" target="_blank" aria-label="إنستغرام"><i class="ph-fill ph-instagram-logo"></i></a>@endif
        @if(!empty($siteSettings['twitter_url']))<a href="{{ $siteSettings['twitter_url'] }}" data-platform="twitter" style="background:#1DA1F2;" target="_blank" aria-label="تويتر"><i class="ph-fill ph-twitter-logo"></i></a>@endif
        @if(!empty($siteSettings['tiktok_url']))<a href="{{ $siteSettings['tiktok_url'] }}" data-platform="tiktok" style="background:#000;" target="_blank" aria-label="تيك توك"><i class="ph-fill ph-tiktok-logo"></i></a>@endif
        @if(!empty($siteSettings['linkedin_url']))<a href="{{ $siteSettings['linkedin_url'] }}" data-platform="linkedin" style="background:#0A66C2;" target="_blank" aria-label="لينكد إن"><i class="ph-fill ph-linkedin-logo"></i></a>@endif
        @if(!empty($siteSettings['youtube_url']))<a href="{{ $siteSettings['youtube_url'] }}" data-platform="youtube" style="background:#FF0000;" target="_blank" aria-label="يوتيوب"><i class="ph-fill ph-youtube-logo"></i></a>@endif
        @if(!empty($siteSettings['whatsapp_number']))<a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings['whatsapp_number']) }}" data-platform="whatsapp" style="background:#25D366;" target="_blank" aria-label="واتساب"><i class="ph-fill ph-whatsapp-logo"></i></a>@endif
    </div>

    <style>
    .skip-link { position:absolute; top:-40px; left:0; background:var(--ink); color:var(--surface); padding:8px 16px; z-index:100; border-radius:0 0 8px 0; transition:top .3s; font-weight:700; }
    .skip-link:focus { top:0; }
    .header-spacer { height:56px; }
    .main-content-v3 { min-height:60vh; }
    @media(min-width:768px){.header-spacer{height:64px;}}
    @media(min-width:1024px){.header-spacer{height:80px;}}
    </style>
    <script>
    (function() {
        function adjustSpacer() {
            var header = document.getElementById('mainHeaderV3');
            var spacer = document.querySelector('.header-spacer');
            if (header && spacer) {
                spacer.style.height = header.getBoundingClientRect().height + 'px';
            }
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', adjustSpacer);
        } else {
            adjustSpacer();
        }
        window.addEventListener('resize', adjustSpacer);
        window.addEventListener('load', adjustSpacer);
    })();
    </script>
@include('frontend.layouts.partials.theme-switcher')
</body>
</html>
