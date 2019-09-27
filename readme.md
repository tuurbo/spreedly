**[changelog](#changelog) !! As of the 2.0 release the amount must be an integer as required by Spreedly. E.g., 1098 for \$10.98 !!**

# Getting Started

## Setup/Install

Install through Composer.

```
composer require tuurbo/spreedly
```

#### Laravel 4 or 5 Setup

Next, update app/config/app.php to include a reference to this package's service provider in the providers array and the facade in the aliases array.

```php
'providers' => [
    ...
   'Tuurbo\Spreedly\SpreedlyServiceProvider'
]

'aliases' => [
    ...
    'Spreedly' => 'Tuurbo\Spreedly\SpreedlyFacade'
]
```

[Login](https://spreedly.com) to your Spreedly account to retrieve your api credentials. You can set your default gateway once you've created your first gateway.

Add to app/config/services.php config file.

```php
return [

    ...

    'spreedly' => [
        'key' => '', // (required) Environment key
        'secret' => '', // (required) Signing Secret
        'gateway' => '', // (required) Default gateway
        'timeout' => '', // (optional) Default 64 seconds (recommended by Spreedly)
        'connect_timeout' => '', // (optional) Default 10 seconds
    ]

];
```

#### Default Setup (Non Laravel)

```php
$config = [
    'key' => '', // (required) Environment key
    'secret' => '', // (required) Signing Secret
    'gateway' => '', // (required) Default gateway
    'timeout' => '', // (optional) Default 64 seconds (recommended by Spreedly)
    'connect_timeout' => '', // (optional) Default 10 seconds
];

$spreedly = new Tuurbo\Spreedly\Spreedly($config);

// The amount must be an integer as per required by Spreedly. E.g., 1098 for $10.98.
$resp = $spreedly->payment($paymentToken)->purchase(1098);
```

## Example response handling

```php
// If the call to Spreedly is successful
if ($resp->success()) {
    return $resp->response();
    // $resp->transactionToken();
    // $resp->paymentToken();
    // $resp->message();
}

// If the call to Spreedly fails or payment declines
if ($resp->fails()) {

    // returns array
    return $resp->errors();

    // returns list of errors as a string
    return $resp->errors(true);
}
```

## More Docs

### [Gateways](docs/gateways.md)

### [Payment Methods](docs/payment-methods.md)

### [Transactions](docs/transactions.md)

## Quick list of all methods

**NOTE: Many of the methods below return multiple tokens. Be sure when storing tokens, you store the correct ones for later use.**

```php
// Gateway calls.
Spreedly::gateway()->setup();
Spreedly::gateway()->all();
Spreedly::gateway()->show();
Spreedly::gateway()->create();
Spreedly::gateway()->disable();
Spreedly::gateway()->update();
Spreedly::gateway()->transactions();

// If using multiple gateways, you can set the gateway token before the payment call.
Spreedly::gateway()->payment()->purchase();
Spreedly::gateway()->payment()->authorize();

// Uses default gateway.
Spreedly::payment()->all();
Spreedly::payment()->create();
Spreedly::payment()->update();
Spreedly::payment()->disable();
Spreedly::payment()->retain();
Spreedly::payment()->recache();
Spreedly::payment()->store();
Spreedly::payment()->get();
Spreedly::payment()->transactions();
Spreedly::payment()->purchase();
Spreedly::payment()->authorize();
Spreedly::payment()->verify();
Spreedly::payment()->generalCredit();

// Transaction calls
Spreedly::transaction()->all();
Spreedly::transaction()->get();
Spreedly::transaction()->referencing();
Spreedly::transaction()->transcript();
Spreedly::transaction()->purchase();
Spreedly::transaction()->void();
Spreedly::transaction()->credit();
Spreedly::transaction()->capture();
Spreedly::transaction()->complete();
```

## Development

Clone the repo and run `npm install`. This will `composer install`.

**Testing**

Tests are in the spec directory. They are written with [phpspec](https://www.phpspec.net/en/stable/).

To run your tests, simply do `npm test`. If you don't want to use npm, that's fine, simply run `vendor/bin/phpspec run`

Please ensure you have added proper test coverage for each Pull Request.

## Changelog

### 2.3.1

- added support for overriding the Spreedly base url

### 2.3

- added support for laravel 5.4

### 2.2

- added ability to merge configs.

### 2.1

- changed default timeout from 15 seconds to 64 seconds as recommended by Spreedly.
- added timeout method to change timeout per api call. E.g., `Spreedly::timeout(25)->payment()->purchase()`.
- added new `Tuurbo\Spreedly\Exceptions\TimeoutException` for catching timeouts.

### 2.0

- amount is no longer converted to cents.
  - the amount must be an integer as required by Spreedly. E.g., 1098 for \$10.98
- switched from Spreedly xml api to json api.
- renamed `->declined()` method to `->message()`.
