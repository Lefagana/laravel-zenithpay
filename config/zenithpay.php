<?php

return [
    'base_url' => env('ZENITHPAY_BASE_URL', 'https://api.zenithpay.com'),
    'merchant_id' => env('ZENITHPAY_MERCHANT_ID'),
    'secret_key' => env('ZENITHPAY_SECRET_KEY'),
    'webhook_secret' => env('ZENITHPAY_WEBHOOK_SECRET'),

    'timeout' => env('ZENITHPAY_HTTP_TIMEOUT', 30),

    'retry' => [
        'times' => env('ZENITHPAY_HTTP_RETRY_TIMES', 2),
        'sleep' => env('ZENITHPAY_HTTP_RETRY_SLEEP', 500),
    ],

    'webhook' => [
        'secret' => env('ZENITHPAY_WEBHOOK_SECRET'),
        'allowed_ips' => array_values(array_filter(array_map('trim', explode(',', env('ZENITHPAY_WEBHOOK_ALLOWED_IPS', ''))))),
        'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', env('ZENITHPAY_WEBHOOK_TRUSTED_PROXIES', ''))))),
        'signature_ttl' => env('ZENITHPAY_WEBHOOK_SIGNATURE_TTL', 300),
        'handler' => \ZenithPay\Services\DefaultWebhookHandler::class,
        'middleware' => array_values(array_filter(array_map('trim', explode(',', env('ZENITHPAY_WEBHOOK_MIDDLEWARE', 'api'))))),
        'routes' => [
            'enabled' => env('ZENITHPAY_WEBHOOK_ROUTES_ENABLED', true),
            'prefix' => env('ZENITHPAY_WEBHOOK_ROUTE_PREFIX', '/zenithpay/webhooks'),
        ],
    ],
];