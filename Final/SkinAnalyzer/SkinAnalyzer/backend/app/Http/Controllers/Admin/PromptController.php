<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePromptRequest;
use App\Http\Requests\Admin\UpdatePromptRequest;
use App\Models\SystemPrompt;
use Illuminate\Http\JsonResponse;

class PromptController extends Controller
{
    public function index(): JsonResponse
    {
        $prompts = SystemPrompt::orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $prompts]);
    }

    public function show(int $id): JsonResponse
    {
        $prompt = SystemPrompt::findOrFail($id);

        return response()->json([
            'data' => $prompt,
            'available_variables' => $prompt->getAvailableVariables(),
        ]);
    }

    public function store(StorePromptRequest $request): JsonResponse
    {
        $prompt = SystemPrompt::create($request->validated());

        return response()->json([
            'message' => 'Prompt created successfully.',
            'data' => $prompt,
            'available_variables' => $prompt->getAvailableVariables(),
        ], 201);
    }

    public function update(int $id, UpdatePromptRequest $request): JsonResponse
    {
        $prompt = SystemPrompt::findOrFail($id);

        $prompt->update($request->validated());

        return response()->json([
            'message' => 'Prompt updated successfully.',
            'data' => $prompt->fresh(),
            'available_variables' => $prompt->getAvailableVariables(),
        ]);
    }

    public function variables(): JsonResponse
    {
        $variables = [
            '{skin_type}' => 'نوع البشرة (دهنية، جافة، مختلطة، حساسة، عادية) / Skin type (oily, dry, combination, sensitive, normal)',
            '{skin_concerns}' => 'مشاكل البشرة المكتشفة / Detected skin concerns',
            '{defects}' => 'عيوب البشرة (بقع، تجاعيد، حب شباب) / Skin defects (spots, wrinkles, acne)',
            '{product_names}' => 'أسماء المنتجات الموصى بها / Recommended product names',
            '{product_recommendations}' => 'توصيات المنتجات الكاملة / Full product recommendations',
            '{health_score}' => 'درجة صحة البشرة (0-100) / Skin health score (0-100)',
            '{radar_metrics}' => 'مقاييس الرادار (نضارة، نسيج، ترطيب، إلخ) / Radar metrics (brightness, texture, hydration, etc.)',
            '{user_name}' => 'اسم المستخدم / User name',
            '{app_name}' => 'اسم التطبيق / App name (from white-label settings)',
            '{date}' => 'تاريخ التحليل / Analysis date',
            '{arabic_greeting}' => 'تحية عربية مناسبة / Appropriate Arabic greeting',
            '{closing_message}' => 'رسالة ختامية / Closing message',
        ];

        return response()->json(['data' => $variables]);
    }
}
