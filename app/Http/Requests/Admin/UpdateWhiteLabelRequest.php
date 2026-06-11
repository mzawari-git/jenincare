<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWhiteLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'app_name_ar' => ['sometimes', 'string', 'max:255'],
            'app_name_en' => ['sometimes', 'string', 'max:255'],
            'clinic_name_ar' => ['nullable', 'string', 'max:255'],
            'clinic_name_en' => ['nullable', 'string', 'max:255'],
            'clinic_address_ar' => ['nullable', 'string', 'max:500'],
            'clinic_address_en' => ['nullable', 'string', 'max:500'],
            'clinic_phone' => ['nullable', 'string', 'max:30'],
            'clinic_email' => ['nullable', 'email', 'max:255'],
            'report_header_ar' => ['nullable', 'string', 'max:255'],
            'report_header_en' => ['nullable', 'string', 'max:255'],
            'report_footer_ar' => ['nullable', 'string', 'max:500'],
            'report_footer_en' => ['nullable', 'string', 'max:500'],
            'primary_color' => ['sometimes', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'accent_color' => ['nullable', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'background_color' => ['nullable', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'font_family' => ['nullable', 'string', 'max:100'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'website_url' => ['nullable', 'url', 'max:500'],
            'server_url' => ['nullable', 'url', 'max:500'],
            'android_app_url' => ['nullable', 'url', 'max:500'],
            'ios_app_url' => ['nullable', 'url', 'max:500'],
            'terms_url' => ['nullable', 'url', 'max:500'],
            'privacy_url' => ['nullable', 'url', 'max:500'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'social_links' => ['nullable', 'array'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'powered_by' => ['nullable', 'boolean'],
        ];
    }
}
