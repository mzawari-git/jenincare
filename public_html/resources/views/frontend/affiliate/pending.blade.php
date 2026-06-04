@extends('frontend.layouts.editorial.app')

@section('title', 'قيد المراجعة | شركة جنين للتجميل')

@section('content')
<section style="background:#ffffff;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem;">
    <div style="text-align:center;max-width:400px;">
        <div style="width:5rem;height:5rem;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
            <i class="ph ph-clock" style="font-size:2.5rem;color:#b45309;"></i>
        </div>
        <h1 style="font-size:1.5rem;font-weight:900;color:#0f172a;margin-bottom:.75rem;">حسابك قيد المراجعة</h1>
        <p style="color:#475569;font-size:.9rem;line-height:1.7;">تم استلام طلب انضمامك بنجاح. سنراجعه ونفعّله في أقرب وقت.</p>
        <a href="{{ route('home') }}" style="display:inline-flex;margin-top:2rem;padding:.75rem 2rem;border-radius:9999px;font-size:.9rem;font-weight:700;color:#fff;background:linear-gradient(135deg,#ec4899,#be185d);text-decoration:none;">العودة للرئيسية</a>
    </div>
</section>
@endsection
