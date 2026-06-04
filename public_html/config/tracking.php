<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Master Tracking Switch
    |--------------------------------------------------------------------------
    */
    'enabled' => env('TRACKING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Test Mode
    |--------------------------------------------------------------------------
    | When enabled, events are sent with test_event_code for platforms that
    | support it (Meta, TikTok). Use for debugging without affecting data.
    */
    'test_mode' => env('TRACKING_TEST_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Async Mode
    |--------------------------------------------------------------------------
    | When enabled, CAPI events are dispatched via Laravel queue workers.
    | When disabled, events are sent synchronously (blocking).
    */
    'async_mode' => env('TRACKING_ASYNC_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Default Queue
    |--------------------------------------------------------------------------
    | The queue connection used for CAPI events.
    */
    'queue' => env('TRACKING_QUEUE', 'capi-events'),

    /*
    |--------------------------------------------------------------------------
    | Event Deduplication Window (seconds)
    |--------------------------------------------------------------------------
    | Default window within which duplicate events are blocked.
    | Can be overridden per event type in dedup_windows.
    */
    'dedup_window' => env('TRACKING_DEDUP_WINDOW', 300),

    /*
    |--------------------------------------------------------------------------
    | Per-Event Deduplication Windows (seconds)
    |--------------------------------------------------------------------------
    | Longer windows for purchase/lead to prevent double-counting.
    | Shorter windows for view/add_to_cart to allow natural repeats.
    */
    'dedup_windows' => [
        'ViewContent' => 60,
        'AddToCart' => 120,
        'InitiateCheckout' => 300,
        'Purchase' => 86400,
        'Lead' => 86400,
        'Subscribe' => 86400,
        'Search' => 30,
        'Contact' => 86400,
    ],

    /*
    |--------------------------------------------------------------------------
    | CAPI Retry Configuration
    |--------------------------------------------------------------------------
    | Number of retries and base delay for HTTP connection failures.
    */
    'retry' => [
        'max_attempts' => env('TRACKING_RETRY_ATTEMPTS', 3),
        'base_delay_ms' => env('TRACKING_RETRY_DELAY', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout (seconds)
    |--------------------------------------------------------------------------
    | Maximum time to wait for CAPI endpoint response.
    */
    'timeout' => env('TRACKING_HTTP_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    | Log all CAPI events to capi_event_logs table for debugging/analytics.
    */
    'log_events' => env('TRACKING_LOG_EVENTS', true),

    /*
    |--------------------------------------------------------------------------
    | Platform-Specific Settings
    |--------------------------------------------------------------------------
    */
    'platforms' => [

        'facebook' => [
            'api_version' => env('FACEBOOK_API_VERSION', 'v22.0'),
            'enabled' => env('FACEBOOK_CAPI_ENABLED', false),
        ],

        'tiktok' => [
            'api_base' => env('TIKTOK_API_BASE', 'https://analytics.tiktok.com/api/v2/offline/events'),
            'enabled' => env('TIKTOK_CAPI_ENABLED', false),
        ],

        'google' => [
            'enabled' => env('GOOGLE_ADS_ENABLED', false),
            'conversion_id' => env('GOOGLE_CONVERSION_ID'),
            'conversion_label' => env('GOOGLE_CONVERSION_LABEL'),
        ],

        'snapchat' => [
            'enabled' => env('SNAPCHAT_CAPI_ENABLED', false),
        ],

        'pinterest' => [
            'enabled' => env('PINTEREST_CAPI_ENABLED', false),
        ],

        'twitter' => [
            'enabled' => env('TWITTER_CAPI_ENABLED', false),
        ],

        'linkedin' => [
            'enabled' => env('LINKEDIN_CAPI_ENABLED', false),
        ],

        'shopify' => [
            'enabled' => env('SHOPIFY_CONNECTOR_ENABLED', false),
        ],

        'woocommerce' => [
            'enabled' => env('WOOCOMMERCE_CONNECTOR_ENABLED', false),
        ],

        'custom_api' => [
            'enabled' => env('CUSTOM_API_ENABLED', false),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | First-Party Tracking
    |--------------------------------------------------------------------------
    | Configure the first-party tracking domain (CNAME).
    */
    /*
    |--------------------------------------------------------------------------
    | Bot Detection & Safe Page Routing
    |--------------------------------------------------------------------------
    */
    'bot_detection' => [
        'enabled' => env('BOT_DETECTION_ENABLED', true),
        'threshold' => env('BOT_DETECTION_THRESHOLD', 70),
        'known_bot_ips' => explode(',', env('KNOWN_BOT_IPS', '')),
        'safe_page_enabled' => env('SAFE_PAGE_ENABLED', true),
    ],

    'first_party' => [
        'domain' => env('TRACKING_DOMAIN', 'track.jenincare.com'),
        'cookie_name' => env('TRACKING_COOKIE_NAME', '_juuid'),
        'cookie_lifetime_days' => env('TRACKING_COOKIE_LIFETIME', 400),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Compliance & Sanitization
    |--------------------------------------------------------------------------
    */
    'ai' => [
        'enabled' => env('AI_SANITIZATION_ENABLED', true),
        'fallback_chain' => ['openai', 'claude', 'llama'],
        'cache_ttl' => 3600,
        'provider_defaults' => [
            'openai' => ['timeout' => 5],
            'claude' => ['timeout' => 5],
            'llama' => ['timeout' => 10],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Value & Margin Filtering
    |--------------------------------------------------------------------------
    */
    'filtering' => [
        'min_order_value' => env('FILTER_MIN_ORDER_VALUE', 0),
        'min_margin_percent' => env('FILTER_MIN_MARGIN_PERCENT', 0),
        'block_test_emails' => env('FILTER_BLOCK_TEST_EMAILS', true),
        'block_cod_high_cancellation' => env('FILTER_BLOCK_COD_CANCELLATION', true),
        'cod_cancellation_threshold' => env('FILTER_COD_CANCELLATION_THRESHOLD', 0.6),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ad Account Health Scoring
    |--------------------------------------------------------------------------
    */
    /*
    |--------------------------------------------------------------------------
    | Predictive LTV & Value Multiplier
    |--------------------------------------------------------------------------
    */
    'ltv' => [
        'enabled' => env('LTV_ENABLED', true),
        'service_url' => env('LTV_SERVICE_URL', 'http://localhost:8000'),
        'multipliers' => [
            'b2b' => 1.5,
            'b2c' => 1.0,
            'one_time' => 0.8,
        ],
        'platform_multipliers' => [
            'facebook' => ['b2b' => 1.8, 'b2c' => 1.2, 'one_time' => 0.9],
            'tiktok' => ['b2b' => 1.3, 'b2c' => 0.9, 'one_time' => 0.7],
            'google' => ['b2b' => 1.6, 'b2c' => 1.1, 'one_time' => 0.8],
        ],
    ],

    'health' => [
        'rejection_weight' => 30,
        'error_weight' => 25,
        'duplicate_weight' => 15,
        'sanitization_weight' => 10,
        'alert_threshold' => env('HEALTH_ALERT_THRESHOLD', 50),
        'check_interval_minutes' => env('HEALTH_CHECK_INTERVAL', 60),
    ],

];
