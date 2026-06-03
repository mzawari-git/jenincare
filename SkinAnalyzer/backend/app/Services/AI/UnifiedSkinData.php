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

    public array $heatmapCoordinates = [];

    public array $defects = [];

    public array $customArabicAnalysis = [];

    public array $expertFreeTips = [];

    public string $customArabicAnalysisText = '';

    public array $rawResponse = [];

    public string $provider = '';

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

        if (isset($raw['heatmap_coordinates']) && is_array($raw['heatmap_coordinates'])) {
            foreach ($raw['heatmap_coordinates'] as $point) {
                if (isset($point['x'], $point['y'], $point['label'])) {
                    $instance->heatmapCoordinates[] = [
                        'x' => (float) $point['x'],
                        'y' => (float) $point['y'],
                        'label' => (string) $point['label'],
                        'severity' => isset($point['severity']) ? (int) $point['severity'] : 0,
                    ];
                }
            }
        }

        if (isset($raw['defects']) && is_array($raw['defects'])) {
            foreach ($raw['defects'] as $defect) {
                if (isset($defect['type'])) {
                    $instance->defects[] = [
                        'type' => (string) $defect['type'],
                        'severity' => isset($defect['severity']) ? (int) $defect['severity'] : 0,
                        'description' => $defect['description'] ?? '',
                    ];
                }
            }
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

        return $instance;
    }

    public function toArray(): array
    {
        return [
            'overall_health_score' => $this->overallHealthScore,
            'radar_metrics' => $this->radarMetrics,
            'heatmap_coordinates' => $this->heatmapCoordinates,
            'defects' => $this->defects,
            'custom_arabic_analysis' => $this->customArabicAnalysis,
            'custom_arabic_analysis_text' => $this->customArabicAnalysisText,
            'expert_free_tips' => $this->expertFreeTips,
            'raw_response' => $this->rawResponse,
            'provider' => $this->provider,
        ];
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | $options);
    }
}
