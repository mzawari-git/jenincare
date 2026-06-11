<?php

namespace Modules\CustomAdmin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MarketingSetting;
use App\Services\MetaReportingService;

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
            'custom_api' => [
                'enabled' => $caEnabled === '1',
                'api_key' => $this->gv('custom_api_key'),
            ],
            'tracking_enabled' => $this->gv('tracking_enabled', '1') === '1',
            'test_mode' => $this->gv('tracking_test_mode', '0') === '1',
        ];

        return view('admin.account-configuration.index', compact('settings'));
    }

    public function metaMarketingDashboard()
    {
        $reporting = app(MetaReportingService::class);
        $settings = MarketingSetting::getAllTrackingSettings();
        $overview = $reporting->getOverview(30);
        $revenueTrend = $reporting->getRevenueTrend(30);
        $capiTrend = $reporting->getCapiTrend(7);
        $campaigns = $reporting->getCampaignPerformance();
        $healthScores = $reporting->getHealthScores();
        $hourlyVolume = $reporting->getHourlyCapiVolume(7);
        $bookingStatus = $reporting->getBookingStatusDistribution(30);

        return view('admin.meta-marketing.index', [
            'settings' => $settings,
            'overview' => $overview,
            'revenueTrend' => $revenueTrend,
            'capiTrend' => $capiTrend,
            'campaigns' => $campaigns,
            'healthScores' => $healthScores,
            'hourlyVolume' => $hourlyVolume,
            'bookingStatus' => $bookingStatus,
        ]);
    }

    private function gv($key, $def = '')
    {
        try {
            $v = MarketingSetting::get($key);
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
                'success' => \App\Models\CapiEventLog::where('success', true)->count(),
                'failed' => \App\Models\CapiEventLog::where('success', false)->count(),
                'pending' => \App\Models\CapiEventLog::whereNull('success')->count(),
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
        $keys = ['facebook_pixel_enabled', 'facebook_pixel_id', 'facebook_capi_enabled', 'facebook_access_token', 'facebook_test_event_code'];
        foreach ($r->all() as $k => $v) {
            if (in_array($k, $keys)) {
                MarketingSetting::setValue($k, is_bool($v) ? ($v ? '1' : '0') : (string) $v, 'facebook');
            }
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات فيسبوك']);
    }

    public function updateTikTok(Request $r)
    {
        $keys = ['tiktok_pixel_enabled', 'tiktok_pixel_id', 'tiktok_capi_enabled', 'tiktok_access_token'];
        foreach ($r->all() as $k => $v) {
            if (in_array($k, $keys)) {
                MarketingSetting::setValue($k, is_bool($v) ? ($v ? '1' : '0') : (string) $v, 'tiktok');
            }
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات تيك توك']);
    }

    public function updateGoogle(Request $r)
    {
        $map = ['enabled' => 'google_ads_enabled', 'conversion_id' => 'google_conversion_id',
                'conversion_label' => 'google_conversion_label', 'google_ads_cid' => 'google_ads_cid',
                'developer_token' => 'google_ads_developer_token', 'refresh_token' => 'google_ads_refresh_token'];
        foreach ($r->all() as $k => $v) {
            if (isset($map[$k])) {
                MarketingSetting::setValue($map[$k], is_bool($v) ? ($v ? '1' : '0') : (string) $v, 'google');
            }
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات Google Ads']);
    }

    public function updateSnapchat(Request $r)
    {
        $map = ['enabled' => 'snapchat_pixel_enabled', 'pixel_id' => 'snapchat_pixel_id', 'api_token' => 'snapchat_api_token'];
        foreach ($r->all() as $k => $v) {
            if (isset($map[$k])) {
                MarketingSetting::setValue($map[$k], is_bool($v) ? ($v ? '1' : '0') : (string) $v, 'snapchat');
            }
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات سناب شات']);
    }

    public function updatePinterest(Request $r)
    {
        $map = ['enabled' => 'pinterest_tag_enabled', 'tag_id' => 'pinterest_tag_id',
                'access_token' => 'pinterest_access_token', 'ad_account_id' => 'pinterest_ad_account_id'];
        foreach ($r->all() as $k => $v) {
            if (isset($map[$k])) {
                MarketingSetting::setValue($map[$k], is_bool($v) ? ($v ? '1' : '0') : (string) $v, 'pinterest');
            }
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات بنترست']);
    }

    public function updateTwitter(Request $r)
    {
        $map = ['enabled' => 'twitter_pixel_enabled', 'pixel_id' => 'twitter_pixel_id', 'api_key' => 'twitter_api_key'];
        foreach ($r->all() as $k => $v) {
            if (isset($map[$k])) {
                MarketingSetting::setValue($map[$k], is_bool($v) ? ($v ? '1' : '0') : (string) $v, 'twitter');
            }
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات تويتر']);
    }

    public function updateLinkedIn(Request $r)
    {
        $map = ['enabled' => 'linkedin_insight_enabled', 'partner_id' => 'linkedin_partner_id',
                'access_token' => 'linkedin_access_token', 'conversion_rule_id' => 'linkedin_conversion_rule_id'];
        foreach ($r->all() as $k => $v) {
            if (isset($map[$k])) {
                MarketingSetting::setValue($map[$k], is_bool($v) ? ($v ? '1' : '0') : (string) $v, 'linkedin');
            }
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات لينكد إن']);
    }

    public function updateGeneral(Request $r)
    {
        foreach ($r->except('_token') as $k => $v) {
            MarketingSetting::setValue($k, (string) $v, 'general');
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
            //'shopify' => ['SHOPIFY_API_KEY', 'SHOPIFY_API_SECRET'],
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
            $result = $service->testFacebook();
            return response()->json($result);
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
            $result = $service->trackEvent(
                eventName: $r->input('event_name', 'CustomEvent'),
                eventData: $r->input('custom_data', ['value' => 1.00, 'currency' => 'ILS']),
                userData: $r->input('user_data', []),
                actionSource: 'admin_test',
            );
            return response()->json(['success' => ($result['success'] ?? false), 'event_id' => $result['event_id'] ?? null, 'message' => 'تم إرسال حدث اختباري']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'فشل: ' . $e->getMessage()]);
        }
    }

    public function saveCustomApi(Request $r)
    {
        $keys = ['custom_api_enabled', 'custom_api_key'];
        foreach ($r->all() as $k => $v) {
            if (in_array($k, $keys)) {
                MarketingSetting::setValue($k, is_bool($v) ? ($v ? '1' : '0') : (string) $v, 'custom_api');
            }
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات API المخصص']);
    }

    public function updateCustomApi(Request $r)
    {
        $map = ['enabled' => 'custom_api_enabled', 'api_key' => 'custom_api_key'];
        foreach ($r->all() as $k => $v) {
            if (isset($map[$k])) {
                MarketingSetting::setValue($map[$k], is_bool($v) ? ($v ? '1' : '0') : (string) $v, 'custom_api');
            }
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات API المخصص']);
    }

    public function updateShopify(Request $r)
    {
        $map = ['enabled' => 'shopify_integration_enabled', 'shop_url' => 'shopify_shop_url',
                'access_token' => 'shopify_access_token', 'api_key' => 'shopify_api_key',
                'api_secret' => 'shopify_api_secret'];
        foreach ($r->all() as $k => $v) {
            if (isset($map[$k])) {
                MarketingSetting::setValue($map[$k], is_bool($v) ? ($v ? '1' : '0') : (string) $v, 'shopify');
            }
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات Shopify']);
    }

    public function updateWooCommerce(Request $r)
    {
        $map = ['enabled' => 'woocommerce_integration_enabled', 'site_url' => 'woocommerce_site_url',
                'consumer_key' => 'woocommerce_consumer_key', 'consumer_secret' => 'woocommerce_consumer_secret'];
        foreach ($r->all() as $k => $v) {
            if (isset($map[$k])) {
                MarketingSetting::setValue($map[$k], is_bool($v) ? ($v ? '1' : '0') : (string) $v, 'woocommerce');
            }
        }
        \App\Helpers\SettingsHelper::clearCache();
        return response()->json(['success' => true, 'message' => 'تم حفظ إعدادات WooCommerce']);
    }

    public function testShopify()
    {
        try {
            $service = app(\App\Services\ShopifyService::class);
            return $service->testConnection()
                ? response()->json(['success' => true, 'message' => 'Shopify connection successful'])
                : response()->json(['success' => false, 'message' => 'Shopify connection failed']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function testWooCommerce()
    {
        try {
            $service = app(\App\Services\WooCommerceService::class);
            return $service->testConnection()
                ? response()->json(['success' => true, 'message' => 'WooCommerce connection successful'])
                : response()->json(['success' => false, 'message' => 'WooCommerce connection failed']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
