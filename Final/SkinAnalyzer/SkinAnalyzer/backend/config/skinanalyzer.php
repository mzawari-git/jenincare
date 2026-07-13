<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | The default AI provider driver key used when none is explicitly specified
    | for a skin analysis request.
    |
    */

    'default_ai_provider' => env('SKIN_ANALYZER_DEFAULT_AI_PROVIDER', 'yimei'),

    /*
    |--------------------------------------------------------------------------
    | Available AI Providers
    |--------------------------------------------------------------------------
    |
    | All supported AI engine providers and their driver keys. Providers must
    | have a corresponding entry in config/services.php.
    |
    */

    'available_providers' => [
        'yimei' => [
            'name' => 'Yimei AI',
            'engine_type' => 'structured',
        ],
        'openai' => [
            'name' => 'OpenAI',
            'engine_type' => 'generative',
        ],
        'claude' => [
            'name' => 'Anthropic Claude',
            'engine_type' => 'generative',
        ],
        'gemini' => [
            'name' => 'Google Gemini',
            'engine_type' => 'hybrid',
        ],
        'native' => [
            'name' => 'Native Skin AI',
            'engine_type' => 'structured',
        ],
        'hautai' => [
            'name' => 'Haut.AI',
            'engine_type' => 'structured',
        ],
        'perfectcorp' => [
            'name' => 'Perfect Corp',
            'engine_type' => 'hybrid',
        ],
        'skinive' => [
            'name' => 'Skinive',
            'engine_type' => 'structured',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Settings
    |--------------------------------------------------------------------------
    */

    'upload' => [
        'max_size' => env('SKIN_ANALYZER_UPLOAD_MAX_SIZE', 10240), // KB
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/bmp'],
        'encrypt_on_disk' => env('SKIN_ANALYZER_ENCRYPT_UPLOADS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | PIN Settings
    |--------------------------------------------------------------------------
    */

    'pin' => [
        'expiry_minutes' => env('SKIN_ANALYZER_PIN_EXPIRY_MINUTES', 43200),
        'max_attempts' => env('SKIN_ANALYZER_PIN_MAX_ATTEMPTS', 5),
        'lockout_minutes' => env('SKIN_ANALYZER_PIN_LOCKOUT_MINUTES', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Settings
    |--------------------------------------------------------------------------
    */

    'report' => [
        'enable_arabic_tips' => env('SKIN_ANALYZER_ENABLE_ARABIC_TIPS', true),
        'enable_product_recommendations' => env('SKIN_ANALYZER_ENABLE_PRODUCT_RECS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | White Label Defaults
    |--------------------------------------------------------------------------
    */

    'white_label' => [
        'app_name' => env('SKIN_ANALYZER_WHITE_LABEL_NAME', 'SkinAnalyzer'),
        'primary_color' => env('SKIN_ANALYZER_PRIMARY_COLOR', '#7C3AED'),
        'logo_url' => env('SKIN_ANALYZER_LOGO_URL'),
        'support_email' => env('SKIN_ANALYZER_SUPPORT_EMAIL', 'support@jenincare.shop'),
        'support_phone' => env('SKIN_ANALYZER_SUPPORT_PHONE'),
        'website_url' => env('SKIN_ANALYZER_WEBSITE_URL', 'https://jenincare.shop'),
    ],

];
