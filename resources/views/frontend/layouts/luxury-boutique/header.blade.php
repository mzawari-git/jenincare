@php if(!isset($headerCategories)){$headerCategories=\App\Models\Category::active()->withCount(['products'=>fn($q)=>$q->where('is_active',true)])->having('products_count','>',0)->orderBy('sort_order')->get()->map(function($cat){$arName=preg_replace('/[a-zA-Z&\-\(\)]+/','',$cat->name_ar);$arName=preg_replace('/\s{2,}/',' ',trim($arName));$cat->ar_label=!empty($arName)?$arName:$cat->name_ar;return $cat;});} @endphp

<header class="fixed top-0 w-full z-50" id="mainHeaderV3" style="background: var(--header-bg); border-bottom: 1px solid var(--glass-border); transition: background 0.3s;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-8 flex-1">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-black tracking-tight flex-shrink-0" style="color: var(--ink);">
                @if(!empty($siteSettings['site_logo_url']))<img src="{{ $siteSettings['site_logo_url'] }}" alt="{{ $siteSettings['site_name']??'JeniCare' }}" class="h-9 w-auto object-contain brightness-0 invert">@else{{ $siteSettings['site_name_ar']??$siteSettings['site_name']??'JeniCare' }}<span class="text-brand-500">.</span>@endif
            </a>
            <nav class="hidden lg:flex items-center gap-8 text-xs font-bold tracking-widest uppercase">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home')?'active':'' }}">الرئيسية</a>
                <a href="{{ route('shop') }}" class="nav-link {{ request()->routeIs('shop')?'active':'' }}">المتجر</a>
                <a href="{{ route('b2b') }}" class="nav-link">الأعمال</a>
                <a href="{{ route('contact') }}" class="nav-link">تواصل</a>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="toggleSearchV3()" class="icon-btn" aria-label="بحث"><i class="ph ph-magnifying-glass text-lg"></i></button>
            <a href="{{ route('cart') }}" class="icon-btn relative" aria-label="السلة"><i class="ph ph-shopping-bag text-lg"></i><span class="absolute -top-0.5 -right-0.5 bg-brand-500 text-white text-[9px] font-bold h-4 w-4 rounded-full flex items-center justify-center" id="cart-count-v3">{{ $cartCount??0 }}</span></a>
            @auth<a href="{{ route('account') }}" class="hidden sm:flex items-center gap-1.5 text-xs font-medium nav-link"><i class="ph ph-user-circle"></i> حسابي</a>@else<a href="{{ route('login') }}" class="hidden sm:inline-flex btn-ghost text-xs">دخول</a>@endauth
            <a href="{{ route('shop') }}" class="btn-primary text-xs hidden md:inline-flex"><i class="ph ph-storefront"></i> تسوق</a>
            <button onclick="toggleMobileMenuV3()" class="lg:hidden icon-btn"><i class="ph ph-list text-xl" id="mobileMenuIconV3"></i></button>
        </div>
    </div>
    <div class="hidden lg:block border-t" style="border-color:var(--glass-border);overflow:hidden;">
        <div class="marquee-track">
            @foreach($headerCategories as $cat)<a href="{{ route('shop',['category'=>$cat->slug]) }}" class="marquee-item">{{ $cat->ar_label }}</a>@endforeach
            @foreach($headerCategories as $cat)<a href="{{ route('shop',['category'=>$cat->slug]) }}" class="marquee-item">{{ $cat->ar_label }}</a>@endforeach
        </div>
    </div>
</header>

<div id="searchOverlayV3" class="fixed inset-0 z-[60] hidden items-start justify-center pt-32" style="background:rgba(0,0,0,0.8);backdrop-filter:blur(4px);"><div class="luxury-card rounded-xl w-full max-w-lg mx-4 p-6"><button onclick="toggleSearchV3()" class="absolute top-3 left-3 text-ink-dim text-xl">&times;</button><form action="{{ route('shop') }}" method="GET" class="flex gap-2"><input type="text" name="search" placeholder="ابحثي..." autofocus class="flex-1 bg-transparent border-b-2 border-white/10 px-2 py-3 text-sm focus:outline-none focus:border-brand-500" style="color:var(--ink);"><button type="submit" class="btn-primary">بحث</button></form></div></div>

