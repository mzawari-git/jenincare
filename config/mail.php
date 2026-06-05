<?php

return [

    'default' => env('MAIL_MAILER', 'log'),

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.hostinger.com'),
            'port' => env('MAIL_PORT', 465),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', 'jenincare.shop'),
        ],

        'ses' => ['transport' => 'ses'],
        'mailgun' => ['transport' => 'mailgun'],
        'postmark' => ['transport' => 'postmark'],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL', 'stack'),
        ],

        'array' => ['transport' => 'array'],

        'failover' => [
            'transport' => 'failover',
            'mailers' => ['smtp', 'log'],
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@jenincare.shop'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Jenin Care')),
    ],

    'markdown' => [
        'theme' => 'default',
        'paths' => [resource_path('views/vendor/mail')],
    ],

];
