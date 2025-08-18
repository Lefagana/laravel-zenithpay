<?php

namespace ZenithPay\Services;

use Illuminate\Support\Facades\Http;

class ZenithPayClient
{
    protected $baseUrl;
    protected $merchantId;
    protected $secretKey;

    public function __construct($baseUrl, $merchantId, $secretKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->merchantId = $merchantId;
        $this->secretKey = $secretKey;
    }

    protected function headers()
    {
        return [
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /** Create Virtual Account */
    public function createVirtualAccount(array $data)
    {
        return Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/virtual-accounts", array_merge($data, [
                'merchant_id' => $this->merchantId,
            ]))
            ->json();
    }

    /** Verify Account/Transaction */
    public function verifyTransaction(string $transactionId)
    {
        return Http::withHeaders($this->headers())
            ->get("{$this->baseUrl}/transactions/{$transactionId}")
            ->json();
    }
}