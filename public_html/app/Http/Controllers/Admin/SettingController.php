<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use App\Helpers\SettingsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    protected $defaultSettings = [
        // General
        'site_name_ar' => 'JeninCare',
        'site_name_en' => 'JeninCare',
        'site_logo' => null,
        'site_favicon' => null,
        'site_theme' => 'natural',
        'site_description_ar' => '',
        'site_description_en' => '',
        'contact_email' => 'info@jenincare.com',
        'contact_phone' => '',
        'whatsapp_number' => '',
        'address' => '',

        // SEO
        'meta_title_ar' => '',
        'meta_title_en' => '',
        'meta_description_ar' => '',
        'meta_description_en' => '',
        'meta_keywords' => '',

        // Business
        'currency' => 'ILS',
        'currency_symbol' => '₪',
        'tax_rate' => '0',
        'shipping_cost' => '0',
        'free_shipping_min' => '0',

        // Social Media
        'facebook_url' => '',
        'instagram_url' => '',
        'twitter_url' => '',
        'linkedin_url' => '',
        'tiktok_url' => '',
        'youtube_url' => '',
        'snapchat_url' => '',
        'pinterest_url' => '',

        // Social Login (OAuth)
        'google_client_id' => '',
        'google_client_secret' => '',
        'facebook_client_id' => '',
        'facebook_client_secret' => '',

        // Marketing & Tracking
        'google_analytics_id' => '',
        'facebook_pixel_id' => '',
        'facebook_access_token' => '',
        'facebook_pixel_enabled' => '1',
        'facebook_capi_enabled' => '0',
        'facebook_test_event_code' => '',
        'tiktok_pixel_id' => '',
        'tiktok_access_token' => '',
        'tiktok_pixel_enabled' => '0',

        // Features
        'maintenance_mode' => '0',
        'registration_enabled' => '1',
        'b2b_enabled' => '1',
        'reviews_enabled' => '1',
        'wishlist_enabled' => '1',

        // Payment Methods
        'payment_cod_enabled' => '1',
        'payment_bank_enabled' => '0',
        'payment_bank_name' => '',
        'payment_bank_holder' => '',
        'payment_bank_account' => '',
        'payment_bank_iban' => '',
        'payment_jawwal_enabled' => '0',
        'payment_jawwal_phone' => '',
        'payment_jawwal_holder' => '',
        'payment_reflect_enabled' => '0',
        'payment_reflect_holder' => '',
        'payment_reflect_phone' => '',
    ];

    public function index()
    {
        $dbSettings = Setting::pluck('value', 'key')->toArray();
        $settings = array_merge($this->defaultSettings, $dbSettings);

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $tab = $request->get('tab', 'general');

        // Handle file uploads
        if ($request->hasFile('site_logo')) {
            $logoPath = $request->file('site_logo')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'site_logo'], ['value' => $logoPath]);
        }

        if ($request->hasFile('site_favicon')) {
            $faviconPath = $request->file('site_favicon')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'site_favicon'], ['value' => $faviconPath]);
        }

        // Save other settings
        foreach ($request->except('_token', '_method', 'tab', 'site_logo', 'site_favicon') as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : ($value ?? '')]
            );
        }

        // Clear settings cache so changes apply immediately
        SettingsHelper::clearCache();

        return redirect()->route('admin.settings', ['tab' => $tab])
            ->with('success', 'تم حفظ الإعدادات بنجاح');
    }

    public function deleteLogo(Request $request)
    {
        $setting = Setting::where('key', 'site_logo')->first();
        if ($setting && $setting->value) {
            Storage::disk('public')->delete($setting->value);
            $setting->delete();
        }

        // Clear cache after deleting logo
        SettingsHelper::clearCache();

        return redirect()->route('admin.settings', ['tab' => 'general'])
            ->with('success', 'تم حذف الشعار بنجاح');
    }

    public function deleteAllProducts()
    {
        $count = Product::count();
        Product::query()->delete();
        return redirect()->route('admin.products.index')
            ->with('success', "تم حذف جميع المنتجات ($count منتج) بنجاح");
    }
}
