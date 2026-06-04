<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\WhiteLabelSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WhiteLabelController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = WhiteLabelSetting::forClinic()->first();

        return response()->json([
            'config' => $settings ? [
                'app_name_ar' => $settings->app_title ?? 'محلل البشرة',
                'app_name_en' => $settings->clinic_name ?? 'SkinAnalyzer',
                'primary_color' => $settings->primary_color ?? '#1a8870',
                'accent_color' => $settings->accent_color ?? '#f0a04b',
                'background_color' => '#f8fafc',
                'logo_url' => $settings->logo_url ? url($settings->logo_url) : null,
                'server_url' => config('app.url'),
                'powered_by' => true,
                'contact_phone' => '',
                'contact_email' => '',
                'footer_text_ar' => '',
                'footer_text_en' => '',
                'clinic_name_ar' => $settings->clinic_name ?? '',
                'clinic_name_en' => '',
                'clinic_address_ar' => '',
                'clinic_address_en' => '',
                'clinic_phone' => '',
                'clinic_email' => '',
            ] : config('white-label.defaults', [
                'app_name_ar' => 'محلل البشرة',
                'app_name_en' => 'SkinAnalyzer',
                'primary_color' => '#1a8870',
                'accent_color' => '#f0a04b',
                'background_color' => '#f8fafc',
                'logo_url' => null,
                'server_url' => config('app.url'),
                'powered_by' => true,
                'contact_phone' => '',
                'contact_email' => '',
                'footer_text_ar' => '',
                'footer_text_en' => '',
                'clinic_name_ar' => '',
                'clinic_name_en' => '',
                'clinic_address_ar' => '',
                'clinic_address_en' => '',
                'clinic_phone' => '',
                'clinic_email' => '',
            ]),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'app_name_ar' => 'nullable|string|max:255',
            'app_name_en' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'background_color' => 'nullable|string|max:7',
            'server_url' => 'nullable|url',
            'powered_by' => 'nullable|boolean',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'footer_text_ar' => 'nullable|string|max:500',
            'footer_text_en' => 'nullable|string|max:500',
            'clinic_name_ar' => 'nullable|string|max:255',
            'clinic_name_en' => 'nullable|string|max:255',
            'clinic_address_ar' => 'nullable|string|max:500',
            'clinic_address_en' => 'nullable|string|max:500',
            'clinic_phone' => 'nullable|string|max:50',
            'clinic_email' => 'nullable|email|max:255',
        ]);

        $settings = WhiteLabelSetting::forClinic()->firstOrNew([]);
        $settings->fill([
            'clinic_name' => $data['app_name_en'] ?? $data['clinic_name_en'] ?? $settings->clinic_name,
            'primary_color' => $data['primary_color'] ?? $settings->primary_color,
            'accent_color' => $data['accent_color'] ?? $settings->accent_color,
            'app_title' => $data['app_name_ar'] ?? $data['clinic_name_ar'] ?? $settings->app_title,
        ]);
        $settings->save();

        return response()->json(['success' => true, 'config' => $data]);
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate(['logo' => 'required|image|max:5120']);

        $path = $request->file('logo')->store('white-label', 'public');

        $settings = WhiteLabelSetting::forClinic()->firstOrNew([]);
        $settings->logo_url = Storage::url($path);
        $settings->save();

        return response()->json([
            'success' => true,
            'logo_url' => Storage::url($path),
            'url' => Storage::url($path),
        ]);
    }
}
