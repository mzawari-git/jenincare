<?php

namespace App\Services\AI\Providers;

use App\Enums\EngineType;
use App\Services\AI\BaseAIProvider;
use App\Services\AI\AIProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PerfectCorpProvider extends BaseAIProvider implements AIProviderInterface
{
    private const BASE_URL = 'https://api.perfectcorp.com/aiskin/v1';

    private const SKIN_ANALYSIS_ENDPOINT = '/analyze';

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
                    'image_base64' => $base64Image,
                    'analysis_modules' => ['skin_health', 'defects', 'facial_map', 'recommendations'],
                    'locale' => 'ar_SA',
                ]);

            if ($response->failed()) {
                throw new RequestException($response);
            }

            $data = $response->json();

            $rawResponse = [
                'engine' => 'perfectcorp',
                'request_id' => $data['session_id'] ?? null,
                'model_version' => $data['ai_engine_version'] ?? 'unknown',
                'processing_time_ms' => $data['duration_ms'] ?? null,
                'confidence' => $data['ai_confidence'] ?? 0.0,
                'overall_health_score' => $data['skin_report']['overall_score'] ?? 50,
                'radar_metrics' => [
                    'hydration' => $data['skin_report']['hydration_score'] ?? 50,
                    'sebum' => $data['skin_report']['oil_score'] ?? $data['skin_report']['sebum_score'] ?? 50,
                    'pigmentation' => $data['skin_report']['pigmentation_score'] ?? 50,
                    'pores' => $data['skin_report']['pores_score'] ?? 50,
                    'elasticity' => $data['skin_report']['firmness_score'] ?? $data['skin_report']['elasticity_score'] ?? 50,
                ],
                'heatmap_coordinates' => $this->mapHeatmap($data['facial_map'] ?? []),
                'defects' => $this->mapDefects($data['defects'] ?? []),
                'custom_arabic_analysis' => $data['arabic_consultation'] ?? [],
                'custom_arabic_analysis_text' => $data['arabic_consultation']['full_text'] ?? '',
                'expert_free_tips' => $data['recommendations']['tips'] ?? [],
            ];

            $this->logRequest($rawResponse);

            return $rawResponse;
        } catch (\Exception $e) {
            Log::error('PerfectCorp API request failed', [
                'provider' => $this->aiProvider->driver_key,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException("PerfectCorp analysis failed: {$e->getMessage()}", 0, $e);
        }
    }

    public function getEngineType(): EngineType
    {
        return EngineType::STRUCTURED;
    }

    private function buildHeaders(): array
    {
        return [
            'X-Api-Key' => $this->credentials('api_key'),
            'X-Api-Secret' => $this->credentials('api_secret', ''),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    private function extractBase64(array $imageData): string
    {
        if (! empty($imageData['base64'])) {
            $base64 = $imageData['base64'];
            if (str_contains($base64, 'base64,')) {
                $parts = explode('base64,', $base64, 2);
                return $parts[1];
            }
            return $base64;
        }

        if (! empty($imageData['path'])) {
            $content = $this->disk->get($imageData['path']);
            return base64_encode($content);
        }

        throw new \InvalidArgumentException('No valid image data provided.');
    }

    private function mapHeatmap(array $facialMap): array
    {
        $mapped = [];

        foreach ($facialMap as $point) {
            $mapped[] = [
                'x' => (float) ($point['x_percent'] ?? $point['x'] ?? 50),
                'y' => (float) ($point['y_percent'] ?? $point['y'] ?? 50),
                'label' => (string) ($point['facial_zone'] ?? $point['label'] ?? 'unknown'),
                'severity' => (int) ($point['concern_rating'] ?? $point['severity'] ?? 0),
            ];
        }

        return $mapped;
    }

    private function mapDefects(array $defects): array
    {
        $mapped = [];

        foreach ($defects as $defect) {
            $mapped[] = [
                'type' => (string) ($defect['defect_type'] ?? $defect['type'] ?? 'unknown'),
                'severity' => (int) ($defect['severity'] ?? $defect['intensity'] ?? 0),
                'description' => (string) ($defect['description_arabic'] ?? $defect['description'] ?? ''),
            ];
        }

        return $mapped;
    }
}
