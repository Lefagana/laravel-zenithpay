<?php

declare(strict_types=1);

namespace ZenithPay\Services;

use Illuminate\Http\Request;

class WebhookVerifier
{
    public function verifyRequest(Request $request): array
    {
        $rawBody = (string) $request->getContent();
        if (trim($rawBody) == '') {
            return $this->error(400, 'Empty body');
        }

        $decoded = json_decode($rawBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error(400, 'Invalid JSON');
        }

        $timestamp = (string) $request->header('X-Zenithpay-Timestamp', '');
        $signature = (string) $request->header('X-Zenithpay-Signature', '');
        $event = (string) $request->header('X-Zenithpay-Event', 'unknown');
        $eventId = (string) $request->header('X-Zenithpay-Event-Id', '');

        if ($timestamp === '' || $signature === '') {
            return $this->error(401, 'Missing signature headers');
        }

        $signatureWindow = (int) config('zenithpay.webhook.signature_ttl', 300);
        if (abs(time() - (int) $timestamp) > max($signatureWindow, 1)) {
            return $this->error(401, 'Request timestamp expired');
        }

        $secret = (string) config('zenithpay.webhook.secret', config('zenithpay.webhook_secret'));
        if ($secret === '') {
            return $this->error(500, 'Server configuration error');
        }

        $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $rawBody, $secret);
        if (!hash_equals($expectedSignature, $signature)) {
            return $this->error(401, 'Invalid signature');
        }

        $allowedIps = $this->normalizeList(config('zenithpay.webhook.allowed_ips', []));
        if (!empty($allowedIps)) {
            $trustedProxies = $this->normalizeList(config('zenithpay.webhook.trusted_proxies', []));
            $ipCandidates = $this->getIpCandidates($request, $trustedProxies);
            $matchedIp = null;

            foreach ($ipCandidates as $candidate) {
                if (in_array($candidate, $allowedIps, true)) {
                    $matchedIp = $candidate;
                    break;
                }
            }

            if ($matchedIp === null) {
                return $this->error(403, 'Forbidden: Invalid source IP');
            }
        }

        return [
            'ok' => true,
            'payload' => $decoded,
            'event' => $event,
            'event_id' => $eventId,
            'timestamp' => $timestamp,
        ];
    }

    private function error(int $status, string $message): array
    {
        return [
            'ok' => false,
            'status' => $status,
            'message' => $message,
        ];
    }

    private function normalizeList(array|string $value): array
    {
        if (is_string($value)) {
            $value = array_map('trim', explode(',', $value));
        }

        return array_values(array_filter($value, fn ($item) => (string) $item !== ''));
    }

    private function getIpCandidates(Request $request, array $trustedProxies): array
    {
        $candidates = [];
        $remoteAddr = trim((string) $request->server('REMOTE_ADDR', ''));
        if ($remoteAddr !== '') {
            $candidates[] = $remoteAddr;
        }

        $zenithpaySourceIp = trim((string) $request->header('X-Zenithpay-Source-IP', ''));
        if ($zenithpaySourceIp !== '') {
            $candidates[] = $zenithpaySourceIp;
        }

        if ($remoteAddr !== '' && in_array($remoteAddr, $trustedProxies, true)) {
            $forwarded = trim((string) $request->header('X-Forwarded-For', ''));
            if ($forwarded !== '') {
                $parts = array_map('trim', explode(',', $forwarded));
                if (!empty($parts[0])) {
                    $candidates[] = $parts[0];
                }
            }

            $cfConnectingIp = trim((string) $request->header('CF-Connecting-IP', ''));
            if ($cfConnectingIp !== '') {
                $candidates[] = $cfConnectingIp;
            }
        }

        $candidates = array_values(array_unique(array_filter($candidates, fn ($ip) => $ip !== '')));

        return $candidates;
    }
}
