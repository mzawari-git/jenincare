<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WhiteLabelSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppConfigController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $whiteLabel = null;
        if ($request->user()?->clinic_id) {
            $whiteLabel = WhiteLabelSetting::forClinic($request->user()->clinic_id)->first();
        }

        $defaults = WhiteLabelSetting::getDefaults();

        return response()->json([
            'data' => [
                'login_enabled' => true,
                'registration_enabled' => true,
                'maintenance_mode' => false,
                'maintenance_message_ar' => null,
                'app_name' => $whiteLabel?->app_title ?? 'SkinAnalyzer',
                'app_name_en' => $whiteLabel?->app_title ?? 'SkinAnalyzer',
                'primary_color' => $whiteLabel?->primary_color ?? $defaults['primary_color'],
                'accent_color' => $whiteLabel?->accent_color ?? $defaults['accent_color'],
                'logo_url' => $whiteLabel?->logo_url ? url($whiteLabel->logo_url) : url('/android-chrome-512x512.png'),
                'server_url' => url('/'),
                'min_app_version' => '1.0.1',
                'latest_app_version' => config('app.update_version', '1.0.1'),
                'white_label' => $whiteLabel ? [
                    'clinic_name' => $whiteLabel->clinic_name,
                    'logo_url' => $whiteLabel->logo_url ? url($whiteLabel->logo_url) : null,
                    'primary_color' => $whiteLabel->primary_color,
                    'accent_color' => $whiteLabel->accent_color,
                    'theme_mode' => $whiteLabel->theme_mode,
                    'style_preset' => $whiteLabel->style_preset,
                    'app_title' => $whiteLabel->app_title,
                ] : null,
            ],
        ]);
    }
}
