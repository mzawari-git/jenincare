@php if(!isset($headerCategories)){$headerCategories=\App\Models\Category::active()->withCount(['products'=>fn($q)=>$q->where('is_active',true)])->having('products_count','>',0)->orderBy('sort_order')->get()->map(function($cat){$arName=preg_replace('/[a-zA-Z&\-\(\)]+/','',$cat->name_ar);$arName=preg_replace('/\s{2,}/',' ',trim($arName));$cat->ar_label=!empty($arName)?$arName:$cat->name_ar;return $cat;});} @endphp

<header class="fixed top-0 w-full z-50" id="mainHeaderV3" style="background: var(--header-bg); border-bottom: 1px solid var(--glass-border); transition: background 0.3s;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-8 flex-1">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-black tracking-tight flex-shrink-0" style="color: var(--ink);">
                {{ $siteSettings['site_name_ar']??$siteSettings['site_name']??'JeniCare' }}<span class="text-brand-500">.</span>
            </a>
            <nav class="hidden lg:flex items-center gap-6 text-sm font-bold">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home')?'active':'' }}">الرئيسية</a>
                <a href="{{ route('shop') }}" class="nav-link {{ request()->routeIs('shop')?'active':'' }}">المتجر</a>
                <a href="{{ route('b2b') }}" class="nav-link">الأعمال</a>
                <a href="{{ route('contact') }}" class="nav-link">تواصل</a>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="toggleSearchV3()" class="icon-btn" aria-label="بحث"><i class="ph ph-magnifying-glass text-lg"></i></button>
            <a href="{{ route('cart') }}" class="icon-btn relative" aria-label="السلة"><i class="ph ph-shopping-bag text-lg"></i><span class="absolute -top-0.5 -right-0.5 bg-brand-500 text-white text-[9px] font-bold h-4 w-4 rounded-full flex items-center justify-center" id="cart-count-v3">{{ $cartCount??0 }}</span></a>
            @auth<a href="{{ route('account') }}" class="hidden sm:flex items-center gap-1.5 text-sm font-medium nav-link"><i class="ph ph-user-circle"></i> حسابي</a>@else<a href="{{ route('login') }}" class="hidden sm:inline-flex btn-ghost text-sm">دخول</a>@endauth
            <a href="{{ route('shop') }}" class="btn-primary text-sm hidden md:inline-flex"><i class="ph ph-storefront"></i> تسوق</a>
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

<div id="searchOverlayV3" class="fixed inset-0 z-[60] hidden items-start justify-center pt-32" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(2px);"><div class="editorial-card rounded-sm w-full max-w-lg mx-4 p-6"><button onclick="toggleSearchV3()" class="absolute top-3 left-3 text-ink-dim text-xl">&times;</button><form action="{{ route('shop') }}" method="GET" class="flex gap-2"><input type="text" name="search" placeholder="ابحث..." autofocus class="flex-1 bg-transparent border-b border-white/10 px-2 py-3 text-sm focus:outline-none focus:border-brand-500" style="color:var(--ink);"><button type="submit" class="btn-primary">بحث</button></form></div></div>

