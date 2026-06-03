<?php

namespace App\Services\AI;

use App\Enums\AnalysisStatus;
use App\Models\AIProvider;
use App\Models\SkinAnalysis;
use App\Models\SkinAnalysisPin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AIOrchestrator
{
    private const MIN_IMAGE_WIDTH = 200;
    private const MIN_IMAGE_HEIGHT = 200;
    private const MAX_IMAGE_WIDTH = 4096;
    private const MAX_IMAGE_HEIGHT = 4096;
    private const MAX_IMAGE_SIZE_BYTES = 10_485_760;

    protected AIProviderFactory $factory;

    protected ?string $lastProviderUsed = null;

    public function __construct(AIProviderFactory $factory)
    {
        $this->factory = $factory;
    }

    public function processScan(string $imagePath, ?string $providerKey = null): UnifiedSkinData
    {
        Log::info('Starting scan processing', [
            'image_path' => $imagePath,
            'requested_provider' => $providerKey,
        ]);

        $this->preprocessImage($imagePath);

        $providerKey = $this->checkQuotaAndFailover($providerKey);

        try {
            $result = $this->routeToProvider($imagePath, $providerKey);
            $this->lastProviderUsed = $providerKey;

            Log::info('Scan processing completed successfully', [
                'provider' => $providerKey,
                'overall_score' => $result->overallHealthScore,
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::warning('Primary provider failed, initiating failover', [
                'provider' => $providerKey,
                'error' => $e->getMessage(),
            ]);

            $result = $this->handleFailover($imagePath);

            Log::info('Failover processing completed', [
                'failover_provider' => $this->lastProviderUsed,
                'overall_score' => $result->overallHealthScore,
            ]);

            return $result;
        }
    }

    public function preprocessImage(string $imagePath): bool
    {
        $disk = Storage::disk(config('skinanalyzer.scan_disk', 'local'));

        if (! $disk->exists($imagePath)) {
            throw new \RuntimeException("Image file not found: {$imagePath}");
        }

        $size = $disk->size($imagePath);

        if ($size > self::MAX_IMAGE_SIZE_BYTES) {
            throw new \RuntimeException(sprintf(
                'Image exceeds maximum size of %d MB.',
                self::MAX_IMAGE_SIZE_BYTES / 1_048_576
            ));
        }

        if ($size === 0) {
            throw new \RuntimeException('Image file is empty.');
        }

        $mimeType = $disk->mimeType($imagePath);

        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new \RuntimeException("Unsupported image format: {$mimeType}. Only JPEG, PNG, and WebP are accepted.");
        }

        $imageInfo = @getimagesize($disk->path($imagePath));

        if ($imageInfo === false) {
            throw new \RuntimeException('Unable to read image dimensions. The file may be corrupted.');
        }

        [$width, $height] = $imageInfo;

        if ($width < self::MIN_IMAGE_WIDTH || $height < self::MIN_IMAGE_HEIGHT) {
            throw new \RuntimeException(sprintf(
                'Image dimensions too small: %dx%d. Minimum required: %dx%d.',
                $width,
                $height,
                self::MIN_IMAGE_WIDTH,
                self::MIN_IMAGE_HEIGHT
            ));
        }

        if ($width > self::MAX_IMAGE_WIDTH || $height > self::MAX_IMAGE_HEIGHT) {
            throw new \RuntimeException(sprintf(
                'Image dimensions too large: %dx%d. Maximum allowed: %dx%d.',
                $width,
                $height,
                self::MAX_IMAGE_WIDTH,
                self::MAX_IMAGE_HEIGHT
            ));
        }

        Log::info('Image preprocessing passed', [
            'path' => $imagePath,
            'size' => $size,
            'dimensions' => "{$width}x{$height}",
            'mime' => $mimeType,
        ]);

        return true;
    }

    public function routeToProvider(string $imagePath, string $providerKey): UnifiedSkinData
    {
        $provider = $this->resolveProvider($providerKey);

        if (! $provider->isAvailable()) {
            throw new \RuntimeException("Provider '{$providerKey}' is not available.");
        }

        $imageData = ['path' => $imagePath];

        $rawResponse = $provider->analyze($imageData);

        $normalized = UnifiedSkinData::fromProviderResponse($rawResponse, $providerKey);

        return $normalized;
    }

    public function handleFailover(string $imagePath): UnifiedSkinData
    {
        $nativeProvider = $this->factory->getProviderByKey('native');

        if ($nativeProvider && $nativeProvider->isAvailable()) {
            $this->lastProviderUsed = 'native';
            $imageData = ['path' => $imagePath];
            $rawResponse = $nativeProvider->analyze($imageData);
            return UnifiedSkinData::fromProviderResponse($rawResponse, 'native');
        }

        $nativeModel = new AIProvider([
            'name' => 'Native Engine (Fallback)',
            'driver_key' => 'native',
            'engine_type' => 'structured',
            'is_active' => true,
            'quota_limit' => 0,
            'quota_used' => 0,
            'config' => [],
        ]);

        $native = new \App\Services\AI\Providers\NativeEngineProvider($nativeModel);
        $this->lastProviderUsed = 'native';

        $imageData = ['path' => $imagePath];
        $rawResponse = $native->analyze($imageData);

        return UnifiedSkinData::fromProviderResponse($rawResponse, 'native');
    }

    public function checkQuotaAndFailover(?string $requestedKey = null): string
    {
        if ($requestedKey !== null && $requestedKey !== 'native') {
            $provider = $this->factory->getProviderByKey($requestedKey);

            if ($provider && $provider->isAvailable()) {
                return $requestedKey;
            }

            Log::warning("Requested provider '{$requestedKey}' is unavailable, checking quotas.", [
                'requested' => $requestedKey,
            ]);
        }

        $providers = AIProvider::where('is_active', true)
            ->where('driver_key', '!=', 'native')
            ->orderBy('quota_used')
            ->get();

        foreach ($providers as $dbProvider) {
            if ($dbProvider->hasQuotaAvailable() && ! empty($dbProvider->api_credentials)) {
                Log::info('Selected provider based on quota', [
                    'provider' => $dbProvider->driver_key,
                    'quota_used' => $dbProvider->quota_used,
                    'quota_limit' => $dbProvider->quota_limit,
                ]);
                return $dbProvider->driver_key;
            }
        }

        Log::info('No external providers available, using native engine.');

        return 'native';
    }

    public function persistScanResult(
        SkinAnalysis $scan,
        UnifiedSkinData $result,
        ?string $providerKey = null
    ): SkinAnalysis {
        $providerKey = $providerKey ?? $this->lastProviderUsed ?? 'native';

        $provider = AIProvider::where('driver_key', $providerKey)->first();

        DB::transaction(function () use ($scan, $result, $provider) {
            $scan->update([
                'ai_provider_id' => $provider?->id,
                'status' => AnalysisStatus::PENDING->value,
                'overall_health_score' => $result->overallHealthScore,
                'radar_metrics' => $result->radarMetrics,
                'heatmap_coordinates' => $result->heatmapCoordinates,
                'custom_arabic_analysis' => $result->customArabicAnalysisText,
                'expert_free_tips' => $result->expertFreeTips,
                'raw_vendor_response' => $result->rawResponse,
            ]);

            if ($provider) {
                $provider->incrementQuota();
            }

            SkinAnalysisPin::create([
                'skin_analysis_id' => $scan->id,
                'pin_code' => SkinAnalysisPin::generatePin(),
                'is_used' => false,
                'expires_at' => now()->addDays(30),
            ]);
        });

        return $scan->fresh();
    }

    public function getLastProviderUsed(): ?string
    {
        return $this->lastProviderUsed;
    }

    private function resolveProvider(string $providerKey): AIProviderInterface
    {
        return $this->factory->getProviderByKey($providerKey)
            ?? $this->factory->getActiveProvider();
    }
}
