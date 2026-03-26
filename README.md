# Laravel ZenithPay

A Laravel package for integrating ZenithPay dedicated virtual accounts into your application. This package provides a simple and elegant way to create and manage dedicated virtual accounts for your customers.

[![Latest Version](https://img.shields.io/packagist/v/zenithpay/laravel-zenithpay.svg)](https://packagist.org/packages/zenithpay/laravel-zenithpay)
[![License](https://img.shields.io/packagist/l/zenithpay/laravel-zenithpay.svg)](https://packagist.org/packages/zenithpay/laravel-zenithpay)

## Requirements

- PHP 8.0 or higher
- Laravel 10.x, 11.x, or 12.x

## Installation

Install the package via Composer:

```bash
composer require zenithpay/laravel-zenithpay
```

### Publish Configuration

Publish the configuration file to customize the package settings:

```bash
php artisan vendor:publish --tag=zenithpay-config
```

This will create a `config/zenithpay.php` file in your application.

### Environment Configuration

Add the following environment variables to your `.env` file:

```bash
ZENITHPAY_BASE_URL=https://zenithpay.ng
ZENITHPAY_MERCHANT_ID=your_merchant_id
ZENITHPAY_SECRET_KEY=your_secret_key
ZENITHPAY_WEBHOOK_SECRET=your_webhook_secret
ZENITHPAY_HTTP_TIMEOUT=30
ZENITHPAY_HTTP_RETRY_TIMES=2
ZENITHPAY_HTTP_RETRY_SLEEP=500
ZENITHPAY_WEBHOOK_ALLOWED_IPS=1.2.3.4,5.6.7.8
ZENITHPAY_WEBHOOK_TRUSTED_PROXIES=
ZENITHPAY_WEBHOOK_SIGNATURE_TTL=300
ZENITHPAY_WEBHOOK_ROUTES_ENABLED=true
ZENITHPAY_WEBHOOK_ROUTE_PREFIX=/zenithpay/webhooks
ZENITHPAY_WEBHOOK_MIDDLEWARE=api
```

**Getting Your API Credentials:**
1. Sign up for a ZenithPay account at [https://zenithpay.ng](https://zenithpay.ng)
2. Navigate to your dashboard settings
3. Copy your Merchant ID and Secret Key
4. Generate a webhook secret for secure webhook verification

## Configuration

The `config/zenithpay.php` file contains the following options:

```php
return [
    'base_url' => env('ZENITHPAY_BASE_URL', 'https://api.zenithpay.com'),
    'merchant_id' => env('ZENITHPAY_MERCHANT_ID'),
    'secret_key' => env('ZENITHPAY_SECRET_KEY'),
    'webhook_secret' => env('ZENITHPAY_WEBHOOK_SECRET'),

    'timeout' => env('ZENITHPAY_HTTP_TIMEOUT', 30),

    'retry' => [
        'times' => env('ZENITHPAY_HTTP_RETRY_TIMES', 2),
        'sleep' => env('ZENITHPAY_HTTP_RETRY_SLEEP', 500),
    ],

    'webhook' => [
        'secret' => env('ZENITHPAY_WEBHOOK_SECRET'),
        'allowed_ips' => array_values(array_filter(array_map('trim', explode(',', env('ZENITHPAY_WEBHOOK_ALLOWED_IPS', ''))))),
        'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', env('ZENITHPAY_WEBHOOK_TRUSTED_PROXIES', ''))))),
        'signature_ttl' => env('ZENITHPAY_WEBHOOK_SIGNATURE_TTL', 300),
        'handler' => \ZenithPay\Services\DefaultWebhookHandler::class,
        'middleware' => array_values(array_filter(array_map('trim', explode(',', env('ZENITHPAY_WEBHOOK_MIDDLEWARE', 'api'))))),
        'routes' => [
            'enabled' => env('ZENITHPAY_WEBHOOK_ROUTES_ENABLED', true),
            'prefix' => env('ZENITHPAY_WEBHOOK_ROUTE_PREFIX', '/zenithpay/webhooks'),
        ],
    ],
];
```

## Usage

### Using the Facade

Import the ZenithPay facade at the top of your file:

```php
use ZenithPay\Facades\ZenithPay;
```

### Creating a Dedicated Virtual Account

Create a dedicated virtual account for a customer:

```php
use ZenithPay\Facades\ZenithPay;
use Illuminate\Http\Client\RequestException;

public function createAccount()
{
    try {
        $response = ZenithPay::createDedicatedAccount([
            'bvn'          => '12345678901',
            'account_name' => 'John Doe',
            'first_name'   => 'John',
            'last_name'    => 'Doe',
            'email'        => 'john@example.com',
        ]);

        return response()->json([
            'success' => true,
            'data' => $response
        ]);

    } catch (RequestException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to create account',
            'error' => $e->response->json()
        ], $e->response->status());
    }
}
```

### Pay With Transfer (PWT)

```php
use ZenithPay\Facades\ZenithPay;

$response = ZenithPay::initializePwt([
    'amount' => 1250.00,
    'email' => 'customer@example.com',
    'callback_url' => 'https://merchant.example.com/zenithpay/callback',
    'meta_data' => [
        'order_id' => 'ORDER-12345',
    ],
], [
    'Idempotency-Key' => 'ORDER-12345',
]);
```

### Query PWT Status

```php
use ZenithPay\Facades\ZenithPay;

$response = ZenithPay::queryPwtStatus([
    'order_no' => 'PALMPAY_ORDER_NO',
]);
```

### Query PWT Callback Diagnostics

```php
use ZenithPay\Facades\ZenithPay;

$response = ZenithPay::queryPwtCallbackDiagnostics([
    'order_id' => 'ZTSPWT-...',
]);
```

### Retry PWT Callback

```php
use ZenithPay\Facades\ZenithPay;

$response = ZenithPay::retryPwtCallback([
    'order_no' => 'PALMPAY_ORDER_NO',
]);
```

### Using Dependency Injection

You can also inject the `ZenithPayClient` directly into your controllers or services:

```php
use ZenithPay\Services\ZenithPayClient;

class PaymentController extends Controller
{
    protected $zenithPay;

    public function __construct(ZenithPayClient $zenithPay)
    {
        $this->zenithPay = $zenithPay;
    }

    public function createAccount(Request $request)
    {
        $response = $this->zenithPay->createDedicatedAccount([
            'bvn'          => $request->bvn,
            'account_name' => $request->account_name,
            'first_name'   => $request->first_name,
            'last_name'    => $request->last_name,
            'email'        => $request->email,
        ]);

        return response()->json($response);
    }
}
```

## API Reference

### Create Dedicated Account

Creates a dedicated virtual account for a customer.

**Method:** `createDedicatedAccount(array $data): array`

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| bvn | string | Yes | Customer's Bank Verification Number (11 digits) |
| account_name | string | Yes | Full name for the account |
| first_name | string | Yes | Customer's first name |
| last_name | string | Yes | Customer's last name |
| email | string | Yes | Customer's email address |

**Example Request:**

```php
$response = ZenithPay::createDedicatedAccount([
    'bvn'          => '12345678901',
    'account_name' => 'John Doe',
    'first_name'   => 'John',
    'last_name'    => 'Doe',
    'email'        => 'john@example.com',
]);
```

**Example Response:**

```json
{
  "reference": "ZTSVA-01JY73PXPSASK9MBCP7P7VW8",
  "account_number": "6639486000",
  "bank": "PALMPAY",
  "account_name": "John INVENTURES LTD(ZenithPay)",
  "status": "Enabled"
}
```

**Response Fields:**

| Field | Type | Description |
|-------|------|-------------|
| reference | string | Unique reference for the virtual account |
| account_number | string | The generated virtual account number |
| bank | string | Bank provider for the virtual account |
| account_name | string | The registered account name |
| status | string | Account status (Enabled/Disabled) |

### Initialize PWT

**Method:** `initializePwt(array $data): array`

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| amount | number | Yes | Amount in NGN (major units) |
| email | string | Yes | Customer email |
| callback_url | string | No | Merchant callback URL for status updates |
| meta_data | array | No | Optional metadata |

### Query PWT Status

**Method:** `queryPwtStatus(array $data): array`

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| order_id | string | No | ZenithPay order ID |
| order_no | string | No | Provider order number |

### Query PWT Callback Diagnostics

**Method:** `queryPwtCallbackDiagnostics(array $data): array`

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| order_id | string | No | ZenithPay order ID |
| order_no | string | No | Provider order number |

### Retry PWT Callback

**Method:** `retryPwtCallback(array $data): array`

**Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| order_id | string | No | ZenithPay order ID |
| order_no | string | No | Provider order number |

## Webhooks

The package ships with optional webhook routes and a verifier that matches ZenithPay's signature scheme:

- Signature: `sha256(<timestamp>.<raw_body>, <ZENITHPAY_WEBHOOK_SECRET>)`
- Headers: `X-Zenithpay-Event`, `X-Zenithpay-Event-Id`, `X-Zenithpay-Timestamp`, `X-Zenithpay-Signature`, `X-Zenithpay-Source-IP`

### Enable Routes

By default, the package registers:

- `POST /zenithpay/webhooks/pwt`
- `POST /zenithpay/webhooks/virtual-account`

You can change the prefix or disable route registration in config.

### Custom Webhook Handler

Create a handler to process verified payloads:

```php
namespace App\ZenithPay;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use ZenithPay\Contracts\WebhookHandlerInterface;

class MyWebhookHandler implements WebhookHandlerInterface
{
    public function handle(array $payload, string $event, Request $request): Response|array|null
    {
        // Update your order status, notify customers, etc.

        return [
            'status' => 'received',
        ];
    }
}
```

Register it in `config/zenithpay.php`:

```php
'webhook' => [
    'handler' => App\ZenithPay\MyWebhookHandler::class,
],
```

### Webhook Event

Every verified webhook also dispatches a Laravel event you can listen to:

```php
use ZenithPay\Events\ZenithPayWebhookReceived;

Event::listen(ZenithPayWebhookReceived::class, function (ZenithPayWebhookReceived $event) {
    // $event->payload, $event->event, $event->request
});
```

## Error Handling

The package throws `Illuminate\Http\Client\RequestException` when API requests fail. Always wrap your calls in try-catch blocks:

```php
use Illuminate\Http\Client\RequestException;

try {
    $response = ZenithPay::createDedicatedAccount($data);

} catch (RequestException $e) {
    // Get the status code
    $statusCode = $e->response->status();

    // Get the error response body
    $error = $e->response->json();

    // Log the error
    \Log::error('ZenithPay API Error', [
        'status' => $statusCode,
        'error' => $error
    ]);

    // Handle the error appropriately
}
```

## Common Error Codes

| Status Code | Description |
|-------------|-------------|
| 400 | Bad Request - Invalid parameters |
| 401 | Unauthorized - Invalid API credentials |
| 422 | Unprocessable Entity - Validation error |
| 500 | Internal Server Error - ZenithPay server error |

## Testing

For testing purposes, you can mock the ZenithPayClient:

```php
use ZenithPay\Services\ZenithPayClient;

public function test_create_account()
{
    $mock = $this->mock(ZenithPayClient::class);

    $mock->shouldReceive('createDedicatedAccount')
         ->once()
         ->with([
             'bvn' => '12345678901',
             'account_name' => 'John Doe',
             'first_name' => 'John',
             'last_name' => 'Doe',
             'email' => 'john@example.com',
         ])
         ->andReturn([
             'reference' => 'ZTSVA-TEST123',
             'account_number' => '1234567890',
             'bank' => 'TEST BANK',
             'account_name' => 'John Doe',
             'status' => 'Enabled'
         ]);

    // Your test assertions here
}
```

## Security

- Never commit your `.env` file or expose your API credentials
- Store your `ZENITHPAY_SECRET_KEY` securely
- Use HTTPS for all API communications
- Validate webhook signatures using the `ZENITHPAY_WEBHOOK_SECRET`

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Credits

- [Malah Lefagana](https://github.com/zenithpay)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

For support, please contact:
- Email: malahmusalg@gmail.com
- Website: [https://zenithpay.ng](https://zenithpay.ng)

## Resources

- [ZenithPay Official Documentation](https://zenithpay.ng/docs)
- [API Reference](https://zenithpay.ng/api-docs)
- [Support Center](https://zenithpay.ng/support)
