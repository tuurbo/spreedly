# Getting Started

## Setup/Install

Install through Composer.
```
"require": {
    "tuurbo/amazon-payment": "~0.9"
}
```

#### Laravel 4 or 5 Setup

Next, update app/config/app.php to include a reference to this package's service provider in the providers array and the facade in the aliases array.

```
'providers' => [
    ...
   'Tuurbo\Spreedly\SpreedlyPaymentServiceProvider'
]

'aliases' => [
    ...
    'Spreedly' => 'Tuurbo\Spreedly\SpreedlyFacade'
]
```

Add to app/config/services.php config file.
[Login](https://spreedly.com) to your spreedly account to retrieve your api credentials. You can set your default gateway once you've created your first gateway.
```
return [
    'key' => '', // Environment key
    'secret' => '',  // Signing Secret
    'defaultGateway' => '',
];
```

#### Default Setup

```
$config = [
    'key' => '', // Environment key
    'secret' => '',  // Signing Secret
    'gateway' => '', // Default gateway
];

$spreedly = new Tuurbo\Spreedly\Spreedly($config);

$resp = $spreedly->payment(...)->purchase(4.99);
```

## Example response handling

```
// If the call to spreedly is successfull
if ($resp->success()) {
    return $resp->response();
}

// If the call to spreedly fails or payment declines
if ($resp->fails()) {

    // returns array
    return $resp->errors();

    // returns list of errors seperated by periods
    return $resp->errors(true);
}
```

## More Docs

### [Gateways](docs/gateways.md)

### [Payment Methods](docs/payment-methods.md)

### [Transactions](docs/transactions.md)

## Quick list of all methods

**NOTE: Many of the methods below return multiple tokens. Be sure when storing tokens, you store the correct ones for later use.**

```
// Gateway calls.
Spreedly::gateway()->setup();
Spreedly::gateway()->create();
Spreedly::gateway()->all();
Spreedly::gateway()->disable();
Spreedly::gateway()->update();

// If using multiple gateways, you can set the gateway token before the payment call.
Spreedly::gateway()->payment()->purchase();
Spreedly::gateway()->payment()->authorize();

// Uses default gateway.
Spreedly::payment()->all();
Spreedly::payment()->create();
Spreedly::payment()->update();
Spreedly::payment()->disable();
Spreedly::payment()->retain();
Spreedly::payment()->store();
Spreedly::payment()->get();
Spreedly::payment()->transactions();
Spreedly::payment()->purchase();
Spreedly::payment()->authorize();

// Transaction calls
Spreedly::transaction()->all();
Spreedly::transaction()->get();
Spreedly::transaction()->referencing();
Spreedly::transaction()->transcript();
Spreedly::transaction()->purchase();
Spreedly::transaction()->void();
Spreedly::transaction()->credit();
```
