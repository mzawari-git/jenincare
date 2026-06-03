<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\WhiteLabelSetting;
use Illuminate\Http\JsonResponse;

class AppConfigController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = AppSetting::first();
        $whiteLabel = WhiteLabelSetting::first();

        return response()->json([
            'data' => [
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
            ],
        ]);
    }
}
