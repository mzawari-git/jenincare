<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AIProvider;
use App\Enums\EngineType;

class AIProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'name' => 'Native Skin AI',
                'driver_key' => 'native',
                'engine_type' => EngineType::STRUCTURED->value,
                'api_credentials' => [
                    'model' => 'built-in-v1',
                    'mode' => 'offline',
                    'version' => '2.4.0',
                ],
                'is_active' => true,
                'quota_limit' => 0,
                'quota_used' => 0,
                'config' => [
                    'description' => 'المحرك المدمج | يعمل بدون إنترنت | تحليل موضعي',
                    'description_en' => 'Built-in engine | Works offline | Local analysis',
                    'priority' => 1,
                    'supports_offline' => true,
                    'supports_heatmap' => true,
                    'supports_radar' => true,
                    'supports_arabic' => true,
                    'response_time_ms' => 500,
                    'max_image_size_mb' => 10,
                ],
            ],
            [
                'name' => 'Yimei AI',
                'driver_key' => 'yimei',
                'engine_type' => EngineType::STRUCTURED->value,
                'api_credentials' => [
                    'api_url' => 'https://api.yimei.ai/v1',
                    'model' => 'skin-analysis-v3',
                    'timeout' => 30,
                ],
                'is_active' => false,
                'quota_limit' => 1000,
                'quota_used' => 0,
                'config' => [
                    'description' => 'Yimei AI | تحليل منظم سحابي | دقة عالية',
                    'description_en' => 'Yimei AI | Cloud structured analysis | High accuracy',
                    'priority' => 2,
                    'supports_offline' => false,
                    'supports_heatmap' => true,
                    'supports_radar' => true,
                    'supports_arabic' => false,
                    'response_time_ms' => 2000,
                    'max_image_size_mb' => 5,
                    'regions' => ['global', 'asia'],
                ],
            ],
            [
                'name' => 'OpenAI',
                'driver_key' => 'openai',
                'engine_type' => EngineType::GENERATIVE->value,
                'api_credentials' => [
                    'api_url' => 'https://api.openai.com/v1',
                    'model' => 'gpt-4-vision-preview',
                    'timeout' => 60,
                    'max_tokens' => 2048,
                ],
                'is_active' => false,
                'quota_limit' => 500,
                'quota_used' => 0,
                'config' => [
                    'description' => 'ChatGPT-4 Vision | تقارير تفاعلية | تحليل شامل بالعربية',
                    'description_en' => 'ChatGPT-4 Vision | Interactive reports | Arabic analysis',
                    'priority' => 3,
                    'supports_offline' => false,
                    'supports_heatmap' => false,
                    'supports_radar' => false,
                    'supports_arabic' => true,
                    'response_time_ms' => 8000,
                    'max_image_size_mb' => 20,
                    'requires_structured_output' => true,
                ],
            ],
            [
                'name' => 'Anthropic Claude',
                'driver_key' => 'claude',
                'engine_type' => EngineType::GENERATIVE->value,
                'api_credentials' => [
                    'api_url' => 'https://api.anthropic.com/v1',
                    'model' => 'claude-3-opus-20240229',
                    'timeout' => 60,
                    'max_tokens' => 2048,
                ],
                'is_active' => false,
                'quota_limit' => 500,
                'quota_used' => 0,
                'config' => [
                    'description' => 'Claude 3 Opus | تحليل طبي دقيق | تقارير مفصلة',
                    'description_en' => 'Claude 3 Opus | Precise medical analysis | Detailed reports',
                    'priority' => 4,
                    'supports_offline' => false,
                    'supports_heatmap' => false,
                    'supports_radar' => false,
                    'supports_arabic' => true,
                    'response_time_ms' => 6000,
                    'max_image_size_mb' => 10,
                    'requires_structured_output' => true,
                ],
            ],
            [
                'name' => 'Google Gemini',
                'driver_key' => 'gemini',
                'engine_type' => EngineType::HYBRID->value,
                'api_credentials' => [
                    'api_url' => 'https://generativelanguage.googleapis.com/v1beta',
                    'model' => 'gemini-pro-vision',
                    'timeout' => 60,
                ],
                'is_active' => false,
                'quota_limit' => 500,
                'quota_used' => 0,
                'config' => [
                    'description' => 'Gemini Pro Vision | تحليل هجين | رؤية حاسوبية متقدمة',
                    'description_en' => 'Gemini Pro Vision | Hybrid analysis | Advanced CV',
                    'priority' => 5,
                    'supports_offline' => false,
                    'supports_heatmap' => true,
                    'supports_radar' => true,
                    'supports_arabic' => true,
                    'response_time_ms' => 4000,
                    'max_image_size_mb' => 10,
                ],
            ],
            [
                'name' => 'Haut.AI',
                'driver_key' => 'hautai',
                'engine_type' => EngineType::STRUCTURED->value,
                'api_credentials' => [
                    'api_url' => 'https://api.haut.ai/v1',
                    'model' => 'skin-analysis',
                    'timeout' => 30,
                ],
                'is_active' => false,
                'quota_limit' => 500,
                'quota_used' => 0,
                'config' => [
                    'description' => 'Haut.AI | تحليل بنية البشرة | قياسات دقيقة',
                    'description_en' => 'Haut.AI | Skin structure analysis | Precise metrics',
                    'priority' => 6,
                    'supports_offline' => false,
                    'supports_heatmap' => true,
                    'supports_radar' => true,
                    'supports_arabic' => false,
                    'response_time_ms' => 3000,
                    'max_image_size_mb' => 8,
                ],
            ],
            [
                'name' => 'Perfect Corp',
                'driver_key' => 'perfectcorp',
                'engine_type' => EngineType::HYBRID->value,
                'api_credentials' => [
                    'api_url' => 'https://api.perfectcorp.com/v2',
                    'model' => 'ai-skin-diagnosis',
                    'timeout' => 30,
                ],
                'is_active' => false,
                'quota_limit' => 500,
                'quota_used' => 0,
                'config' => [
                    'description' => 'PerfectCorp | تشخيص شامل | 14 بُعد تحليلي',
                    'description_en' => 'PerfectCorp | Comprehensive diagnosis | 14 analysis dimensions',
                    'priority' => 7,
                    'supports_offline' => false,
                    'supports_heatmap' => true,
                    'supports_radar' => true,
                    'supports_arabic' => false,
                    'response_time_ms' => 2500,
                    'max_image_size_mb' => 5,
                ],
            ],
            [
                'name' => 'Skinive',
                'driver_key' => 'skinive',
                'engine_type' => EngineType::STRUCTURED->value,
                'api_credentials' => [
                    'api_url' => 'https://api.skinive.com/v1',
                    'model' => 'skin-assessment',
                    'timeout' => 30,
                ],
                'is_active' => false,
                'quota_limit' => 500,
                'quota_used' => 0,
                'config' => [
                    'description' => 'Skinive | تقييم مخاطر البشرة | كشف مبكر',
                    'description_en' => 'Skinive | Skin risk assessment | Early detection',
                    'priority' => 8,
                    'supports_offline' => false,
                    'supports_heatmap' => true,
                    'supports_radar' => false,
                    'supports_arabic' => false,
                    'response_time_ms' => 3500,
                    'max_image_size_mb' => 5,
                    'risk_assessment' => true,
                ],
            ],
        ];

        foreach ($providers as $data) {
            AIProvider::updateOrCreate(
                ['driver_key' => $data['driver_key']],
                $data
            );
        }

        $this->command?->table(
            ['Name', 'Driver Key', 'Type', 'Active', 'Quota'],
            collect($providers)->map(fn ($p) => [
                $p['name'],
                $p['driver_key'],
                $p['engine_type'],
                $p['is_active'] ? '✅' : '❌',
                $p['quota_limit'] > 0 ? "{$p['quota_used']}/{$p['quota_limit']}" : '∞',
            ])->toArray()
        );

        $this->command?->info('8 AI providers seeded successfully.');
    }
}
