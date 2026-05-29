<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackingEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $apiKey = $this->header('X-API-Key');
        $storedKey = \App\Models\MarketingSetting::get('custom_api_key');
        $enabled = (bool) \App\Models\MarketingSetting::get('custom_api_enabled', false);

        if (!$enabled) return false;
        if (!$storedKey) return true;
        return $apiKey === $storedKey;
    }

    public function rules(): array
    {
        return [
            'event_name' => 'required|string|max:100',
            'event_id' => 'sometimes|string|max:100',
            'user_data' => 'sometimes|array',
            'user_data.email' => 'sometimes|email|max:255',
            'user_data.phone' => 'sometimes|string|max:50',
            'user_data.first_name' => 'sometimes|string|max:100',
            'user_data.last_name' => 'sometimes|string|max:100',
            'user_data.city' => 'sometimes|string|max:100',
            'user_data.country' => 'sometimes|string|max:100',
            'user_data.zip' => 'sometimes|string|max:20',
            'custom_data' => 'sometimes|array',
            'custom_data.value' => 'sometimes|numeric',
            'custom_data.currency' => 'sometimes|string|size:3',
            'custom_data.content_ids' => 'sometimes|array',
            'custom_data.content_ids.*' => 'string',
            'custom_data.content_type' => 'sometimes|string|max:50',
            'source' => 'sometimes|string|max:50',
            'platforms' => 'sometimes|array',
            'platforms.*' => 'string|in:facebook,tiktok,google,snapchat,pinterest,twitter,linkedin',
        ];
    }

    public function messages(): array
    {
        return [
            'event_name.required' => 'event_name is required',
            'platforms.*.in' => 'Invalid platform. Valid: facebook, tiktok, google, snapchat, pinterest, twitter, linkedin',
        ];
    }
}
