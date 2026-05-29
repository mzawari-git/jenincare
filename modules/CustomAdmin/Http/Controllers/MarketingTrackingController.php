<?php

namespace Modules\CustomAdmin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\MarketingSetting;

class MarketingTrackingController extends Controller
{
    public function index()
    {
        $fbEnabled = $this->gv('facebook_pixel_enabled', '0');
        $ttEnabled = $this->gv('tiktok_pixel_enabled', '0');
        $gaEnabled = $this->gv('google_ads_enabled', '0');
        $scEnabled = $this->gv('snapchat_pixel_enabled', '0');
        $piEnabled = $this->gv('pinterest_tag_enabled', '0');
        $twEnabled = $this->gv('twitter_pixel_enabled', '0');
        $liEnabled = $this->gv('linkedin_insight_enabled', '0');
        $spEnabled = $this->gv('shopify_enabled', '0');
        $wcEnabled = $this->gv('woocommerce_enabled', '0');
        $caEnabled = $this->gv('custom_api_enabled', '0');

        $settings = [
            'facebook' => [
                'enabled' => $fbEnabled === '1',
                'pixel_id' => $this->gv('facebook_pixel_id'),
                'access_token' => $this->gv('facebook_access_token'),
                'capi_enabled' => $this->gv('facebook_capi_enabled', '0') === '1',
                'test_event_code' => $this->gv('facebook_test_event_code'),
                'test_mode' => false,
            ],
            'tiktok' => [
                'enabled' => $ttEnabled === '1',
                'pixel_id' => $this->gv('tiktok_pixel_id'),
                'access_token' => $this->gv('tiktok_access_token'),
                'capi_enabled' => $this->gv('tiktok_capi_enabled', '0') === '1',
                'test_mode' => false,
            ],
            'google' => [
                'enabled' => $gaEnabled === '1',
                'conversion_id' => $this->gv('google_conversion_id'),
                'conversion_label' => $this->gv('google_conversion_label'),
                'google_ads_cid' => $this->gv('google_ads_cid'),
                'developer_token' => $this->gv('google_ads_developer_token'),
                'refresh_token' => $this->gv('google_ads_refresh_token'),
            ],
            'snapchat' => [
                'enabled' => $scEnabled === '1',
                'pixel_id' => $this->gv('snapchat_pixel_id'),
                'api_token' => $this->gv('snapchat_api_token'),
            ],
            'pinterest' => [
                'enabled' => $piEnabled === '1',
                'tag_id' => $this->gv('pinterest_tag_id'),
                'access_token' => $this->gv('pinterest_access_token'),
                'ad_account_id' => $this->gv('pinterest_ad_account_id'),
            ],
            'twitter' => [
                'enabled' => $twEnabled === '1',
                'pixel_id' => $this->gv('twitter_pixel_id'),
                'api_key' => $this->gv('twitter_api_key'),
            ],
            'linkedin' => [
                'enabled' => $liEnabled === '1',
                'partner_id' => $this->gv('linkedin_partner_id'),
                'access_token' => $this->gv('linkedin_access_token'),
                'conversion_rule_id' => $this->gv('linkedin_conversion_rule_id'),
            ],
            'shopify' => [
                'enabled' => $spEnabled === '1',
                'shop_domain' => $this->gv('shopify_shop_domain'),
                'access_token' => $this->gv('shopify_access_token'),
                'api_secret' => $this->gv('shopify_api_secret'),
            ],
            'woocommerce' => [
                'enabled' => $wcEnabled === '1',
                'store_url' => $this->gv('woocommerce_store_url'),
                'consumer_key' => $this->gv('woocommerce_consumer_key'),
                'consumer_secret' => $this->gv('woocommerce_consumer_secret'),
            ],
            'custom_api' => [
                'enabled' => $caEnabled === '1',
                'api_key' => $this->gv('custom_api_key'),
            ],
            'tracking_enabled' => $this->gv('tracking_enabled', '1') === '1',
            'test_mode' => $this->gv('tracking_test_mode', '0') === '1',
        ];

        return view('admin.marketing.index', compact('settings'));
    }

