<?php

namespace App\Services\AI;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class UnifiedSkinData implements Arrayable, Jsonable
{
    public int $overallHealthScore = 0;

    public array $radarMetrics = [
        'hydration' => 0,
        'sebum' => 0,
        'pigmentation' => 0,
        'pores' => 0,
        'elasticity' => 0,
    ];

    public array $advancedMetrics = [
        'brightness' => 0,
        'texture' => 0,
        'redness' => 0,
        'sensitivity' => 0,
        'oiliness' => 0,
    ];

    public array $heatmapCoordinates = [];

    public array $defects = [];

    public array $facialZoneAnalysis = [];

    public array $spectralAnalysis = [];

    public array $customArabicAnalysis = [];

    public string $customArabicAnalysisText = '';

    public array $expertFreeTips = [];

    public array $rawResponse = [];

    public string $provider = '';

    public int $confidence = 0;

    public static function fromProviderResponse(array $raw, string $provider): self
    {
        $instance = new self();
        $instance->provider = $provider;
        $instance->rawResponse = $raw;

        if (isset($raw['overall_health_score'])) {
            $instance->overallHealthScore = max(0, min(100, (int) $raw['overall_health_score']));
        }

        if (isset($raw['radar_metrics']) && is_array($raw['radar_metrics'])) {
            foreach (['hydration', 'sebum', 'pigmentation', 'pores', 'elasticity'] as $metric) {
                if (isset($raw['radar_metrics'][$metric])) {
                    $instance->radarMetrics[$metric] = max(0, min(100, (int) $raw['radar_metrics'][$metric]));
                }
            }
        }

        if (isset($raw['advanced_metrics']) && is_array($raw['advanced_metrics'])) {
            foreach (['brightness', 'texture', 'redness', 'sensitivity', 'oiliness'] as $metric) {
                if (isset($raw['advanced_metrics'][$metric])) {
                    $instance->advancedMetrics[$metric] = max(0, min(100, (int) $raw['advanced_metrics'][$metric]));
                }
            }
        }

        if (isset($raw['heatmap_coordinates']) && is_array($raw['heatmap_coordinates'])) {
            foreach ($raw['heatmap_coordinates'] as $point) {
                if (isset($point['x'], $point['y'])) {
                    $instance->heatmapCoordinates[] = [
                        'x' => (float) $point['x'],
                        'y' => (float) $point['y'],
                        'label' => (string) ($point['label'] ?? ''),
                        'label_ar' => (string) ($point['label_ar'] ?? ''),
                        'severity' => (int) ($point['severity'] ?? 0),
                        'type' => (string) ($point['type'] ?? ''),
                    ];
                }
            }
        }

        if (isset($raw['defects']) && is_array($raw['defects'])) {
            foreach ($raw['defects'] as $defect) {
                if (isset($defect['type'])) {
                    $instance->defects[] = [
                        'type' => (string) $defect['type'],
                        'severity' => (int) ($defect['severity'] ?? 0),
                        'description' => (string) ($defect['description'] ?? ''),
                        'description_ar' => (string) ($defect['description_ar'] ?? ''),
                        'confidence' => (float) ($defect['confidence'] ?? 0),
                        'category' => (string) ($defect['category'] ?? ''),
                        'requires_medical' => (bool) ($defect['requires_medical'] ?? false),
                        'recommended_ingredients' => $defect['recommended_ingredients'] ?? [],
                    ];
                }
            }
        }

        if (isset($raw['facial_zone_analysis']) && is_array($raw['facial_zone_analysis'])) {
            $instance->facialZoneAnalysis = $raw['facial_zone_analysis'];
        }

        if (isset($raw['spectral_analysis']) && is_array($raw['spectral_analysis'])) {
            $instance->spectralAnalysis = $raw['spectral_analysis'];
        }

        if (isset($raw['custom_arabic_analysis']) && is_array($raw['custom_arabic_analysis'])) {
            $instance->customArabicAnalysis = $raw['custom_arabic_analysis'];
        }

        if (isset($raw['custom_arabic_analysis_text'])) {
            $instance->customArabicAnalysisText = (string) $raw['custom_arabic_analysis_text'];
        }

        if (isset($raw['expert_free_tips']) && is_array($raw['expert_free_tips'])) {
            $instance->expertFreeTips = $raw['expert_free_tips'];
        }

        if (isset($raw['confidence'])) {
            $instance->confidence = (int) ($raw['confidence'] * 100);
        }

        return $instance;
    }

    public function toArray(): array
    {
        return [
            'overall_health_score' => $this->overallHealthScore,
            'radar_metrics' => $this->radarMetrics,
            'advanced_metrics' => $this->advancedMetrics,
            'heatmap_coordinates' => $this->heatmapCoordinates,
            'defects' => $this->defects,
            'facial_zone_analysis' => $this->facialZoneAnalysis,
            'spectral_analysis' => $this->spectralAnalysis,
            'custom_arabic_analysis' => $this->customArabicAnalysis,
            'custom_arabic_analysis_text' => $this->customArabicAnalysisText,
            'expert_free_tips' => $this->expertFreeTips,
            'raw_response' => $this->rawResponse,
            'provider' => $this->provider,
            'confidence' => $this->confidence,
        ];
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | $options);
    }
}
