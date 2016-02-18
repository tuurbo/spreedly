# Gateways

## List of supported Gateways

When getting ready to add a gateway, the first thing you'll want to do is find out what your gateway type is and what credentials you need to set it up. You can get a list of support gateways and options from [Spreedly](https://docs.spreedly.com/reference/supported-gateways/) if you prefer.

```php
Spreedly::gateway()->setup();
```

## List all Gateways

To see the gateways that you've created, you'd make the following call. The gateways returned will be sorted by created_at and then token. It returns the oldest 20 gateways.

```php
Spreedly::gateway()->all();

// If you have more than 20 gateways, you can always paginate to get the remainder after the token specified.
Spreedly::gateway()->all($gatewayToken);
```

## View a Gateway

Get a specific gateway that has already been created.

```php
Spreedly::gateway()->show();
```

## Create a Gateway

If needed, use ```Spreedly::gateway()->setup()``` to get a list of gateways and their configs.

```php
// Example: Create a 'Test' Gateway.
Spreedly::gateway()->create('test');

// Example: Create a 'PayPal' Gateway.
Spreedly::gateway()->create('paypal', [
	'mode' => 'delegate',
	'email' => 'your_paypal_email_address'
]);
```

## Update a Gateway

You can't update a gateway's type, but you can update its credentials if they change.

```php
Spreedly::gateway($gatewayToken)->update([
	'login' => 'new_login',
	'password' => 'new_password'
]);
```

## Disable a Gateway

Gateways can't be deleted (since they're permanently associated with any transactions run against them), but the sensitive credential information in them can be redacted so that they're inactive.

```php
Spreedly::gateway($gatewayToken)->disable();
```
