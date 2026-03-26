<?php

declare(strict_types=1);

namespace ZenithPay\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use ZenithPay\Contracts\WebhookHandlerInterface;

class DefaultWebhookHandler implements WebhookHandlerInterface
{
    public function handle(array $payload, string $event, Request $request): Response|array|null
    {
        Log::info('ZenithPay webhook received', [
            'event' => $event,
            'event_id' => $request->header('X-Zenithpay-Event-Id'),
            'order_no' => $payload['orderNo'] ?? null,
            'order_id' => $payload['orderId'] ?? null,
        ]);

        return null;
    }
}
