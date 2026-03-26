<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use ZenithPay\Http\Controllers\ZenithPayWebhookController;

$prefix = (string) config('zenithpay.webhook.routes.prefix', '/zenithpay/webhooks');
$middleware = config('zenithpay.webhook.middleware', ['api']);

Route::middleware($middleware)->group(function () use ($prefix) {
    Route::post(rtrim($prefix, '/') . '/pwt', [ZenithPayWebhookController::class, 'pwt'])
        ->name('zenithpay.webhooks.pwt');

    Route::post(rtrim($prefix, '/') . '/virtual-account', [ZenithPayWebhookController::class, 'virtualAccount'])
        ->name('zenithpay.webhooks.virtual_account');
});
