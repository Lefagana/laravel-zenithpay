<?php

return [
    'base_url' => env('ZENITHPAY_BASE_URL', 'https://api.zenithpay.com'),
    'merchant_id' => env('ZENITHPAY_MERCHANT_ID'),
    'secret_key' => env('ZENITHPAY_SECRET_KEY'),
    'webhook_secret' => env('ZENITHPAY_WEBHOOK_SECRET'),
];