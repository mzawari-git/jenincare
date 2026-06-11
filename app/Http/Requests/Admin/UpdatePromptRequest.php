<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $promptId = $this->route('id');

        return [
            'name' => ['sometimes', 'string', 'max:255', "unique:system_prompts,name,{$promptId}"],
            'category' => ['sometimes', 'string', 'max:100'],
            'prompt_text' => ['sometimes', 'string', 'min:10'],
            'tone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
            'language' => ['nullable', 'string', 'in:en,ar,both'],
        ];
    }
}
