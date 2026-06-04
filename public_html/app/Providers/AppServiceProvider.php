<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Helpers\SettingsHelper;
use App\Models\Cart;
use Modules\Commerce\Services\CartService;
use Modules\Commerce\Services\OrderProcessor;
use App\Services\PricingEngine;
use Modules\Commerce\Services\MetaCapiService;
use App\Services\DeduplicationService;
use App\Services\IdentityService;
use App\Services\EventSourcingService;
use App\Services\AttributionService;
use App\Services\AISanitizerService;
use App\Services\AI\OpenAIProvider;
use App\Services\AI\ClaudeProvider;
use App\Services\AI\LlamaProvider;
use App\Services\AdAccountHealthService;
use App\Services\OfflineConversionService;
use App\Services\LtvMultiplierService;
use App\Services\MultiPixelService;
use App\Services\Sanitization\SanitizationPipeline;
use App\Services\Sanitization\TriggerWordFilter;
use App\Services\Sanitization\LLMFilter;
use App\Services\Sanitization\PolicyChecker;
use App\Services\Sanitization\ValueFilter;
use App\Services\Sanitization\JunkFilter;
use Modules\AICompliance\Services\ContentModerationService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PricingEngine::class, function ($app) {
            return new PricingEngine();
        });

        $this->app->singleton(CartService::class, function ($app) {
            return new CartService($app->make(PricingEngine::class));
        });

        $this->app->singleton(MetaCapiService::class, function ($app) {
            return new MetaCapiService();
        });

        $this->app->singleton(\App\Services\AdvertisingTrackingService::class, function ($app) {
            return new \App\Services\AdvertisingTrackingService($app->make(DeduplicationService::class));
        });

        $this->app->singleton(DeduplicationService::class, function ($app) {
            return new DeduplicationService();
        });

        $this->app->singleton(\App\Services\GoogleAdsService::class, function ($app) {
            return new \App\Services\GoogleAdsService();
        });

        $this->app->singleton(\App\Services\SnapchatService::class, function ($app) {
            return new \App\Services\SnapchatService();
        });

        $this->app->singleton(\App\Services\PinterestService::class, function ($app) {
            return new \App\Services\PinterestService();
        });

        $this->app->singleton(\App\Services\TwitterService::class, function ($app) {
            return new \App\Services\TwitterService();
        });

        $this->app->singleton(\App\Services\LinkedInService::class, function ($app) {
            return new \App\Services\LinkedInService();
        });

        $this->app->singleton(ContentModerationService::class, function ($app) {
            return new ContentModerationService();
        });

        $this->app->singleton(IdentityService::class, function ($app) {
            return new IdentityService();
        });

        $this->app->singleton(EventSourcingService::class, function ($app) {
            return new EventSourcingService();
        });

        $this->app->singleton(AttributionService::class, function ($app) {
            return new AttributionService(
                $app->make(EventSourcingService::class)
            );
        });

        $this->app->singleton(OpenAIProvider::class, function ($app) {
            return new OpenAIProvider();
        });

        $this->app->singleton(ClaudeProvider::class, function ($app) {
            return new ClaudeProvider();
        });

        $this->app->singleton(LlamaProvider::class, function ($app) {
            return new LlamaProvider();
        });

        $this->app->singleton(AISanitizerService::class, function ($app) {
            return new AISanitizerService(
                $app->make(OpenAIProvider::class),
                $app->make(ClaudeProvider::class),
                $app->make(LlamaProvider::class),
            );
        });

        $this->app->singleton(AdAccountHealthService::class, function ($app) {
            return new AdAccountHealthService();
        });

        $this->app->singleton(OfflineConversionService::class, function ($app) {
            return new OfflineConversionService();
        });

        $this->app->singleton(LtvMultiplierService::class, function ($app) {
            return new LtvMultiplierService();
        });

        $this->app->singleton(MultiPixelService::class, function ($app) {
            return new MultiPixelService();
        });

        $this->app->singleton(SanitizationPipeline::class, function ($app) {
            return (new SanitizationPipeline())
                ->addStep(new TriggerWordFilter())
                ->addStep(new LLMFilter($app->make(AISanitizerService::class)))
                ->addStep(new PolicyChecker())
                ->addStep(new ValueFilter())
                ->addStep(new JunkFilter());
        });

        $this->app->singleton(OrderProcessor::class, function ($app) {
            return new OrderProcessor(
                $app->make(PricingEngine::class),
                $app->make(CartService::class),
                $app->make(MetaCapiService::class)
            );
        });

        if (class_exists(\Laravel\Pulse\PulseServiceProvider::class)) {
            $this->app->register(\Laravel\Pulse\PulseServiceProvider::class);
        }
    }

    public function boot(): void
    {
        try {
            $dbSettings = \App\Models\Setting::pluck('value', 'key')->toArray();

            if (!empty($dbSettings['google_client_id']) && !empty($dbSettings['google_client_secret'])) {
                config([
                    'services.google.client_id' => $dbSettings['google_client_id'],
                    'services.google.client_secret' => $dbSettings['google_client_secret'],
                ]);
            }

            if (!empty($dbSettings['facebook_client_id']) && !empty($dbSettings['facebook_client_secret'])) {
                config([
                    'services.facebook.client_id' => $dbSettings['facebook_client_id'],
                    'services.facebook.client_secret' => $dbSettings['facebook_client_secret'],
                ]);
            }
        } catch (\Exception $e) {
            // Table might not exist yet (first migration)
        }

        $defaultSettings = [
            'site_name' => config('app.name'),
            'site_name_ar' => config('app.name'),
            'site_name_en' => config('app.name'),
            'contact_email' => 'info@jenincare.com',
            'currency' => 'ILS',
            'currency_symbol' => '₪',
            'shipping_cost' => 25,
            'free_shipping_min' => 200,
            'site_theme' => 'minimal',
            'facebook_pixel_enabled' => '1',
            'payment_bank_enabled' => '0',
            'payment_jawwal_enabled' => '0',
            'payment_reflect_enabled' => '0',
        ];

        View::composer('*', function ($view) use ($defaultSettings) {
            $settings = SettingsHelper::getAll();
            $s = array_merge($defaultSettings, $settings);

            // ── Read user theme preferences from cookies (persist across logout) ──
            $knownThemes = ['rose','midnight','natural','forest','minimal','ocean','sunset','luxury'];
            $activeTheme = $_COOKIE['شركة جنين للتجميل_color'] ?? $s['site_theme'] ?? 'rose';
            if (!in_array($activeTheme, $knownThemes)) {
                $activeTheme = 'rose';
            }

            // Default: derive architecture from the active theme color
            $layoutArchitecture = match($activeTheme) {
                'rose', 'midnight' => 'cyber-lab',
                'natural', 'forest' => 'organic-spa',
                'minimal', 'ocean' => 'editorial',
                'sunset', 'luxury' => 'luxury-boutique',
                default => 'cyber-lab',
            };
            // Architecture cookie overrides (user explicitly chose a layout)
            if (isset($_COOKIE['شركة جنين للتجميل_arch'])) {
                $ca = $_COOKIE['شركة جنين للتجميل_arch'];
                if (in_array($ca, ['cyber-lab','organic-spa','editorial','luxury-boutique'])) {
                    $layoutArchitecture = $ca;
                }
            }

            $mode = $_COOKIE['شركة جنين للتجميل_mode'] ?? 'light';
            $isLightTheme = $mode !== 'dark';

            $view->with('layoutArchitecture', $layoutArchitecture);
            $view->with('layoutPath', 'frontend.layouts.' . $layoutArchitecture . '.app');
            $view->with('activeTheme', $activeTheme);
            $view->with('isLightTheme', $isLightTheme);

            $view->with('siteSettings', [
                'site_name' => $s['site_name'] ?? $s['site_name_ar'] ?? config('app.name'),
                'site_name_ar' => $s['site_name_ar'] ?? config('app.name'),
                'site_name_en' => $s['site_name_en'] ?? config('app.name'),
                'site_logo' => $s['site_logo'] ?? null,
                'site_logo_url' => SettingsHelper::siteLogo(),
                'site_favicon' => $s['site_favicon'] ?? null,
                'site_favicon_url' => SettingsHelper::siteFavicon(),
                'site_description' => $s['site_description_ar'] ?? $s['site_description'] ?? '',
                'site_email' => $s['contact_email'] ?? $s['site_email'] ?? 'info@jenincare.com',
                'contact_email' => $s['contact_email'] ?? $s['site_email'] ?? 'info@jenincare.com',
                'site_phone' => $s['contact_phone'] ?? $s['site_phone'] ?? '',
                'contact_phone' => $s['contact_phone'] ?? $s['site_phone'] ?? '',
                'whatsapp_number' => $s['whatsapp_number'] ?? $s['site_whatsapp'] ?? '',
                'site_whatsapp' => $s['whatsapp_number'] ?? $s['site_whatsapp'] ?? '',
                'address' => $s['address'] ?? $s['site_address'] ?? '',
                'site_address' => $s['address'] ?? $s['site_address'] ?? '',
                'facebook_url' => $s['facebook_url'] ?? $s['social_facebook'] ?? '',
                'social_facebook' => $s['facebook_url'] ?? $s['social_facebook'] ?? '',
                'instagram_url' => $s['instagram_url'] ?? $s['social_instagram'] ?? '',
                'social_instagram' => $s['instagram_url'] ?? $s['social_instagram'] ?? '',
                'tiktok_url' => $s['tiktok_url'] ?? $s['social_tiktok'] ?? '',
                'social_tiktok' => $s['tiktok_url'] ?? $s['social_tiktok'] ?? '',
                'twitter_url' => $s['twitter_url'] ?? $s['social_twitter'] ?? '',
                'linkedin_url' => $s['linkedin_url'] ?? '',
                'youtube_url' => $s['youtube_url'] ?? '',
                'snapchat_url' => $s['snapchat_url'] ?? '',
                'pinterest_url' => $s['pinterest_url'] ?? '',
                'currency' => $s['currency'] ?? 'ILS',
                'currency_symbol' => $s['currency_symbol'] ?? '₪',
                'shipping_cost' => floatval($s['shipping_cost'] ?? 25),
                'free_shipping_min' => floatval($s['free_shipping_threshold'] ?? $s['free_shipping_min'] ?? 200),
                'site_theme' => $s['site_theme'] ?? 'rose',
                'facebook_pixel_id' => $s['facebook_pixel_id'] ?? '',
                'facebook_pixel_enabled' => $s['facebook_pixel_enabled'] ?? '1',
                'payment_bank_enabled' => $s['payment_bank_enabled'] ?? '0',
                'payment_jawwal_enabled' => $s['payment_jawwal_enabled'] ?? '0',
                'payment_reflect_enabled' => $s['payment_reflect_enabled'] ?? '0',
            ]);
        });

        // Share identity data with all views
        View::composer('*', function ($view) {
            try {
                $identity = request()?->get('_identity');
                $view->with('_identity', $identity);
            } catch (\Exception $e) {
                $view->with('_identity', null);
            }
        });

        // Share cart count with all views
        View::composer('*', function ($view) {
            try {
                if (Auth::check()) {
                    $cart = Cart::where('user_id', Auth::id())->where('is_active', true)->first();
                } else {
                    $cart = Cart::where('session_id', Session::getId())->where('is_active', true)->first();
                }
                $view->with('cartCount', $cart ? $cart->items()->sum('quantity') : 0);
            } catch (\Exception $e) {
                $view->with('cartCount', 0);
            }
        });
    }
}
