<?php

namespace ZenithPay;

use Illuminate\Support\ServiceProvider;
use ZenithPay\Contracts\WebhookHandlerInterface;
use ZenithPay\Services\DefaultWebhookHandler;
use ZenithPay\Services\WebhookVerifier;
use ZenithPay\Services\ZenithPayClient;

class ZenithPayServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/zenithpay.php', 'zenithpay');

        $this->app->singleton(ZenithPayClient::class, function ($app) {
            return new ZenithPayClient(
                config('zenithpay.base_url'),
                config('zenithpay.merchant_id'),
                config('zenithpay.secret_key')
            );
        });

        $this->app->singleton(WebhookVerifier::class, function () {
            return new WebhookVerifier();
        });

        $this->app->bind(WebhookHandlerInterface::class, function ($app) {
            $handlerClass = config('zenithpay.webhook.handler');

            if (is_string($handlerClass) && class_exists($handlerClass)) {
                return $app->make($handlerClass);
            }

            return new DefaultWebhookHandler();
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/zenithpay.php' => config_path('zenithpay.php'),
        ], 'zenithpay-config');

        if (config('zenithpay.webhook.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/zenithpay.php');
        }
    }
}