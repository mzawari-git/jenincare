<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ERP Integration Configuration
    |--------------------------------------------------------------------------
    |
    | إعدادات ربط نظام ERP/POS الخارجي مع المتجر الإلكتروني
    | يتيح هذه الإعدادات مزامنة المخزون لحظياً وتبادل البيانات
    |
    */

    'enabled' => env('ERP_ENABLED', false),

    'webhook_url' => env('ERP_WEBHOOK_URL', ''),

    'api_key' => env('ERP_API_KEY', ''),

    'sync_interval' => env('ERP_SYNC_INTERVAL', 60), // ثواني

    'auto_sync_inventory' => env('ERP_AUTO_SYNC', true),

    'sync_fields' => [
        'stock_quantity',
        'b2c_price',
        'b2b_price',
        'barcode',
        'status',
    ],

    'pos_bridge' => [
        'enabled' => env('POS_BRIDGE_ENABLED', true),
        'auto_update_inventory' => true,
        'match_by' => ['barcode', 'sku', 'name'], // ترتيب الأولوية للمطابقة
    ],
];
