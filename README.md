# Laravel ZenithPay

A Laravel package for integrating ZenithPay dedicated virtual accounts.

## Installation
```bash
composer require zenithpay/laravel-zenithpay

php artisan vendor:publish --tag=zenithpay-config

bash
ZENITHPAY_BASE_URL=https://zenithpay.ng
ZENITHPAY_SECRET_KEY=your_secret_key

use ZenithPay\Facades\ZenithPay;

public function createAccount()
{
    $response = ZenithPay::createDedicatedAccount([
        "bvn"          => "12345678901",
        "account_name" => "John Doe",
        "first_name"   => "John",
        "last_name"    => "Doe",
        "email"        => "john@example.com",
    ]);

    return response()->json($response);
}
