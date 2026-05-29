@extends('admin.layouts.app')

@section('title', 'التسويق والتتبع')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-chart-line" style="color:var(--pink-600);margin-left:8px;"></i> إعدادات التتبع والإعلانات</h1>
        <p class="text-muted small mb-0">إدارة جميع منصات التتبع والإعلانات من مكان واحد</p>
    </div>
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
        ['key' => 'shopify', 'name' => 'Shopify', 'icon' => 'fab fa-shopify', 'color' => '#96BF48', 'enabled' => $settings['shopify']['enabled']],
        ['key' => 'woocommerce', 'name' => 'WooCommerce', 'icon' => 'fab fa-wordpress', 'color' => '#7F54B3', 'enabled' => $settings['woocommerce']['enabled']],
        ['key' => 'custom_api', 'name' => 'API مخصص', 'icon' => 'fas fa-code', 'color' => '#10B981', 'enabled' => $settings['custom_api']['enabled']],
    ] as $platform)
    <div class="col-md-3 col-6">
        <div class="stat-card d-flex align-items-center gap-3 p-3 rounded-3" style="background:#fff;border:1px solid var(--gray-200);">
            <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:{{ $platform['color'] }}10;color:{{ $platform['color'] }};font-size:1.2rem;flex-shrink:0;">
                <i class="{{ $platform['icon'] }}"></i>
            </div>
            <div class="min-w-0">
                <div class="stat-value">
                    <span class="badge rounded-pill fs-6 px-3 py-1 bg-{{ $platform['enabled'] ? 'success' : 'secondary' }} platform-status-{{ $platform['key'] }}">
                        {{ $platform['enabled'] ? 'مفعل' : 'معطل' }}
                    </span>
                </div>
                <div class="stat-label text-muted small mt-1">{{ $platform['name'] }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-4" id="marketing-app">

    {{-- Facebook Pixel & CAPI --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header" style="background:linear-gradient(135deg,#1877F2,#0D6EFD);color:#fff;">
                <i class="fab fa-facebook"></i> Facebook Pixel &amp; CAPI
            </div>
            <div class="card-body">
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="fb_pixel_enabled" {{ $settings['facebook']['enabled'] ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="fb_pixel_enabled">تفعيل Facebook Pixel</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Facebook Pixel ID</label>
                    <input type="text" class="form-control font-monospace" id="fb_pixel_id" value="{{ $settings['facebook']['pixel_id'] }}" placeholder="1234567890123456">
                </div>
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="fb_capi_enabled" {{ $settings['facebook']['capi_enabled'] ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="fb_capi_enabled">تفعيل Facebook Conversions API (Server-Side)</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Facebook Access Token</label>
                    <input type="password" class="form-control font-monospace" id="fb_access_token" value="{{ $settings['facebook']['access_token'] }}" placeholder="EAAxxxxxxxxxxxxx">
                </div>
                <div class="mb-3">
                    <label class="form-label">Test Event Code <small class="text-muted">(اختياري)</small></label>
                    <input type="text" class="form-control font-monospace" id="fb_test_code" value="{{ $settings['facebook']['test_event_code'] ?? '' }}" placeholder="TEST12345">
                </div>

                <div class="d-flex gap-2 flex-wrap mt-3">
                    <button class="btn btn-pink" onclick="saveFacebook()"><i class="fas fa-save"></i> حفظ</button>
                    <button class="btn btn-outline-pink btn-sm" onclick="testConnection('facebook')"><i class="fas fa-plug"></i> اختبار الاتصال</button>
                </div>

                <div id="fb-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- TikTok Pixel & Events API --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header" style="background:#000;color:#fff;">
                <i class="fab fa-tiktok"></i> TikTok Pixel &amp; Events API
            </div>
            <div class="card-body">
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="tt_pixel_enabled" {{ $settings['tiktok']['enabled'] ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="tt_pixel_enabled">تفعيل TikTok Pixel</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">TikTok Pixel ID</label>
                    <input type="text" class="form-control font-monospace" id="tt_pixel_id" value="{{ $settings['tiktok']['pixel_id'] }}" placeholder="XXXXXXXXXX">
                </div>
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="tt_capi_enabled" {{ $settings['tiktok']['capi_enabled'] ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="tt_capi_enabled">تفعيل TikTok Events API (Server-Side)</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">TikTok Access Token</label>
                    <input type="password" class="form-control font-monospace" id="tt_access_token" value="{{ $settings['tiktok']['access_token'] }}" placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-dark" onclick="saveTikTok()"><i class="fas fa-save"></i> حفظ</button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="testConnection('tiktok')"><i class="fas fa-plug"></i> اختبار الاتصال</button>
                </div>
                <div id="tt-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- Google Ads --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header" style="background:linear-gradient(135deg,#4285F4,#34A853);color:#fff;">
                <i class="fab fa-google"></i> Google Ads Conversion Tracking
            </div>
            <div class="card-body">
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="ga_enabled" {{ $settings['google']['enabled'] ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="ga_enabled">تفعيل Google Ads</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Google Conversion ID</label>
                    <input type="text" class="form-control font-monospace" id="ga_conversion_id" value="{{ $settings['google']['conversion_id'] }}" placeholder="AW-123456789">
                </div>
                <div class="mb-3">
                    <label class="form-label">Conversion Label</label>
                    <input type="text" class="form-control font-monospace" id="ga_conversion_label" value="{{ $settings['google']['conversion_label'] }}" placeholder="xxxxxxxxxx">
                </div>
                <div class="mb-3">
                    <label class="form-label">Google Ads CID <small class="text-muted">(Customer ID)</small></label>
                    <input type="text" class="form-control font-monospace" id="ga_cid" value="{{ $settings['google']['google_ads_cid'] }}" placeholder="123-456-7890">
                </div>
                <div class="mb-3">
                    <label class="form-label">Developer Token</label>
                    <input type="password" class="form-control font-monospace" id="ga_dev_token" value="{{ $settings['google']['developer_token'] ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Refresh Token <small class="text-muted">(لـ OAuth)</small></label>
                    <input type="password" class="form-control font-monospace" id="ga_refresh_token" value="{{ $settings['google']['refresh_token'] ?? '' }}">
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn" style="background:#4285F4;color:#fff;" onclick="saveGoogle()"><i class="fas fa-save"></i> حفظ</button>
                    <button class="btn btn-outline-primary btn-sm" onclick="testConnection('google')"><i class="fas fa-plug"></i> اختبار الاتصال</button>
                </div>
                <div id="ga-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- Snapchat --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header" style="background:linear-gradient(135deg,#FFFC00,#FFE600);color:#000;">
                <i class="fab fa-snapchat"></i> Snapchat Pixel
            </div>
            <div class="card-body">
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="sc_enabled" {{ $settings['snapchat']['enabled'] ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="sc_enabled">تفعيل Snapchat Pixel</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Snapchat Pixel ID</label>
                    <input type="text" class="form-control font-monospace" id="sc_pixel_id" value="{{ $settings['snapchat']['pixel_id'] }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">API Token</label>
                    <input type="password" class="form-control font-monospace" id="sc_api_token" value="{{ $settings['snapchat']['api_token'] }}">
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn" style="background:#FFFC00;color:#000;" onclick="saveSnapchat()"><i class="fas fa-save"></i> حفظ</button>
                    <button class="btn btn-outline-warning btn-sm" onclick="testConnection('snapchat')"><i class="fas fa-plug"></i> اختبار الاتصال</button>
                </div>
                <div id="sc-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- Pinterest --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header" style="background:#E60023;color:#fff;">
                <i class="fab fa-pinterest"></i> Pinterest Tag
            </div>
            <div class="card-body">
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="pi_enabled" {{ $settings['pinterest']['enabled'] ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="pi_enabled">تفعيل Pinterest Tag</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Pinterest Tag ID</label>
                    <input type="text" class="form-control font-monospace" id="pi_tag_id" value="{{ $settings['pinterest']['tag_id'] }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Access Token</label>
                    <input type="password" class="form-control font-monospace" id="pi_access_token" value="{{ $settings['pinterest']['access_token'] }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Ad Account ID</label>
                    <input type="text" class="form-control font-monospace" id="pi_ad_account_id" value="{{ $settings['pinterest']['ad_account_id'] ?? '' }}">
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn" style="background:#E60023;color:#fff;" onclick="savePinterest()"><i class="fas fa-save"></i> حفظ</button>
                    <button class="btn btn-outline-danger btn-sm" onclick="testConnection('pinterest')"><i class="fas fa-plug"></i> اختبار الاتصال</button>
                </div>
                <div id="pi-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- Twitter / X --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header" style="background:#000;color:#fff;">
                <i class="fab fa-x-twitter"></i> X (Twitter) Pixel
            </div>
            <div class="card-body">
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="tw_enabled" {{ $settings['twitter']['enabled'] ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="tw_enabled">تفعيل X (Twitter) Pixel</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Pixel ID</label>
                    <input type="text" class="form-control font-monospace" id="tw_pixel_id" value="{{ $settings['twitter']['pixel_id'] }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">API Key</label>
                    <input type="password" class="form-control font-monospace" id="tw_api_key" value="{{ $settings['twitter']['api_key'] }}">
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-dark" onclick="saveTwitter()"><i class="fas fa-save"></i> حفظ</button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="testConnection('twitter')"><i class="fas fa-plug"></i> اختبار الاتصال</button>
                </div>
                <div id="tw-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- LinkedIn --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header" style="background:#0A66C2;color:#fff;">
                <i class="fab fa-linkedin"></i> LinkedIn Insight Tag
            </div>
            <div class="card-body">
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="li_enabled" {{ $settings['linkedin']['enabled'] ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="li_enabled">تفعيل LinkedIn Insight Tag</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Partner ID</label>
                    <input type="text" class="form-control font-monospace" id="li_partner_id" value="{{ $settings['linkedin']['partner_id'] }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Access Token</label>
                    <input type="password" class="form-control font-monospace" id="li_access_token" value="{{ $settings['linkedin']['access_token'] }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Conversion Rule ID <small class="text-muted">(اختياري)</small></label>
                    <input type="text" class="form-control font-monospace" id="li_conversion_rule_id" value="{{ $settings['linkedin']['conversion_rule_id'] ?? '' }}">
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn" style="background:#0A66C2;color:#fff;" onclick="saveLinkedIn()"><i class="fas fa-save"></i> حفظ</button>
                    <button class="btn btn-outline-primary btn-sm" onclick="testConnection('linkedin')"><i class="fas fa-plug"></i> اختبار الاتصال</button>
                </div>
                <div id="li-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- Shopify --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="fab fa-shopify" style="color:#96BF48;margin-left:6px;"></i> Shopify</div>
            <div class="card-body">
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="sp_enabled" {{ $settings['shopify']['enabled'] ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="sp_enabled">تفعيل Shopify Connector</label>
                </div>
                <div class="mb-2">
                    <label class="form-label small">متجر Shopify (myshopify.com)</label>
                    <input type="text" class="form-control font-monospace" id="sp_shop_domain" value="{{ $settings['shopify']['shop_domain'] }}" placeholder="example.myshopify.com">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Admin API Access Token</label>
                    <input type="password" class="form-control font-monospace" id="sp_access_token" value="{{ $settings['shopify']['access_token'] }}" placeholder="shpat_...">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Webhook API Secret</label>
                    <input type="password" class="form-control font-monospace" id="sp_api_secret" value="{{ $settings['shopify']['api_secret'] }}">
                </div>
                <div class="mb-2 small text-muted">
                    <i class="fas fa-info-circle"></i>
                    Webhook URL: <code class="text-info">{{ url('/api/webhooks/shopify/{topic}') }}</code>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn" style="background:#96BF48;color:#fff;" onclick="saveShopify()"><i class="fas fa-save"></i> حفظ</button>
                    <button class="btn btn-outline-primary btn-sm" onclick="testConnection('shopify')"><i class="fas fa-plug"></i> اختبار الاتصال</button>
                </div>
                <div id="sp-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- WooCommerce --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="fab fa-wordpress" style="color:#7F54B3;margin-left:6px;"></i> WooCommerce</div>
            <div class="card-body">
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="wc_enabled" {{ $settings['woocommerce']['enabled'] ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="wc_enabled">تفعيل WooCommerce Connector</label>
                </div>
                <div class="mb-2">
                    <label class="form-label small">رابط المتجر</label>
                    <input type="url" class="form-control font-monospace" id="wc_store_url" value="{{ $settings['woocommerce']['store_url'] }}" placeholder="https://yourstore.com">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Consumer Key</label>
                    <input type="text" class="form-control font-monospace" id="wc_consumer_key" value="{{ $settings['woocommerce']['consumer_key'] }}" placeholder="ck_...">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Consumer Secret</label>
                    <input type="password" class="form-control font-monospace" id="wc_consumer_secret" value="{{ $settings['woocommerce']['consumer_secret'] }}" placeholder="cs_...">
                </div>
                <div class="mb-2 small text-muted">
                    <i class="fas fa-info-circle"></i>
                    Webhook URL: <code class="text-info">{{ url('/api/webhooks/woocommerce') }}</code>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn" style="background:#7F54B3;color:#fff;" onclick="saveWooCommerce()"><i class="fas fa-save"></i> حفظ</button>
                    <button class="btn btn-outline-primary btn-sm" onclick="testConnection('woocommerce')"><i class="fas fa-plug"></i> اختبار الاتصال</button>
                </div>
                <div id="wc-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- Custom API --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-code" style="color:#10B981;margin-left:6px;"></i> API المخصص</div>
            <div class="card-body">
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="ca_enabled" {{ $settings['custom_api']['enabled'] ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="ca_enabled">تفعيل API المخصص</label>
                </div>
                <div class="mb-2">
                    <label class="form-label small">مفتاح API (اختياري)</label>
                    <input type="text" class="form-control font-monospace" id="ca_api_key" value="{{ $settings['custom_api']['api_key'] }}" placeholder="اترك فارغاً بدون مفتاح">
                </div>
                <div class="mb-2 small text-muted">
                    <i class="fas fa-info-circle"></i>
                    POST <code class="text-info">{{ url('/api/tracking/event') }}</code> — إرسال حدث واحد<br>
                    POST <code class="text-info">{{ url('/api/tracking/batch') }}</code> — إرسال أحداث متعددة<br>
                    GET <code class="text-info">{{ url('/api/tracking/health') }}</code> — فحص الحالة
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn" style="background:#10B981;color:#fff;" onclick="saveCustomApi()"><i class="fas fa-save"></i> حفظ</button>
                    <button class="btn btn-outline-primary btn-sm" onclick="testConnection('custom_api')"><i class="fas fa-plug"></i> اختبار</button>
                </div>
                <div id="ca-alert" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- General Settings --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-cog" style="color:var(--pink-600);margin-left:6px;"></i> الإعدادات العامة</div>
            <div class="card-body">
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="tracking_enabled" {{ !$settings['test_mode'] ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="tracking_enabled">تفعيل نظام التتبع بالكامل</label>
                </div>
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="test_mode" {{ $settings['test_mode'] ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="test_mode">وضع الاختبار (Test Mode)</label>
                </div>
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="async_mode" checked>
                    <label class="form-check-label fw-bold" for="async_mode">استخدام Queue (إرسال في الخلفية)</label>
                </div>
                <div class="mt-3 p-3 rounded-3" style="background:#f8f9fa;">
                    <label class="form-label fw-bold mb-2"><i class="fas fa-info-circle text-info"></i> إحصائيات التتبع (آخر 24 ساعة)</label>
                    <div class="row g-2 text-center small" id="capi-stats">
                        <div class="col-4"><div class="p-2 rounded-3 bg-white border"><strong class="d-block text-success" id="stat-success">0</strong><span class="text-muted">ناجح</span></div></div>
                        <div class="col-4"><div class="p-2 rounded-3 bg-white border"><strong class="d-block text-danger" id="stat-failed">0</strong><span class="text-muted">فاشل</span></div></div>
                        <div class="col-4"><div class="p-2 rounded-3 bg-white border"><strong class="d-block text-primary" id="stat-total">0</strong><span class="text-muted">إجمالي</span></div></div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button class="btn btn-pink btn-sm" onclick="saveGeneral()"><i class="fas fa-save"></i> حفظ</button>
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
    el.innerHTML = `<div class="alert alert-${type} py-2 px-3 mb-0 rounded-3 small">${msg}</div>`;
    setTimeout(() => el.innerHTML = '', 4000);
}

async function savePlatform(url, data, alertId, callback) {
    try {
        const r = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content||''},
            body: JSON.stringify(data)
        });
        const d = await r.json();
        alertBox(alertId, d.message||'تم الحفظ', 'success');
        if (callback) callback(d);
        location.reload();
    } catch(e) {
        alertBox(alertId, 'فشل الحفظ', 'danger');
    }
}

function saveFacebook() {
    savePlatform(BASE + '/admin/marketing/facebook', {
        facebook_pixel_enabled: document.getElementById('fb_pixel_enabled').checked ? 1 : 0,
        facebook_pixel_id: document.getElementById('fb_pixel_id').value,
        facebook_capi_enabled: document.getElementById('fb_capi_enabled').checked ? 1 : 0,
        facebook_access_token: document.getElementById('fb_access_token').value,
        facebook_test_event_code: document.getElementById('fb_test_code').value,
    }, 'fb-alert');
}

function saveTikTok() {
    savePlatform(BASE + '/admin/marketing/tiktok', {
        tiktok_pixel_enabled: document.getElementById('tt_pixel_enabled').checked ? 1 : 0,
        tiktok_pixel_id: document.getElementById('tt_pixel_id').value,
        tiktok_capi_enabled: document.getElementById('tt_capi_enabled').checked ? 1 : 0,
        tiktok_access_token: document.getElementById('tt_access_token').value,
    }, 'tt-alert');
}

function saveGoogle() {
    savePlatform(BASE + '/admin/marketing/google', {
        enabled: document.getElementById('ga_enabled').checked ? 1 : 0,
        conversion_id: document.getElementById('ga_conversion_id').value,
        conversion_label: document.getElementById('ga_conversion_label').value,
        google_ads_cid: document.getElementById('ga_cid').value,
        developer_token: document.getElementById('ga_dev_token').value,
        refresh_token: document.getElementById('ga_refresh_token').value,
    }, 'ga-alert');
}

function saveSnapchat() {
    savePlatform(BASE + '/admin/marketing/snapchat', {
        enabled: document.getElementById('sc_enabled').checked ? 1 : 0,
        pixel_id: document.getElementById('sc_pixel_id').value,
        api_token: document.getElementById('sc_api_token').value,
    }, 'sc-alert');
}

function savePinterest() {
    savePlatform(BASE + '/admin/marketing/pinterest', {
        enabled: document.getElementById('pi_enabled').checked ? 1 : 0,
        tag_id: document.getElementById('pi_tag_id').value,
        access_token: document.getElementById('pi_access_token').value,
        ad_account_id: document.getElementById('pi_ad_account_id').value,
    }, 'pi-alert');
}

function saveTwitter() {
    savePlatform(BASE + '/admin/marketing/twitter', {
        enabled: document.getElementById('tw_enabled').checked ? 1 : 0,
        pixel_id: document.getElementById('tw_pixel_id').value,
        api_key: document.getElementById('tw_api_key').value,
    }, 'tw-alert');
}

function saveLinkedIn() {
    savePlatform(BASE + '/admin/marketing/linkedin', {
        enabled: document.getElementById('li_enabled').checked ? 1 : 0,
        partner_id: document.getElementById('li_partner_id').value,
        access_token: document.getElementById('li_access_token').value,
        conversion_rule_id: document.getElementById('li_conversion_rule_id').value,
    }, 'li-alert');
}

function saveShopify() {
    savePlatform(BASE + '/admin/marketing/shopify', {
        enabled: document.getElementById('sp_enabled').checked ? 1 : 0,
        shop_domain: document.getElementById('sp_shop_domain').value,
        access_token: document.getElementById('sp_access_token').value,
        api_secret: document.getElementById('sp_api_secret').value,
    }, 'sp-alert');
}

function saveWooCommerce() {
    savePlatform(BASE + '/admin/marketing/woocommerce', {
        enabled: document.getElementById('wc_enabled').checked ? 1 : 0,
        store_url: document.getElementById('wc_store_url').value,
        consumer_key: document.getElementById('wc_consumer_key').value,
        consumer_secret: document.getElementById('wc_consumer_secret').value,
    }, 'wc-alert');
}

function saveCustomApi() {
    savePlatform(BASE + '/admin/marketing/custom-api', {
        enabled: document.getElementById('ca_enabled').checked ? 1 : 0,
        api_key: document.getElementById('ca_api_key').value,
    }, 'ca-alert');
}

function saveGeneral() {
    savePlatform(BASE + '/admin/marketing/general', {
        tracking_enabled: document.getElementById('tracking_enabled').checked ? 1 : 0,
        tracking_test_mode: document.getElementById('test_mode').checked ? 1 : 0,
        tracking_async_mode: document.getElementById('async_mode').checked ? 1 : 0,
    }, 'gen-alert');
}

function testConnection(platform) {
    const endpoints = {
        facebook: '/admin/marketing/test-facebook',
        tiktok: '/admin/marketing/test-tiktok',
        google: '/admin/marketing/test-google',
        snapchat: '/admin/marketing/test-snapchat',
        pinterest: '/admin/marketing/test-pinterest',
        twitter: '/admin/marketing/test-twitter',
        linkedin: '/admin/marketing/test-linkedin',
        shopify: '/admin/marketing/test-shopify',
        woocommerce: '/admin/marketing/test-woocommerce',
        custom_api: '/admin/marketing/test-custom-api',
    };
    const url = BASE + (endpoints[platform] || '');
    fetch(url).then(r => r.json()).then(d => {
        alert(d.message || 'تم الاتصال بنجاح');
    }).catch(() => alert('فشل الاتصال'));
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
