@extends('admin.layouts.app')

@section('title', 'الإعدادات')

@push('extra-styles')
<style>
.settings-tabs .nav-link{color:var(--gray-600);font-weight:600;font-size:.85rem;padding:.75rem 1.25rem;border:none;border-bottom:3px solid transparent;transition:all .2s}
.settings-tabs .nav-link:hover{color:var(--gray-900);border-bottom-color:var(--gray-300)}
.settings-tabs .nav-link.active{color:var(--pink-600);border-bottom-color:var(--pink-600);background:transparent}
.tab-content > .tab-pane{display:none}
.tab-content > .tab-pane.active{display:block}
.setting-card{background:#fff;border-radius:14px;padding:1.25rem;border:1px solid var(--gray-200)}
.setting-card .card-label{font-weight:700;font-size:.8rem;color:var(--gray-700);margin-bottom:4px}
.payment-toggle-card{background:#f9fafb;border-radius:12px;padding:1rem;border:1px solid var(--gray-200);transition:all .2s}
.payment-toggle-card:hover{box-shadow:0 2px 12px rgba(0,0,0,.04)}
.save-bar{position:sticky;bottom:0;z-index:100;background:var(--gray-900);border-radius:16px 16px 0 0;padding:.75rem 1.5rem;display:flex;justify-content:space-between;align-items:center;box-shadow:0 -4px 20px rgba(0,0,0,.15)}
.quick-link{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:12px;border:1px solid var(--gray-200);text-decoration:none;color:var(--gray-700);transition:all .2s}
.quick-link:hover{background:var(--pink-50);border-color:var(--pink-300);color:var(--pink-600)}
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">إعدادات الموقع</h1>
        <p class="text-muted small mb-0">إدارة جميع تكوينات الموقع من مكان واحد</p>
    </div>
</div>

{{-- Quick Links --}}
<div class="row g-3 mb-4">
    <div class="col-md-3"><a href="#tab-business" onclick="switchTab('business')" class="quick-link"><i class="fas fa-shipping-fast text-pink fs-5"></i><div><strong class="d-block small">الشحن والتوصيل</strong><small class="text-muted">تكلفة الشحن والحد الأدنى</small></div></a></div>
    <div class="col-md-3"><a href="#tab-payments" onclick="switchTab('payments')" class="quick-link"><i class="fas fa-credit-card text-success fs-5"></i><div><strong class="d-block small">طرق الدفع</strong><small class="text-muted">COD، بنكي، جوال باي</small></div></a></div>
    <div class="col-md-3"><a href="#tab-marketing" onclick="switchTab('marketing')" class="quick-link"><i class="fas fa-bullhorn text-primary fs-5"></i><div><strong class="d-block small">التسويق والبكسل</strong><small class="text-muted">Facebook، TikTok، Google</small></div></a></div>
    <div class="col-md-3"><a href="#tab-seo" onclick="switchTab('seo')" class="quick-link"><i class="fas fa-search text-info fs-5"></i><div><strong class="d-block small">SEO</strong><small class="text-muted">ميتا، كلمات مفتاحية</small></div></a></div>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="mainSettingsForm">
@csrf

{{-- Tabs Navigation --}}
<ul class="nav settings-tabs mb-4" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-tab="general" onclick="switchTab('general')" href="javascript:void(0)"><i class="fas fa-cog"></i> عام</a></li>
    <li class="nav-item"><a class="nav-link" data-tab="business" onclick="switchTab('business')" href="javascript:void(0)"><i class="fas fa-store"></i> الأعمال</a></li>
    <li class="nav-item"><a class="nav-link" data-tab="payments" onclick="switchTab('payments')" href="javascript:void(0)"><i class="fas fa-credit-card"></i> الدفع</a></li>
    <li class="nav-item"><a class="nav-link" data-tab="social" onclick="switchTab('social')" href="javascript:void(0)"><i class="fas fa-share-alt"></i> تسجيل الدخول</a></li>
    <li class="nav-item"><a class="nav-link" data-tab="marketing" onclick="switchTab('marketing')" href="javascript:void(0)"><i class="fas fa-bullhorn"></i> التسويق</a></li>
    <li class="nav-item"><a class="nav-link" data-tab="seo" onclick="switchTab('seo')" href="javascript:void(0)"><i class="fas fa-search"></i> SEO</a></li>
    <li class="nav-item"><a class="nav-link" data-tab="danger" onclick="switchTab('danger')" href="javascript:void(0)"><i class="fas fa-exclamation-triangle"></i> متقدم</a></li>
</ul>

{{-- Tab: General --}}
<div class="tab-content">
<div class="tab-pane active" id="tab-general">
    <div class="setting-card mb-4">
        <h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-image" style="color:var(--pink-600);margin-left:8px;"></i> الشعار والهوية</h5>
        <div class="row g-3">
            <div class="col-12">
                <div class="d-flex align-items-center gap-3">
                    <div class="border rounded p-2" style="width:200px;height:100px;display:flex;align-items:center;justify-content:center;background:#f8fafc;">
                        @if(!empty($settings['site_logo']))
                            <img src="{{ url('files/' . $settings['site_logo']) }}" style="max-height:80px;max-width:180px;object-fit:contain;">
                        @else
                            <span class="text-muted small">لا يوجد شعار</span>
                        @endif
                    </div>
                    <div>
                        <input type="file" name="site_logo" class="form-control" accept="image/*">
                        <small class="text-muted">200x80 بكسل مقترح</small>
                        @if(!empty($settings['site_logo']))
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('حذف الشعار؟')) document.getElementById('deleteLogoForm').submit();"><i class="fas fa-trash"></i> حذف</button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="setting-card mb-4">
        <h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-palette" style="color:var(--pink-600);margin-left:8px;"></i> التصميم والمظهر</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="card-label">قالب الموقع</label>
                <select name="site_theme" class="form-select" onchange="previewTheme(this.value)">
                    <option value="rose" {{ ($settings['site_theme'] ?? 'rose') == 'rose' ? 'selected' : '' }}>الوردي - Rose (الافتراضي)</option>
                    <option value="midnight" {{ ($settings['site_theme'] ?? '') == 'midnight' ? 'selected' : '' }}>ميدنايت - Midnight (داكن)</option>
                    <option value="ocean" {{ ($settings['site_theme'] ?? '') == 'ocean' ? 'selected' : '' }}>المحيط - Ocean (أزرق)</option>
                    <option value="forest" {{ ($settings['site_theme'] ?? '') == 'forest' ? 'selected' : '' }}>الغابة - Forest (أخضر)</option>
                    <option value="luxury" {{ ($settings['site_theme'] ?? '') == 'luxury' ? 'selected' : '' }}>الفخامة - Luxury (ذهبي)</option>
                    <option value="minimal" {{ ($settings['site_theme'] ?? '') == 'minimal' ? 'selected' : '' }}>مينيمال - Minimal (بسيط)</option>
                    <option value="sunset" {{ ($settings['site_theme'] ?? '') == 'sunset' ? 'selected' : '' }}>الغروب - Sunset (دافئ)</option>
                    <option value="natural" {{ ($settings['site_theme'] ?? '') == 'natural' ? 'selected' : '' }}>طبيعي - Natural (أخضر عضوي)</option>
                </select>
                <div class="form-text">اختر القالب المناسب لهوية متجرك</div>
            </div>
        </div>
    </div>

    <div class="setting-card mb-4">
        <h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-info-circle" style="color:var(--pink-600);margin-left:8px;"></i> معلومات المتجر</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="card-label">اسم الموقع (عربي)</label><input type="text" name="site_name_ar" class="form-control" value="{{ $settings['site_name_ar'] ?? config('app.name') }}"></div>
            <div class="col-md-6"><label class="card-label">اسم الموقع (إنجليزي)</label><input type="text" name="site_name_en" class="form-control" value="{{ $settings['site_name_en'] ?? config('app.name') }}"></div>
            <div class="col-md-6"><label class="card-label">البريد الإلكتروني</label><input type="email" name="contact_email" class="form-control" value="{{ $settings['contact_email'] ?? $settings['site_email'] ?? '' }}"></div>
            <div class="col-md-6"><label class="card-label">رقم الهاتف</label><input type="text" name="contact_phone" class="form-control" value="{{ $settings['contact_phone'] ?? $settings['site_phone'] ?? '' }}"></div>
            <div class="col-md-6"><label class="card-label">واتساب (مع رمز الدولة)</label><input type="text" name="whatsapp_number" class="form-control" value="{{ $settings['whatsapp_number'] ?? '' }}" placeholder="972590000000"></div>
            <div class="col-12"><label class="card-label">العنوان</label><input type="text" name="address" class="form-control" value="{{ $settings['address'] ?? $settings['site_address'] ?? '' }}" placeholder="المدينة، الشارع"></div>
        </div>
    </div>

    <div class="setting-card">
        <h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-globe" style="color:var(--pink-600);margin-left:8px;"></i> روابط التواصل الاجتماعي</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="card-label">فيسبوك</label><input type="url" name="facebook_url" class="form-control" value="{{ $settings['facebook_url'] ?? '' }}"></div>
            <div class="col-md-6"><label class="card-label">إنستغرام</label><input type="url" name="instagram_url" class="form-control" value="{{ $settings['instagram_url'] ?? '' }}"></div>
            <div class="col-md-6"><label class="card-label">تيك توك</label><input type="url" name="tiktok_url" class="form-control" value="{{ $settings['tiktok_url'] ?? '' }}"></div>
            <div class="col-md-6"><label class="card-label">تويتر</label><input type="url" name="twitter_url" class="form-control" value="{{ $settings['twitter_url'] ?? '' }}"></div>
            <div class="col-md-6"><label class="card-label">لينكدإن</label><input type="url" name="linkedin_url" class="form-control" value="{{ $settings['linkedin_url'] ?? '' }}"></div>
            <div class="col-md-6"><label class="card-label">يوتيوب</label><input type="url" name="youtube_url" class="form-control" value="{{ $settings['youtube_url'] ?? '' }}"></div>
            <div class="col-md-6"><label class="card-label">سناب شات</label><input type="url" name="snapchat_url" class="form-control" value="{{ $settings['snapchat_url'] ?? '' }}"></div>
            <div class="col-md-6"><label class="card-label">بينتيريست</label><input type="url" name="pinterest_url" class="form-control" value="{{ $settings['pinterest_url'] ?? '' }}"></div>
        </div>
    </div>
</div>

{{-- Tab: Business --}}
<div class="tab-pane" id="tab-business">
    <div class="setting-card mb-4">
        <h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-shipping-fast" style="color:var(--pink-600);margin-left:8px;"></i> الشحن والتوصيل</h5>
        <div class="row g-3">
            <div class="col-md-4"><label class="card-label">العملة</label><input type="text" name="currency" class="form-control" value="{{ $settings['currency'] ?? 'ILS' }}"></div>
            <div class="col-md-4"><label class="card-label">رمز العملة</label><input type="text" name="currency_symbol" class="form-control" value="{{ $settings['currency_symbol'] ?? '₪' }}"></div>
            <div class="col-md-4"><label class="card-label">نسبة الضريبة (%)</label><input type="number" step="0.01" name="tax_rate" class="form-control" value="{{ $settings['tax_rate'] ?? '0' }}"></div>
            <div class="col-md-6"><label class="card-label">تكلفة الشحن الافتراضية (₪)</label><input type="number" step="0.01" name="shipping_cost" class="form-control" value="{{ $settings['shipping_cost'] ?? '0' }}"></div>
            <div class="col-md-6"><label class="card-label">الحد الأدنى للشحن المجاني (₪)</label><input type="number" step="0.01" name="free_shipping_min" class="form-control" value="{{ $settings['free_shipping_min'] ?? '0' }}"></div>
        </div>
    </div>

    <div class="setting-card">
        <h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-cogs" style="color:var(--pink-600);margin-left:8px;"></i> خيارات الموقع</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input type="hidden" name="maintenance_mode" value="0">
                    <input type="checkbox" name="maintenance_mode" class="form-check-input" value="1" id="maintenance" {{ ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="maintenance">وضع الصيانة</label>
                    <div class="form-text">عند التفعيل، يظهر الموقع في وضع الصيانة للزوار</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input type="hidden" name="registration_enabled" value="0">
                    <input type="checkbox" name="registration_enabled" class="form-check-input" value="1" id="registration" {{ ($settings['registration_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="registration">تفعيل التسجيل</label>
                    <div class="form-text">السماح للزوار بإنشاء حسابات جديدة</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tab: Payments --}}
<div class="tab-pane" id="tab-payments">
    <p class="text-muted small mb-3">فعل أو عطل طرق الدفع المتاحة للعملاء عند إتمام الشراء</p>

    <div class="payment-toggle-card mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2"><i class="fas fa-money-bill-wave text-success fs-5"></i><strong>الدفع عند الاستلام (COD)</strong></div>
            <div class="form-check form-switch mb-0">
                <input type="hidden" name="payment_cod_enabled" value="0">
                <input type="checkbox" name="payment_cod_enabled" class="form-check-input" value="1" {{ ($settings['payment_cod_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
            </div>
        </div>
        <small class="text-muted">الدفع نقداً عند استلام الطلب</small>
    </div>

    <div class="payment-toggle-card mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2"><i class="fas fa-university text-primary fs-5"></i><strong>التحويل البنكي</strong></div>
            <div class="form-check form-switch mb-0">
                <input type="hidden" name="payment_bank_enabled" value="0">
                <input type="checkbox" name="payment_bank_enabled" class="form-check-input" value="1" {{ ($settings['payment_bank_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
            </div>
        </div>
        <div class="row g-2">
            <div class="col-md-6"><label class="form-label small">اسم البنك</label><input type="text" name="payment_bank_name" class="form-control form-control-sm" value="{{ $settings['payment_bank_name'] ?? '' }}" placeholder="بنك فلسطين"></div>
            <div class="col-md-6"><label class="form-label small">اسم صاحب الحساب</label><input type="text" name="payment_bank_holder" class="form-control form-control-sm" value="{{ $settings['payment_bank_holder'] ?? '' }}" placeholder="الاسم الكامل"></div>
            <div class="col-md-6"><label class="form-label small">رقم الحساب</label><input type="text" name="payment_bank_account" class="form-control form-control-sm" value="{{ $settings['payment_bank_account'] ?? '' }}"></div>
            <div class="col-md-6"><label class="form-label small">IBAN</label><input type="text" name="payment_bank_iban" class="form-control form-control-sm" value="{{ $settings['payment_bank_iban'] ?? '' }}"></div>
        </div>
    </div>

    <div class="payment-toggle-card mb-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2"><i class="fas fa-mobile-alt text-warning fs-5"></i><strong>جوال باي (Jawwal Pay)</strong></div>
            <div class="form-check form-switch mb-0">
                <input type="hidden" name="payment_jawwal_enabled" value="0">
                <input type="checkbox" name="payment_jawwal_enabled" class="form-check-input" value="1" {{ ($settings['payment_jawwal_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
            </div>
        </div>
        <div class="row g-2">
            <div class="col-md-6"><label class="form-label small">رقم جوال باي</label><input type="text" name="payment_jawwal_phone" class="form-control form-control-sm" dir="ltr" value="{{ $settings['payment_jawwal_phone'] ?? '' }}" placeholder="059X XXXXXX"></div>
            <div class="col-md-6"><label class="form-label small">اسم صاحب الحساب</label><input type="text" name="payment_jawwal_holder" class="form-control form-control-sm" value="{{ $settings['payment_jawwal_holder'] ?? '' }}" placeholder="الاسم الكامل"></div>
        </div>
    </div>

    <div class="payment-toggle-card">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2"><i class="fas fa-university text-info fs-5"></i><strong>ريفلكت (Reflect)</strong></div>
            <div class="form-check form-switch mb-0">
                <input type="hidden" name="payment_reflect_enabled" value="0">
                <input type="checkbox" name="payment_reflect_enabled" class="form-check-input" value="1" {{ ($settings['payment_reflect_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
            </div>
        </div>
        <div class="row g-2">
            <div class="col-md-6"><label class="form-label small">اسم صاحب الحساب</label><input type="text" name="payment_reflect_holder" class="form-control form-control-sm" value="{{ $settings['payment_reflect_holder'] ?? '' }}"></div>
            <div class="col-md-6"><label class="form-label small">رقم هاتف Reflect</label><input type="text" name="payment_reflect_phone" class="form-control form-control-sm" dir="ltr" value="{{ $settings['payment_reflect_phone'] ?? '' }}" placeholder="059X XXXXXX"></div>
        </div>
    </div>
</div>

{{-- Tab: Social Login --}}
<div class="tab-pane" id="tab-social">
    <div class="setting-card">
        <h5 class="mb-3 pb-2 border-bottom"><i class="fab fa-google text-danger" style="margin-left:8px;"></i> Google OAuth</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="card-label">Google Client ID</label><input type="text" name="google_client_id" class="form-control font-monospace" dir="ltr" value="{{ $settings['google_client_id'] ?? '' }}" placeholder="xxx.apps.googleusercontent.com"></div>
            <div class="col-md-6"><label class="card-label">Google Client Secret</label><input type="text" name="google_client_secret" class="form-control font-monospace" dir="ltr" value="{{ $settings['google_client_secret'] ?? '' }}" placeholder="GOCSPX-xxx"></div>
        </div>
    </div>
    <div class="setting-card mt-3">
        <h5 class="mb-3 pb-2 border-bottom"><i class="fab fa-facebook text-primary" style="margin-left:8px;"></i> Facebook OAuth</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="card-label">Facebook App ID</label><input type="text" name="facebook_client_id" class="form-control font-monospace" dir="ltr" value="{{ $settings['facebook_client_id'] ?? '' }}"></div>
            <div class="col-md-6"><label class="card-label">Facebook App Secret</label><input type="text" name="facebook_client_secret" class="form-control font-monospace" dir="ltr" value="{{ $settings['facebook_client_secret'] ?? '' }}"></div>
        </div>
        <div class="mt-3"><small class="text-muted"><i class="fas fa-info-circle"></i> بعد الحفظ، جرب تسجيل الدخول للتأكد من عمل الإعدادات</small></div>
    </div>
</div>

{{-- Tab: Marketing --}}
<div class="tab-pane" id="tab-marketing">
    <div class="setting-card mb-4">
        <h5 class="mb-3 pb-2 border-bottom"><i class="fab fa-facebook text-primary" style="margin-left:8px;"></i> Facebook Pixel & CAPI</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="card-label">Pixel ID</label><input type="text" name="facebook_pixel_id" class="form-control font-monospace" dir="ltr" value="{{ $settings['facebook_pixel_id'] ?? '' }}" placeholder="1234567890"></div>
            <div class="col-md-6"><label class="card-label">Access Token (CAPI)</label><input type="text" name="facebook_access_token" class="form-control font-monospace" dir="ltr" value="{{ $settings['facebook_access_token'] ?? '' }}"></div>
            <div class="col-md-4">
                <div class="form-check form-switch mt-4">
                    <input type="hidden" name="facebook_pixel_enabled" value="0">
                    <input type="checkbox" name="facebook_pixel_enabled" class="form-check-input" value="1" {{ ($settings['facebook_pixel_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold">تفعيل Pixel</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch mt-4">
                    <input type="hidden" name="facebook_capi_enabled" value="0">
                    <input type="checkbox" name="facebook_capi_enabled" class="form-check-input" value="1" {{ ($settings['facebook_capi_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold">تفعيل CAPI (Server)</label>
                </div>
            </div>
            <div class="col-md-4"><label class="card-label">Test Event Code</label><input type="text" name="facebook_test_event_code" class="form-control" value="{{ $settings['facebook_test_event_code'] ?? '' }}"></div>
        </div>
        <div class="mt-2"><small class="text-muted"><i class="fas fa-link"></i> إدارة الحملات: <a href="{{ route('admin.ads.dashboard') }}">لوحة الإعلانات</a> | <a href="{{ route('admin.leads-hub.index') }}">Leads Hub</a></small></div>
    </div>

    <div class="setting-card mb-4">
        <h5 class="mb-3 pb-2 border-bottom"><i class="fab fa-tiktok" style="margin-left:8px;"></i> TikTok Pixel</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="card-label">TikTok Pixel ID</label><input type="text" name="tiktok_pixel_id" class="form-control font-monospace" dir="ltr" value="{{ $settings['tiktok_pixel_id'] ?? '' }}"></div>
            <div class="col-md-6"><label class="card-label">Access Token</label><input type="text" name="tiktok_access_token" class="form-control font-monospace" dir="ltr" value="{{ $settings['tiktok_access_token'] ?? '' }}"></div>
            <div class="col-md-6">
                <div class="form-check form-switch mt-2">
                    <input type="hidden" name="tiktok_pixel_enabled" value="0">
                    <input type="checkbox" name="tiktok_pixel_enabled" class="form-check-input" value="1" {{ ($settings['tiktok_pixel_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold">تفعيل TikTok Pixel</label>
                </div>
            </div>
        </div>
    </div>

    <div class="setting-card">
        <h5 class="mb-3 pb-2 border-bottom"><i class="fab fa-google text-success" style="margin-left:8px;"></i> Google Analytics</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="card-label">Google Analytics ID</label><input type="text" name="google_analytics_id" class="form-control font-monospace" dir="ltr" value="{{ $settings['google_analytics_id'] ?? '' }}" placeholder="G-XXXXXXXXXX"></div>
        </div>
    </div>
</div>

{{-- Tab: SEO --}}
<div class="tab-pane" id="tab-seo">
    <div class="setting-card">
        <h5 class="mb-3 pb-2 border-bottom"><i class="fas fa-search" style="color:var(--pink-600);margin-left:8px;"></i> إعدادات SEO العامة</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="card-label">Meta Title (عربي)</label><input type="text" name="meta_title_ar" class="form-control" value="{{ $settings['meta_title_ar'] ?? '' }}"></div>
            <div class="col-md-6"><label class="card-label">Meta Title (إنجليزي)</label><input type="text" name="meta_title_en" class="form-control" value="{{ $settings['meta_title_en'] ?? '' }}"></div>
            <div class="col-12"><label class="card-label">Meta Description (عربي)</label><textarea name="meta_description_ar" class="form-control" rows="2" maxlength="320">{{ $settings['meta_description_ar'] ?? '' }}</textarea><small><span class="meta-count">0</span>/320</small></div>
            <div class="col-12"><label class="card-label">Meta Description (إنجليزي)</label><textarea name="meta_description_en" class="form-control" rows="2" maxlength="320">{{ $settings['meta_description_en'] ?? '' }}</textarea></div>
            <div class="col-12"><label class="card-label">Meta Keywords</label><input type="text" name="meta_keywords" class="form-control" value="{{ $settings['meta_keywords'] ?? '' }}" placeholder="كلمة1, كلمة2, كلمة3"></div>
            <div class="col-md-6"><label class="card-label">وصف الموقع (عربي)</label><textarea name="site_description_ar" class="form-control" rows="2">{{ $settings['site_description_ar'] ?? '' }}</textarea></div>
            <div class="col-md-6"><label class="card-label">وصف الموقع (إنجليزي)</label><textarea name="site_description_en" class="form-control" rows="2">{{ $settings['site_description_en'] ?? '' }}</textarea></div>
        </div>
        <div class="mt-2"><small class="text-muted"><i class="fas fa-link"></i> <a href="{{ route('admin.seo.index') }}">إدارة SEO المنتجات</a></small></div>
    </div>
</div>

{{-- Tab: Danger Zone --}}
<div class="tab-pane" id="tab-danger">
    <div class="card border-danger">
        <div class="card-header" style="background:#FEE2E2;color:#991B1B;"><i class="fas fa-exclamation-triangle"></i> منطقة الخطر</div>
        <div class="card-body">
            <p class="text-muted small mb-3">حذف جميع المنتجات من قاعدة البيانات. لا يمكن التراجع.</p>
            <a href="javascript:void(0)" onclick="if(confirm('هل أنت متأكد؟ هذا لا يمكن التراجع عنه!')){document.getElementById('deleteProductsForm').submit()}" class="btn btn-danger"><i class="fas fa-trash"></i> حذف جميع المنتجات</a>
        </div>
    </div>
</div>
</div>

{{-- Save Bar --}}
<div class="save-bar" id="saveBar">
    <div><small class="text-white-50">تأكد من صحة البيانات قبل الحفظ</small></div>
    <input type="hidden" name="tab" id="activeTab" value="general">
    <button type="submit" class="btn btn-pink btn-lg px-5"><i class="fas fa-save"></i> حفظ جميع الإعدادات</button>
</div>

</form>

<form id="deleteProductsForm" action="{{ route('admin.settings.delete-products') }}" method="POST" style="display:none">@csrf @method('DELETE')</form>
<form id="deleteLogoForm" action="{{ route('admin.settings.delete-logo') }}" method="POST" style="display:none">@csrf @method('DELETE')</form>

<script>
function switchTab(tabName) {
    document.querySelectorAll('.settings-tabs .nav-link').forEach(l => l.classList.remove('active'));
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-' + tabName).classList.add('active');
    localStorage.setItem('settingsActiveTab', tabName);
    document.getElementById('activeTab').value = tabName;
}

function previewTheme(themeName) {
    var link = document.getElementById('themePreview');
    if (!link) {
        link = document.createElement('link');
        link.id = 'themePreview';
        link.rel = 'stylesheet';
        document.head.appendChild(link);
    }
    link.href = '{{ url('/css/themes/') }}/' + themeName + '.css';
}

document.addEventListener('DOMContentLoaded', function() {
    const saved = localStorage.getItem('settingsActiveTab');
    if (saved && document.getElementById('tab-' + saved)) switchTab(saved);

    const desc = document.querySelector('textarea[name="meta_description_ar"]');
    if (desc) {
        document.querySelector('.meta-count').textContent = desc.value.length;
        desc.addEventListener('input', function() { document.querySelector('.meta-count').textContent = this.value.length; });
    }
});
</script>
@endsection
