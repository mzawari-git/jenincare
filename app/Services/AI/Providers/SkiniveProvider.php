<?php

namespace App\Services\AI\Providers;

use App\Enums\EngineType;
use App\Services\AI\BaseAIProvider;
use App\Services\AI\AIProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SkiniveProvider extends BaseAIProvider implements AIProviderInterface
{
    private const BASE_URL = 'https://api.skinive.com/v2';

    private const SKIN_ANALYSIS_ENDPOINT = '/skin/assess';

    private const MAX_RETRIES = 2;

    private const RETRY_DELAY_MS = 1500;

    private const TIMEOUT_SECONDS = 30;

    public function analyze(array $imageData): array
    {
        $this->validateImage($imageData);

        $base64Image = $this->extractBase64($imageData);

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->retry(self::MAX_RETRIES, self::RETRY_DELAY_MS, function ($exception) {
                    return $exception instanceof ConnectionException
                        || ($exception instanceof RequestException && $exception->response->serverError());
                })
                ->withHeaders($this->buildHeaders())
                ->post(self::BASE_URL . self::SKIN_ANALYSIS_ENDPOINT, [
                    'image' => $base64Image,
                    'mode' => 'full',
                    'detailed' => true,
                    'return_heatmap' => true,
                ]);

            if ($response->failed()) {
                throw new RequestException($response);
            }

            $data = $response->json();

            $rawResponse = [
                'engine' => 'skinive',
                'request_id' => $data['assessment_id'] ?? null,
                'model_version' => $data['algorithm_version'] ?? 'unknown',
                'processing_time_ms' => $data['elapsed_ms'] ?? null,
                'confidence' => $data['confidence'] ?? 0.0,
                'overall_health_score' => $data['assessment']['total_score'] ?? 50,
                'radar_metrics' => [
                    'hydration' => $data['assessment']['hydration_index'] ?? 50,
                    'sebum' => $data['assessment']['sebum_index'] ?? 50,
                    'pigmentation' => $data['assessment']['pigment_index'] ?? 50,
                    'pores' => $data['assessment']['pore_index'] ?? 50,
                    'elasticity' => $data['assessment']['firmness_index'] ?? $data['assessment']['elasticity_index'] ?? 50,
                ],
                'heatmap_coordinates' => $this->mapHeatmap($data['heatmap'] ?? []),
                'defects' => $this->mapDefects($data['assessment']['concerns'] ?? []),
                'custom_arabic_analysis' => $data['consultation_arabic'] ?? [],
                'custom_arabic_analysis_text' => $data['consultation_arabic']['full_text'] ?? '',
                'expert_free_tips' => $data['care_recommendations'] ?? [],
            ];

            $this->logRequest($rawResponse);

            return $rawResponse;
        } catch (\Exception $e) {
            Log::error('Skinive API request failed', [
                'provider' => $this->aiProvider->driver_key,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException("Skinive analysis failed: {$e->getMessage()}", 0, $e);
        }
    }

    public function getEngineType(): EngineType
    {
        return EngineType::STRUCTURED;
    }

    private function buildHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->credentials('api_key'),
            'X-API-Key' => $this->credentials('api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    private function extractBase64(array $imageData): string
    {
        if (! empty($imageData['base64'])) {
            return $imageData['base64'];
        }

        if (! empty($imageData['path'])) {
            $content = $this->disk->get($imageData['path']);
            $mimeType = $this->disk->mimeType($imageData['path']) ?: 'image/jpeg';
            return 'data:' . $mimeType . ';base64,' . base64_encode($content);
        }

        throw new \InvalidArgumentException('No valid image data provided.');
    }

    private function mapHeatmap(array $heatmapData): array
    {
        $mapped = [];

        foreach ($heatmapData as $point) {
            $mapped[] = [
                'x' => (float) ($point['pos_x'] ?? $point['x'] ?? 50),
                'y' => (float) ($point['pos_y'] ?? $point['y'] ?? 50),
                'label' => (string) ($point['area_name'] ?? $point['label'] ?? 'unknown'),
                'severity' => (int) ($point['risk_level'] ?? $point['severity'] ?? 0),
            ];
        }

        return $mapped;
    }

    private function mapDefects(array $concerns): array
    {
        $mapped = [];

        foreach ($concerns as $concern) {
            $mapped[] = [
                'type' => (string) ($concern['concern_type'] ?? $concern['type'] ?? 'unknown'),
                'severity' => (int) ($concern['severity'] ?? $concern['level'] ?? 0),
                'description' => (string) ($concern['arabic_desc'] ?? $concern['description'] ?? ''),
            ];
        }

        return $mapped;
    }
}
