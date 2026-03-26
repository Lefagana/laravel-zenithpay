<?php

declare(strict_types=1);

namespace ZenithPay\Tests\Feature;

use Illuminate\Support\Facades\Http;
use ZenithPay\Services\ZenithPayClient;
use ZenithPay\Tests\TestCase;

class ZenithPayClientTest extends TestCase
{
    public function test_initialize_pwt_sends_request(): void
    {
        Http::fake([
            'https://api.zenithpay.com/*' => Http::response(['status' => true], 200),
        ]);

        $client = new ZenithPayClient('https://api.zenithpay.com', 'merchant-1', 'secret');

        $response = $client->initializePwt([
            'amount' => 100.0,
            'email' => 'customer@example.com',
        ], [
            'Idempotency-Key' => 'ORDER-123',
        ]);

        $this->assertSame(['status' => true], $response);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.zenithpay.com/api/transaction/initialize'
                && $request->hasHeader('Authorization', 'Bearer secret')
                && $request->hasHeader('X-Merchant-Id', 'merchant-1')
                && $request->hasHeader('Idempotency-Key', 'ORDER-123');
        });
    }
}
