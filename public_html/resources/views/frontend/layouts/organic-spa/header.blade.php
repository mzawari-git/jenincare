@php if(!isset($headerCategories)){$headerCategories=\App\Models\Category::active()->withCount(['products'=>fn($q)=>$q->where('is_active',true)])->having('products_count','>',0)->orderBy('sort_order')->get()->map(function($cat){$arName=preg_replace('/[a-zA-Z&\-\(\)]+/','',$cat->name_ar);$arName=preg_replace('/\s{2,}/',' ',trim($arName));$cat->ar_label=!empty($arName)?$arName:$cat->name_ar;return $cat;});} @endphp

<header class="fixed top-0 w-full z-50 border-b-2" id="mainHeaderV3" style="background: rgba(12, 10, 12, 0.92) !important; backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); border-color: var(--glass-border); border-radius: 0 0 2rem 2rem; transition: background 0.3s, border-radius 0.3s;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 md:h-16 flex items-center justify-between">
        <div class="flex items-center gap-8 flex-1">
            <a href="{{ route('home') }}" class="flex items-center gap-2 flex-shrink-0 group" style="color: var(--ink);">
                @if(!empty($siteSettings['site_logo_url']))
                <div class="relative flex items-center justify-center rounded-lg p-1 transition-all duration-300 group-hover:scale-105" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);">
                    <img src="{{ $siteSettings['site_logo_url'] }}" alt="{{ $siteSettings['site_name']??'شركة جنين للتجميل' }}" class="h-6 md:h-10 w-auto object-contain" style="max-height:40px;max-width:120px;">
                </div>
                @else
                <span class="text-lg md:text-xl font-black tracking-tight">{{ $siteSettings['site_name_ar']??$siteSettings['site_name']??'شركة جنين للتجميل' }}<span class="text-brand-500">.</span></span>
                @endif
            </a>
            <nav class="hidden lg:flex items-center gap-6 text-sm font-bold">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home')?'active':'' }}">الرئيسية</a>
                <a href="{{ route('shop') }}" class="nav-link {{ request()->routeIs('shop')?'active':'' }}">المتجر</a>
                <a href="{{ route('b2b') }}" class="nav-link">الأعمال</a>
                <a href="{{ route('affiliate.landing') }}" class="nav-link" style="color:#ec4899;">تسويق</a>
                <a href="{{ route('blog.index') }}" class="nav-link">مدونة</a>
                <a href="{{ route('contact') }}" class="nav-link">تواصل</a>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="toggleSearchV3()" class="icon-btn" aria-label="بحث"><i class="ph ph-magnifying-glass text-lg"></i></button>
            <a href="{{ route('cart') }}" class="icon-btn relative" aria-label="السلة"><i class="ph ph-shopping-bag text-lg"></i><span class="absolute -top-0.5 -right-0.5 bg-brand-500 text-white text-[9px] font-bold h-4 w-4 rounded-full flex items-center justify-center" id="cart-count-v3">{{ $cartCount??0 }}</span></a>
            @auth<a href="{{ route('account') }}" class="hidden sm:flex items-center gap-1.5 text-sm font-medium nav-link"><i class="ph ph-user-circle"></i> حسابي</a>@else<a href="{{ route('login') }}" class="hidden sm:inline-flex btn-ghost text-sm">دخول</a>@endauth
            <a href="{{ route('shop') }}" class="btn-primary text-sm inline-flex"><i class="ph ph-storefront"></i> <span class="hidden sm:inline">تسوق الآن</span></a>
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

<div id="searchOverlayV3" class="fixed inset-0 z-[60] hidden items-start justify-center pt-32" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);">
    <div class="spa-card rounded-2xl w-full max-w-lg mx-4 p-6"><button onclick="toggleSearchV3()" class="absolute top-3 left-3 text-ink-dim text-xl">&times;</button><form action="{{ route('shop') }}" method="GET" class="flex gap-2"><input type="text" name="search" placeholder="ابحثي عن منتج..." autofocus class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-500" style="color:var(--ink);"><button type="submit" class="btn-primary">بحث</button></form></div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     PROFESSIONAL MOBILE MENU V3
     ═══════════════════════════════════════════════════════════ --}}
