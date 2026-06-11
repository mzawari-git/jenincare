<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UnlockScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pin_code' => [
                'required',
                'string',
                'size:4',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'pin_code.required' => 'يرجى إدخال رمز PIN.',
            'pin_code.string' => 'رمز PIN يجب أن يكون نصاً.',
            'pin_code.size' => 'رمز PIN يجب أن يكون مكوناً من 4 أرقام.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('pin_code')) {
            $this->merge([
                'pin_code' => trim((string) $this->input('pin_code')),
            ]);
        }
    }
}
