<?php

declare(strict_types=1);

namespace ZenithPay\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            \ZenithPay\ZenithPayServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'ZenithPay' => \ZenithPay\Facades\ZenithPay::class,
            'ZenithPayWebhook' => \ZenithPay\Facades\ZenithPayWebhook::class,
        ];
    }
}
