<?php

return [
    'api_version' => 'wc/v3',
    'webhook_path' => '/api/webhooks/woocommerce',
    'webhook_topics' => [
        'order.created',
        'order.updated',
        'order.deleted',
        'order.status_changed',
        'product.created',
        'product.updated',
        'customer.created',
    ],
    'timeout' => env('WOOCOMMERCE_API_TIMEOUT', 30),
    'retry_attempts' => env('WOOCOMMERCE_RETRY_ATTEMPTS', 2),
];
