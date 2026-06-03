<?php

namespace App\Services\AI\Providers;

use App\Enums\EngineType;
use App\Services\AI\BaseAIProvider;
use App\Services\AI\AIProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YimeiAIProvider extends BaseAIProvider implements AIProviderInterface
{
    private const BASE_URL = 'https://api.yimei.ai/v1';

    private const SKIN_ANALYSIS_ENDPOINT = '/vision/skin-analysis';

    private const MAX_RETRIES = 3;

    private const RETRY_DELAY_MS = 1000;

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
                    'return_type' => 'structured',
                    'language' => 'ar',
                    'features' => ['radar', 'heatmap', 'defects', 'overall'],
                ]);

            if ($response->failed()) {
                throw new RequestException($response);
            }

            $data = $response->json();

            $rawResponse = [
                'engine' => 'yimei_ai',
                'request_id' => $data['request_id'] ?? null,
                'model_version' => $data['model_version'] ?? 'unknown',
                'processing_time_ms' => $data['processing_time'] ?? null,
                'confidence' => $data['confidence'] ?? 0.0,
                'overall_health_score' => $data['skin_score']['overall'] ?? 50,
                'radar_metrics' => [
                    'hydration' => $data['skin_score']['hydration'] ?? 50,
                    'sebum' => $data['skin_score']['sebum'] ?? 50,
                    'pigmentation' => $data['skin_score']['pigmentation'] ?? 50,
                    'pores' => $data['skin_score']['pores'] ?? 50,
                    'elasticity' => $data['skin_score']['elasticity'] ?? 50,
                ],
                'heatmap_coordinates' => $this->mapHeatmap($data['heatmap'] ?? []),
                'defects' => $this->mapDefects($data['defects'] ?? []),
                'custom_arabic_analysis' => $data['arabic_analysis'] ?? [],
                'custom_arabic_analysis_text' => $data['arabic_analysis']['summary'] ?? '',
                'expert_free_tips' => $data['arabic_tips'] ?? [],
            ];

            $this->logRequest($rawResponse);

            return $rawResponse;
        } catch (\Exception $e) {
            Log::error('YimeiAI API request failed', [
                'provider' => $this->aiProvider->driver_key,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \RuntimeException("YimeiAI analysis failed: {$e->getMessage()}", 0, $e);
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
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Client-Name' => 'SkinAnalyzer',
            'X-Client-Version' => config('app.version', '1.0.0'),
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
                'x' => (float) ($point['x'] ?? 50),
                'y' => (float) ($point['y'] ?? 50),
                'label' => (string) ($point['region'] ?? $point['label'] ?? 'unknown'),
                'severity' => (int) ($point['severity'] ?? 0),
            ];
        }

        return $mapped;
    }

    private function mapDefects(array $defectsData): array
    {
        $mapped = [];

        foreach ($defectsData as $defect) {
            $mapped[] = [
                'type' => (string) ($defect['type'] ?? 'unknown'),
                'severity' => (int) ($defect['severity'] ?? 0),
                'description' => (string) ($defect['description'] ?? $defect['name'] ?? ''),
            ];
        }

        return $mapped;
    }
}
