<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\WhiteLabelSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AppConfigController extends Controller
{
    public function index(): JsonResponse
    {
        $config = Cache::remember('app.config', 3600, function () {
            return $this->buildConfig();
        });

        return response()->json(['data' => $config]);
    }

    public function checkAppUpdate(): JsonResponse
    {
        $settings = AppSetting::first();

        return response()->json([
            'latest_version' => $settings?->latest_app_version ?? '1.3.0',
            'version_code' => (int) ($settings?->version_code ?? 6),
            'download_url' => $settings?->update_url ?? config('app.url') . '/download',
            'release_notes' => $settings?->release_notes_ar ?? 'تحسينات في الأداء وإصلاح الأخطاء',
            'force_update' => (bool) ($settings?->force_update ?? false),
            'update_available' => true,
        ]);
    }

    private function buildConfig(): array
    {
        $settings = AppSetting::first();
        $whiteLabel = WhiteLabelSetting::first();

        return [
            'login_enabled' => $settings?->login_enabled ?? true,
            'registration_enabled' => $settings?->registration_enabled ?? true,
            'maintenance_mode' => $settings?->maintenance_mode ?? false,
            'maintenance_message_ar' => $settings?->maintenance_message_ar,
            'maintenance_message_en' => $settings?->maintenance_message_en,
            'min_app_version' => $settings?->min_app_version,
            'latest_app_version' => $settings?->latest_app_version,
            'app_name' => $whiteLabel?->app_name_ar ?? 'SkinAnalyzer',
            'app_name_en' => $whiteLabel?->app_name_en ?? 'SkinAnalyzer',
            'primary_color' => $whiteLabel?->primary_color ?? '#4CAF50',
            'accent_color' => $whiteLabel?->accent_color ?? '#81C784',
            'logo_url' => $whiteLabel?->logo_url,
            'server_url' => $whiteLabel?->server_url ?? config('app.url'),
        ];
    }
}
