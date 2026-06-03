<?php

namespace App\Providers;

use App\Models\AIProvider;
use App\Services\AI\AIProviderInterface;
use App\Services\AI\AIProviderFactory;
use App\Services\AI\AIOrchestrator;
use App\Services\AI\BroadcastService;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AIProviderFactory::class, function ($app) {
            return new AIProviderFactory($app);
        });

        $this->app->singleton(AIOrchestrator::class, function ($app) {
            return new AIOrchestrator(
                $app->make(AIProviderFactory::class),
            );
        });

        $this->app->singleton(BroadcastService::class, function ($app) {
            return new BroadcastService();
        });

        $this->app->bind(AIProviderInterface::class, function ($app) {
            $provider = AIProvider::active()->first();

            if (! $provider) {
                $provider = AIProvider::first();
                if (! $provider) {
                    throw new \RuntimeException('No AI provider configured.');
                }
            }

            return $app->make(AIProviderFactory::class)->create($provider->driver_key, $provider->toArray());
        });

        $this->registerProviderDrivers();
    }

    public function boot(): void
    {
        //
    }

    private function registerProviderDrivers(): void
    {
        $providers = AIProvider::all();

        foreach ($providers as $provider) {
            $bindingKey = "ai.provider.{$provider->driver_key}";

            $this->app->bind($bindingKey, function ($app) use ($provider) {
                return $app->make(AIProviderFactory::class)
                    ->create($provider->driver_key, $provider->fresh()->toArray());
            });
        }
    }
}
