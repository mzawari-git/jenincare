@extends($layoutPath)

@section('title', 'عناويني - ' . ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))

@section('content')
<section class="pt-32 pb-8 bg-gradient-to-b border-b border-white/5 text-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-white mb-2">عناويني</h1>
        <p class="text-white-dim">إدارة عناوين التوصيل الخاصة بك</p>
    </div>
</section>

<div class="container" style="padding:0 16px 60px;">
    <div class="row g-4">
        <div class="col-lg-3">
            @include('frontend.account.sidebar')
        </div>

        <div class="col-lg-9">
            <div style="background:var(--glass-bg);border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden;">
                <div style="padding:20px 24px;border-bottom:1px solid var(--glass-border);display:flex;justify-content:space-between;align-items:center;">
                    <h2 style="font-size:1.15rem;margin:0;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-map-marker-alt" style="color:var(--brand-500);"></i> العناوين المسجلة
                    </h2>
                    <button onclick="alert('سيتم إضافة هذه الميزة قريباً')" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:linear-gradient(135deg,var(--brand-500),var(--brand-500));color:#fff;border:none;border-radius:50px;font-weight:600;font-size:.8rem;cursor:pointer;transition:all .3s;box-shadow:0 2px 8px rgba(219,39,119,0.15);" onmouseover="this.style.boxShadow='0 4px 16px rgba(219,39,119,0.25)'" onmouseout="this.style.boxShadow='0 2px 8px rgba(219,39,119,0.15)'">
                        <i class="fas fa-plus"></i> إضافة عنوان
                    </button>
                </div>
                <div style="padding:24px;">
                    @if($addresses->isEmpty())
                    <div style="text-align:center;padding:40px 20px;">
                        <div style="font-size:4rem;color:var(--gray-300);margin-bottom:20px;">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <h3 style="font-size:1.3rem;font-weight:700;margin-bottom:8px;color:var(--ink);">لا توجد عناوين</h3>
                        <p style="font-size:.95rem;color:var(--ink-dim);margin-bottom:0;">لم تقم بإضافة أي عنوان توصيل بعد. أضف عنواناً لتسهيل عملية الشراء.</p>
                    </div>
                    @else
                    <div class="row g-3">
                        @foreach($addresses as $address)
                        <div class="col-md-6">
                            <div style="border:1px solid var(--glass-border);border-radius:12px;padding:20px;transition:all .3s;position:relative;" onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow='none'">
                                @if($address->is_default)
                                <span style="position:absolute;top:12px;left:12px;display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:9999px;font-size:.75rem;font-weight:600;background:#DBEAFE;color:#1E40AF;">افتراضي</span>
                                @endif
                                <p style="font-weight:700;margin-bottom:8px;font-size:.95rem;">{{ $address->label ?? 'عنوان التوصيل' }}</p>
                                <p style="color:var(--ink-dim);font-size:.85rem;margin-bottom:4px;"><i class="fas fa-user" style="width:18px;"></i> {{ $address->name ?? Auth::user()->name }}</p>
                                <p style="color:var(--ink-dim);font-size:.85rem;margin-bottom:4px;"><i class="fas fa-phone" style="width:18px;"></i> {{ $address->phone }}</p>
                                <p style="color:var(--ink-dim);font-size:.85rem;margin-bottom:4px;"><i class="fas fa-map-pin" style="width:18px;"></i> {{ $address->address }}</p>
                                <p style="color:var(--ink-dim);font-size:.85rem;margin:0;"><i class="fas fa-city" style="width:18px;"></i> {{ $address->city }}{{ $address->region ? '، ' . $address->region : '' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
