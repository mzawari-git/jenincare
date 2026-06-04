<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function getSkinAnalyzer(): JsonResponse
    {
        $freeScanMode = Setting::get('skinanalyzer.free_scan_mode', false);

        return response()->json([
            'free_scan_mode' => (bool) $freeScanMode,
        ]);
    }

    public function updateSkinAnalyzer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'free_scan_mode' => 'required|boolean',
        ]);

        Setting::set('skinanalyzer.free_scan_mode', $data['free_scan_mode']);

        return response()->json([
            'free_scan_mode' => (bool) $data['free_scan_mode'],
            'message' => 'تم حفظ الإعدادات',
        ]);
    }

    public function clearCache(): JsonResponse
    {
        \Illuminate\Support\Facades\Cache::flush();
        return response()->json(['message' => 'تم مسح الذاكرة المؤقتة']);
    }
}
