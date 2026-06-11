<?php

namespace App\Services\AI\Providers;

use App\Enums\EngineType;
use App\Services\AI\BaseAIProvider;
use App\Services\AI\AIProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HautAIProvider extends BaseAIProvider implements AIProviderInterface
{
    private const BASE_URL = 'https://api.haut.ai/v1';

    private const SKIN_ANALYSIS_ENDPOINT = '/skin/analyze';

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
                    'analysis_type' => 'full',
                    'include_heatmap' => true,
                    'language' => 'ar',
                ]);

            if ($response->failed()) {
                throw new RequestException($response);
            }

            $data = $response->json();

            $rawResponse = [
                'engine' => 'haut_ai',
                'request_id' => $data['analysis_id'] ?? null,
                'model_version' => $data['model_version'] ?? 'unknown',
                'processing_time_ms' => $data['processing_time_ms'] ?? null,
                'confidence' => $data['confidence_score'] ?? 0.0,
                'overall_health_score' => $data['skin_health']['overall_score'] ?? 50,
                'radar_metrics' => [
                    'hydration' => $data['skin_health']['hydration'] ?? 50,
                    'sebum' => $data['skin_health']['sebum'] ?? 50,
                    'pigmentation' => $data['skin_health']['pigmentation'] ?? 50,
                    'pores' => $data['skin_health']['pores'] ?? 50,
                    'elasticity' => $data['skin_health']['elasticity'] ?? 50,
                ],
                'heatmap_coordinates' => $this->mapHeatmap($data['facial_regions'] ?? []),
                'defects' => $this->mapDefects($data['detected_issues'] ?? []),
                'custom_arabic_analysis' => $data['arabic_insights'] ?? [],
                'custom_arabic_analysis_text' => $data['arabic_insights']['summary'] ?? '',
                'expert_free_tips' => $data['skincare_recommendations'] ?? [],
            ];

            $this->logRequest($rawResponse);

            return $rawResponse;
        } catch (\Exception $e) {
            Log::error('HautAI API request failed', [
                'provider' => $this->aiProvider->driver_key,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException("HautAI analysis failed: {$e->getMessage()}", 0, $e);
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

    private function mapHeatmap(array $regions): array
    {
        $mapped = [];

        foreach ($regions as $region) {
            $mapped[] = [
                'x' => (float) ($region['coordinate_x'] ?? $region['x'] ?? 50),
                'y' => (float) ($region['coordinate_y'] ?? $region['y'] ?? 50),
                'label' => (string) ($region['name'] ?? $region['label'] ?? 'unknown'),
                'severity' => (int) ($region['concern_level'] ?? $region['severity'] ?? 0),
            ];
        }

        return $mapped;
    }

    private function mapDefects(array $issues): array
    {
        $mapped = [];

        foreach ($issues as $issue) {
            $mapped[] = [
                'type' => (string) ($issue['type'] ?? $issue['category'] ?? 'unknown'),
                'severity' => (int) ($issue['severity'] ?? $issue['intensity'] ?? 0),
                'description' => (string) ($issue['description_ar'] ?? $issue['description'] ?? ''),
            ];
        }

        return $mapped;
    }
}
