<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\AIProvider;
use App\Models\SystemPrompt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromptController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $prompts = SystemPrompt::withTrashed()->orderBy('name')->get()->map(function ($prompt) {
            return $this->formatPrompt($prompt);
        });

        return response()->json(['prompts' => $prompts, 'data' => $prompts]);
    }

    public function show($id): JsonResponse
    {
        $prompt = SystemPrompt::withTrashed()->findOrFail($id);
        return response()->json(['prompt' => $this->formatPrompt($prompt)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'prompt_text' => 'required|string',
        ]);

        $prompt = SystemPrompt::create([
            'name' => $data['name'],
            'provider_key' => '',
            'tone' => 'balanced',
            'system_instruction' => $data['prompt_text'],
            'is_active' => true,
            'version' => 1,
        ]);

        return response()->json(['prompt' => $this->formatPrompt($prompt)]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $prompt = SystemPrompt::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'provider_id' => 'nullable|string',
            'tone' => 'nullable|string|in:balanced,medical,promotional',
            'prompt_text' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($data['name'])) {
            $prompt->name = $data['name'];
        }
        if (isset($data['provider_id'])) {
            $prompt->provider_key = $data['provider_id'] ?: null;
        }
        if (isset($data['tone'])) {
            $prompt->tone = $data['tone'];
        }
        if (isset($data['prompt_text'])) {
            if ($prompt->system_instruction !== $data['prompt_text']) {
                $prompt->version = ($prompt->version ?? 0) + 1;
            }
            $prompt->system_instruction = $data['prompt_text'];
        }
        if (isset($data['is_active'])) {
            $prompt->is_active = $data['is_active'];
        }

        $prompt->save();

        return response()->json(['prompt' => $this->formatPrompt($prompt)]);
    }

    public function destroy($id): JsonResponse
    {
        $prompt = SystemPrompt::findOrFail($id);
        $prompt->delete();

        return response()->json(['message' => 'تم حذف التعليمة']);
    }

    public function history($id): JsonResponse
    {
        $prompt = SystemPrompt::withTrashed()->findOrFail($id);
        return response()->json([
            'versions' => [$this->formatPrompt($prompt)],
            'history' => [$this->formatPrompt($prompt)],
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $request->validate(['prompt_text' => 'required|string']);

        $text = $request->prompt_text;
        $text = str_replace(
            ['{skin_type}', '{defects}', '{product_names}', '{customer_name}', '{center_name}', '{score}', '{recommendations}'],
            ['دهنية', 'حب شباب، مسام واسعة', 'غسول نيفيا، كريم بانثينول', 'أحمد', 'مركز العناية', '78%', 'استخدام غسول يومي + كريم واقي شمس'],
            $text
        );

        return response()->json(['preview' => $text]);
    }

    private function formatPrompt(SystemPrompt $prompt): array
    {
        $providerName = 'كل المزودين';
        if (!empty($prompt->provider_key)) {
            $provider = AIProvider::where('driver_key', $prompt->provider_key)->orWhere('id', $prompt->provider_key)->first();
            if ($provider) {
                $providerName = $provider->name;
            }
        }

        return [
            'id' => $prompt->id,
            'name' => $prompt->name,
            'provider_id' => $prompt->provider_key ?: '',
            'provider_name' => $providerName,
            'tone' => $prompt->tone ?? 'balanced',
            'prompt_text' => $prompt->system_instruction,
            'is_active' => (bool) $prompt->is_active,
            'version' => $prompt->version ?? 1,
            'created_at' => $prompt->created_at,
            'updated_at' => $prompt->updated_at,
        ];
    }
}
