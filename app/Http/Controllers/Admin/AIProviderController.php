<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EngineType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProviderRequest;
use App\Models\AIProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AIProviderController extends Controller
{
    public function index(): JsonResponse
    {
        $providers = AIProvider::withCount('skinAnalyses')
            ->orderBy('engine_type')
            ->orderBy('name')
            ->get()
            ->map(fn (AIProvider $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'engine' => $p->driver_key,
                'driver_key' => $p->driver_key,
                'engine_type' => $p->engine_type,
                'engine_type_label' => EngineType::from($p->engine_type)->label(),
                'engine_label' => EngineType::from($p->engine_type)->label(),
                'is_active' => $p->is_active,
                'quota_limit' => $p->quota_limit,
                'quota_used' => $p->quota_used,
                'quota_percentage' => $p->quota_limit > 0
                    ? round(($p->quota_used / $p->quota_limit) * 100, 1)
                    : 0,
                'has_quota' => $p->hasQuotaAvailable(),
                'has_credentials' => ! empty($p->api_credentials),
                'total_scans' => $p->skin_analyses_count,
                'created_at' => $p->created_at,
                'updated_at' => $p->updated_at,
            ]);

        return response()->json(['data' => $providers]);
    }

    public function show(int $id): JsonResponse
    {
        $provider = AIProvider::withCount('skinAnalyses')->findOrFail($id);

        $data = [
            'id' => $provider->id,
            'name' => $provider->name,
            'driver_key' => $provider->driver_key,
            'engine_type' => $provider->engine_type,
            'engine_type_label' => EngineType::from($provider->engine_type)->label(),
            'is_active' => $provider->is_active,
            'quota_limit' => $provider->quota_limit,
            'quota_used' => $provider->quota_used,
            'quota_percentage' => $provider->quota_limit > 0
                ? round(($provider->quota_used / $provider->quota_limit) * 100, 1)
                : 0,
            'has_quota' => $provider->hasQuotaAvailable(),
            'config' => $provider->config,
            'total_scans' => $provider->skin_analyses_count,
            'created_at' => $provider->created_at,
            'updated_at' => $provider->updated_at,
        ];

        return response()->json(['data' => $data]);
    }

    public function update(int $id, UpdateProviderRequest $request): JsonResponse
    {
        $provider = AIProvider::findOrFail($id);

        $data = $request->only([
            'name', 'engine_type', 'api_credentials', 'quota_limit', 'config',
        ]);

        if ($request->has('config')) {
            $existingConfig = $provider->config ?? [];
            $data['config'] = array_merge($existingConfig, $request->input('config'));
        }

        $provider->update($data);

        return response()->json([
            'message' => 'Provider updated successfully.',
            'data' => $provider->fresh(),
        ]);
    }

    public function activate(int $id, Request $request): JsonResponse
    {
        $provider = AIProvider::findOrFail($id);

        $action = $request->input('action', 'activate');

        if ($action === 'deactivate') {
            return $this->deactivate($id);
        }

        DB::transaction(function () use ($provider) {
            AIProvider::where('engine_type', $provider->engine_type)
                ->where('id', '!=', $provider->id)
                ->update(['is_active' => false]);

            $provider->update(['is_active' => true]);
        });

        $provider->refresh();

        return response()->json([
            'message' => "{$provider->name} has been activated.",
            'provider' => [
                'id' => $provider->id,
                'name' => $provider->name,
                'engine' => $provider->driver_key,
                'is_active' => $provider->is_active,
            ],
        ]);
    }

    public function deactivate(int $id): JsonResponse
    {
        $provider = AIProvider::findOrFail($id);

        $provider->update(['is_active' => false]);

        return response()->json([
            'message' => "{$provider->name} has been deactivated.",
            'provider' => [
                'id' => $provider->id,
                'name' => $provider->name,
                'engine' => $provider->driver_key,
                'is_active' => $provider->is_active,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'driver_key' => 'required|string|max:50|unique:ai_providers,driver_key',
            'engine_type' => 'required|in:structured,generative,hybrid',
            'api_key' => 'nullable|string|max:500',
            'endpoint_url' => 'nullable|url|max:500',
            'model' => 'nullable|string|max:100',
            'quota_limit' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $provider = AIProvider::create([
            'name' => $validated['name'],
            'driver_key' => $validated['driver_key'],
            'engine_type' => $validated['engine_type'],
            'api_credentials' => [
                'api_key' => $validated['api_key'] ?? '',
                'endpoint_url' => $validated['endpoint_url'] ?? '',
                'model' => $validated['model'] ?? '',
            ],
            'is_active' => false,
            'quota_limit' => $validated['quota_limit'] ?? 0,
            'quota_used' => 0,
            'config' => [
                'description' => $validated['description'] ?? '',
            ],
        ]);

        return response()->json([
            'message' => "Provider '{$provider->name}' created successfully.",
            'data' => [
                'id' => $provider->id,
                'name' => $provider->name,
                'engine' => $provider->driver_key,
                'driver_key' => $provider->driver_key,
                'engine_type' => $provider->engine_type,
                'engine_type_label' => EngineType::from($provider->engine_type)->label(),
                'engine_label' => EngineType::from($provider->engine_type)->label(),
                'is_active' => $provider->is_active,
                'quota_limit' => $provider->quota_limit,
                'quota_used' => $provider->quota_used,
                'quota_percentage' => 0,
                'has_quota' => true,
                'has_credentials' => !empty($validated['api_key'] ?? ''),
                'total_scans' => 0,
                'created_at' => $provider->created_at,
                'updated_at' => $provider->updated_at,
            ],
        ], 201);
    }

    public function quotaStatus(): JsonResponse
    {
        $providers = AIProvider::orderBy('engine_type')
            ->orderBy('name')
            ->get()
            ->map(fn (AIProvider $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'driver_key' => $p->driver_key,
                'engine_type' => $p->engine_type,
                'is_active' => $p->is_active,
                'quota_limit' => $p->quota_limit,
                'quota_used' => $p->quota_used,
                'quota_remaining' => max(0, $p->quota_limit - $p->quota_used),
                'percentage' => $p->quota_limit > 0
                    ? round(($p->quota_used / $p->quota_limit) * 100, 1)
                    : 0,
                'status' => match (true) {
                    ! $p->is_active => 'inactive',
                    ! $p->hasQuotaAvailable() => 'exhausted',
                    $p->quota_limit > 0 && ($p->quota_used / $p->quota_limit) > 0.85 => 'warning',
                    default => 'healthy',
                },
            ]);

        return response()->json(['data' => $providers]);
    }

    public function testConnection(int $id): JsonResponse
    {
        $provider = AIProvider::findOrFail($id);

        if (empty($provider->api_credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'No API credentials configured for this provider.',
            ], 400);
        }

        try {
            $endpoint = $provider->api_credentials['endpoint_url']
                ?? $this->getDefaultEndpoint($provider->driver_key);

            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . ($provider->api_credentials['api_key'] ?? ''),
                    'Accept' => 'application/json',
                ])
                ->get($endpoint);

            return response()->json([
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'latency_ms' => $response->transferStats?->getTransferTime()
                    ? round($response->transferStats->getTransferTime() * 1000)
                    : null,
                'message' => $response->successful()
                    ? 'Connection successful.'
                    : 'Connection failed: ' . $response->reason(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'status_code' => null,
                'latency_ms' => null,
                'message' => 'Connection error: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getDefaultEndpoint(string $driverKey): string
    {
        return match ($driverKey) {
            'openai' => 'https://api.openai.com/v1/models',
            'claude' => 'https://api.anthropic.com/v1/messages',
            'gemini' => 'https://generativelanguage.googleapis.com/v1beta/models',
            'yimei' => 'https://api.yimei.ai/v1/status',
            'native' => config('app.url') . '/api/health',
            default => config('app.url') . '/api/health',
        };
    }
}
