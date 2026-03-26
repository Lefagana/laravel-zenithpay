<?php

namespace ZenithPay\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class ZenithPayClient
{
    protected $baseUrl;
    protected $secretKey;
    protected $merchantId;

    public function __construct($baseUrl, $merchantId, $secretKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->secretKey = $secretKey;
        $this->merchantId = $merchantId;
    }

    protected function headers()
    {
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Content-Type' => 'application/json',
            'Accept-Encoding' => 'gzip,deflate',
        ];

        if (!empty($this->merchantId)) {
            $headers['X-Merchant-Id'] = $this->merchantId;
        }

        return $headers;
    }

    protected function post(string $path, array $data, array $headers = []): array
    {
        $response = Http::withHeaders(array_merge($this->headers(), $headers))
            ->timeout(config('zenithpay.timeout', 30))
            ->retry(config('zenithpay.retry.times', 2), config('zenithpay.retry.sleep', 500))
            ->post("{$this->baseUrl}{$path}", $data);

        if ($response->failed()) {
            throw new RequestException($response);
        }

        return $response->json();
    }

    /**
     * Create Dedicated Virtual Account
     *
     * @param array $data
     * @return array
     *
     * @throws RequestException
     */
    public function createDedicatedAccount(array $data): array
    {
        return $this->post('/api/dedicated_account/assign', $data);
    }

    /**
     * Initialize a Pay With Transfer (PWT) transaction.
     */
    public function initializePwt(array $data, array $headers = []): array
    {
        return $this->post('/api/transaction/initialize', $data, $headers);
    }

    /**
     * Query PWT transaction status.
     */
    public function queryPwtStatus(array $data, array $headers = []): array
    {
        return $this->post('/api/transaction/status', $data, $headers);
    }

    /**
     * Query PWT callback diagnostics.
     */
    public function queryPwtCallbackDiagnostics(array $data, array $headers = []): array
    {
        return $this->post('/api/transaction/callback-diagnostics', $data, $headers);
    }

    /**
     * Retry a PWT callback delivery.
     */
    public function retryPwtCallback(array $data, array $headers = []): array
    {
        return $this->post('/api/transaction/callback-retry', $data, $headers);
    }
}