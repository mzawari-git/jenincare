<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third-Party Services
    |--------------------------------------------------------------------------
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Yimei AI — Structured Skin Analysis
    |--------------------------------------------------------------------------
    */

    'yimei' => [
        'api_key' => env('YIMEI_API_KEY'),
        'api_url' => env('YIMEI_API_URL', 'https://api.yimei.ai/v1'),
        'model' => env('YIMEI_MODEL', 'skin-analysis-v3'),
        'timeout' => env('YIMEI_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI — GPT-4 Vision / Generative Analysis
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'api_url' => env('OPENAI_API_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_MODEL', 'gpt-4-vision-preview'),
        'timeout' => env('OPENAI_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Anthropic Claude — Generative Analysis
    |--------------------------------------------------------------------------
    */

    'claude' => [
        'api_key' => env('CLAUDE_API_KEY'),
        'api_url' => env('CLAUDE_API_URL', 'https://api.anthropic.com/v1'),
        'model' => env('CLAUDE_MODEL', 'claude-3-opus-20240229'),
        'timeout' => env('CLAUDE_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Gemini — Hybrid Analysis
    |--------------------------------------------------------------------------
    */

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'api_url' => env('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'model' => env('GEMINI_MODEL', 'gemini-pro-vision'),
        'timeout' => env('GEMINI_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Haut.AI — Structured Skin Analysis
    |--------------------------------------------------------------------------
    */

    'hautai' => [
        'api_key' => env('HAUTAI_API_KEY'),
        'api_url' => env('HAUTAI_API_URL', 'https://api.haut.ai/v1'),
        'model' => env('HAUTAI_MODEL', 'skin-analysis'),
        'timeout' => env('HAUTAI_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Perfect Corp — Hybrid Skin Analysis
    |--------------------------------------------------------------------------
    */

    'perfectcorp' => [
        'api_key' => env('PERFECTCORP_API_KEY'),
        'api_secret' => env('PERFECTCORP_API_SECRET'),
        'api_url' => env('PERFECTCORP_API_URL', 'https://api.perfectcorp.com/v2'),
        'model' => env('PERFECTCORP_MODEL', 'ai-skin-diagnosis'),
        'timeout' => env('PERFECTCORP_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Skinive — Structured Skin Analysis
    |--------------------------------------------------------------------------
    */

    'skinive' => [
        'api_key' => env('SKINIVE_API_KEY'),
        'api_url' => env('SKINIVE_API_URL', 'https://api.skinive.com/v1'),
        'model' => env('SKINIVE_MODEL', 'skin-assessment'),
        'timeout' => env('SKINIVE_TIMEOUT', 30),
    ],

];
