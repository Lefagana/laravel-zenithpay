# Laravel ZenithPay

A Laravel package for integrating ZenithPay dedicated virtual accounts.

## Installation
```bash
composer require zenithpay/laravel-zenithpay

bash
php artisan vendor:publish --tag=zenithpay-config

env
ZENITHPAY_BASE_URL=https://zenithpay.ng
ZENITHPAY_SECRET_KEY=your_secret_key
