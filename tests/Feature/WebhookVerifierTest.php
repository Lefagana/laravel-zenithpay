<?php

declare(strict_types=1);

namespace ZenithPay\Tests\Feature;

use Illuminate\Http\Request;
use ZenithPay\Services\WebhookVerifier;
use ZenithPay\Tests\TestCase;

class WebhookVerifierTest extends TestCase
{
    public function test_verifies_valid_signature(): void
    {
        config()->set('zenithpay.webhook.secret', 'test-secret');

        $payload = ['orderNo' => 'ORDER-1', 'status' => 'success'];
        $rawBody = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $rawBody, 'test-secret');

        $request = Request::create('/zenithpay/webhooks/pwt', 'POST', [], [], [], [], $rawBody);
        $request->headers->set('X-Zenithpay-Timestamp', $timestamp);
        $request->headers->set('X-Zenithpay-Signature', $signature);
        $request->headers->set('X-Zenithpay-Event', 'pwt.intent.succeeded');

        $verifier = new WebhookVerifier();
        $result = $verifier->verifyRequest($request);

        $this->assertTrue($result['ok']);
        $this->assertSame('pwt.intent.succeeded', $result['event']);
        $this->assertSame($payload, $result['payload']);
    }

    public function test_rejects_invalid_signature(): void
    {
        config()->set('zenithpay.webhook.secret', 'test-secret');

        $payload = ['orderNo' => 'ORDER-2', 'status' => 'failed'];
        $rawBody = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $timestamp = (string) time();

        $request = Request::create('/zenithpay/webhooks/pwt', 'POST', [], [], [], [], $rawBody);
        $request->headers->set('X-Zenithpay-Timestamp', $timestamp);
        $request->headers->set('X-Zenithpay-Signature', 'bad-signature');

        $verifier = new WebhookVerifier();
        $result = $verifier->verifyRequest($request);

        $this->assertFalse($result['ok']);
        $this->assertSame(401, $result['status']);
    }

    public function test_rejects_expired_timestamp(): void
    {
        config()->set('zenithpay.webhook.secret', 'test-secret');
        config()->set('zenithpay.webhook.signature_ttl', 1);

        $payload = ['orderNo' => 'ORDER-3'];
        $rawBody = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $timestamp = (string) (time() - 10);
        $signature = hash_hmac('sha256', $timestamp . '.' . $rawBody, 'test-secret');

        $request = Request::create('/zenithpay/webhooks/pwt', 'POST', [], [], [], [], $rawBody);
        $request->headers->set('X-Zenithpay-Timestamp', $timestamp);
        $request->headers->set('X-Zenithpay-Signature', $signature);

        $verifier = new WebhookVerifier();
        $result = $verifier->verifyRequest($request);

        $this->assertFalse($result['ok']);
        $this->assertSame(401, $result['status']);
    }
}
