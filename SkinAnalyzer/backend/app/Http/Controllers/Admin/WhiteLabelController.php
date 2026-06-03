<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateWhiteLabelRequest;
use App\Models\WhiteLabelSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WhiteLabelController extends Controller
{
    public function show(): JsonResponse
    {
        $settings = WhiteLabelSetting::getCurrent();

        if (! $settings) {
            $settings = new WhiteLabelSetting([
                'app_name' => config('skinanalyzer.white_label.app_name', 'SkinAnalyzer'),
                'primary_color' => config('skinanalyzer.white_label.primary_color', '#7C3AED'),
                'support_email' => config('skinanalyzer.white_label.support_email', 'support@jenincare.shop'),
                'website_url' => config('skinanalyzer.white_label.website_url', 'https://jenincare.shop'),
                'support_phone' => config('skinanalyzer.white_label.support_phone'),
                'logo_path' => config('skinanalyzer.white_label.logo_url'),
            ]);
        }

        return response()->json([
            'data' => [
                'app_name' => $settings->app_name,
                'primary_color' => $settings->primary_color,
                'secondary_color' => $settings->secondary_color,
                'accent_color' => $settings->accent_color,
                'font_family' => $settings->font_family,
                'logo_url' => $settings->logo_url,
                'favicon_url' => $settings->favicon_url,
                'support_email' => $settings->support_email,
                'support_phone' => $settings->support_phone,
                'website_url' => $settings->website_url,
                'server_url' => $settings->server_url,
                'android_app_url' => $settings->android_app_url,
                'ios_app_url' => $settings->ios_app_url,
                'terms_url' => $settings->terms_url,
                'privacy_url' => $settings->privacy_url,
                'footer_text' => $settings->footer_text,
                'social_links' => $settings->social_links,
                'updated_at' => $settings->updated_at,
            ],
        ]);
    }

    public function update(UpdateWhiteLabelRequest $request): JsonResponse
    {
        $settings = WhiteLabelSetting::getCurrent();

        if (! $settings) {
            $settings = WhiteLabelSetting::create($request->validated());
        } else {
            $settings->update($request->validated());
        }

        return response()->json([
            'message' => 'White-label settings updated successfully.',
            'data' => $settings->fresh(),
        ]);
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:5120'],
            'favicon' => ['nullable', 'image', 'mimes:png,ico,svg', 'max:1024'],
        ]);

        $settings = WhiteLabelSetting::getCurrent();

        if (! $settings) {
            $settings = WhiteLabelSetting::create(['app_name' => config('skinanalyzer.white_label.app_name')]);
        }

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            $logoPath = $request->file('logo')->store('white-label/logos', 'public');
            $settings->update(['logo_path' => $logoPath]);
        }

        if ($request->hasFile('favicon')) {
            if ($settings->favicon_path) {
                Storage::disk('public')->delete($settings->favicon_path);
            }

            $faviconPath = $request->file('favicon')->store('white-label/favicons', 'public');
            $settings->update(['favicon_path' => $faviconPath]);
        }

        return response()->json([
            'message' => 'Logo(s) uploaded successfully.',
            'data' => $settings->fresh(),
        ]);
    }

    public function preview(): JsonResponse
    {
        $settings = WhiteLabelSetting::getCurrent();

        if (! $settings) {
            $settings = new WhiteLabelSetting([
                'app_name' => config('skinanalyzer.white_label.app_name', 'SkinAnalyzer'),
                'primary_color' => config('skinanalyzer.white_label.primary_color', '#7C3AED'),
            ]);
        }

        $preview = [
            'app_name' => $settings->app_name,
            'theme' => [
                'primary_color' => $settings->primary_color ?? '#7C3AED',
                'secondary_color' => $settings->secondary_color ?? '#10B981',
                'accent_color' => $settings->accent_color ?? '#F59E0B',
                'font_family' => $settings->font_family ?? 'system-ui',
            ],
            'branding' => [
                'logo_url' => $settings->logo_url,
                'favicon_url' => $settings->favicon_url,
            ],
            'links' => [
                'website' => $settings->website_url,
                'support_email' => $settings->support_email,
                'support_phone' => $settings->support_phone,
                'android_app' => $settings->android_app_url,
                'ios_app' => $settings->ios_app_url,
                'terms' => $settings->terms_url,
                'privacy' => $settings->privacy_url,
            ],
            'footer' => [
                'text' => $settings->footer_text ?? "© " . date('Y') . " {$settings->app_name}. All rights reserved.",
            ],
            'social_links' => $settings->social_links,
        ];

        return response()->json(['data' => $preview]);
    }
}
