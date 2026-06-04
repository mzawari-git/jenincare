<aside style="background:var(--glass-bg);border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden;">
    <div style="padding:24px 20px;text-align:center;color:var(--ink);border-bottom:1px solid var(--glass-border);">
        <div style="width:64px;height:64px;margin:0 auto 12px;border-radius:50%;background:var(--brand-500);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;">
            <i class="fas fa-user"></i>
        </div>
        <p style="font-weight:700;font-size:1rem;margin-bottom:2px;">{{ Auth::user()->name }}</p>
        <p style="font-size:.8rem;opacity:.7;margin:0;">{{ Auth::user()->email }}</p>
    </div>
    <ul style="list-style:none;padding:0;margin:0;">
        <li style="border-bottom:1px solid var(--glass-border);">
            <a href="{{ route('account') }}" style="display:flex;align-items:center;gap:10px;padding:14px 20px;color:var(--ink);font-size:.9rem;font-weight:500;text-decoration:none;transition:all .2s;{{ request()->routeIs('account') && !request()->routeIs('orders*') && !request()->routeIs('wishlist') && !request()->routeIs('addresses') ? 'background:var(--brand-500);color:#fff;font-weight:600;' : '' }}" onmouseover="this.style.background='{{ request()->routeIs('account') && !request()->routeIs('orders*') && !request()->routeIs('wishlist') && !request()->routeIs('addresses') ? 'var(--brand-500)' : 'var(--brand-500)' }}';this.style.color='{{ request()->routeIs('account') && !request()->routeIs('orders*') && !request()->routeIs('wishlist') && !request()->routeIs('addresses') ? '#fff' : 'var(--brand-500)' }}'" onmouseout="this.style.background='{{ request()->routeIs('account') && !request()->routeIs('orders*') && !request()->routeIs('wishlist') && !request()->routeIs('addresses') ? 'var(--brand-500)' : 'transparent' }}';this.style.color='{{ request()->routeIs('account') && !request()->routeIs('orders*') && !request()->routeIs('wishlist') && !request()->routeIs('addresses') ? '#fff' : 'var(--ink)' }}'">
                <i class="fas fa-tachometer-alt" style="width:20px;text-align:center;"></i> لوحة التحكم
            </a>
        </li>
        <li style="border-bottom:1px solid var(--glass-border);">
            <a href="{{ route('orders') }}" style="display:flex;align-items:center;gap:10px;padding:14px 20px;color:var(--ink);font-size:.9rem;font-weight:500;text-decoration:none;transition:all .2s;{{ request()->routeIs('orders*') ? 'background:var(--brand-500);color:#fff;font-weight:600;' : '' }}" onmouseover="this.style.background='{{ request()->routeIs('orders*') ? 'var(--brand-500)' : 'var(--brand-500)' }}';this.style.color='{{ request()->routeIs('orders*') ? '#fff' : 'var(--brand-500)' }}'" onmouseout="this.style.background='{{ request()->routeIs('orders*') ? 'var(--brand-500)' : 'transparent' }}';this.style.color='{{ request()->routeIs('orders*') ? '#fff' : 'var(--ink)' }}'">
                <i class="fas fa-box" style="width:20px;text-align:center;"></i> طلباتي
            </a>
        </li>
        <li style="border-bottom:1px solid var(--glass-border);">
            <a href="{{ route('wishlist') }}" style="display:flex;align-items:center;gap:10px;padding:14px 20px;color:var(--ink);font-size:.9rem;font-weight:500;text-decoration:none;transition:all .2s;{{ request()->routeIs('wishlist') ? 'background:var(--brand-500);color:#fff;font-weight:600;' : '' }}" onmouseover="this.style.background='{{ request()->routeIs('wishlist') ? 'var(--brand-500)' : 'var(--brand-500)' }}';this.style.color='{{ request()->routeIs('wishlist') ? '#fff' : 'var(--brand-500)' }}'" onmouseout="this.style.background='{{ request()->routeIs('wishlist') ? 'var(--brand-500)' : 'transparent' }}';this.style.color='{{ request()->routeIs('wishlist') ? '#fff' : 'var(--ink)' }}'">
                <i class="fas fa-heart" style="width:20px;text-align:center;"></i> المفضلة
            </a>
        </li>
        <li style="border-bottom:1px solid var(--glass-border);">
            <a href="{{ route('addresses') }}" style="display:flex;align-items:center;gap:10px;padding:14px 20px;color:var(--ink);font-size:.9rem;font-weight:500;text-decoration:none;transition:all .2s;{{ request()->routeIs('addresses') ? 'background:var(--brand-500);color:#fff;font-weight:600;' : '' }}" onmouseover="this.style.background='{{ request()->routeIs('addresses') ? 'var(--brand-500)' : 'var(--brand-500)' }}';this.style.color='{{ request()->routeIs('addresses') ? '#fff' : 'var(--brand-500)' }}'" onmouseout="this.style.background='{{ request()->routeIs('addresses') ? 'var(--brand-500)' : 'transparent' }}';this.style.color='{{ request()->routeIs('addresses') ? '#fff' : 'var(--ink)' }}'">
                <i class="fas fa-map-marker-alt" style="width:20px;text-align:center;"></i> عناويني
            </a>
        </li>
        <li style="border-bottom:1px solid var(--glass-border);">
            <a href="{{ route('affiliate.dashboard') }}" style="display:flex;align-items:center;gap:10px;padding:14px 20px;font-size:.9rem;font-weight:500;text-decoration:none;transition:all .2s;{{ request()->routeIs('affiliate.*') ? 'background:#ec4899;color:#fff;font-weight:600;' : 'color:#ec4899;' }}" onmouseover="this.style.background='#ec4899';this.style.color='#fff'" onmouseout="this.style.background='{{ request()->routeIs('affiliate.*') ? '#ec4899' : 'transparent' }}';this.style.color='{{ request()->routeIs('affiliate.*') ? '#fff' : '#ec4899' }}'">
                <i class="fas fa-hand-holding-usd" style="width:20px;text-align:center;"></i> التسويق بالعمولة
            </a>
        </li>
        <li>
            <a href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();" style="display:flex;align-items:center;gap:10px;padding:14px 20px;color:var(--ink);font-size:.9rem;font-weight:500;text-decoration:none;transition:all .2s;" onmouseover="this.style.background='var(--brand-500)';this.style.color='#EF4444'" onmouseout="this.style.background='transparent';this.style.color='var(--ink)'">
                <i class="fas fa-sign-out-alt" style="width:20px;text-align:center;color:#EF4444;"></i> تسجيل الخروج
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
        </li>
    </ul>
</aside>
