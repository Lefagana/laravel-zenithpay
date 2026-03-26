<?php

declare(strict_types=1);

namespace ZenithPay\Events;

use Illuminate\Http\Request;

class ZenithPayWebhookReceived
{
    public array $payload;
    public string $event;
    public Request $request;

    public function __construct(array $payload, string $event, Request $request)
    {
        $this->payload = $payload;
        $this->event = $event;
        $this->request = $request;
    }
}
