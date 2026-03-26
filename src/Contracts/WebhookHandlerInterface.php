<?php

declare(strict_types=1);

namespace ZenithPay\Contracts;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface WebhookHandlerInterface
{
    /**
     * Handle a verified ZenithPay webhook.
     *
     * Return a Response or array to override the default JSON response.
     */
    public function handle(array $payload, string $event, Request $request): Response|array|null;
}
