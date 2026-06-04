<?php

return [
    /*
    |--------------------------------------------------------------------------
    | IP Blocking
    |--------------------------------------------------------------------------
    */
    'blocked_ips' => env('SECURITY_BLOCKED_IPS') ? explode(',', env('SECURITY_BLOCKED_IPS')) : [],

    'blocked_ranges' => [
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit_max_attempts' => env('SECURITY_RATE_LIMIT', 60),
    'rate_limit_decay_minutes' => env('SECURITY_RATE_LIMIT_DECAY', 1),

    /*
    |--------------------------------------------------------------------------
    | Throttle
    |--------------------------------------------------------------------------
    */
    'throttle_login_attempts' => env('SECURITY_LOGIN_THROTTLE', 5),
    'throttle_login_decay' => env('SECURITY_LOGIN_DECAY', 15),

    /*
    |--------------------------------------------------------------------------
    | Headers
    |--------------------------------------------------------------------------
    */
    'x_frame_options' => env('SECURITY_X_FRAME', 'SAMEORIGIN'),
    'x_content_type_options' => 'nosniff',
    'referrer_policy' => 'strict-origin-when-cross-origin',
];
