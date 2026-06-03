<?php

namespace App\Services\AI;

use App\Models\AIProvider;
use Illuminate\Contracts\Foundation\Application;
use RuntimeException;

class AIProviderFactory
{
    public function __construct(
        private readonly Application $app,
    ) {}

    public function create(string $driverKey, array $config): AIProviderInterface
    {
        $driverClass = $this->resolveDriverClass($driverKey);

        if (! class_exists($driverClass)) {
            throw new RuntimeException("AI provider driver [{$driverKey}] not found. Expected class: {$driverClass}");
        }

        return $this->app->make($driverClass, ['config' => $config]);
    }

    private array $driverMap = [
        'native'       => 'NativeEngineProvider',
        'yimei'        => 'YimeiAIProvider',
        'openai'       => 'OpenaiProvider',
        'claude'       => 'ClaudeProvider',
        'gemini'       => 'GeminiProvider',
        'hautai'       => 'HautAIProvider',
        'perfectcorp'  => 'PerfectCorpProvider',
        'skinive'      => 'SkiniveProvider',
        'huggingface'  => 'HuggingFaceProvider',
    ];

    public function getProviderByKey(string $key): AIProviderInterface
    {
        $provider = AIProvider::where('driver_key', $key)->first();
        if (!$provider) {
            throw new RuntimeException("AI provider [{$key}] not found in database.");
        }
        return $this->create($key, $provider->api_credentials ?? []);
    }

    public function getActiveProvider(): AIProviderInterface
    {
        $provider = AIProvider::where('is_active', true)->first();
        if (!$provider) {
            return $this->getProviderByKey('native');
        }
        return $this->create($provider->driver_key, $provider->api_credentials ?? []);
    }

    private function resolveDriverClass(string $driverKey): string
    {
        $className = $this->driverMap[$driverKey] ?? null;

        if ($className === null) {
            throw new RuntimeException("Unknown AI provider driver key: [{$driverKey}]");
        }

        return "App\\Services\\AI\\Providers\\{$className}";
    }
}
