<?php

namespace App\Http\Controllers\Admin\Api;

use App\Enums\EngineType;
use App\Http\Controllers\Controller;
use App\Models\AIProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AIProviderController extends Controller
{
    public function index(): JsonResponse
    {
        $providers = AIProvider::orderBy('engine_type')
            ->orderBy('name')
            ->get()
            ->map(fn(AIProvider $p) => $this->formatProvider($p));

        return response()->json(['data' => $providers]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'sometimes|nullable|string|max:255',
            'driver_key' => 'required|string|unique:ai_providers,driver_key',
            'engine_type' => 'required|in:structured,generative,hybrid',
            'quota_limit' => 'sometimes|integer|min:0',
            'priority' => 'sometimes|integer|min:0|max:255',
            'description' => 'sometimes|string|max:500',
        ]);

        $apiCredentials = array_filter([
            'api_key' => $request->input('api_key'),
            'endpoint_url' => $request->input('endpoint_url'),
        ]);

        $config = array_filter([
            'model' => $request->input('model'),
            'description' => $request->input('description'),
        ]);

        $provider = AIProvider::create([
            'name' => $validated['name'],
            'name_ar' => $validated['name_ar'] ?? null,
            'driver_key' => $validated['driver_key'],
            'engine_type' => $validated['engine_type'],
            'api_credentials' => !empty($apiCredentials) ? $apiCredentials : null,
            'config' => !empty($config) ? $config : null,
            'quota_limit' => $validated['quota_limit'] ?? 0,
            'priority' => $validated['priority'] ?? 0,
        ]);

        return response()->json([
            'data' => $this->formatProvider($provider),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $provider = AIProvider::findOrFail($id);

        return response()->json([
            'data' => $this->formatProviderDetail($provider),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $provider = AIProvider::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'sometimes|nullable|string|max:255',
            'engine_type' => 'sometimes|in:structured,generative,hybrid',
            'quota_limit' => 'sometimes|integer|min:0',
            'priority' => 'sometimes|integer|min:0|max:255',
            'api_key' => 'sometimes|nullable|string',
            'endpoint' => 'sometimes|nullable|string',
            'model_id' => 'sometimes|nullable|string',
        ]);

        if ($request->has('name')) {
            $provider->name = $request->input('name');
        }

        if ($request->has('name_ar')) {
            $provider->name_ar = $request->input('name_ar');
        }

        if ($request->has('engine_type')) {
            $provider->engine_type = $request->input('engine_type');
        }

        if ($request->has('quota_limit')) {
            $provider->quota_limit = $request->input('quota_limit');
        }

        if ($request->has('priority')) {
            $provider->priority = $request->input('priority');
        }

        $credentials = $provider->api_credentials ?? [];
        if ($request->has('api_key')) {
            $credentials['api_key'] = $request->input('api_key');
        }
        if ($request->has('endpoint')) {
            $credentials['endpoint_url'] = $request->input('endpoint');
        }
        $provider->api_credentials = $credentials;

        $config = $provider->config ?? [];
        if ($request->has('model_id')) {
            $config['model'] = $request->input('model_id');
        }
        $provider->config = $config;

        $provider->save();

        return response()->json([
            'message' => 'تم تحديث المزود بنجاح',
            'provider' => $this->formatProviderDetail($provider),
        ]);
    }

    public function toggle(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate',
        ]);

        $provider = AIProvider::findOrFail($id);

        if ($request->input('action') === 'activate') {
            DB::transaction(function () use ($provider) {
                AIProvider::where('engine_type', $provider->engine_type)
                    ->where('id', '!=', $provider->id)
                    ->update(['is_active' => false]);

                $provider->update(['is_active' => true]);
            });

            return response()->json([
                'message' => "تم تفعيل {$provider->name} بنجاح",
                'provider' => ['is_active' => true],
            ]);
        }

        $provider->update(['is_active' => false]);

        return response()->json([
            'message' => "تم إلغاء تفعيل {$provider->name} بنجاح",
            'provider' => ['is_active' => false],
        ]);
    }

    public function quotaStatus(): JsonResponse
    {
        $providers = AIProvider::orderBy('engine_type')
            ->orderBy('name')
            ->get()
            ->map(fn(AIProvider $p) => [
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
                    !$p->is_active => 'inactive',
                    !$p->hasQuotaAvailable() => 'exhausted',
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
                'message' => 'لم يتم تكوين بيانات اعتماد API لهذا المزود',
            ], 400);
        }

        try {
            $apiKey = $provider->api_credentials['api_key'] ?? '';
            $endpoint = $provider->api_credentials['endpoint_url']
                ?? $this->getDefaultEndpoint($provider->driver_key);

            $http = Http::timeout(15)
                ->withHeaders(['Accept' => 'application/json']);

            if ($provider->driver_key === 'zyla') {
                $separator = str_contains($endpoint, '?') ? '&' : '?';
                $response = $http->get($endpoint . $separator . 'apikey=' . urlencode($apiKey));
            } else {
                $response = $http->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                ])->get($endpoint);
            }

            $provider->update(['last_check_at' => now()]);

            return response()->json([
                'success' => $response->successful(),
                'message' => $response->successful()
                    ? 'تم الاتصال بنجاح'
                    : 'فشل الاتصال: ' . $response->reason(),
                'latency' => $response->transferStats?->getTransferTime()
                    ? round($response->transferStats->getTransferTime() * 1000, 0)
                    : null,
            ]);
        } catch (\Throwable $e) {
            $provider->update(['last_check_at' => now()]);

            return response()->json([
                'success' => false,
                'message' => 'خطأ في الاتصال: ' . $e->getMessage(),
                'latency' => null,
            ], 500);
        }
    }

    private function formatProvider(AIProvider $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'name_ar' => $p->name_ar,
            'priority' => $p->priority,
            'driver_key' => $p->driver_key,
            'engine_type' => $p->engine_type,
            'engine_type_label' => EngineType::from($p->engine_type)->label(),
            'is_active' => $p->is_active,
            'quota_limit' => $p->quota_limit,
            'quota_used' => $p->quota_used,
            'quota_percentage' => $p->quota_limit > 0
                ? round(($p->quota_used / $p->quota_limit) * 100, 1)
                : 0,
            'has_quota' => $p->hasQuotaAvailable(),
            'has_credentials' => !empty($p->api_credentials),
            'api_key' => $p->api_credentials['api_key'] ?? null,
            'endpoint' => $p->api_credentials['endpoint_url'] ?? null,
            'model_id' => $p->config['model'] ?? null,
            'last_check_at' => $p->last_check_at,
            'created_at' => $p->created_at,
            'updated_at' => $p->updated_at,
        ];
    }

    private function formatProviderDetail(AIProvider $p): array
    {
        $data = $this->formatProvider($p);
        $data['config'] = $p->config;
        $data['api_key'] = $p->api_credentials['api_key'] ?? null;
        $data['endpoint'] = $p->api_credentials['endpoint_url'] ?? null;
        $data['model_id'] = $p->config['model'] ?? null;
        return $data;
    }

    private function getDefaultEndpoint(string $driverKey): string
    {
        return match ($driverKey) {
            'openai' => 'https://api.openai.com/v1/models',
            'claude' => 'https://api.anthropic.com/v1/messages',
            'gemini' => 'https://generativelanguage.googleapis.com/v1beta/models',
            'huggingface' => 'https://api-inference.huggingface.co/models',
            'zyla' => 'https://zylalabs.com/api/1991/skin+analysis+api',
            'native' => config('app.url') . '/api/health',
            default => config('app.url') . '/api/health',
        };
    }
}
