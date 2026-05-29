<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OAuth Redirect URI Base
    |--------------------------------------------------------------------------
    | All platforms redirect to: {APP_URL}/admin/oauth/{platform}/callback
    */
    'redirect_uri_base' => env('OAUTH_REDIRECT_BASE', '/admin/oauth'),

    /*
    |--------------------------------------------------------------------------
    | Facebook / Meta
    |--------------------------------------------------------------------------
    */
    'meta' => [
        'name' => 'Facebook / Meta',
        'icon' => 'fab fa-facebook',
        'color' => '#1877F2',
        'client_id' => env('META_APP_ID', ''),
        'client_secret' => env('META_APP_SECRET', ''),
        'redirect' => '/admin/oauth/meta/callback',
        'scopes' => ['ads_management', 'ads_read'],
        'auth_url' => 'https://www.facebook.com/v22.0/dialog/oauth',
        'token_url' => 'https://graph.facebook.com/v22.0/oauth/access_token',
        'refresh_url' => 'https://graph.facebook.com/v22.0/oauth/access_token',
        'refresh_grant' => 'fb_exchange_token',
        'account_model' => \App\Models\Meta\MetaAdAccount::class,
        'service' => \App\Services\Meta\FacebookGraphService::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | TikTok
    |--------------------------------------------------------------------------
    */
    'tiktok' => [
        'name' => 'TikTok',
        'icon' => 'fab fa-tiktok',
        'color' => '#000000',
        'client_id' => env('TIKTOK_APP_ID', ''),
        'client_secret' => env('TIKTOK_APP_SECRET', ''),
        'redirect' => '/admin/oauth/tiktok/callback',
        'scopes' => ['user.info.basic', 'ad.management'],
        'auth_url' => 'https://business-api.tiktok.com/open_api/v1.3/oauth/authorize',
        'token_url' => 'https://business-api.tiktok.com/open_api/v1.3/oauth/access_token',
        'account_service' => \App\Services\AdvertisingTrackingService::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Ads
    |--------------------------------------------------------------------------
    */
    'google' => [
        'name' => 'Google Ads',
        'icon' => 'fab fa-google',
        'color' => '#4285F4',
        'client_id' => env('GOOGLE_CLIENT_ID', ''),
        'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
        'redirect' => '/admin/oauth/google/callback',
        'scopes' => ['https://www.googleapis.com/auth/adwords'],
        'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'account_service' => \App\Services\GoogleAdsService::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Snapchat
    |--------------------------------------------------------------------------
    */
    'snapchat' => [
        'name' => 'Snapchat',
        'icon' => 'fab fa-snapchat',
        'color' => '#FFFC00',
        'client_id' => env('SNAPCHAT_CLIENT_ID', ''),
        'client_secret' => env('SNAPCHAT_CLIENT_SECRET', ''),
        'redirect' => '/admin/oauth/snapchat/callback',
        'scopes' => ['snapchat-marketing-api'],
        'auth_url' => 'https://accounts.snapchat.com/login/oauth2/authorize',
        'token_url' => 'https://accounts.snapchat.com/login/oauth2/access_token',
        'account_service' => \App\Services\SnapchatService::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pinterest
    |--------------------------------------------------------------------------
    */
    'pinterest' => [
        'name' => 'Pinterest',
        'icon' => 'fab fa-pinterest',
        'color' => '#E60023',
        'client_id' => env('PINTEREST_APP_ID', ''),
        'client_secret' => env('PINTEREST_APP_SECRET', ''),
        'redirect' => '/admin/oauth/pinterest/callback',
        'scopes' => ['ads:read', 'ads:write', 'user_accounts:read'],
        'auth_url' => 'https://www.pinterest.com/oauth',
        'token_url' => 'https://api.pinterest.com/v5/oauth/token',
        'account_service' => \App\Services\PinterestService::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Twitter / X
    |--------------------------------------------------------------------------
    */
    'twitter' => [
        'name' => 'X (Twitter)',
        'icon' => 'fa-brands fa-x-twitter',
        'color' => '#1DA1F2',
        'client_id' => env('TWITTER_CLIENT_ID', ''),
        'client_secret' => env('TWITTER_CLIENT_SECRET', ''),
        'redirect' => '/admin/oauth/twitter/callback',
        'scopes' => ['tweet.read', 'users.read', 'offline.access', 'ads:read', 'ads:write'],
        'auth_url' => 'https://twitter.com/i/oauth2/authorize',
        'token_url' => 'https://api.twitter.com/2/oauth2/token',
        'account_service' => \App\Services\TwitterService::class,
        'use_pkce' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | LinkedIn
    |--------------------------------------------------------------------------
    */
    'linkedin' => [
        'name' => 'LinkedIn',
        'icon' => 'fab fa-linkedin',
        'color' => '#0A66C2',
        'client_id' => env('LINKEDIN_CLIENT_ID', ''),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET', ''),
        'redirect' => '/admin/oauth/linkedin/callback',
        'scopes' => ['r_ads', 'r_ads_reporting', 'rw_ads', 'r_basicprofile'],
        'auth_url' => 'https://www.linkedin.com/oauth/v2/authorization',
        'token_url' => 'https://www.linkedin.com/oauth/v2/accessToken',
        'account_service' => \App\Services\LinkedInService::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Shopify (OAuth access via shop install)
    |--------------------------------------------------------------------------
    */
    'shopify' => [
        'name' => 'Shopify',
        'icon' => 'fab fa-shopify',
        'color' => '#96BF48',
        'client_id' => env('SHOPIFY_API_KEY', ''),
        'client_secret' => env('SHOPIFY_API_SECRET', ''),
        'redirect' => '/admin/oauth/shopify/callback',
        'scopes' => ['read_orders', 'read_products', 'read_customers'],
        'install_mode' => true,
        'account_service' => \App\Services\ShopifyService::class,
    ],
];
