<?php

namespace ZenithPay\Facades;

use Illuminate\Support\Facades\Facade;

class ZenithPay extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \ZenithPay\Services\ZenithPayClient::class;
    }
}