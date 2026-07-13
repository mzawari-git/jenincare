<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BatchApproveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:skin_analyses,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'At least one scan ID is required.',
            'ids.*.exists' => 'One or more scan IDs are invalid.',
        ];
    }
}
