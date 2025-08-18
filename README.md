# Laravel ZenithPay

A Laravel package for integrating ZenithPay dedicated virtual accounts.

## Installation
```bash
composer require zenithpay/laravel-zenithpay
```

``
## Configuration
```bash
Publish the config file:

php artisan vendor:publish --tag=zenithpay-config

##.ENV
ZENITHPAY_BASE_URL=https://zenithpay.ng
ZENITHPAY_SECRET_KEY=your_secret_key
