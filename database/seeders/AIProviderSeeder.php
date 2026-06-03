<?php

namespace Database\Seeders;

use App\Models\AIProvider;
use Illuminate\Database\Seeder;

class AIProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'name' => 'Yimei AI',
                'driver_key' => 'yimei',
                'engine_type' => 'structured',
                'is_active' => false,
                'quota_limit' => 0,
            ],
            [
                'name' => 'OpenAI',
                'driver_key' => 'openai',
                'engine_type' => 'generative',
                'is_active' => false,
                'quota_limit' => 0,
            ],
            [
                'name' => 'Anthropic Claude',
                'driver_key' => 'claude',
                'engine_type' => 'generative',
                'is_active' => false,
                'quota_limit' => 0,
            ],
            [
                'name' => 'Google Gemini',
                'driver_key' => 'gemini',
                'engine_type' => 'hybrid',
                'is_active' => false,
                'quota_limit' => 0,
            ],
            [
                'name' => 'Native Skin AI',
                'driver_key' => 'native',
                'engine_type' => 'structured',
                'is_active' => false,
                'quota_limit' => 0,
            ],
            [
                'name' => 'Haut.AI',
                'driver_key' => 'hautai',
                'engine_type' => 'structured',
                'is_active' => false,
                'quota_limit' => 0,
            ],
            [
                'name' => 'Perfect Corp',
                'driver_key' => 'perfectcorp',
                'engine_type' => 'hybrid',
                'is_active' => false,
                'quota_limit' => 0,
            ],
            [
                'name' => 'Skinive',
                'driver_key' => 'skinive',
                'engine_type' => 'structured',
                'is_active' => false,
                'quota_limit' => 0,
            ],
        ];

        foreach ($providers as $provider) {
            AIProvider::firstOrCreate(
                ['driver_key' => $provider['driver_key']],
                $provider
            );
        }
    }
}
