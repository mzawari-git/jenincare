<?php

namespace App\Services\AI;

use App\Models\AIProvider;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

abstract class BaseAIProvider implements AIProviderInterface
{
    protected AIProvider $aiProvider;

    protected Filesystem $disk;

    protected SkinDefectLibrary $defectLibrary;

    public function __construct(AIProvider $aiProvider)
    {
        $this->aiProvider = $aiProvider;
        $this->disk = Storage::disk(config('skinanalyzer.scan_disk', 'public'));
        $this->defectLibrary = app(SkinDefectLibrary::class);
    }

    public function getProviderName(): string
    {
        return $this->aiProvider->name;
    }

    public function isAvailable(): bool
    {
        return $this->aiProvider->is_active
            && $this->aiProvider->hasQuotaAvailable()
            && !empty($this->aiProvider->api_credentials);
    }

    public function getQuotaStatus(): array
    {
        return [
            'provider' => $this->aiProvider->driver_key,
            'limit' => $this->aiProvider->quota_limit,
            'used' => $this->aiProvider->quota_used,
            'remaining' => $this->aiProvider->quota_limit > 0
                ? max(0, $this->aiProvider->quota_limit - $this->aiProvider->quota_used)
                : null,
            'available' => $this->isAvailable(),
        ];
    }

    protected function validateImage(array $imageData): void
    {
        if (empty($imageData['path']) && empty($imageData['base64'])) {
            throw new \InvalidArgumentException('Image data must contain either a path or base64 encoded string.');
        }

        if (!empty($imageData['path'])) {
            if (!$this->disk->exists($imageData['path'])) {
                throw new \InvalidArgumentException("Image file not found at path: {$imageData['path']}");
            }
        }
    }

    protected function encryptImage(string $path): string
    {
        $rawContent = $this->disk->get($path);
        $encrypted = Crypt::encryptString($rawContent);
        $encryptedPath = $path . '.enc';
        $this->disk->put($encryptedPath, $encrypted);
        return $encryptedPath;
    }

    protected function decryptImage(string $path): string
    {
        $encrypted = $this->disk->get($path);
        return Crypt::decryptString($encrypted);
    }

    protected function logRequest(array $response): void
    {
        Log::channel('ai_requests')->info('AI Provider request completed', [
            'provider' => $this->aiProvider->driver_key,
            'engine_type' => $this->aiProvider->engine_type,
            'response_keys' => array_keys($response),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    protected function normalizeResponse(array $rawResponse): array
    {
        return UnifiedSkinData::fromProviderResponse($rawResponse, $this->aiProvider->driver_key)->toArray();
    }

    public function getEngineType(): \App\Enums\EngineType
    {
        return \App\Enums\EngineType::from($this->aiProvider->engine_type ?? 'structured');
    }

    protected function credentials(string $key, mixed $default = null): mixed
    {
        $credentials = $this->aiProvider->api_credentials;
        if (!is_array($credentials)) {
            return $default;
        }
        return $credentials[$key] ?? $default;
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        $config = $this->aiProvider->config;
        if (!is_array($config)) {
            return $default;
        }
        return $config[$key] ?? $default;
    }
}
