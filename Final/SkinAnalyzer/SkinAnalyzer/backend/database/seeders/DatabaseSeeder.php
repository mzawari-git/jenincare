<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AIProvider;
use App\Models\Product;
use App\Models\WhiteLabelSetting;
use App\Enums\EngineType;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->createDefaultAdmin();
        $this->seedAIProviders();
        $this->seedProducts();
        $this->createDefaultWhiteLabel();
    }

    private function createDefaultAdmin(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@jenincare.shop'],
            [
                'name' => 'Jenin Care Admin',
                'password' => Hash::make('admin123!@#'),
                'is_admin' => true,
                'phone' => null,
                'email_verified_at' => now(),
            ]
        );

        $this->command?->info('Default admin user created: admin@jenincare.shop');
    }

    private function seedAIProviders(): void
    {
        $providers = [
            [
                'name' => 'Native Skin AI',
                'driver_key' => 'native',
                'engine_type' => EngineType::STRUCTURED->value,
                'api_credentials' => json_encode(['model' => 'built-in-v1', 'mode' => 'offline']),
                'is_active' => true,
                'quota_limit' => 0,
                'quota_used' => 0,
                'config' => json_encode(['description' => 'محرك التحليل المدمج — يعمل بدون إنترنت', 'priority' => 1]),
            ],
            [
                'name' => 'Yimei AI',
                'driver_key' => 'yimei',
                'engine_type' => EngineType::STRUCTURED->value,
                'api_credentials' => json_encode([]),
                'is_active' => false,
                'quota_limit' => 1000,
                'quota_used' => 0,
                'config' => json_encode(['description' => 'محرك تحليل البشرة السحابي — Yimei AI', 'priority' => 2]),
            ],
            [
                'name' => 'OpenAI',
                'driver_key' => 'openai',
                'engine_type' => EngineType::GENERATIVE->value,
                'api_credentials' => json_encode([]),
                'is_active' => false,
                'quota_limit' => 500,
                'quota_used' => 0,
                'config' => json_encode(['description' => 'ChatGPT-4 Vision — تقارير تفاعلية بالعربية', 'priority' => 3]),
            ],
            [
                'name' => 'Anthropic Claude',
                'driver_key' => 'claude',
                'engine_type' => EngineType::GENERATIVE->value,
                'api_credentials' => json_encode([]),
                'is_active' => false,
                'quota_limit' => 500,
                'quota_used' => 0,
                'config' => json_encode(['description' => 'Claude 3 Opus — تحليل طبي دقيق', 'priority' => 4]),
            ],
            [
                'name' => 'Google Gemini',
                'driver_key' => 'gemini',
                'engine_type' => EngineType::HYBRID->value,
                'api_credentials' => json_encode([]),
                'is_active' => false,
                'quota_limit' => 500,
                'quota_used' => 0,
                'config' => json_encode(['description' => 'Gemini Pro Vision — تحليل هجين شامل', 'priority' => 5]),
            ],
            [
                'name' => 'Haut.AI',
                'driver_key' => 'hautai',
                'engine_type' => EngineType::STRUCTURED->value,
                'api_credentials' => json_encode([]),
                'is_active' => false,
                'quota_limit' => 500,
                'quota_used' => 0,
                'config' => json_encode(['description' => 'Haut.AI — تحليل بنية البشرة', 'priority' => 6]),
            ],
            [
                'name' => 'Perfect Corp',
                'driver_key' => 'perfectcorp',
                'engine_type' => EngineType::HYBRID->value,
                'api_credentials' => json_encode([]),
                'is_active' => false,
                'quota_limit' => 500,
                'quota_used' => 0,
                'config' => json_encode(['description' => 'PerfectCorp — تشخيص شامل للبشرة', 'priority' => 7]),
            ],
            [
                'name' => 'Skinive',
                'driver_key' => 'skinive',
                'engine_type' => EngineType::STRUCTURED->value,
                'api_credentials' => json_encode([]),
                'is_active' => false,
                'quota_limit' => 500,
                'quota_used' => 0,
                'config' => json_encode(['description' => 'Skinive — تقييم مخاطر البشرة', 'priority' => 8]),
            ],
        ];

        foreach ($providers as $provider) {
            AIProvider::updateOrCreate(
                ['driver_key' => $provider['driver_key']],
                $provider
            );
        }

        $this->command?->info('8 AI providers seeded (Native active by default)');
    }

    private function seedProducts(): void
    {
        $products = [
            [
                'name' => 'Purifying Facial Cleanser',
                'name_ar' => 'غسول الوجه المنقي',
                'description' => 'Deep cleansing gel for oily and acne-prone skin. Removes excess oil and impurities.',
                'description_ar' => 'جل تنظيف عميق للبشرة الدهنية والمعرضة لحب الشباب. يزيل الزيوت الزائدة والشوائب.',
                'price' => 75.00,
                'image_path' => 'products/cleanser.jpg',
                'stock' => 50,
                'category' => 'cleanser',
                'is_active' => true,
            ],
            [
                'name' => 'Hydrating Moisturizer SPF 30',
                'name_ar' => 'مرطب يومي مع حماية SPF 30',
                'description' => 'Lightweight daily moisturizer with sun protection. Suitable for all skin types.',
                'description_ar' => 'مرطب يومي خفيف مع حماية من الشمس. مناسب لجميع أنواع البشرة.',
                'price' => 120.00,
                'image_path' => 'products/moisturizer_spf.jpg',
                'stock' => 35,
                'category' => 'moisturizer',
                'is_active' => true,
            ],
            [
                'name' => 'Vitamin C Brightening Serum',
                'name_ar' => 'سيروم فيتامين C المفتح',
                'description' => 'High-potency vitamin C serum to reduce pigmentation and even skin tone.',
                'description_ar' => 'سيروم فيتامين C عالي الفعالية لتقليل التصبغات وتوحيد لون البشرة.',
                'price' => 180.00,
                'image_path' => 'products/vitamin_c_serum.jpg',
                'stock' => 25,
                'category' => 'serum',
                'is_active' => true,
            ],
            [
                'name' => 'Retinol Night Cream',
                'name_ar' => 'كريم الريتينول الليلي',
                'description' => 'Anti-aging night cream with retinol to reduce fine lines and improve skin texture.',
                'description_ar' => 'كريم ليلي مضاد للشيخوخة بالريتينول لتقليل الخطوط الدقيقة وتحسين ملمس البشرة.',
                'price' => 220.00,
                'image_path' => 'products/retinol_cream.jpg',
                'stock' => 15,
                'category' => 'treatment',
                'is_active' => true,
            ],
            [
                'name' => 'Pore Minimizing Toner',
                'name_ar' => 'تونر تضييق المسام',
                'description' => 'Alcohol-free toner that tightens pores and balances skin pH after cleansing.',
                'description_ar' => 'تونر خالٍ من الكحول يضيق المسام ويوازن درجة حموضة البشرة بعد التنظيف.',
                'price' => 95.00,
                'image_path' => 'products/toner.jpg',
                'stock' => 40,
                'category' => 'toner',
                'is_active' => true,
            ],
            [
                'name' => 'Acne Spot Treatment Gel',
                'name_ar' => 'جل علاج حب الشباب الموضعي',
                'description' => 'Fast-acting gel with salicylic acid to target and heal acne breakouts.',
                'description_ar' => 'جل سريع المفعول بحمض الساليسيليك لاستهداف وعلاج حب الشباب.',
                'price' => 85.00,
                'image_path' => 'products/acne_gel.jpg',
                'stock' => 30,
                'category' => 'treatment',
                'is_active' => true,
            ],
            [
                'name' => 'Hyaluronic Acid Hydration Mask',
                'name_ar' => 'قناع حمض الهيالورونيك المرطب',
                'description' => 'Intensive hydration sheet mask with hyaluronic acid for dry and dehydrated skin.',
                'description_ar' => 'قناع ترطيب مكثف بحمض الهيالورونيك للبشرة الجافة والمجففة.',
                'price' => 45.00,
                'image_path' => 'products/hydration_mask.jpg',
                'stock' => 60,
                'category' => 'mask',
                'is_active' => true,
            ],
            [
                'name' => 'Gentle Exfoliating Scrub',
                'name_ar' => 'مقشر لطيف للوجه',
                'description' => 'Micro-bead exfoliator that removes dead skin cells without irritation.',
                'description_ar' => 'مقشر بحبيبات دقيقة يزيل خلايا الجلد الميتة دون تهيج.',
                'price' => 110.00,
                'image_path' => 'products/exfoliator.jpg',
                'stock' => 20,
                'category' => 'exfoliator',
                'is_active' => true,
            ],
            [
                'name' => 'Eye Contour Cream',
                'name_ar' => 'كريم محيط العين',
                'description' => 'Targeted treatment for dark circles, puffiness, and fine lines around eyes.',
                'description_ar' => 'علاج موجه للهالات السوداء والانتفاخ والخطوط الدقيقة حول العينين.',
                'price' => 160.00,
                'image_path' => 'products/eye_cream.jpg',
                'stock' => 18,
                'category' => 'eye_care',
                'is_active' => true,
            ],
            [
                'name' => 'Skin Barrier Repair Cream',
                'name_ar' => 'كريم إصلاح حاجز البشرة',
                'description' => 'Ceramide-rich cream to strengthen and repair compromised skin barrier.',
                'description_ar' => 'كريم غني بالسيراميد لتقوية وإصلاح حاجز البشرة المتضرر.',
                'price' => 200.00,
                'image_path' => 'products/barrier_cream.jpg',
                'stock' => 12,
                'category' => 'treatment',
                'is_active' => true,
            ],
            [
                'name' => 'Oil-Free Sunscreen SPF 50',
                'name_ar' => 'واقي شمس خالٍ من الزيوت SPF 50',
                'description' => 'Matte-finish sunscreen ideal for oily and combination skin. No white cast.',
                'description_ar' => 'واقي شمس بملمس مطفي مثالي للبشرة الدهنية والمختلطة. لا يترك أثراً أبيض.',
                'price' => 140.00,
                'image_path' => 'products/sunscreen.jpg',
                'stock' => 28,
                'category' => 'sunscreen',
                'is_active' => true,
            ],
            [
                'name' => 'Collagen Booster Supplement',
                'name_ar' => 'مكمل الكولاجين المعزز',
                'description' => 'Oral supplement with collagen peptides, biotin, and antioxidants for skin health.',
                'description_ar' => 'مكمل غذائي ببتيدات الكولاجين والبيوتين ومضادات الأكسدة لصحة البشرة.',
                'price' => 250.00,
                'image_path' => 'products/collagen_supplement.jpg',
                'stock' => 22,
                'category' => 'supplement',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['name' => $product['name']],
                $product
            );
        }

        $this->command?->info('12 sample products seeded');
    }

    private function createDefaultWhiteLabel(): void
    {
        WhiteLabelSetting::updateOrCreate(
            ['id' => 1],
            [
                'app_name' => 'Jenin Care',
                'primary_color' => '#7C3AED',
                'secondary_color' => '#10B981',
                'logo_url' => null,
                'favicon_url' => null,
                'support_email' => 'support@jenincare.shop',
                'support_phone' => null,
                'website_url' => 'https://jenincare.shop',
                'privacy_policy_url' => 'https://jenincare.shop/privacy',
                'terms_url' => 'https://jenincare.shop/terms',
                'app_store_url' => null,
                'google_play_url' => null,
                'social_facebook' => null,
                'social_instagram' => null,
                'social_twitter' => null,
                'welcome_message_ar' => 'مرحباً بك في جنين كير — منصة تحليل البشرة الذكية',
                'welcome_message_en' => 'Welcome to Jenin Care — Smart Skin Analysis Platform',
                'is_customized' => false,
            ]
        );

        $this->command?->info('Default white-label configuration created');
    }
}
