<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingSetting extends Model
{
    protected $table = 'marketing_settings';

    protected $fillable = [
        'key', 'value', 'group', 'type', 'options', 'description',
        'is_translatable', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'value' => 'json',
        'options' => 'json',
        'is_translatable' => 'boolean',
    ];

    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if (!$setting || $setting->value === null) return $default;
        $decoded = json_decode($setting->value, true);
        return $decoded ?? $setting->value;
    }

    public static function set($key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => json_encode($value, JSON_UNESCAPED_UNICODE)]
        );
    }

    public static function setValue($key, $value, $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => json_encode($value, JSON_UNESCAPED_UNICODE), 'group' => $group]
        );
    }

    public static function getAllTrackingSettings(): array
    {
        return [
            'facebook' => [
                'enabled' => (bool) static::get('facebook_pixel_enabled', false),
                'pixel_id' => static::get('facebook_pixel_id'),
                'capi_enabled' => (bool) static::get('facebook_capi_enabled', false),
                'access_token' => static::get('facebook_access_token'),
                'test_event_code' => static::get('facebook_test_event_code'),
            ],
            'tiktok' => [
                'enabled' => (bool) static::get('tiktok_pixel_enabled', false),
                'pixel_id' => static::get('tiktok_pixel_id'),
                'capi_enabled' => (bool) static::get('tiktok_capi_enabled', false),
                'access_token' => static::get('tiktok_access_token'),
            ],
            'google' => [
                'enabled' => (bool) static::get('google_ads_enabled', false),
                'conversion_id' => static::get('google_conversion_id'),
                'conversion_label' => static::get('google_conversion_label'),
                'google_ads_cid' => static::get('google_ads_cid'),
            ],
            'snapchat' => [
                'enabled' => (bool) static::get('snapchat_pixel_enabled', false),
                'pixel_id' => static::get('snapchat_pixel_id'),
                'api_token' => static::get('snapchat_api_token'),
            ],
            'pinterest' => [
                'enabled' => (bool) static::get('pinterest_tag_enabled', false),
                'tag_id' => static::get('pinterest_tag_id'),
                'access_token' => static::get('pinterest_access_token'),
            ],
            'twitter' => [
                'enabled' => (bool) static::get('twitter_pixel_enabled', false),
                'pixel_id' => static::get('twitter_pixel_id'),
                'api_key' => static::get('twitter_api_key'),
            ],
            'linkedin' => [
                'enabled' => (bool) static::get('linkedin_insight_enabled', false),
                'partner_id' => static::get('linkedin_partner_id'),
                'access_token' => static::get('linkedin_access_token'),
            ],
            'test_mode' => (bool) static::get('tracking_test_mode', false),
        ];
    }

    public static function getMarketingDefaults(): array
    {
        return [
            'facebook_pixel_enabled' => false,
            'facebook_capi_enabled' => false,
            'tiktok_pixel_enabled' => false,
            'tiktok_capi_enabled' => false,
            'google_ads_enabled' => false,
            'snapchat_pixel_enabled' => false,
            'pinterest_tag_enabled' => false,
            'twitter_pixel_enabled' => false,
            'linkedin_insight_enabled' => false,
            'tracking_enabled' => true,
            'tracking_test_mode' => false,
            'tracking_async_mode' => true,
        ];
    }

    public static function saveFacebookSettings(array $data): void
    {
        $keys = ['facebook_pixel_enabled', 'facebook_pixel_id', 'facebook_capi_enabled', 'facebook_access_token', 'facebook_test_event_code'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                static::setValue($key, $data[$key], 'facebook');
            }
        }
    }

    public static function saveTikTokSettings(array $data): void
    {
        $keys = ['tiktok_pixel_enabled', 'tiktok_pixel_id', 'tiktok_capi_enabled', 'tiktok_access_token'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                static::setValue($key, $data[$key], 'tiktok');
            }
        }
    }

    public static function saveGoogleAdsSettings(array $data): void
    {
        $keys = ['google_ads_enabled', 'google_conversion_id', 'google_conversion_label', 'google_ads_cid', 'google_ads_developer_token', 'google_ads_refresh_token'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                static::setValue($key, $data[$key], 'google');
            }
        }
    }

    public static function saveSnapchatSettings(array $data): void
    {
        $keys = ['snapchat_pixel_enabled', 'snapchat_pixel_id', 'snapchat_api_token'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                static::setValue($key, $data[$key], 'snapchat');
            }
        }
    }

    public static function savePinterestSettings(array $data): void
    {
        $keys = ['pinterest_tag_enabled', 'pinterest_tag_id', 'pinterest_access_token', 'pinterest_ad_account_id'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                static::setValue($key, $data[$key], 'pinterest');
            }
        }
    }

    public static function saveTwitterSettings(array $data): void
    {
        $keys = ['twitter_pixel_enabled', 'twitter_pixel_id', 'twitter_api_key'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                static::setValue($key, $data[$key], 'twitter');
            }
        }
    }

    public static function saveLinkedInSettings(array $data): void
    {
        $keys = ['linkedin_insight_enabled', 'linkedin_partner_id', 'linkedin_access_token', 'linkedin_conversion_rule_id'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                static::setValue($key, $data[$key], 'linkedin');
            }
        }
    }

    public static function isFacebookPixelEnabled(): bool { return (bool) static::get('facebook_pixel_enabled', false); }
    public static function isFacebookCAPIEnabled(): bool { return (bool) static::get('facebook_capi_enabled', false); }
    public static function getFacebookPixelId(): ?string { return static::get('facebook_pixel_id'); }
    public static function getFacebookAccessToken(): ?string { return static::get('facebook_access_token'); }

    public static function isTikTokPixelEnabled(): bool { return (bool) static::get('tiktok_pixel_enabled', false); }
    public static function isTikTokCAPIEnabled(): bool { return (bool) static::get('tiktok_capi_enabled', false); }
    public static function getTikTokPixelId(): ?string { return static::get('tiktok_pixel_id'); }
    public static function getTikTokAccessToken(): ?string { return static::get('tiktok_access_token'); }

    public static function isGoogleAdsEnabled(): bool { return (bool) static::get('google_ads_enabled', false); }
    public static function isSnapchatEnabled(): bool { return (bool) static::get('snapchat_pixel_enabled', false); }
    public static function isPinterestEnabled(): bool { return (bool) static::get('pinterest_tag_enabled', false); }
    public static function isTwitterEnabled(): bool { return (bool) static::get('twitter_pixel_enabled', false); }
    public static function isLinkedInEnabled(): bool { return (bool) static::get('linkedin_insight_enabled', false); }

    public static function isTrackingEnabled(): bool { return (bool) static::get('tracking_enabled', true); }
    public static function isTestMode(): bool { return (bool) static::get('tracking_test_mode', false); }

    public static function saveShopifySettings(array $data): void
    {
        $keys = ['shopify_enabled', 'shopify_shop_domain', 'shopify_access_token', 'shopify_api_secret'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                static::setValue($key, $data[$key], 'shopify');
            }
        }
    }

    public static function saveWooCommerceSettings(array $data): void
    {
        $keys = ['woocommerce_enabled', 'woocommerce_store_url', 'woocommerce_consumer_key', 'woocommerce_consumer_secret'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                static::setValue($key, $data[$key], 'woocommerce');
            }
        }
    }

    public static function saveCustomApiSettings(array $data): void
    {
        $keys = ['custom_api_enabled', 'custom_api_key'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                static::setValue($key, $data[$key], 'custom_api');
            }
        }
    }

    public static function isShopifyEnabled(): bool { return (bool) static::get('shopify_enabled', false); }
    public static function isWooCommerceEnabled(): bool { return (bool) static::get('woocommerce_enabled', false); }
    public static function isCustomApiEnabled(): bool { return (bool) static::get('custom_api_enabled', false); }
}
