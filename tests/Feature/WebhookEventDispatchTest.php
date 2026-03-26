<?php

declare(strict_types=1);

namespace ZenithPay\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use ZenithPay\Contracts\WebhookHandlerInterface;
use ZenithPay\Events\ZenithPayWebhookReceived;
use ZenithPay\Http\Controllers\ZenithPayWebhookController;
use ZenithPay\Services\WebhookVerifier;
use ZenithPay\Tests\TestCase;

class WebhookEventDispatchTest extends TestCase
{
    public function test_dispatches_webhook_event(): void
    {
        config()->set('zenithpay.webhook.secret', 'test-secret');

        $payload = ['orderNo' => 'ORDER-9', 'status' => 'success'];
        $rawBody = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $rawBody, 'test-secret');

        $request = Request::create('/zenithpay/webhooks/pwt', 'POST', [], [], [], [], $rawBody);
        $request->headers->set('X-Zenithpay-Timestamp', $timestamp);
        $request->headers->set('X-Zenithpay-Signature', $signature);
        $request->headers->set('X-Zenithpay-Event', 'pwt.intent.succeeded');

        Event::fake([ZenithPayWebhookReceived::class]);

        $handler = new class implements WebhookHandlerInterface {
            public function handle(array $payload, string $event, Request $request): array|null
            {
                return null;
            }
        };

        $controller = new ZenithPayWebhookController(new WebhookVerifier(), $handler);
        $controller->pwt($request);

        Event::assertDispatched(ZenithPayWebhookReceived::class, function ($event) use ($payload) {
            return $event->payload === $payload && $event->event === 'pwt.intent.succeeded';
        });
    }
}
