<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsHelper
{
    private static $settings = null;

    /**
     * Get all settings from cache or database
     */
    public static function getAll(): array
    {
        if (self::$settings === null) {
            self::$settings = Cache::remember('site_settings', 60, function () {
                try {
                    $settings = [];
                    $rows = Setting::all();
                    foreach ($rows as $row) {
                        $val = $row->value;
                        if (is_string($val)) {
                            $decoded = json_decode($val, true);
                            if (is_string($decoded) && $decoded !== null) {
                                $val = $decoded;
                            }
                        }
                        $settings[$row->key] = $val;
                    }
                    return $settings;
                } catch (\Exception $e) {
                    return [];
                }
            });
        }

        return self::$settings;
    }

    /**
     * Get a specific setting value
     */
    public static function get(string $key, $default = null)
    {
        $settings = self::getAll();
        return $settings[$key] ?? $default;
    }

    /**
     * Get site name
     */
    public static function siteName(): string
    {
        return self::get('site_name_ar') ?? self::get('site_name') ?? config('app.name');
    }

    /**
     * Get site logo URL
     */
    public static function siteLogo(): ?string
    {
        $logo = self::get('site_logo');
        return $logo ? url('files/' . $logo) : null;
    }

    /**
     * Get site favicon URL
     */
    public static function siteFavicon(): ?string
    {
        $favicon = self::get('site_favicon');
        return $favicon ? url('files/' . $favicon) : null;
    }

    /**
     * Get currency symbol
     */
    public static function currencySymbol(): string
    {
        return self::get('currency_symbol', '₪');
    }

    /**
     * Format price with currency
     */
    public static function formatPrice($price): string
    {
        $symbol = self::currencySymbol();
        return number_format($price, 2) . ' ' . $symbol;
    }

    /**
     * Get social media URLs
     */
    public static function socialUrls(): array
    {
        return [
            'facebook' => self::get('facebook_url') ?? self::get('social_facebook') ?? '',
            'instagram' => self::get('instagram_url') ?? self::get('social_instagram') ?? '',
            'twitter' => self::get('twitter_url') ?? self::get('social_twitter') ?? '',
            'tiktok' => self::get('tiktok_url') ?? self::get('social_tiktok') ?? '',
            'linkedin' => self::get('linkedin_url') ?? '',
            'youtube' => self::get('youtube_url') ?? '',
        ];
    }

    /**
     * Get contact information
     */
    public static function contactInfo(): array
    {
        return [
            'email' => self::get('contact_email') ?? self::get('site_email') ?? 'info@jenincare.com',
            'phone' => self::get('contact_phone') ?? self::get('site_phone') ?? '',
            'whatsapp' => self::get('whatsapp_number') ?? self::get('site_whatsapp') ?? '',
            'address' => self::get('address') ?? self::get('site_address') ?? '',
        ];
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('site_settings');
        self::$settings = null;
    }
}
