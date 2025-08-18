<?php

namespace ZenithPay\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class ZenithPayClient
{
    protected $baseUrl;
    protected $secretKey;

    public function __construct($baseUrl, $merchantId, $secretKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->secretKey = $secretKey;
    }

    protected function headers()
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Content-Type' => 'application/json',
            'Accept-Encoding' => 'gzip,deflate',
        ];
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
        $response = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/api/dedicated_account/assign", $data);

        if ($response->failed()) {
            throw new RequestException($response);
        }

        return $response->json();
    }
}