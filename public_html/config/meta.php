<?php

return [
    'api_version' => env('META_API_VERSION', 'v22.0'),
    'app_id' => env('META_APP_ID', ''),
    'app_secret' => env('META_APP_SECRET', ''),
    'webhook_verify_token' => env('META_WEBHOOK_VERIFY_TOKEN', 'شركة جنين للتجميل_webhook_2026'),

    'scopes' => [
        'ads_management', 'ads_read', 'business_management',
        'pages_manage_metadata', 'pages_messaging', 'pages_read_engagement',
        'leads_retrieval', 'pages_show_list', 'instagram_basic',
        'instagram_manage_messages', 'instagram_manage_comments',
    ],

    'insights_cache_ttl' => env('META_INSIGHTS_CACHE_TTL', 900),

    'rate_limit' => [
        'max_calls' => 200,
        'period_seconds' => 3600,
    ],
];
