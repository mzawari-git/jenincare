<?php

return [
    'api_version' => env('SHOPIFY_API_VERSION', '2024-10'),
    'webhook_path' => '/api/webhooks/shopify/{topic}',
    'webhook_topics' => [
        'orders/create',
        'orders/updated',
        'orders/paid',
        'orders/fulfilled',
        'orders/cancelled',
        'carts/create',
        'carts/update',
        'checkouts/create',
        'checkouts/update',
        'app/uninstalled',
        'shop/update',
    ],
];
