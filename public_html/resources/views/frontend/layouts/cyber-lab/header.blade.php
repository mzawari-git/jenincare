@php
    if (!isset($headerCategories)) {
        $headerCategories = \App\Models\Category::active()
            ->withCount(['products' => fn($q) => $q->where('is_active', true)])
            ->having('products_count', '>', 0)
            ->orderBy('sort_order')
            ->get()
            ->map(function($cat) {
                $arName = preg_replace('/[a-zA-Z&\-\(\)]+/', '', $cat->name_ar);
                $arName = preg_replace('/\s{2,}/', ' ', trim($arName));
                $cat->ar_label = !empty($arName) ? $arName : $cat->name_ar;
                return $cat;
            });
    }
@endphp

<header class="fixed top-0 w-full z-50 border-b border-white/5" id="mainHeaderV3" style="background: var(--header-bg, rgba(12,12,12,0.55)); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); transition: background 0.3s, box-shadow 0.3s;">
    {{-- Top Row --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-8 flex-1">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-black tracking-tight flex-shrink-0" style="color: var(--ink);">
                @if(!empty($siteSettings['site_logo_url']))
                    <img src="{{ $siteSettings['site_logo_url'] }}" alt="{{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}" class="h-9 w-auto object-contain">
                @else
                    {{ $siteSettings['site_name_ar'] ?? $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}<span class="text-brand-500">.</span>
                @endif
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden lg:flex items-center gap-6 text-sm font-bold">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">الرئيسية</a>
                <a href="{{ route('shop') }}" class="nav-link {{ request()->routeIs('shop') ? 'active' : '' }}">المتجر</a>
                <a href="{{ route('b2b') }}" class="nav-link {{ request()->routeIs('b2b') ? 'active' : '' }}">الأعمال</a>
                <a href="{{ route('affiliate.landing') }}" class="nav-link {{ request()->routeIs('affiliate.*') ? 'active' : '' }}" style="color:#ec4899;">تسويق</a>
                <a href="{{ route('blog.index') }}" class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}">مدونة</a>
                <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">تواصل</a>
            </nav>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button onclick="toggleSearchV3()" class="icon-btn" title="بحث" aria-label="بحث"><i class="ph ph-magnifying-glass text-lg"></i></button>
            <a href="{{ route('cart') }}" class="icon-btn relative" title="السلة" aria-label="السلة"><i class="ph ph-shopping-bag text-lg"></i><span class="absolute -top-0.5 -right-0.5 bg-brand-500 text-white text-[9px] font-bold h-4 w-4 rounded-full flex items-center justify-center" id="cart-count-v3">{{ $cartCount ?? 0 }}</span></a>
            @auth
            <a href="{{ route('account') }}" class="hidden sm:flex items-center gap-1.5 text-sm font-medium nav-link"><i class="ph ph-user-circle"></i> حسابي</a>
            @else
            <a href="{{ route('login') }}" class="hidden sm:inline-flex btn-ghost text-sm"><i class="ph ph-sign-in mr-1"></i> دخول</a>
            @endauth
            <a href="{{ route('shop') }}" class="btn-primary text-sm hidden md:inline-flex"><i class="ph ph-storefront"></i> تسوق الآن</a>
            <button onclick="toggleMobileMenuV3()" class="lg:hidden icon-btn"><i class="ph ph-list text-xl" id="mobileMenuIconV3"></i></button>
        </div>
    </div>

    {{-- Category Marquee (desktop) --}}
    <div class="hidden lg:block border-t border-white/5" style="overflow:hidden;">
        <div class="marquee-track" style="display:flex; white-space:nowrap; animation: catMarquee 30s linear infinite;">
            @foreach($headerCategories as $cat)
            <a href="{{ route('shop', ['category' => $cat->slug]) }}" class="marquee-item">{{ $cat->ar_label }}</a>
            @endforeach
            @foreach($headerCategories as $cat)
            <a href="{{ route('shop', ['category' => $cat->slug]) }}" class="marquee-item">{{ $cat->ar_label }}</a>
            @endforeach
        </div>
    </div>
</header>

{{-- Search Overlay --}}
<div id="searchOverlayV3" class="fixed inset-0 z-[60] hidden items-start justify-center pt-32" style="background:rgba(0,0,0,0.7); backdrop-filter:blur(4px);">
    <div class="glass-panel rounded-2xl w-full max-w-lg mx-4 p-6">
        <button onclick="toggleSearchV3()" class="absolute top-3 left-3 text-ink-dim hover:text-ink text-xl" aria-label="إغلاق">&times;</button>
        <form action="{{ route('shop') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" placeholder="ابحثي عن منتج..." autofocus value="{{ request('search') }}" class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-500" style="color:var(--ink);">
            <button type="submit" class="btn-primary">بحث</button>
        </form>
    </div>
</div>

{{-- Mobile Menu --}}
<div id="mobileMenuV3" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0" style="background:rgba(0,0,0,0.6);" onclick="toggleMobileMenuV3()"></div>
    <div class="absolute top-0 right-0 w-80 h-full shadow-2xl transform translate-x-full transition-transform duration-300 p-0 flex flex-col" id="mobileMenuPanelV3" style="background:var(--surface);">
        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:rgba(255,255,255,0.06);">
            <div class="flex items-center gap-2.5">
                @if(!empty($siteSettings['site_logo_url']))
                <img src="{{ $siteSettings['site_logo_url'] }}" alt="شركة جنين للتجميل" class="h-7 w-auto object-contain">
                @else
                <span class="text-base font-black" style="color:var(--ink);">{{ $siteSettings['site_name'] ?? 'شركة جنين للتجميل' }}</span>
                @endif
            </div>
            <button onclick="toggleMobileMenuV3()" class="w-9 h-9 rounded-full flex items-center justify-center" style="background:var(--surface-alt);color:var(--ink-dim);border:none;cursor:pointer;"><i class="ph ph-x text-lg"></i></button>
        </div>
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-1">
            <a href="{{ route('shop') }}" class="flex items-center justify-center gap-2 py-3 rounded-xl font-bold text-sm mb-4" style="background:linear-gradient(135deg,#ec4899,#be185d);color:#fff;text-decoration:none;"><i class="ph ph-storefront"></i> تصفحي المتجر</a>
            <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium" style="color:var(--ink);text-decoration:none;"><span class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-house text-lg" style="color:var(--ink-dim);"></i></span> الرئيسية</a>
            <a href="{{ route('shop') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium" style="color:var(--ink);text-decoration:none;"><span class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-storefront text-lg" style="color:var(--ink-dim);"></i></span> المتجر</a>
            <a href="{{ route('b2b') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium" style="color:var(--ink);text-decoration:none;"><span class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-buildings text-lg" style="color:var(--ink-dim);"></i></span> الأعمال</a>
            <a href="{{ route('affiliate.landing') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-bold" style="color:#ec4899;text-decoration:none;"><span class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(236,72,153,0.1);"><i class="ph ph-share-network text-lg" style="color:#ec4899;"></i></span> التسويق بالعمولة</a>
            <a href="{{ route('blog.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium" style="color:var(--ink);text-decoration:none;"><span class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-article text-lg" style="color:var(--ink-dim);"></i></span> المدونة</a>
            <a href="{{ route('contact') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium" style="color:var(--ink);text-decoration:none;"><span class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-envelope text-lg" style="color:var(--ink-dim);"></i></span> تواصل معنا</a>
        </div>
        <div class="border-t px-4 py-3" style="border-color:rgba(255,255,255,0.06);">
            @auth
            <a href="{{ route('account') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium" style="color:var(--ink);text-decoration:none;"><span class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-user-circle text-lg" style="color:var(--ink-dim);"></i></span> حسابي</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium w-full" style="color:#ef4444;background:none;border:none;cursor:pointer;"><span class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(239,68,68,0.1);"><i class="ph ph-sign-out text-lg" style="color:#ef4444;"></i></span> تسجيل الخروج</button></form>
            @else
            <a href="{{ route('login') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium" style="color:var(--ink);text-decoration:none;"><span class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-sign-in text-lg" style="color:var(--ink-dim);"></i></span> تسجيل الدخول</a>
            <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 py-3 rounded-xl font-bold text-sm mt-2" style="background:var(--surface-alt);color:var(--ink);text-decoration:none;"><i class="ph ph-user-plus"></i> إنشاء حساب جديد</a>
            @endauth
        </div>
    </div>
</div>
    </div>
</div>

<style>
/* ── Nav Links ── */
.nav-link {
    color: var(--ink-muted, #999);
    border: none;
    background: none;
    cursor: pointer;
    transition: color 0.2s;
    text-decoration: none !important;
    padding: 4px 0;
}
.nav-link:hover, .nav-link.active { color: var(--brand-500) !important; }

/* ── Icon Button ── */
.icon-btn {
    display: flex; align-items: center; justify-content: center;
    width: 38px; height: 38px; border-radius: 50%;
    background: transparent; border: 1px solid transparent;
    color: var(--ink-muted); cursor: pointer;
    transition: all 0.2s; text-decoration: none !important;
}
.icon-btn:hover { background: rgba(255,255,255,0.06); color: var(--brand-500); border-color: rgba(255,255,255,0.08); }

/* ── Primary Button ── */
.btn-primary {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: 9999px;
    font-weight: 700; font-size: 0.8125rem;
    background: var(--gradient-primary);
    color: #fff;
    border: none; cursor: pointer; text-decoration: none !important;
    transition: all 0.25s;
    box-shadow: var(--neon-glow);
}
.btn-primary:hover { box-shadow: var(--neon-glow-strong); transform: translateY(-1px); filter: brightness(1.1); }

/* ── Ghost Button ── */
.btn-ghost {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 9999px;
    font-weight: 600; text-decoration: none !important;
    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
    color: var(--ink); transition: all 0.2s;
}
.btn-ghost:hover { background: rgba(255,255,255,0.1); border-color: var(--brand-500); }

/* ── Marquee ── */
.marquee-item {
    flex-shrink: 0; padding: 5px 18px;
    font-size: 0.6875rem; font-weight: 600;
    color: var(--ink-dim); text-decoration: none !important;
    transition: color 0.2s; border-radius: 9999px;
}
.marquee-item:hover { color: var(--brand-500); }
.marquee-track:hover { animation-play-state: paused; }

@keyframes catMarquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

/* ── Mobile Link ── */
.mobile-link {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 12px; border-radius: 10px;
    font-size: 0.875rem; font-weight: 600;
    color: var(--ink-dim); text-decoration: none !important;
    transition: all 0.15s;
}
.mobile-link:hover, .mobile-link.active { background: rgba(255,255,255,0.05); color: var(--ink); }
</style>

<script>
function toggleSearchV3(){var o=document.getElementById('searchOverlayV3');o.classList.contains('hidden')?(o.classList.remove('hidden'),o.classList.add('flex'),o.querySelector('input')?.focus()):(o.classList.add('hidden'),o.classList.remove('flex'));}
document.getElementById('searchOverlayV3')?.addEventListener('click',function(e){if(e.target===this)toggleSearchV3();});
function toggleMobileMenuV3(){var m=document.getElementById('mobileMenuV3'),p=document.getElementById('mobileMenuPanelV3'),i=document.getElementById('mobileMenuIconV3');m.classList.contains('hidden')?(m.classList.remove('hidden'),setTimeout(function(){p.style.transform='translateX(0)'},10),i.className='ph ph-x text-xl'):(p.style.transform='translateX(100%)',setTimeout(function(){m.classList.add('hidden')},300),i.className='ph ph-list text-xl');}
window.addEventListener('scroll',function(){var h=document.getElementById('mainHeaderV3');if(!h)return;h.style.background=window.scrollY>50?'rgba(5,5,5,0.92)':'var(--header-bg)';h.style.boxShadow=window.scrollY>50?'0 4px 30px rgba(0,0,0,0.4)':'none';});
document.addEventListener('keydown',function(e){if(e.key==='Escape'){var s=document.getElementById('searchOverlayV3');if(s&&!s.classList.contains('hidden'))toggleSearchV3();var m=document.getElementById('mobileMenuV3');if(m&&!m.classList.contains('hidden'))toggleMobileMenuV3();}});
</script>