<div id="mobileMenuV3" class="fixed inset-0 z-[60] hidden"><div class="absolute inset-0" style="background:rgba(0,0,0,0.6);" onclick="toggleMobileMenuV3()"></div><div class="absolute top-0 right-0 w-72 h-full shadow-2xl transform translate-x-full transition-transform duration-200 p-6" id="mobileMenuPanelV3" style="background:var(--surface-alt);"><div class="flex justify-between items-center mb-6"><span class="text-lg font-black" style="color:var(--ink);">{{ $siteSettings['site_name']??'JeniCare' }}<span class="text-brand-500">.</span></span><button onclick="toggleMobileMenuV3()" class="icon-btn"><i class="ph ph-x text-xl"></i></button></div><a href="{{ route('shop') }}" class="btn-primary w-full justify-center mb-4">تسوق</a><nav class="space-y-0 mb-4 border-t pt-3" style="border-color:var(--glass-border);"><a href="{{ route('home') }}" class="mobile-link"><i class="ph ph-house"></i> الرئيسية</a><a href="{{ route('shop') }}" class="mobile-link"><i class="ph ph-storefront"></i> المتجر</a><a href="{{ route('b2b') }}" class="mobile-link"><i class="ph ph-buildings"></i> أعمال</a><a href="{{ route('contact') }}" class="mobile-link"><i class="ph ph-envelope"></i> تواصل</a></nav>@auth<a href="{{ route('account') }}" class="mobile-link"><i class="ph ph-user-circle"></i> حسابي</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="mobile-link text-red-400 w-full text-right">خروج</button></form>@else<a href="{{ route('login') }}" class="mobile-link"><i class="ph ph-sign-in"></i> دخول</a>@endauth</div></div>

<style>
.nav-link{color:var(--ink-dim);border:none;background:none;cursor:pointer;transition:color .2s;text-decoration:none!important;text-transform:uppercase;letter-spacing:.05em;font-weight:500;font-size:.75rem;padding:4px 0;}.nav-link:hover,.nav-link.active{color:var(--ink)!important;}
.icon-btn{display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:0;background:transparent;border:none;color:var(--ink-dim);cursor:pointer;transition:color .2s;text-decoration:none!important;}.icon-btn:hover{color:var(--ink);}
.btn-primary{display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:0;font-weight:700;font-size:.75rem;background:var(--ink);color:var(--surface);border:none;cursor:pointer;text-decoration:none!important;transition:all .2s;}.btn-primary:hover{background:var(--brand-500);color:#fff;}
.btn-ghost{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;font-weight:500;font-size:.75rem;text-decoration:none!important;color:var(--ink-dim);border:none;background:none;}.btn-ghost:hover{color:var(--ink);}
.marquee-item{flex-shrink:0;padding:4px 14px;font-size:.625rem;font-weight:500;color:var(--ink-dim);text-decoration:none!important;text-transform:uppercase;letter-spacing:.05em;}.marquee-item:hover{color:var(--ink);}.marquee-track:hover{animation-play-state:paused;}
.marquee-track{display:flex;white-space:nowrap;animation:catMarquee 35s linear infinite;}
@keyframes catMarquee{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.mobile-link{display:flex;align-items:center;gap:10px;padding:10px 12px;font-size:.8rem;color:var(--ink-dim);text-decoration:none!important;border-bottom:1px solid rgba(255,255,255,.05);}.mobile-link:hover{color:var(--ink);}
</style>

<script>
function toggleSearchV3(){var o=document.getElementById('searchOverlayV3');o.classList.contains('hidden')?(o.classList.remove('hidden'),o.classList.add('flex'),o.querySelector('input')?.focus()):(o.classList.add('hidden'),o.classList.remove('flex'));}
document.getElementById('searchOverlayV3')?.addEventListener('click',function(e){if(e.target===this)toggleSearchV3();});
function toggleMobileMenuV3(){var m=document.getElementById('mobileMenuV3'),p=document.getElementById('mobileMenuPanelV3'),i=document.getElementById('mobileMenuIconV3');m.classList.contains('hidden')?(m.classList.remove('hidden'),setTimeout(function(){p.style.transform='translateX(0)'},10),i.className='ph ph-x text-xl'):(p.style.transform='translateX(100%)',setTimeout(function(){m.classList.add('hidden')},200),i.className='ph ph-list text-xl');}
document.addEventListener('keydown',function(e){if(e.key==='Escape'){var s=document.getElementById('searchOverlayV3');if(s&&!s.classList.contains('hidden'))toggleSearchV3();var m=document.getElementById('mobileMenuV3');if(m&&!m.classList.contains('hidden'))toggleMobileMenuV3();}});
</script>
