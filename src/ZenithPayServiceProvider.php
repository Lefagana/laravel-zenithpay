<?php

namespace ZenithPay;

use Illuminate\Support\ServiceProvider;
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
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/zenithpay.php' => config_path('zenithpay.php'),
        ], 'zenithpay-config');
    }
}