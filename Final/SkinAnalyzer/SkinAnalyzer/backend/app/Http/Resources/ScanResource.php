<?php

namespace App\Http\Resources;

use App\Enums\AnalysisStatus;
use App\Models\SkinAnalysis;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ScanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var SkinAnalysis $this */
        $data = [
            'id' => $this->id,
            'status' => $this->status,
            'image_url' => $this->generateSignedImageUrl(),
            'overall_health_score' => $this->overall_health_score,
            'formatted_score' => $this->formatted_score,
            'radar_metrics' => $this->radar_metrics,
            'heatmap_coordinates' => $this->heatmap_coordinates,
            'defects' => $this->defectsFromRawResponse(),
            'custom_arabic_analysis' => $this->getArabicAnalysis(),
            'custom_arabic_analysis_text' => $this->custom_arabic_analysis,
            'expert_free_tips' => $this->expert_free_tips,
            'provider' => $this->aiProvider?->driver_key,
            'provider_name' => $this->aiProvider?->name,
            'recommended_products' => $this->when(
                $this->relationLoaded('recommendedProducts'),
                function () {
                    return $this->recommendedProducts->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name_ar ?? $product->name,
                            'brand' => $product->brand ?? '',
                            'price' => $product->price ?? 0,
                            'currency' => $product->currency ?? 'SAR',
                            'image_url' => $product->image_url ?? null,
                            'matching_reason' => $product->pivot->matching_reason ?? '',
                        ];
                    });
                }
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
        ];

        if ($this->relationLoaded('accessPin') && $this->accessPin) {
            $displayPin = $request->user()?->id === $this->user_id;

            if ($displayPin) {
                $data['access_pin'] = [
                    'pin_code' => $this->accessPin->pin_code,
                    'is_used' => $this->accessPin->is_used,
                    'expires_at' => $this->accessPin->expires_at?->toIso8601String(),
                ];
            }
        }

        return $data;
    }

    protected function generateSignedImageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        $disk = Storage::disk(config('skinanalyzer.scan_disk', 'local'));

        $path = $this->image_path;

        if (str_ends_with($path, '.enc')) {
            $path = substr($path, 0, -4);
        }

        if (! $disk->exists($path)) {
            return null;
        }

        return $disk->temporaryUrl($path, now()->addMinutes(15));
    }

    protected function defectsFromRawResponse(): array
    {
        $raw = $this->raw_vendor_response;

        if (is_array($raw) && isset($raw['defects'])) {
            return $raw['defects'];
        }

        return [];
    }

    protected function getArabicAnalysis(): array
    {
        $raw = $this->raw_vendor_response;

        if (is_array($raw) && isset($raw['custom_arabic_analysis'])) {
            return $raw['custom_arabic_analysis'];
        }

        if (! empty($this->custom_arabic_analysis) && is_string($this->custom_arabic_analysis)) {
            return ['summary' => $this->custom_arabic_analysis];
        }

        return [];
    }
}
