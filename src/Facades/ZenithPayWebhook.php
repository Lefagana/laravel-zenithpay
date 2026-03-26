<?php

declare(strict_types=1);

namespace ZenithPay\Facades;

use Illuminate\Support\Facades\Facade;

class ZenithPayWebhook extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \ZenithPay\Services\WebhookVerifier::class;
    }
}