<div id="mobileMenuV3" class="fixed inset-0 z-[60] hidden" style="direction:rtl;">
    <div class="absolute inset-0" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);" onclick="toggleMobileMenuV3()"></div>
    <div class="absolute top-0 right-0 w-[300px] sm:w-80 h-full shadow-2xl transform translate-x-full transition-transform duration-300 flex flex-col" id="mobileMenuPanelV3" style="background:var(--surface);">

        {{-- Header with gradient --}}
        <div class="relative overflow-hidden px-5 py-5 border-b shrink-0" style="border-color:rgba(255,255,255,0.06);background:linear-gradient(135deg,var(--surface) 0%,rgba(var(--brand-500-rgb,255,42,133),0.08) 100%);">
            <div class="flex items-center justify-between relative z-10">
                <div class="flex items-center gap-2.5">
                    @if(!empty($siteSettings['site_logo_url']))
                        <img src="{{ $siteSettings['site_logo_url'] }}" alt="شركة جنين للتجميل" class="h-8 w-auto object-contain" style="max-height:32px;max-width:120px;">
                    @else
                        <span class="text-lg font-black" style="color:var(--ink);">{{ $siteSettings['site_name']??'شركة جنين للتجميل' }}</span>
                    @endif
                </div>
                <button onclick="toggleMobileMenuV3()" class="w-9 h-9 rounded-full flex items-center justify-center" style="background:var(--surface-alt);color:var(--ink-dim);border:none;cursor:pointer;">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>
            <p class="text-[10px] mt-3 font-bold tracking-wider uppercase" style="color:var(--brand-500);">منتجات أصلية · جمال لا يُقاوم</p>
        </div>

        {{-- Marketing Banner CTA --}}
        <div class="px-4 pt-4 shrink-0">
            <a href="{{ route('shop') }}" class="flex items-center justify-center gap-2 py-3 rounded-xl font-bold text-sm" style="background:linear-gradient(135deg,#ec4899,#be185d);color:#fff;text-decoration:none;box-shadow:0 4px 20px rgba(236,72,153,0.3);">
                <i class="ph ph-sparkle text-lg"></i>
                <span>تصفحي أحدث المنتجات</span>
            </a>
        </div>

        {{-- Navigation --}}
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-1 min-h-0">
            <p class="text-[10px] font-bold tracking-wider uppercase mb-2 mt-2 px-1" style="color:var(--ink-dim);">القائمة الرئيسية</p>

            <a href="{{ route('home') }}" class="mobile-link {{ request()->routeIs('home')?'active':'' }}">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-house text-base" style="color:var(--ink-dim);"></i></span>
                <span>الرئيسية</span>
                @if(request()->routeIs('home'))<i class="ph ph-caret-left ms-auto" style="color:var(--brand-500);"></i>@endif
            </a>
            <a href="{{ route('shop') }}" class="mobile-link {{ request()->routeIs('shop')?'active':'' }}">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-storefront text-base" style="color:var(--ink-dim);"></i></span>
                <span>المتجر</span>
                @if(request()->routeIs('shop'))<i class="ph ph-caret-left ms-auto" style="color:var(--brand-500);"></i>@endif
            </a>
            <a href="{{ route('b2b') }}" class="mobile-link {{ request()->routeIs('b2b')?'active':'' }}">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-buildings text-base" style="color:var(--ink-dim);"></i></span>
                <span>للأعمال</span>
                @if(request()->routeIs('b2b'))<i class="ph ph-caret-left ms-auto" style="color:var(--brand-500);"></i>@endif
            </a>
            <a href="{{ route('affiliate.landing') }}" class="mobile-link {{ request()->routeIs('affiliate.*')?'active':'' }}">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-handshake text-base" style="color:var(--ink-dim);"></i></span>
                <span>تسويق بالعمولة</span>
                @if(request()->routeIs('affiliate.*'))<i class="ph ph-caret-left ms-auto" style="color:var(--brand-500);"></i>@endif
            </a>
            <a href="{{ route('blog.index') }}" class="mobile-link {{ request()->routeIs('blog.*')?'active':'' }}">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-article text-base" style="color:var(--ink-dim);"></i></span>
                <span>المدونة</span>
                @if(request()->routeIs('blog.*'))<i class="ph ph-caret-left ms-auto" style="color:var(--brand-500);"></i>@endif
            </a>
            <a href="{{ route('contact') }}" class="mobile-link {{ request()->routeIs('contact')?'active':'' }}">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-envelope text-base" style="color:var(--ink-dim);"></i></span>
                <span>تواصل معنا</span>
                @if(request()->routeIs('contact'))<i class="ph ph-caret-left ms-auto" style="color:var(--brand-500);"></i>@endif
            </a>

            @if(isset($headerCategories) && $headerCategories->count())
            <p class="text-[10px] font-bold tracking-wider uppercase mb-2 mt-4 px-1" style="color:var(--ink-dim);">تسوقي حسب القسم</p>
            <div class="space-y-1">
                @foreach($headerCategories->take(6) as $cat)
                <a href="{{ route('shop',['category'=>$cat->slug]) }}" class="mobile-link text-xs">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-tag text-base" style="color:var(--ink-dim);"></i></span>
                    <span>{{ $cat->ar_label }}</span>
                    <span class="ms-auto text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:var(--surface-alt);color:var(--ink-dim);">{{ $cat->products_count }}</span>
                </a>
                @endforeach
            </div>
            @endif

            <div class="border-t mt-4 pt-4" style="border-color:rgba(255,255,255,0.06);">
                <p class="text-[10px] font-bold tracking-wider uppercase mb-2 px-1" style="color:var(--ink-dim);">حسابي</p>
                @auth
                <a href="{{ route('account') }}" class="mobile-link">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-user-circle text-base" style="color:var(--ink-dim);"></i></span>
                    <span>حسابي</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" style="display:contents;">
                    @csrf
                    <button type="submit" class="mobile-link" style="width:100%;background:none;border:none;cursor:pointer;text-align:right;">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-sign-out text-base" style="color:var(--ink-dim);"></i></span>
                        <span>تسجيل خروج</span>
                    </button>
                </form>
                @else
                <a href="{{ route('login') }}" class="mobile-link">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-sign-in text-base" style="color:var(--ink-dim);"></i></span>
                    <span>تسجيل دخول</span>
                </a>
                <a href="{{ route('register') }}" class="mobile-link">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--surface-alt);"><i class="ph ph-user-plus text-base" style="color:var(--ink-dim);"></i></span>
                    <span>إنشاء حساب جديد</span>
                </a>
                @endauth
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-4 py-4 border-t shrink-0" style="border-color:rgba(255,255,255,0.06);background:var(--surface-alt);">
            <div class="flex items-center justify-between">
                <div class="flex gap-3">
                    @if(!empty($siteSettings['whatsapp_number']))
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings['whatsapp_number']) }}" target="_blank" class="w-9 h-9 rounded-full flex items-center justify-center" style="background:#25D366;color:#fff;"><i class="ph-fill ph-whatsapp-logo text-lg"></i></a>
                    @endif
                    @if(!empty($siteSettings['instagram_url']))
                    <a href="{{ $siteSettings['instagram_url'] }}" target="_blank" class="w-9 h-9 rounded-full flex items-center justify-center" style="background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);color:#fff;"><i class="ph-fill ph-instagram-logo text-lg"></i></a>
                    @endif
                </div>
                <span class="text-[10px] font-bold" style="color:var(--ink-dim);">© {{ date('Y') }}</span>
            </div>
        </div>
    </div>
