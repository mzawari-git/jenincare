<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AppConfigController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'login_enabled' => true,
                'registration_enabled' => true,
                'maintenance_mode' => false,
                'maintenance_message_ar' => null,
                'app_name' => 'SkinAnalyzer',
                'app_name_en' => 'SkinAnalyzer',
                'primary_color' => '#4CAF50',
                'accent_color' => '#81C784',
                'logo_url' => url('/android-chrome-512x512.png'),
                'server_url' => url('/'),
                'min_app_version' => '1.0.0',
                'latest_app_version' => '1.0.0',
            ],
        ]);
    }
}
