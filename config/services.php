<?php

return [

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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY', ''),
        'model' => env('OPENAI_MODEL', 'gpt-4o'),
        'timeout' => env('OPENAI_TIMEOUT', 15),
    ],

    'claude' => [
        'api_key' => env('CLAUDE_API_KEY', ''),
        'model' => env('CLAUDE_MODEL', 'claude-3-5-sonnet-20241022'),
        'timeout' => env('CLAUDE_TIMEOUT', 15),
    ],

    'llama' => [
        'base_url' => env('LLAMA_BASE_URL', 'http://localhost:11434'),
        'model' => env('LLAMA_MODEL', 'llama3:8b'),
        'timeout' => env('LLAMA_TIMEOUT', 10),
    ],

    'ltv' => [
        'base_url' => env('LTV_SERVICE_URL', 'http://localhost:8000'),
    ],

    'yimei' => [
        'api_key' => env('YIMEI_API_KEY', ''),
        'api_url' => env('YIMEI_API_URL', 'https://api.yimei.ai/v1'),
        'model' => env('YIMEI_MODEL', 'skin-v3'),
        'timeout' => env('YIMEI_TIMEOUT', 30),
    ],

    'hautai' => [
        'api_key' => env('HAUTAI_API_KEY', ''),
        'api_url' => env('HAUTAI_API_URL', 'https://api.haut.ai/v1'),
        'timeout' => env('HAUTAI_TIMEOUT', 30),
    ],

    'perfectcorp' => [
        'api_key' => env('PERFECTCORP_API_KEY', ''),
        'api_secret' => env('PERFECTCORP_API_SECRET', ''),
        'api_url' => env('PERFECTCORP_API_URL', 'https://api.perfectcorp.com/youcam'),
        'timeout' => env('PERFECTCORP_TIMEOUT', 30),
    ],

    'skinive' => [
        'api_key' => env('SKINIVE_API_KEY', ''),
        'api_url' => env('SKINIVE_API_URL', 'https://api.skinive.com/v1'),
        'timeout' => env('SKINIVE_TIMEOUT', 30),
    ],

    'zyla' => [
        'api_key' => env('ZYLA_API_KEY', ''),
        'api_url' => env('ZYLA_API_URL', 'https://zylalabs.com/api/skin-analyze-advanced'),
        'timeout' => env('ZYLA_TIMEOUT', 60),
    ],

];
