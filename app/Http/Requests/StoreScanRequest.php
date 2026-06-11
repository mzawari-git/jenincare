<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxSize = config('skinanalyzer.max_upload_size_kb', 10240);

        return [
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg',
                "max:{$maxSize}",
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'يرجى اختيار صورة للتحليل.',
            'image.image' => 'الملف المرفق ليس صورة صالحة.',
            'image.mimes' => 'صيغة الصورة غير مدعومة. الصيغ المتاحة: JPEG, PNG.',
            'image.max' => 'حجم الصورة كبير جداً. الحد الأقصى المسموح به هو :max كيلوبايت.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->hasFile('image')) {
            $file = $this->file('image');

            if ($file && $file->isValid()) {
                $this->merge([
                    'image_mime' => $file->getMimeType(),
                    'image_size' => $file->getSize(),
                ]);
            }
        }
    }
}
