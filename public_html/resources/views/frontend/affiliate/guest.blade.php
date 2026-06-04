@extends('frontend.layouts.editorial.app')

@section('title', 'برنامج التسويق بالعمولة | شركة جنين للتجميل')

@section('content')
<section style="background:#ffffff;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;">
    <div style="text-align:center;max-width:450px;">
        <div style="width:5rem;height:5rem;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
            <i class="ph ph-share-network" style="font-size:2.5rem;color:#b45309;"></i>
        </div>
        <h1 style="font-size:1.5rem;font-weight:900;color:#0f172a;margin-bottom:.75rem;">برنامج التسويق بالعمولة</h1>
        <p style="color:#475569;font-size:1rem;margin-bottom:2rem;line-height:1.7;">انضمي إلى برنامج التسويق بالعمولة واربحِ 10% على كل طلبية عبر رابطك الخاص. كل ما تحتاجينه هو حساب على جنين كير.</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="{{ route('login') }}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.8rem 2rem;border-radius:9999px;font-weight:700;font-size:1rem;background:linear-gradient(135deg,#ec4899,#be185d);color:#fff;text-decoration:none;transition:all .2s;">
                <i class="ph ph-sign-in"></i> تسجيل الدخول
            </a>
            <a href="{{ route('register') }}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.8rem 2rem;border-radius:9999px;font-weight:700;font-size:1rem;border:2px solid #cbd5e1;color:#334155;text-decoration:none;transition:all .2s;">
                <i class="ph ph-user-plus"></i> إنشاء حساب جديد
            </a>
        </div>
        <p style="color:#94a3b8;font-size:.8rem;margin-top:1.5rem;">بعد تسجيل الدخول يتم تفعيل حسابك التسويقي تلقائياً</p>
    </div>
</section>
@endsection