    public function metaMarketingDashboard()
    {
        return view('admin.meta-marketing.index', [
            'realStats' => ['total_orders' => 0, 'total_revenue' => 0, 'total_users' => 0, 'conversion_rate' => 0],
            'recentOrders' => [],
            'funnelData' => ['product_views' => 0, 'add_to_cart' => 0, 'checkout' => 0, 'purchases' => 0],
            'topProducts' => [],
            'leadStats' => ['hot' => 0, 'warm' => 0, 'cold' => 0, 'engaged' => 0, 'new' => 0],
            'pages' => collect([]),
            'settings' => MarketingSetting::getAllTrackingSettings(),
        ]);
    }

    private function gv($key, $def = '')
    {
        try {
            $v = Setting::where('key', $key)->value('value');
            if (is_string($v)) {
                $d = json_decode($v, true);
                if ($d !== null && is_string($d)) return $d;
            }
            return $v ?? $def;
        } catch (\Exception $e) {
            return $def;
        }
    }

    public function conversations() { return redirect()->route('admin.meta-marketing.index'); }
    public function leads() { return redirect()->route('admin.leads-hub.index'); }
    public function audiences() { return redirect()->route('admin.meta-marketing.index'); }
    public function webhookLogs() { return redirect()->route('admin.meta-marketing.index'); }
    public function dashboardStats() {
        try {
            $stats = [
                'success' => \App\Models\CapiEventLog::where('status', 'success')->count(),
                'failed' => \App\Models\CapiEventLog::where('status', 'failed')->count(),
                'pending' => \App\Models\CapiEventLog::where('status', 'pending')->count(),
                'today' => \App\Models\CapiEventLog::whereDate('created_at', today())->count(),
            ];
            return response()->json(['success' => true, 'stats' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    public function store(Request $r) { return redirect()->route('admin.meta-marketing.index'); }
    public function importPage(Request $r) { return redirect()->route('admin.meta-marketing.index'); }
    public function searchPage(Request $r) { return redirect()->route('admin.meta-marketing.index'); }
    public function conversationShow($id) { return redirect()->route('admin.meta-marketing.index'); }
    public function replyConversation(Request $r, $id) { return redirect()->route('admin.meta-marketing.index'); }
    public function deletePage($id) { return redirect()->route('admin.meta-marketing.index'); }

    public function updateFacebook(Request $r)
    {
        $m = ['facebook_pixel_enabled' => 'facebook_pixel_enabled', 'facebook_pixel_id' => 'facebook_pixel_id',
              'facebook_capi_enabled' => 'facebook_capi_enabled', 'facebook_access_token' => 'facebook_access_token',
              'facebook_test_event_code' => 'facebook_test_event_code'];
        foreach ($r->all() as $k => $v) {
            if (isset($m[$k])) Setting::updateOrCreate(['key' => $m[$k]], ['value' => is_bool($v) ? ($v ? '1' : '0') : (string) $v]);
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات فيسبوك']);
    }

    public function updateTikTok(Request $r)
    {
        $m = ['tiktok_pixel_enabled' => 'tiktok_pixel_enabled', 'tiktok_pixel_id' => 'tiktok_pixel_id',
              'tiktok_capi_enabled' => 'tiktok_capi_enabled', 'tiktok_access_token' => 'tiktok_access_token'];
        foreach ($r->all() as $k => $v) {
            if (isset($m[$k])) Setting::updateOrCreate(['key' => $m[$k]], ['value' => is_bool($v) ? ($v ? '1' : '0') : (string) $v]);
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات تيك توك']);
    }

    public function updateGoogle(Request $r)
    {
        $m = ['enabled' => 'google_ads_enabled', 'conversion_id' => 'google_conversion_id',
              'conversion_label' => 'google_conversion_label', 'google_ads_cid' => 'google_ads_cid',
              'developer_token' => 'google_ads_developer_token', 'refresh_token' => 'google_ads_refresh_token'];
        foreach ($r->all() as $k => $v) {
            if (isset($m[$k])) Setting::updateOrCreate(['key' => $m[$k]], ['value' => is_bool($v) ? ($v ? '1' : '0') : (string) $v]);
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات Google Ads']);
    }

    public function updateSnapchat(Request $r)
    {
        $m = ['enabled' => 'snapchat_pixel_enabled', 'pixel_id' => 'snapchat_pixel_id', 'api_token' => 'snapchat_api_token'];
        foreach ($r->all() as $k => $v) {
            if (isset($m[$k])) Setting::updateOrCreate(['key' => $m[$k]], ['value' => is_bool($v) ? ($v ? '1' : '0') : (string) $v]);
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات سناب شات']);
    }

    public function updatePinterest(Request $r)
    {
        $m = ['enabled' => 'pinterest_tag_enabled', 'tag_id' => 'pinterest_tag_id',
              'access_token' => 'pinterest_access_token', 'ad_account_id' => 'pinterest_ad_account_id'];
        foreach ($r->all() as $k => $v) {
            if (isset($m[$k])) Setting::updateOrCreate(['key' => $m[$k]], ['value' => is_bool($v) ? ($v ? '1' : '0') : (string) $v]);
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات بنترست']);
    }

    public function updateTwitter(Request $r)
    {
        $m = ['enabled' => 'twitter_pixel_enabled', 'pixel_id' => 'twitter_pixel_id', 'api_key' => 'twitter_api_key'];
        foreach ($r->all() as $k => $v) {
            if (isset($m[$k])) Setting::updateOrCreate(['key' => $m[$k]], ['value' => is_bool($v) ? ($v ? '1' : '0') : (string) $v]);
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات تويتر']);
    }

    public function updateLinkedIn(Request $r)
    {
        $m = ['enabled' => 'linkedin_insight_enabled', 'partner_id' => 'linkedin_partner_id',
              'access_token' => 'linkedin_access_token', 'conversion_rule_id' => 'linkedin_conversion_rule_id'];
        foreach ($r->all() as $k => $v) {
            if (isset($m[$k])) Setting::updateOrCreate(['key' => $m[$k]], ['value' => is_bool($v) ? ($v ? '1' : '0') : (string) $v]);
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات لينكد إن']);
    }

    public function updateGeneral(Request $r)
    {
        foreach ($r->except('_token') as $k => $v) {
            Setting::updateOrCreate(['key' => $k], ['value' => (string) $v]);
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true]);
    }

    public function saveOAuthCredentials(Request $r)
    {
        $platform = $r->input('platform');
        $envKeys = [
            'meta' => ['META_APP_ID', 'META_APP_SECRET', 'META_WEBHOOK_VERIFY_TOKEN'],
            'tiktok' => ['TIKTOK_APP_ID', 'TIKTOK_APP_SECRET'],
            'google' => ['GOOGLE_CLIENT_ID', 'GOOGLE_CLIENT_SECRET'],
            'snapchat' => ['SNAPCHAT_CLIENT_ID', 'SNAPCHAT_CLIENT_SECRET'],
            'pinterest' => ['PINTEREST_APP_ID', 'PINTEREST_APP_SECRET'],
            'twitter' => ['TWITTER_CLIENT_ID', 'TWITTER_CLIENT_SECRET'],
            'linkedin' => ['LINKEDIN_CLIENT_ID', 'LINKEDIN_CLIENT_SECRET'],
            'shopify' => ['SHOPIFY_API_KEY', 'SHOPIFY_API_SECRET'],
        ];

        if (!isset($envKeys[$platform])) {
            return response()->json(['success' => false, 'message' => 'منصة غير معروفة']);
        }

        foreach ($envKeys[$platform] as $key) {
            $value = $r->input(strtolower($key), '');
            \App\Helpers\EnvManager::set($key, $value);
        }

        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => "تم حفظ مفاتيح OAuth لـ {$platform} في .env"]);
    }

    public function testFacebook() {
        try {
            $service = app(\App\Services\AdvertisingTrackingService::class);
            return $service->testFacebook()
                ? response()->json(['success' => true, 'message' => 'تم الاتصال بفيسبوك بنجاح'])
                : response()->json(['success' => false, 'message' => 'فشل الاتصال بفيسبوك']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
    }
    public function testTikTok() {
        try {
            $service = app(\App\Services\AdvertisingTrackingService::class);
            return $service->testTikTok()
                ? response()->json(['success' => true, 'message' => 'تم الاتصال بتيك توك بنجاح'])
                : response()->json(['success' => false, 'message' => 'فشل الاتصال بتيك توك']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
    }
    public function testGoogle() {
        try {
            $service = app(\App\Services\GoogleAdsService::class);
            return $service->testConnection()
                ? response()->json(['success' => true, 'message' => 'تم الاتصال بجوجل بنجاح'])
                : response()->json(['success' => false, 'message' => 'فشل الاتصال بجوجل']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
    }
    public function testSnapchat() {
        try {
            $service = app(\App\Services\SnapchatService::class);
            return $service->testConnection()
                ? response()->json(['success' => true, 'message' => 'تم الاتصال بساب شات بنجاح'])
                : response()->json(['success' => false, 'message' => 'فشل الاتصال بساب شات']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
    }
    public function testPinterest() {
        try {
            $service = app(\App\Services\PinterestService::class);
            return $service->testConnection()
                ? response()->json(['success' => true, 'message' => 'تم الاتصال بينترست بنجاح'])
                : response()->json(['success' => false, 'message' => 'فشل الاتصال بينترست']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
    }
    public function testTwitter() {
        try {
            $service = app(\App\Services\TwitterService::class);
            return $service->testConnection()
                ? response()->json(['success' => true, 'message' => 'تم الاتصال بتويتر بنجاح'])
                : response()->json(['success' => false, 'message' => 'فشل الاتصال بتويتر']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
    }
    public function testLinkedIn() {
        try {
            $service = app(\App\Services\LinkedInService::class);
            return $service->testConnection()
                ? response()->json(['success' => true, 'message' => 'تم الاتصال بلينكد إن بنجاح'])
                : response()->json(['success' => false, 'message' => 'فشل الاتصال بلينكد إن']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
    }
    public function sendTestEvent(Request $r) {
        try {
            $service = app(\App\Services\AdvertisingTrackingService::class);
            $eventId = $service->trackEvent(
                eventName: $r->input('event_name', 'CustomEvent'),
                customData: $r->input('custom_data', ['value' => 1.00, 'currency' => 'ILS']),
                userData: $r->input('user_data', []),
                source: 'admin_test',
                platforms: $r->input('platforms', null),
            );
            return response()->json(['success' => true, 'event_id' => $eventId, 'message' => 'تم إرسال حدث اختباري']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'فشل: ' . $e->getMessage()]);
        }
    }

    public function updateShopify(Request $r)
    {
        $m = ['enabled' => 'shopify_enabled', 'shop_domain' => 'shopify_shop_domain',
              'access_token' => 'shopify_access_token', 'api_secret' => 'shopify_api_secret'];
        foreach ($r->all() as $k => $v) {
            if (isset($m[$k])) Setting::updateOrCreate(['key' => $m[$k]], ['value' => is_bool($v) ? ($v ? '1' : '0') : (string) $v]);
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات Shopify']);
    }

    public function updateWooCommerce(Request $r)
    {
        $m = ['enabled' => 'woocommerce_enabled', 'store_url' => 'woocommerce_store_url',
              'consumer_key' => 'woocommerce_consumer_key', 'consumer_secret' => 'woocommerce_consumer_secret'];
        foreach ($r->all() as $k => $v) {
            if (isset($m[$k])) Setting::updateOrCreate(['key' => $m[$k]], ['value' => is_bool($v) ? ($v ? '1' : '0') : (string) $v]);
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات WooCommerce']);
    }

    public function updateCustomApi(Request $r)
    {
        $m = ['enabled' => 'custom_api_enabled', 'api_key' => 'custom_api_key'];
        foreach ($r->all() as $k => $v) {
            if (isset($m[$k])) Setting::updateOrCreate(['key' => $m[$k]], ['value' => is_bool($v) ? ($v ? '1' : '0') : (string) $v]);
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات API المخصص']);
    }

    public function testShopify() {
        try {
            $service = app(\App\Services\ShopifyService::class);
            return $service->testConnection()
                ? response()->json(['success' => true, 'message' => 'تم اختبار الاتصال بـ Shopify بنجاح'])
                : response()->json(['success' => false, 'message' => 'فشل اختبار اتصال Shopify']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
    }
    public function testWooCommerce() {
        try {
            $service = app(\App\Services\WooCommerceService::class);
            return $service->testConnection()
                ? response()->json(['success' => true, 'message' => 'تم اختبار الاتصال بـ WooCommerce بنجاح'])
                : response()->json(['success' => false, 'message' => 'فشل اختبار اتصال WooCommerce']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
    }
    public function testCustomApi() {
        try {
            $enabled = \App\Models\MarketingSetting::get('custom_api_enabled', false);
            return response()->json([
                'success' => (bool) $enabled,
                'message' => $enabled ? 'API المخصص يعمل ومفعل' : 'API المخصص غير مفعل',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
        }
    }
}
