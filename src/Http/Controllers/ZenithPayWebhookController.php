<?php

declare(strict_types=1);

namespace ZenithPay\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use ZenithPay\Contracts\WebhookHandlerInterface;
use ZenithPay\Events\ZenithPayWebhookReceived;
use ZenithPay\Services\WebhookVerifier;

class ZenithPayWebhookController
{
    public function __construct(
        private readonly WebhookVerifier $verifier,
        private readonly WebhookHandlerInterface $handler
    ) {
    }

    public function pwt(Request $request): JsonResponse|Response
    {
        return $this->handle($request);
    }

    public function virtualAccount(Request $request): JsonResponse|Response
    {
        return $this->handle($request);
    }

    private function handle(Request $request): JsonResponse|Response
    {
        $result = $this->verifier->verifyRequest($request);
        if (!$result['ok']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
            ], $result['status']);
        }

        event(new ZenithPayWebhookReceived($result['payload'], $result['event'], $request));

        $handlerResponse = $this->handler->handle($result['payload'], $result['event'], $request);

        if ($handlerResponse instanceof Response) {
            return $handlerResponse;
        }

        if (is_array($handlerResponse)) {
            return response()->json($handlerResponse);
        }

        return response()->json([
            'status' => 'success',
            'event' => $result['event'],
        ]);
    }
}
