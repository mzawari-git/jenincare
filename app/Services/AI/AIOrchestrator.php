<?php

namespace App\Services\AI;

use App\Models\SkinScan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AIOrchestrator
{
    protected AIProviderFactory $factory;

    protected SkinDefectLibrary $defectLibrary;

    public function __construct(AIProviderFactory $factory, SkinDefectLibrary $defectLibrary)
    {
        $this->factory = $factory;
        $this->defectLibrary = $defectLibrary;
    }

    public function analyze(SkinScan $scan, ?string $preferredProvider = null): UnifiedSkinData
    {
        $this->ensureScanDiskRecording($scan);

        $provider = $preferredProvider
            ? $this->factory->create($preferredProvider)
            : $this->factory->createBestAvailable();

        $imageData = $this->prepareImageData($scan);

        $rawResponse = $provider->analyze($imageData);

        $unifiedData = UnifiedSkinData::fromProviderResponse($rawResponse, $provider->getProviderName());

        $this->enrichDefectsWithLibrary($unifiedData);

        $this->saveAnalysisResults($scan, $unifiedData, $provider);

        return $unifiedData;
    }

    public function analyzeWithAllProviders(SkinScan $scan): array
    {
        $results = [];

        $providers = AIProvider::where('is_active', true)
            ->orderBy('priority')
            ->get();

        foreach ($providers as $aiProvider) {
            try {
                $unified = $this->analyze($scan, $aiProvider->driver_key);
                $results[$aiProvider->driver_key] = $unified->toArray();
            } catch (\Throwable $e) {
                Log::warning("Provider {$aiProvider->driver_key} failed: {$e->getMessage()}");
                $results[$aiProvider->driver_key] = [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    protected function prepareImageData(SkinScan $scan): array
    {
        $imageData = [];

        if ($scan->image_path) {
            $imageData['path'] = $scan->image_path;
        }

        $imageData['localized_path'] = $scan->getLocalizedImagePath();

        if ($scan->features) {
            $imageData['features'] = $scan->features;
        }

        $spectralModes = [];
        foreach (['rgb', 'cross', 'parallel', 'uv'] as $mode) {
            $field = $mode . '_path';
            if ($scan->$field) {
                $spectralModes[$mode] = $scan->$field;
            }
        }
        $imageData['spectral_modes'] = $spectralModes;

        $imageData['metadata'] = [
            'scan_id' => $scan->id,
            'client_id' => $scan->client_id,
            'client_name' => $scan->client?->name ?? '',
            'captured_at' => $scan->created_at?->toIso8601String() ?? now()->toIso8601String(),
        ];

        if (!empty($scan->metadata) && is_array($scan->metadata)) {
            $imageData['metadata'] = array_merge($imageData['metadata'], $scan->metadata);
        }

        return $imageData;
    }

    protected function enrichDefectsWithLibrary(UnifiedSkinData $data): void
    {
        $enrichedDefects = [];
        foreach ($data->defects as $defect) {
            $type = $defect['type'] ?? '';

            $libraryDefect = $this->defectLibrary->find($type);

            if ($libraryDefect) {
                $defect['category'] = $defect['category'] ?: ($libraryDefect['category'] ?? '');
                $defect['requires_medical'] = $libraryDefect['requires_medical'] ?? false;

                if (empty($defect['recommended_ingredients']) && !empty($libraryDefect['ingredients'])) {
                    $defect['recommended_ingredients'] = array_slice($libraryDefect['ingredients'], 0, 5);
                }
            }

            $enrichedDefects[] = $defect;
        }
        $data->defects = $enrichedDefects;
    }

    protected function saveAnalysisResults(SkinScan $scan, UnifiedSkinData $data, AIProviderInterface $provider): void
    {
        DB::transaction(function () use ($scan, $data, $provider) {
            $scan->update([
                'analysis_status' => \App\Enums\AnalysisStatus::COMPLETED,
                'analysis_data' => $data->toArray(),
                'overall_score' => $data->overallHealthScore,
                'radar_metrics' => $data->radarMetrics,
                'advanced_metrics' => $data->advancedMetrics,
                'defects' => $data->defects,
                'heatmap_coordinates' => $data->heatmapCoordinates,
                'analyzed_by_provider' => $provider->getProviderName(),
                'confidence_score' => $data->confidence / 100,
                'analyzed_at' => now(),
            ]);
        });
    }

    protected function ensureScanDiskRecording(SkinScan $scan): void
    {
        if (empty($scan->image_path) && $scan instanceof \App\Models\SkinScan) {
            Log::warning("Scan {$scan->id} has no image_path set. Analysis may fail.");
        }
    }
}
