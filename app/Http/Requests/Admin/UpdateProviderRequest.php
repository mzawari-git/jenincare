<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\EngineType;

class UpdateProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'engine_type' => ['sometimes', 'string', Rule::in(array_column(EngineType::cases(), 'value'))],
            'api_credentials' => ['sometimes', 'array'],
            'api_credentials.api_key' => ['required_with:api_credentials', 'string'],
            'api_credentials.api_secret' => ['nullable', 'string'],
            'api_credentials.endpoint_url' => ['nullable', 'url'],
            'quota_limit' => ['sometimes', 'integer', 'min:0'],
            'config' => ['sometimes', 'array'],
            'config.model' => ['nullable', 'string'],
            'config.temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'config.max_tokens' => ['nullable', 'integer', 'min:1'],
            'config.timeout' => ['nullable', 'integer', 'min:1', 'max:300'],
            'config.retry_attempts' => ['nullable', 'integer', 'min:0', 'max:10'],
        ];
    }
}
