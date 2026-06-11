<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:system_prompts,name'],
            'category' => ['required', 'string', 'max:100'],
            'prompt_text' => ['required', 'string', 'min:10'],
            'tone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
            'language' => ['nullable', 'string', 'in:en,ar,both'],
        ];
    }
}
