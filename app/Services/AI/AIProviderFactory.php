<?php

namespace App\Services\AI;

use App\Models\AIProvider;

class AIProviderFactory
{
    protected array $providerMap = [];

    protected SkinDefectLibrary $defectLibrary;

    public function __construct(SkinDefectLibrary $defectLibrary)
    {
        $this->defectLibrary = $defectLibrary;
        $this->registerBuiltIn();
    }

    protected function registerBuiltIn(): void
    {
        $this->register('openai', \App\Services\AI\Providers\OpenAIProvider::class);
        $this->register('claude', \App\Services\AI\Providers\ClaudeProvider::class);
        $this->register('gemini', \App\Services\AI\Providers\GeminiProvider::class);
        $this->register('native', \App\Services\AI\Providers\NativeEngineProvider::class);
    }

    public function register(string $driver, string $className): void
    {
        $this->providerMap[$driver] = $className;
    }

    public function create(string $driverKey): AIProviderInterface
    {
        $aiProvider = AIProvider::where('driver_key', $driverKey)->first();

        if (!$aiProvider) {
            if ($driverKey === 'native') {
                $aiProvider = AIProvider::firstOrCreate(
                    ['driver_key' => 'native'],
                    [
                        'name' => 'Native Engine',
                        'name_ar' => 'المحرك المحلي',
                        'engine_type' => 'structured',
                        'is_active' => true,
                        'quota_limit' => 0,
                        'quota_used' => 0,
                        'config' => [],
                        'api_credentials' => [],
                        'priority' => 0,
                    ]
                );
            } else {
                throw new \RuntimeException("AI provider '{$driverKey}' not found in database.");
            }
        }

        $class = $this->providerMap[$driverKey] ?? null;

        if (!$class) {
            throw new \RuntimeException("No provider class registered for driver: {$driverKey}");
        }

        return new $class($aiProvider);
    }

    public function createBestAvailable(): AIProviderInterface
    {
        $providers = AIProvider::where('is_active', true)
            ->orderBy('priority')
            ->get();

        foreach ($providers as $provider) {
            $class = $this->providerMap[$provider->driver_key] ?? null;
            if (!$class) {
                continue;
            }

            $instance = new $class($provider);
            if ($instance->isAvailable()) {
                return $instance;
            }
        }

        return $this->create('native');
    }

    public function getAvailableProviders(): array
    {
        return AIProvider::where('is_active', true)
            ->orderBy('priority')
            ->get()
            ->map(function (AIProvider $p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'name_ar' => $p->name_ar,
                    'driver_key' => $p->driver_key,
                    'engine_type' => $p->engine_type,
                    'priority' => $p->priority,
                    'is_available' => $p->hasQuotaAvailable(),
                    'quota_status' => [
                        'limit' => $p->quota_limit,
                        'used' => $p->quota_used,
                        'remaining' => $p->quota_limit > 0 ? max(0, $p->quota_limit - $p->quota_used) : null,
                    ],
                ];
            })
            ->toArray();
    }

    public function hasProvider(string $driverKey): bool
    {
        return isset($this->providerMap[$driverKey]);
    }
}