</div>

<style>
.nav-link{color:var(--ink-muted);border:none;background:none;cursor:pointer;transition:color .2s;text-decoration:none!important;padding:4px 0;}.nav-link:hover,.nav-link.active{color:var(--brand-500)!important;}
.icon-btn{display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:50%;background:transparent;border:1px solid transparent;color:var(--ink-muted);cursor:pointer;transition:all .2s;text-decoration:none!important;}.icon-btn:hover{background:rgba(255,255,255,.06);color:var(--brand-500);border-color:rgba(255,255,255,.08);}
.btn-primary{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:9999px;font-weight:700;font-size:.8125rem;background:var(--gradient-primary);color:#fff;border:none;cursor:pointer;text-decoration:none!important;transition:all .25s;box-shadow:var(--neon-glow);}.btn-primary:hover{box-shadow:var(--neon-glow-strong);transform:translateY(-1px);filter:brightness(1.1);}
.btn-ghost{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:9999px;font-weight:600;text-decoration:none!important;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:var(--ink);transition:all .2s;}.btn-ghost:hover{background:rgba(255,255,255,.1);border-color:var(--brand-500);}
.marquee-item{flex-shrink:0;padding:5px 18px;font-size:.6875rem;font-weight:600;color:var(--ink-dim);text-decoration:none!important;transition:color .2s;border-radius:9999px;}.marquee-item:hover{color:var(--brand-500);}.marquee-track:hover{animation-play-state:paused;}
.marquee-track{display:flex;white-space:nowrap;animation:catMarquee 30s linear infinite;}
@keyframes catMarquee{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.mobile-link{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;font-size:.875rem;font-weight:600;color:var(--ink-dim);text-decoration:none!important;transition:all .15s;}.mobile-link:hover{background:rgba(255,255,255,.05);color:var(--ink);}
.mobile-link.active{background:rgba(var(--brand-500-rgb,255,42,133),0.08);color:var(--brand-500)!important;border:1px solid rgba(var(--brand-500-rgb,255,42,133),0.15);}
</style>

<script>
function toggleSearchV3(){var o=document.getElementById('searchOverlayV3');o.classList.contains('hidden')?(o.classList.remove('hidden'),o.classList.add('flex'),o.querySelector('input')?.focus()):(o.classList.add('hidden'),o.classList.remove('flex'));}
document.getElementById('searchOverlayV3')?.addEventListener('click',function(e){if(e.target===this)toggleSearchV3();});
function toggleMobileMenuV3(){var m=document.getElementById('mobileMenuV3'),p=document.getElementById('mobileMenuPanelV3'),i=document.getElementById('mobileMenuIconV3');m.classList.contains('hidden')?(m.classList.remove('hidden'),setTimeout(function(){p.style.transform='translateX(0)'},10),i.className='ph ph-x text-xl'):(p.style.transform='translateX(100%)',setTimeout(function(){m.classList.add('hidden')},300),i.className='ph ph-list text-xl');}
window.addEventListener('scroll',function(){var h=document.getElementById('mainHeaderV3');if(!h)return;h.style.background=window.scrollY>50?'rgba(5,10,8,0.95)':'rgba(12,10,12,0.92)';h.style.borderRadius=window.scrollY>50?'0':'0 0 2rem 2rem';});
document.addEventListener('keydown',function(e){if(e.key==='Escape'){var s=document.getElementById('searchOverlayV3');if(s&&!s.classList.contains('hidden'))toggleSearchV3();var m=document.getElementById('mobileMenuV3');if(m&&!m.classList.contains('hidden'))toggleMobileMenuV3();}});
</script>
