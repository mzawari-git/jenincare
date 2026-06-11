@extends('admin.layouts.app')

@section('title', 'إعدادات الحساب')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-sliders-h" style="color:var(--pink-600);margin-left:8px;"></i> إعدادات الحساب</h1>
        <p class="text-muted small mb-0">إدارة إعدادات التتبع والإعلانات لحسابك في مكان واحد</p>
    </div>
    <button class="btn btn-outline-pink btn-sm px-3" onclick="window.scrollTo({top:0,behavior:'smooth'})">
        <i class="fas fa-arrow-up"></i> العودة للأعلى
    </button>
</div>

{{-- Platform Status Cards --}}
<div class="row g-3 mb-4" id="platform-stats">
    @foreach([
        ['key' => 'facebook', 'name' => 'Facebook Pixel', 'icon' => 'fab fa-facebook', 'color' => '#1877F2', 'enabled' => $settings['facebook']['enabled']],
        ['key' => 'tiktok', 'name' => 'TikTok Pixel', 'icon' => 'fab fa-tiktok', 'color' => '#000', 'enabled' => $settings['tiktok']['enabled']],
        ['key' => 'google', 'name' => 'Google Ads', 'icon' => 'fab fa-google', 'color' => '#4285F4', 'enabled' => $settings['google']['enabled']],
        ['key' => 'snapchat', 'name' => 'Snapchat Pixel', 'icon' => 'fab fa-snapchat', 'color' => '#FFFC00', 'enabled' => $settings['snapchat']['enabled']],
        ['key' => 'pinterest', 'name' => 'Pinterest Tag', 'icon' => 'fab fa-pinterest', 'color' => '#E60023', 'enabled' => $settings['pinterest']['enabled']],
        ['key' => 'twitter', 'name' => 'X (Twitter) Pixel', 'icon' => 'fab fa-x-twitter', 'color' => '#000', 'enabled' => $settings['twitter']['enabled']],
        ['key' => 'linkedin', 'name' => 'LinkedIn Insight', 'icon' => 'fab fa-linkedin', 'color' => '#0A66C2', 'enabled' => $settings['linkedin']['enabled']],
        ['key' => 'custom_api', 'name' => 'API مخصص', 'icon' => 'fas fa-code', 'color' => '#10B981', 'enabled' => $settings['custom_api']['enabled']],
    ] as $platform)
    <div class="col-md-3 col-6">
        <div class="stat-card-new d-flex align-items-center gap-3">
            <div class="stat-icon-new d-flex align-items-center justify-content-center" style="background:{{ $platform['color'] }}12;color:{{ $platform['color'] }};">
                <i class="{{ $platform['icon'] }}"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
                <div style="font-size:.8rem;font-weight:600;color:var(--gray-700);">{{ $platform['name'] }}</div>
                <div class="mt-1">
                    <span class="badge rounded-pill px-3 py-1 {{ $platform['enabled'] ? 'bg-success' : 'bg-secondary' }}" style="font-size:.7rem;">
                        {{ $platform['enabled'] ? 'مفعل' : 'معطل' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-4" id="marketing-app">

    {{-- Facebook Pixel & CAPI --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center" style="width:32px;height:32px;border-radius:8px;background:#1877F2;color:#fff;font-size:.9rem;">
                    <i class="fab fa-facebook"></i>
                </span>
                <span>Facebook Pixel &amp; CAPI</span>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded-3" style="background:var(--gray-50);">
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;">حالة البيكسل</div>
                        <div class="text-muted" style="font-size:.75rem;">تشغيل أو إيقاف Facebook Pixel على الموقع</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="fb_pixel_enabled" {{ $settings['facebook']['enabled'] ? 'checked' : '' }} style="width:3rem;height:1.5rem;cursor:pointer;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.85rem;">
                        <i class="fas fa-id-card text-muted" style="margin-left:6px;"></i>Facebook Pixel ID
                    </label>
                    <input type="text" class="form-control font-monospace" id="fb_pixel_id" value="{{ $settings['facebook']['pixel_id'] }}" placeholder="1234567890123456">
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded-3" style="background:var(--gray-50);">
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;">حالة CAPI</div>
                        <div class="text-muted" style="font-size:.75rem;">تفعيل الإرسال من الخادم (Server-Side)</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="fb_capi_enabled" {{ $settings['facebook']['capi_enabled'] ? 'checked' : '' }} style="width:3rem;height:1.5rem;cursor:pointer;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.85rem;">
                        <i class="fas fa-key text-muted" style="margin-left:6px;"></i>Facebook Access Token
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control font-monospace" id="fb_access_token" value="{{ $settings['facebook']['access_token'] }}" placeholder="EAAxxxxxxxxxxxxx">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('fb_access_token', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.85rem;">
                        <i class="fas fa-vial text-muted" style="margin-left:6px;"></i>Test Event Code
                        <span class="text-muted" style="font-weight:400;font-size:.75rem;">(اختياري)</span>
                    </label>
                    <input type="text" class="form-control font-monospace" id="fb_test_code" value="{{ $settings['facebook']['test_event_code'] ?? '' }}" placeholder="TEST12345">
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-pink" onclick="saveFacebook(this)">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                    <button class="btn btn-outline-pink" onclick="testConnection('facebook')">
                        <i class="fas fa-plug"></i> اختبار الاتصال
                    </button>
                </div>
                <div id="fb-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- TikTok Pixel & Events API --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center" style="width:32px;height:32px;border-radius:8px;background:#000;color:#fff;font-size:.9rem;">
                    <i class="fab fa-tiktok"></i>
                </span>
                <span>TikTok Pixel &amp; Events API</span>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded-3" style="background:var(--gray-50);">
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;">حالة البيكسل</div>
                        <div class="text-muted" style="font-size:.75rem;">تشغيل أو إيقاف TikTok Pixel على الموقع</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="tt_pixel_enabled" {{ $settings['tiktok']['enabled'] ? 'checked' : '' }} style="width:3rem;height:1.5rem;cursor:pointer;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.85rem;">
                        <i class="fas fa-id-card text-muted" style="margin-left:6px;"></i>TikTok Pixel ID
                    </label>
                    <input type="text" class="form-control font-monospace" id="tt_pixel_id" value="{{ $settings['tiktok']['pixel_id'] }}" placeholder="XXXXXXXXXX">
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded-3" style="background:var(--gray-50);">
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;">حالة Events API</div>
                        <div class="text-muted" style="font-size:.75rem;">تفعيل الإرسال من الخادم (Server-Side)</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="tt_capi_enabled" {{ $settings['tiktok']['capi_enabled'] ? 'checked' : '' }} style="width:3rem;height:1.5rem;cursor:pointer;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.85rem;">
                        <i class="fas fa-key text-muted" style="margin-left:6px;"></i>TikTok Access Token
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control font-monospace" id="tt_access_token" value="{{ $settings['tiktok']['access_token'] }}" placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('tt_access_token', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-dark" onclick="saveTikTok(this)">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                    <button class="btn btn-outline-secondary" onclick="testConnection('tiktok')">
                        <i class="fas fa-plug"></i> اختبار الاتصال
                    </button>
                </div>
                <div id="tt-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- Google Ads --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center" style="width:32px;height:32px;border-radius:8px;background:#4285F4;color:#fff;font-size:.9rem;">
                    <i class="fab fa-google"></i>
                </span>
                <span>Google Ads Conversion Tracking</span>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded-3" style="background:var(--gray-50);">
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;">حالة Google Ads</div>
                        <div class="text-muted" style="font-size:.75rem;">تشغيل أو إيقاف تتبع تحويلات Google Ads</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="ga_enabled" {{ $settings['google']['enabled'] ? 'checked' : '' }} style="width:3rem;height:1.5rem;cursor:pointer;">
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Conversion ID</label>
                        <input type="text" class="form-control font-monospace" id="ga_conversion_id" value="{{ $settings['google']['conversion_id'] }}" placeholder="AW-123456789">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Conversion Label</label>
                        <input type="text" class="form-control font-monospace" id="ga_conversion_label" value="{{ $settings['google']['conversion_label'] }}" placeholder="xxxxxxxxxx">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Google Ads CID</label>
                        <input type="text" class="form-control font-monospace" id="ga_cid" value="{{ $settings['google']['google_ads_cid'] }}" placeholder="123-456-7890">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Developer Token</label>
                        <div class="input-group">
                            <input type="password" class="form-control font-monospace" id="ga_dev_token" value="{{ $settings['google']['developer_token'] ?? '' }}">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('ga_dev_token', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Refresh Token</label>
                        <div class="input-group">
                            <input type="password" class="form-control font-monospace" id="ga_refresh_token" value="{{ $settings['google']['refresh_token'] ?? '' }}">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('ga_refresh_token', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap mt-3">
                    <button class="btn" style="background:#4285F4;color:#fff;" onclick="saveGoogle(this)">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                    <button class="btn btn-outline-primary" onclick="testConnection('google')">
                        <i class="fas fa-plug"></i> اختبار الاتصال
                    </button>
                </div>
                <div id="ga-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- Snapchat --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center" style="width:32px;height:32px;border-radius:8px;background:#FFFC00;color:#000;font-size:.9rem;">
                    <i class="fab fa-snapchat"></i>
                </span>
                <span>Snapchat Pixel</span>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded-3" style="background:var(--gray-50);">
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;">حالة Snapchat Pixel</div>
                        <div class="text-muted" style="font-size:.75rem;">تشغيل أو إيقاف Snapchat Pixel</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="sc_enabled" {{ $settings['snapchat']['enabled'] ? 'checked' : '' }} style="width:3rem;height:1.5rem;cursor:pointer;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.85rem;">Snapchat Pixel ID</label>
                    <input type="text" class="form-control font-monospace" id="sc_pixel_id" value="{{ $settings['snapchat']['pixel_id'] }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.85rem;">API Token</label>
                    <div class="input-group">
                        <input type="password" class="form-control font-monospace" id="sc_api_token" value="{{ $settings['snapchat']['api_token'] }}">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('sc_api_token', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn" style="background:#FFFC00;color:#000;" onclick="saveSnapchat(this)">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                    <button class="btn btn-outline-warning" onclick="testConnection('snapchat')">
                        <i class="fas fa-plug"></i> اختبار الاتصال
                    </button>
                </div>
                <div id="sc-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- Pinterest --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center" style="width:32px;height:32px;border-radius:8px;background:#E60023;color:#fff;font-size:.9rem;">
                    <i class="fab fa-pinterest"></i>
                </span>
                <span>Pinterest Tag</span>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded-3" style="background:var(--gray-50);">
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;">حالة Pinterest Tag</div>
                        <div class="text-muted" style="font-size:.75rem;">تشغيل أو إيقاف Pinterest Tag</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="pi_enabled" {{ $settings['pinterest']['enabled'] ? 'checked' : '' }} style="width:3rem;height:1.5rem;cursor:pointer;">
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Pinterest Tag ID</label>
                        <input type="text" class="form-control font-monospace" id="pi_tag_id" value="{{ $settings['pinterest']['tag_id'] }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Ad Account ID</label>
                        <input type="text" class="form-control font-monospace" id="pi_ad_account_id" value="{{ $settings['pinterest']['ad_account_id'] ?? '' }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.85rem;">Access Token</label>
                    <div class="input-group">
                        <input type="password" class="form-control font-monospace" id="pi_access_token" value="{{ $settings['pinterest']['access_token'] }}">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('pi_access_token', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn" style="background:#E60023;color:#fff;" onclick="savePinterest(this)">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                    <button class="btn btn-outline-danger" onclick="testConnection('pinterest')">
                        <i class="fas fa-plug"></i> اختبار الاتصال
                    </button>
                </div>
                <div id="pi-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- Twitter / X --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center" style="width:32px;height:32px;border-radius:8px;background:#000;color:#fff;font-size:.9rem;">
                    <i class="fab fa-x-twitter"></i>
                </span>
                <span>X (Twitter) Pixel</span>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded-3" style="background:var(--gray-50);">
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;">حالة X Pixel</div>
                        <div class="text-muted" style="font-size:.75rem;">تشغيل أو إيقاف X (Twitter) Pixel</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="tw_enabled" {{ $settings['twitter']['enabled'] ? 'checked' : '' }} style="width:3rem;height:1.5rem;cursor:pointer;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.85rem;">Pixel ID</label>
                    <input type="text" class="form-control font-monospace" id="tw_pixel_id" value="{{ $settings['twitter']['pixel_id'] }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.85rem;">API Key</label>
                    <div class="input-group">
                        <input type="password" class="form-control font-monospace" id="tw_api_key" value="{{ $settings['twitter']['api_key'] }}">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('tw_api_key', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-dark" onclick="saveTwitter(this)">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                    <button class="btn btn-outline-secondary" onclick="testConnection('twitter')">
                        <i class="fas fa-plug"></i> اختبار الاتصال
                    </button>
                </div>
                <div id="tw-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- LinkedIn --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center" style="width:32px;height:32px;border-radius:8px;background:#0A66C2;color:#fff;font-size:.9rem;">
                    <i class="fab fa-linkedin"></i>
                </span>
                <span>LinkedIn Insight Tag</span>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded-3" style="background:var(--gray-50);">
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;">حالة LinkedIn Tag</div>
                        <div class="text-muted" style="font-size:.75rem;">تشغيل أو إيقاف LinkedIn Insight Tag</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="li_enabled" {{ $settings['linkedin']['enabled'] ? 'checked' : '' }} style="width:3rem;height:1.5rem;cursor:pointer;">
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Partner ID</label>
                        <input type="text" class="form-control font-monospace" id="li_partner_id" value="{{ $settings['linkedin']['partner_id'] }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold" style="font-size:.85rem;">Conversion Rule ID <span class="text-muted" style="font-weight:400;font-size:.7rem;">(اختياري)</span></label>
                        <input type="text" class="form-control font-monospace" id="li_conversion_rule_id" value="{{ $settings['linkedin']['conversion_rule_id'] ?? '' }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.85rem;">Access Token</label>
                    <div class="input-group">
                        <input type="password" class="form-control font-monospace" id="li_access_token" value="{{ $settings['linkedin']['access_token'] }}">
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('li_access_token', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn" style="background:#0A66C2;color:#fff;" onclick="saveLinkedIn(this)">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                    <button class="btn btn-outline-primary" onclick="testConnection('linkedin')">
                        <i class="fas fa-plug"></i> اختبار الاتصال
                    </button>
                </div>
                <div id="li-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- Custom API + General Settings --}}
    <div class="col-lg-6">
        {{-- Custom API --}}
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center" style="width:32px;height:32px;border-radius:8px;background:#10B981;color:#fff;font-size:.9rem;">
                    <i class="fas fa-code"></i>
                </span>
                <span>API المخصص</span>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded-3" style="background:var(--gray-50);">
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;">حالة API المخصص</div>
                        <div class="text-muted" style="font-size:.75rem;">تشغيل أو إيقاف الإرسال إلى API خارجي</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="ca_enabled" {{ $settings['custom_api']['enabled'] ? 'checked' : '' }} style="width:3rem;height:1.5rem;cursor:pointer;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:.85rem;">مفتاح API <span class="text-muted" style="font-weight:400;font-size:.7rem;">(اختياري)</span></label>
                    <input type="text" class="form-control font-monospace" id="ca_api_key" value="{{ $settings['custom_api']['api_key'] }}" placeholder="اترك فارغاً بدون مفتاح">
                </div>
                <div class="mb-3 p-3 rounded-3" style="background:#f8f9fa;font-size:.75rem;">
                    <div class="fw-bold mb-2" style="font-size:.8rem;"><i class="fas fa-info-circle text-info"></i> نقاط النهاية المتاحة</div>
                    <div class="mb-1"><code class="text-info" style="font-size:.7rem;">POST {{ url('/api/tracking/event') }}</code> — إرسال حدث واحد</div>
                    <div class="mb-1"><code class="text-info" style="font-size:.7rem;">POST {{ url('/api/tracking/batch') }}</code> — إرسال أحداث متعددة</div>
                    <div><code class="text-info" style="font-size:.7rem;">GET {{ url('/api/tracking/health') }}</code> — فحص الحالة</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn" style="background:#10B981;color:#fff;" onclick="saveCustomApi(this)">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                    <button class="btn btn-outline-primary" onclick="testConnection('custom_api')">
                        <i class="fas fa-plug"></i> اختبار
                    </button>
                </div>
                <div id="ca-alert" class="mt-2"></div>
            </div>
        </div>

        {{-- General Settings --}}
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <span class="d-flex align-items-center justify-content-center" style="width:32px;height:32px;border-radius:8px;background:var(--pink-500);color:#fff;font-size:.9rem;">
                    <i class="fas fa-cog"></i>
                </span>
                <span>الإعدادات العامة</span>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded-3" style="background:var(--gray-50);">
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;">تفعيل نظام التتبع</div>
                        <div class="text-muted" style="font-size:.75rem;">تشغيل أو إيقاف جميع أنظمة التتبع دفعة واحدة</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="tracking_enabled" {{ !$settings['test_mode'] ? 'checked' : '' }} style="width:3rem;height:1.5rem;cursor:pointer;">
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded-3" style="background:var(--gray-50);">
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;">وضع الاختبار</div>
                        <div class="text-muted" style="font-size:.75rem;">Test Mode — إرسال الأحداث في وضع التجربة</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="test_mode" {{ $settings['test_mode'] ? 'checked' : '' }} style="width:3rem;height:1.5rem;cursor:pointer;">
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded-3" style="background:var(--gray-50);">
                    <div>
                        <div class="fw-bold" style="font-size:.9rem;">استخدام Queue</div>
                        <div class="text-muted" style="font-size:.75rem;">إرسال الأحداث في الخلفية لتحسين الأداء</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="async_mode" checked style="width:3rem;height:1.5rem;cursor:pointer;">
                    </div>
                </div>
                <div class="p-3 rounded-3" style="background:var(--gray-50);">
                    <div class="fw-bold mb-2" style="font-size:.85rem;">
                        <i class="fas fa-chart-simple text-info"></i> إحصائيات التتبع (آخر 24 ساعة)
                    </div>
                    <div class="row g-2 text-center" id="capi-stats">
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-white border">
                                <strong class="d-block text-success" id="stat-success" style="font-size:1.1rem;">0</strong>
                                <span class="text-muted" style="font-size:.7rem;">ناجح</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-white border">
                                <strong class="d-block text-danger" id="stat-failed" style="font-size:1.1rem;">0</strong>
                                <span class="text-muted" style="font-size:.7rem;">فاشل</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-white border">
                                <strong class="d-block text-primary" id="stat-total" style="font-size:1.1rem;">0</strong>
                                <span class="text-muted" style="font-size:.7rem;">إجمالي</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button class="btn btn-pink btn-sm" onclick="saveGeneral()">
                        <i class="fas fa-save"></i> حفظ الإعدادات العامة
                    </button>
                </div>
                <div id="gen-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

</div>

<script>
const BASE = '{{ url('/') }}';

function alertBox(id, msg, type) {
    const el = document.getElementById(id);
    if (!el) return;
    el.innerHTML = `<div class="alert alert-${type} py-2 px-3 mb-0 rounded-3 small d-flex align-items-center gap-2">
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'}"></i>
        ${msg}
    </div>`;
    setTimeout(() => el.innerHTML = '', 4000);
}

function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function setLoading(btn, loading) {
    if (!btn) return;
    if (loading) {
        btn.disabled = true;
        btn.dataset.html = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> جاري الحفظ...';
    } else {
        btn.disabled = false;
        if (btn.dataset.html) btn.innerHTML = btn.dataset.html;
    }
}

async function savePlatform(url, data, alertId, callback, btn) {
    if (btn) setLoading(btn, true);
    try {
        const r = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content||''},
            body: JSON.stringify(data)
        });
        const d = await r.json();
        alertBox(alertId, d.message||'تم الحفظ', 'success');
        if (callback) callback(d);
        setTimeout(() => location.reload(), 1500);
    } catch(e) {
        alertBox(alertId, 'فشل الحفظ', 'danger');
    } finally {
        if (btn) setLoading(btn, false);
    }
}

function saveFacebook(btn) {
    savePlatform(BASE + '/admin/account-configuration/facebook', {
        facebook_pixel_enabled: document.getElementById('fb_pixel_enabled').checked ? 1 : 0,
        facebook_pixel_id: document.getElementById('fb_pixel_id').value,
        facebook_capi_enabled: document.getElementById('fb_capi_enabled').checked ? 1 : 0,
        facebook_access_token: document.getElementById('fb_access_token').value,
        facebook_test_event_code: document.getElementById('fb_test_code').value,
    }, 'fb-alert', null, btn);
}

function saveTikTok(btn) {
    savePlatform(BASE + '/admin/account-configuration/tiktok', {
        tiktok_pixel_enabled: document.getElementById('tt_pixel_enabled').checked ? 1 : 0,
        tiktok_pixel_id: document.getElementById('tt_pixel_id').value,
        tiktok_capi_enabled: document.getElementById('tt_capi_enabled').checked ? 1 : 0,
        tiktok_access_token: document.getElementById('tt_access_token').value,
    }, 'tt-alert', null, btn);
}

function saveGoogle(btn) {
    savePlatform(BASE + '/admin/account-configuration/google', {
        enabled: document.getElementById('ga_enabled').checked ? 1 : 0,
        conversion_id: document.getElementById('ga_conversion_id').value,
        conversion_label: document.getElementById('ga_conversion_label').value,
        google_ads_cid: document.getElementById('ga_cid').value,
        developer_token: document.getElementById('ga_dev_token').value,
        refresh_token: document.getElementById('ga_refresh_token').value,
    }, 'ga-alert', null, btn);
}

function saveSnapchat(btn) {
    savePlatform(BASE + '/admin/account-configuration/snapchat', {
        enabled: document.getElementById('sc_enabled').checked ? 1 : 0,
        pixel_id: document.getElementById('sc_pixel_id').value,
        api_token: document.getElementById('sc_api_token').value,
    }, 'sc-alert', null, btn);
}

function savePinterest(btn) {
    savePlatform(BASE + '/admin/account-configuration/pinterest', {
        enabled: document.getElementById('pi_enabled').checked ? 1 : 0,
        tag_id: document.getElementById('pi_tag_id').value,
        access_token: document.getElementById('pi_access_token').value,
        ad_account_id: document.getElementById('pi_ad_account_id').value,
    }, 'pi-alert', null, btn);
}

function saveTwitter(btn) {
    savePlatform(BASE + '/admin/account-configuration/twitter', {
        enabled: document.getElementById('tw_enabled').checked ? 1 : 0,
        pixel_id: document.getElementById('tw_pixel_id').value,
        api_key: document.getElementById('tw_api_key').value,
    }, 'tw-alert', null, btn);
}

function saveLinkedIn(btn) {
    savePlatform(BASE + '/admin/account-configuration/linkedin', {
        enabled: document.getElementById('li_enabled').checked ? 1 : 0,
        partner_id: document.getElementById('li_partner_id').value,
        access_token: document.getElementById('li_access_token').value,
        conversion_rule_id: document.getElementById('li_conversion_rule_id').value,
    }, 'li-alert', null, btn);
}

function saveCustomApi(btn) {
    savePlatform(BASE + '/admin/account-configuration/custom-api', {
        enabled: document.getElementById('ca_enabled').checked ? 1 : 0,
        api_key: document.getElementById('ca_api_key').value,
    }, 'ca-alert', null, btn);
}

function saveGeneral(btn) {
    savePlatform(BASE + '/admin/account-configuration/general', {
        tracking_enabled: document.getElementById('tracking_enabled').checked ? 1 : 0,
        tracking_test_mode: document.getElementById('test_mode').checked ? 1 : 0,
        tracking_async_mode: document.getElementById('async_mode').checked ? 1 : 0,
    }, 'gen-alert', null, btn);
}

function testConnection(platform) {
    const endpoints = {
        facebook: '/admin/account-configuration/test-facebook',
        tiktok: '/admin/account-configuration/test-tiktok',
        google: '/admin/account-configuration/test-google',
        snapchat: '/admin/account-configuration/test-snapchat',
        pinterest: '/admin/account-configuration/test-pinterest',
        twitter: '/admin/account-configuration/test-twitter',
        linkedin: '/admin/account-configuration/test-linkedin',
        custom_api: '/admin/account-configuration/test-custom-api',
    };
    const alertId = platform === 'custom_api' ? 'ca-alert' : platform + '-alert';
    const url = BASE + (endpoints[platform] || '');
    fetch(url).then(r => r.json()).then(d => {
        alertBox(alertId, d.message || 'تم الاتصال بنجاح', d.success ? 'success' : 'danger');
    }).catch(() => alertBox(alertId, 'فشل الاتصال بالخادم', 'danger'));
}

async function refreshStats() {
    try {
        const r = await fetch(BASE + '/admin/meta-marketing/stats');
        const d = await r.json();
        if (d.success && d.stats) {
            document.getElementById('stat-success').textContent = d.stats.success || 0;
            document.getElementById('stat-failed').textContent = d.stats.failed || 0;
            document.getElementById('stat-total').textContent = d.stats.total || 0;
        }
    } catch(e) {}
}

document.addEventListener('DOMContentLoaded', () => {
    refreshStats();
    setInterval(refreshStats, 30000);
});
</script>
@endsection