<div id="mobileMenuV3" class="fixed inset-0 z-[60] hidden"><div class="absolute inset-0" style="background:rgba(0,0,0,0.6);" onclick="toggleMobileMenuV3()"></div><div class="absolute top-0 right-0 w-72 h-full shadow-2xl transform translate-x-full transition-transform duration-300 p-6" id="mobileMenuPanelV3" style="background:var(--surface-alt);border-radius:1.5rem 0 0 1.5rem;"><div class="flex justify-between items-center mb-6"><span class="text-lg font-black" style="color:var(--ink);">{{ $siteSettings['site_name']??'JeniCare' }}<span class="text-brand-500">.</span></span><button onclick="toggleMobileMenuV3()" class="icon-btn"><i class="ph ph-x text-xl"></i></button></div><a href="{{ route('shop') }}" class="btn-primary w-full justify-center mb-4"><i class="ph ph-storefront"></i> تسوق</a><nav class="space-y-0 mb-4 border-t pt-3" style="border-color:var(--glass-border);"><a href="{{ route('home') }}" class="mobile-link"><i class="ph ph-house"></i> الرئيسية</a><a href="{{ route('shop') }}" class="mobile-link"><i class="ph ph-storefront"></i> المتجر</a><a href="{{ route('b2b') }}" class="mobile-link"><i class="ph ph-buildings"></i> أعمال</a><a href="{{ route('contact') }}" class="mobile-link"><i class="ph ph-envelope"></i> تواصل</a></nav>@auth<a href="{{ route('account') }}" class="mobile-link"><i class="ph ph-user-circle"></i> حسابي</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="mobile-link text-red-400 w-full text-right">خروج</button></form>@else<a href="{{ route('login') }}" class="mobile-link"><i class="ph ph-sign-in"></i> دخول</a>@endauth</div></div>

<style>
.nav-link{color:var(--ink-dim);border:none;background:none;cursor:pointer;transition:color .2s;text-decoration:none!important;font-weight:700;font-size:.75rem;letter-spacing:.1em;}.nav-link:hover,.nav-link.active{color:var(--brand-500)!important;}
.icon-btn{display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:12px;background:transparent;border:1px solid transparent;color:var(--ink-dim);cursor:pointer;transition:all .2s;text-decoration:none!important;}.icon-btn:hover{background:rgba(255,255,255,.05);color:var(--brand-500);border-color:var(--glass-border);}
.btn-primary{display:inline-flex;align-items:center;gap:6px;padding:8px 20px;border-radius:9999px;font-weight:700;font-size:.8125rem;background:var(--gradient-primary);color:#fff;border:none;cursor:pointer;text-decoration:none!important;transition:all .25s;box-shadow:0 2px 12px rgba(0,0,0,.2);}.btn-primary:hover{box-shadow:0 4px 20px rgba(0,0,0,.3);transform:translateY(-1px);}
.btn-ghost{display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:9999px;font-weight:600;font-size:.8125rem;text-decoration:none!important;background:transparent;border:1px solid var(--glass-border);color:var(--ink);}.btn-ghost:hover{border-color:var(--brand-500);color:var(--brand-500);}
.marquee-item{flex-shrink:0;padding:5px 16px;font-size:.6875rem;font-weight:600;color:var(--ink-dim);text-decoration:none!important;transition:color .2s;border-radius:9999px;}.marquee-item:hover{color:var(--brand-500);}.marquee-track:hover{animation-play-state:paused;}
.marquee-track{display:flex;white-space:nowrap;animation:catMarquee 28s linear infinite;}
@keyframes catMarquee{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.mobile-link{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:12px;font-size:.875rem;font-weight:600;color:var(--ink-dim);text-decoration:none!important;}.mobile-link:hover{background:rgba(255,255,255,.05);color:var(--brand-500);}
</style>

<script>
function toggleSearchV3(){var o=document.getElementById('searchOverlayV3');o.classList.contains('hidden')?(o.classList.remove('hidden'),o.classList.add('flex'),o.querySelector('input')?.focus()):(o.classList.add('hidden'),o.classList.remove('flex'));}
document.getElementById('searchOverlayV3')?.addEventListener('click',function(e){if(e.target===this)toggleSearchV3();});
function toggleMobileMenuV3(){var m=document.getElementById('mobileMenuV3'),p=document.getElementById('mobileMenuPanelV3'),i=document.getElementById('mobileMenuIconV3');m.classList.contains('hidden')?(m.classList.remove('hidden'),setTimeout(function(){p.style.transform='translateX(0)'},10),i.className='ph ph-x text-xl'):(p.style.transform='translateX(100%)',setTimeout(function(){m.classList.add('hidden')},300),i.className='ph ph-list text-xl');}
document.addEventListener('keydown',function(e){if(e.key==='Escape'){var s=document.getElementById('searchOverlayV3');if(s&&!s.classList.contains('hidden'))toggleSearchV3();var m=document.getElementById('mobileMenuV3');if(m&&!m.classList.contains('hidden'))toggleMobileMenuV3();}});
</script>
