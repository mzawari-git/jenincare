@extends($layoutPath)

@section('title', 'تواصل معنا - ' . ($siteSettings['site_name'] ?? 'شركة جنين للتجميل'))
@section('meta_description', 'تواصل مع فريق شركة جنين للتجميل نحن هنا لمساعدتك على مدار الساعة.')

@section('content')
<section style="padding:7rem 1rem 3rem;background:linear-gradient(135deg,#fdf2f8,#f8fafc);text-align:center;border-bottom:1px solid #f1f5f9;">
    <h1 style="font-size:clamp(1.75rem,4vw,2.5rem);font-weight:900;color:#0f172a;margin-bottom:.5rem;">تواصل معنا</h1>
    <p style="color:#64748b;font-size:.95rem;">نحن هنا للإجابة على جميع استفساراتك</p>
</section>

<div style="max-width:1100px;margin:0 auto;padding:2rem 1rem 4rem;background:#ffffff;">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:2rem;">

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1.25rem;padding:2rem;">
            <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:.75rem;background:#fce7f3;">
                    <i class="fas fa-paper-plane" style="color:#ec4899;"></i>
                </span>
                <h3 style="font-size:1.1rem;font-weight:900;color:#0f172a;">أرسل لنا رسالة</h3>
            </div>
            <form method="POST" action="{{ route('contact.store') }}">
                @csrf
                @if(session('success'))
                <div style="background:#f0fdf4;color:#065f46;padding:.75rem 1rem;border-radius:.75rem;font-size:.85rem;margin-bottom:1rem;border:1px solid #bbf7d0;display:flex;align-items:center;gap:.5rem;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
                @endif
                <div style="margin-bottom:1rem;">
                    <input type="text" name="name" style="width:100%;padding:.75rem 1rem;border:1px solid #e2e8f0;border-radius:.75rem;font-size:.9rem;color:#0f172a;outline:none;background:#fff;transition:border-color .2s;" onfocus="this.style.borderColor='#ec4899'" onblur="this.style.borderColor='#e2e8f0'" placeholder="الاسم الكامل" value="{{ old('name') }}" required>
                    @error('name') <div style="color:#dc2626;font-size:.8rem;margin-top:.25rem;">{{ $message }}</div> @enderror
                </div>
                <div style="margin-bottom:1rem;">
                    <input type="email" name="email" style="width:100%;padding:.75rem 1rem;border:1px solid #e2e8f0;border-radius:.75rem;font-size:.9rem;color:#0f172a;outline:none;background:#fff;transition:border-color .2s;" onfocus="this.style.borderColor='#ec4899'" onblur="this.style.borderColor='#e2e8f0'" placeholder="البريد الإلكتروني" value="{{ old('email') }}" required>
                    @error('email') <div style="color:#dc2626;font-size:.8rem;margin-top:.25rem;">{{ $message }}</div> @enderror
                </div>
                <div style="margin-bottom:1rem;">
                    <input type="text" name="phone" style="width:100%;padding:.75rem 1rem;border:1px solid #e2e8f0;border-radius:.75rem;font-size:.9rem;color:#0f172a;outline:none;background:#fff;transition:border-color .2s;" onfocus="this.style.borderColor='#ec4899'" onblur="this.style.borderColor='#e2e8f0'" placeholder="رقم الهاتف (اختياري)" value="{{ old('phone') }}">
                </div>
                <div style="margin-bottom:1rem;">
                    <select name="subject" style="width:100%;padding:.75rem 1rem;border:1px solid #e2e8f0;border-radius:.75rem;font-size:.9rem;color:#0f172a;outline:none;background:#fff;transition:border-color .2s;" onfocus="this.style.borderColor='#ec4899'" onblur="this.style.borderColor='#e2e8f0'">
                        <option value="">اختر الموضوع</option>
                        <option value="استفسار عن منتج" {{ old('subject') == 'استفسار عن منتج' ? 'selected' : '' }}>استفسار عن منتج</option>
                        <option value="طلب شراء B2B" {{ old('subject') == 'طلب شراء B2B' ? 'selected' : '' }}>طلب شراء B2B</option>
                        <option value="شكوى أو اقتراح" {{ old('subject') == 'شكوى أو اقتراح' ? 'selected' : '' }}>شكوى أو اقتراح</option>
                        <option value="أخرى" {{ old('subject') == 'أخرى' ? 'selected' : '' }}>أخرى</option>
                    </select>
                </div>
                <div style="margin-bottom:1rem;">
                    <textarea name="message" rows="4" style="width:100%;padding:.75rem 1rem;border:1px solid #e2e8f0;border-radius:.75rem;font-size:.9rem;color:#0f172a;outline:none;background:#fff;resize:vertical;transition:border-color .2s;" onfocus="this.style.borderColor='#ec4899'" onblur="this.style.borderColor='#e2e8f0'" placeholder="رسالتك..." required>{{ old('message') }}</textarea>
                    @error('message') <div style="color:#dc2626;font-size:.8rem;margin-top:.25rem;">{{ $message }}</div> @enderror
                </div>
                <button type="submit" style="width:100%;padding:.85rem;background:linear-gradient(135deg,#ec4899,#be185d);color:#fff;border:none;border-radius:.75rem;font-weight:700;font-size:.95rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.5rem;transition:all .2s;">
                    <i class="fas fa-paper-plane"></i> إرسال الرسالة
                </button>
            </form>
        </div>

        <div>
            <div style="display:flex;flex-direction:column;gap:.85rem;">
                @foreach([
                    ['fas fa-map-marker-alt','#ec4899','العنوان',$siteSettings['site_address'] ?? '📍جنين - مُقابل مخبز و مطعم السينما'],
                    ['fas fa-phone-alt','#0891b2','الهاتف وواتساب',($siteSettings['site_phone'] ?? '+970 59 123 4567').'<br>'.($siteSettings['site_whatsapp'] ?? '+970 59 123 4567')],
                    ['fas fa-envelope','#d4af37','البريد الإلكتروني',$siteSettings['site_email'] ?? 'info@jenincare.com'],
                    ['fas fa-clock','#16a34a','ساعات العمل','السبت - الخميس: 9ص - 9م<br>الجمعة: مغلق'],
                ] as $info)
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1rem;padding:1.15rem;display:flex;align-items:center;gap:1rem;">
                    <div style="width:3rem;height:3rem;border-radius:.75rem;background:{{$info[1]}}12;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="{{ $info[0] }}" style="font-size:1.2rem;color:{{$info[1]}};"></i>
                    </div>
                    <div>
                        <h4 style="font-size:.85rem;font-weight:700;color:#0f172a;margin-bottom:.2rem;">{{ $info[2] }}</h4>
                        <p style="color:#475569;font-size:.8rem;margin:0;line-height:1.6;">{!! $info[3] !!}</p>
                    </div>
                </div>
                @endforeach

                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:1rem;padding:1.15rem;">
                    <h4 style="font-size:.85rem;font-weight:700;color:#0f172a;margin-bottom:.85rem;">تابعنا على</h4>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        @if($siteSettings['facebook_url'] ?? false)<a href="{{ $siteSettings['facebook_url'] }}" target="_blank" style="width:2.5rem;height:2.5rem;border-radius:50%;background:#1877F2;color:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:.9rem;"><i class="fab fa-facebook-f"></i></a>@endif
                        @if($siteSettings['instagram_url'] ?? false)<a href="{{ $siteSettings['instagram_url'] }}" target="_blank" style="width:2.5rem;height:2.5rem;border-radius:50%;background:#E4405F;color:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:.9rem;"><i class="fab fa-instagram"></i></a>@endif
                        @if($siteSettings['tiktok_url'] ?? false)<a href="{{ $siteSettings['tiktok_url'] }}" target="_blank" style="width:2.5rem;height:2.5rem;border-radius:50%;background:#000;color:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:.9rem;"><i class="fab fa-tiktok"></i></a>@endif
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$siteSettings['site_whatsapp'] ?? '970591234567') }}" target="_blank" style="width:2.5rem;height:2.5rem;border-radius:50%;background:#25D366;color:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:.9rem;"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
