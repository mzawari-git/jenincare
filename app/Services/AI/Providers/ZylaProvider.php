<?php

namespace App\Services\AI\Providers;

use App\Enums\EngineType;
use App\Models\AIProvider;
use App\Services\AI\AIProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ZylaProvider implements AIProviderInterface
{
    private AIProvider $aiProvider;

    private const BASE_URL = 'https://zylalabs.com/api';

    public function __construct(AIProvider $aiProvider)
    {
        $this->aiProvider = $aiProvider;
    }

    public function analyze(array $imageData): array
    {
        $imageBase64 = $this->prepareImage($imageData);

        try {
            $response = Http::timeout(60)
                ->retry(2, 2000)
                ->withHeaders($this->buildHeaders())
                ->attach(
                    'image',
                    base64_decode($imageBase64),
                    'skin_image.jpg',
                    ['Content-Type' => 'image/jpeg']
                )
                ->post($this->getEndpoint());

            if ($response->failed()) {
                Log::error('Zyla API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException("Zyla API returned: " . $response->status());
            }

            $data = $response->json();

            return $this->normalizeResponse($data);
        } catch (\Exception $e) {
            Log::error('Zyla analysis failed', [
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException("Zyla analysis failed: {$e->getMessage()}", 0, $e);
        }
    }

    public function getProviderName(): string
    {
        return $this->aiProvider->name;
    }

    public function getEngineType(): EngineType
    {
        return EngineType::STRUCTURED;
    }

    public function isAvailable(): bool
    {
        $creds = $this->aiProvider->api_credentials ?? [];
        return !empty($creds['api_key']);
    }

    public function getQuotaStatus(): array
    {
        return [
            'limit' => $this->aiProvider->quota_limit,
            'used' => $this->aiProvider->quota_used,
            'remaining' => max(0, $this->aiProvider->quota_limit - $this->aiProvider->quota_used),
        ];
    }

    private function getEndpoint(): string
    {
        $creds = $this->aiProvider->api_credentials ?? [];
        return $creds['endpoint_url'] ?? self::BASE_URL . '/skin-analyze-advanced';
    }

    private function buildHeaders(): array
    {
        $creds = $this->aiProvider->api_credentials ?? [];
        return [
            'Authorization' => 'Bearer ' . ($creds['api_key'] ?? ''),
            'Accept' => 'application/json',
        ];
    }

    private function prepareImage(array $imageData): string
    {
        if (!empty($imageData['base64'])) {
            return preg_replace('#^data:image/\w+;base64,#', '', $imageData['base64']);
        }

        if (!empty($imageData['contents'])) {
            return base64_encode($imageData['contents']);
        }

        if (!empty($imageData['path'])) {
            $disk = Storage::disk('local');
            $content = $disk->get($imageData['path']);
            return base64_encode($content);
        }

        throw new \InvalidArgumentException('No valid image data provided for Zyla analysis.');
    }

    private function normalizeResponse(array $data): array
    {
        $results = $data['results'] ?? $data['data'] ?? $data;

        $radarMetrics = [
            'hydration' => $results['hydration'] ?? $results['moisture'] ?? 50,
            'sebum' => $results['sebum'] ?? $results['oil'] ?? 50,
            'pigmentation' => $results['pigmentation'] ?? $results['spots'] ?? 50,
            'pores' => $results['pores'] ?? 50,
            'elasticity' => $results['elasticity'] ?? $results['texture'] ?? 50,
        ];

        $overallScore = (int) round(array_sum($radarMetrics) / count($radarMetrics));

        $defects = [];
        foreach (($results['conditions'] ?? $results['issues'] ?? $results['defects'] ?? []) as $item) {
            $defects[] = [
                'type' => $item['type'] ?? $item['name'] ?? 'unknown',
                'severity' => (int) ($item['severity'] ?? $item['level'] ?? 1),
                'description' => $item['description'] ?? $item['label'] ?? '',
            ];
        }

        $heatmapCoordinates = [];
        foreach (($results['regions'] ?? $results['heatmap'] ?? $results['areas'] ?? []) as $point) {
            $heatmapCoordinates[] = [
                'x' => (float) ($point['x'] ?? 50),
                'y' => (float) ($point['y'] ?? 50),
                'label' => $point['name'] ?? $point['label'] ?? $point['region'] ?? 'unknown',
                'severity' => (int) ($point['severity'] ?? $point['level'] ?? 0),
            ];
        }

        $arabicAnalysis = $results['arabic_analysis'] ?? $results['analysis_text'] ?? [];
        $arabicTips = $results['tips'] ?? $results['arabic_tips'] ?? $results['recommendations'] ?? [];

        return [
            'engine' => 'zyla_labs',
            'version' => $results['version'] ?? $data['api_version'] ?? '1.0',
            'processing_time_ms' => $results['processing_time'] ?? $data['time_ms'] ?? null,
            'confidence' => (float) ($results['confidence'] ?? $data['confidence'] ?? 0.0),
            'overall_health_score' => $results['overall_score'] ?? $data['overall_health_score'] ?? $overallScore,
            'radar_metrics' => $radarMetrics,
            'heatmap_coordinates' => $heatmapCoordinates,
            'defects' => $defects,
            'custom_arabic_analysis' => $arabicAnalysis,
            'custom_arabic_analysis_text' => is_array($arabicAnalysis)
                ? ($arabicAnalysis['summary'] ?? ($arabicAnalysis['text'] ?? ''))
                : (string) $arabicAnalysis,
            'expert_free_tips' => $arabicTips,
        ];
    }
}
