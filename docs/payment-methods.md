# Payments

## Create a Payment Method

Using this API call instead of using the transparent redirect payment form can significantly increase your PCI compliance requirements. The data attribute can be any arbitrary data you’d like to attach to the payment method. Multiple tokens may be returned after this below code has been called. So be sure you retrieve the payment token and you may want to store it for later use especially if you set retained to true. After a payment method has been successfully used, it must be retained before it can be used again.

```php
Spreedly::payment()->create([
	'credit_card' => [
		'first_name' => 'Joe',
	 	'last_name' => 'Jones',
		'number' => '5555555555554444',
		'month' => '3',
		'year' => '2032',
		// Below are optional.
		'verification_value' => '123', // CVV2
		'email' => 'email@example.com',
		'address1' => 'Test Address 1',
		'address2' => 'Test Address 2',
		'city' => 'Cityville',
		'state' => 'CA',
		'zip' => '90210',
		'country' => 'USA',
		'phone_number' => '555-555-5555',
	],
	// set retained to 'true' to store the credit card on Spreedly
    // (additional fee's may apply, see Spreedly site).
	'retained' => 'true',
	// (optional) custom data you want stored and returned
	'data' => [
		'order_id' => '12345',
		'more' => [
			'color' => 'red',
			'width' => '52'
		]
	]
]);
```

```
Array
(
    [token] => MrTv7uk6kXQ4MvFccBNFaWOXktK
    [created_at] => 2014-09-15T19:32:40Z
    [updated_at] => 2014-09-15T19:32:40Z
    [succeeded] => true
    [transaction_type] => AddPaymentMethod
    [retained] => true
    [state] => succeeded
    [message] => Succeeded!
    [payment_method] => Array
    (
        [token] => ELcTcsilg3CQfhE6971ma7LdA37 <--- payment method token
        [created_at] => 2014-09-15T19:32:40Z
        [updated_at] => 2014-09-15T19:32:40Z
        [email] => email@example.com
        [data] => Array
        (
            [order_id] => 12345
            [more] => Array
            (
                [color] => red
                [width] => 52
            )
        )
        [storage_state] => retained
        [test] => true
        [last_four_digits] => 4444
        [first_six_digits] => 555555
        [card_type] => master
        [first_name] => Joe
        [last_name] => Jones
        [month] => 3
        [year] => 2032
        [address1] => Test Address 1
        [address2] => Test Address 2
        [city] => Cityville
        [state] => CA
        [zip] => 90210
        [country] => USA
        [phone_number] => 555-555-5555
        [full_name] => Joe Jones
        [eligible_for_card_updater] => true
        [payment_method_type] => credit_card
        [errors] =>
        [verification_value] => XXX
        [number] => XXXX-XXXX-XXXX-4444
    )
)
```

## Update a Payment Method

It’s important to note that updating sensitive information like card number and verification value is prohibited using this API call. To change the sensitive information like the credit card number or the verification value, you’ll want to create and retain a new payment.

```php
Spreedly::payment($paymentToken)->update([
	'month' => '04',
	'year' => '2020',
	'address1' => 'My New Address',
	// custom data
	'data' => [
		'more' => [
			'height' => '25'
		]
	]
]);
```

## List all Payment Methods

To see the list of retained payment methods, you can make this api call. The retained payment methods returned will be sorted by created_at and then token. It returns the oldest 20 payment methods.

```php
Spreedly::payment()->all();

// If you have more than 20, you can always paginate to get the remainder
// from after the token provided
Spreedly::payment()->all($paymentToken);
```

## Retain a Payment Method

Unless specifically instructed to do otherwise, Spreedly purges all of the sensitive data of payment methods it has seen every few minutes. To keep the sensitive information of a payment method around for later, simply use the code below. I recommend using the ```retained => true``` param when creating a new payment method, instead of making a separate call with the code below to store the payment method. NOTE: Additional fee's may apply for storing cards, see Spreedly site.

```php
Spreedly::payment($paymentToken)->retain();
```

## Recache a Payment Method

Update a credit card’s verification value (CVV).

```php
Spreedly::payment($paymentToken)->recache(123);
```

## Store/Vault a Payment Method to Third Party

```php
Spreedly::gateway($gatewayToken)->payment($paymentToken)->store();
```

## Disable a Stored Payment Method

You should only keep around the sensitive information of payment methods that you’re really going to use. Rather than deleting a payment method, Spreedly redacts it, removing all sensitive information but leaving a place for any transactions to hang off of. You can only disable payment methods that were retained on Spreedly.

```php
Spreedly::payment($paymentToken)->disable();
```

## Verify a Stored Payment Method

Ask a gateway if a payment method is in good standing.

```php
Spreedly::payment($paymentToken)->verify();

// Retain the card if it has been successfully verified
Spreedly::payment($paymentToken)->verify(true);
```

## Get a Payment Methods details

Get the details of a specific payment method.

```php
Spreedly::payment($paymentToken)->get();
```

## Charge a Payment Method

A purchase call immediately takes funds from the payment method (assuming the transaction succeeds). ```->purchase()``` accepts 2 paramaters. Param 1 is the amount to charge. Param 2 is the currency and is optional. After a payment method has been successfully used, it must be retained before it can be used again.

**The amount must be an integer as per required by Spreedly. E.g., 1098 for $10.98.**

```php
// Use default gateway. (Charged: $10.98)
Spreedly::payment($paymentToken)->purchase(1098);
Spreedly::payment($paymentToken)->purchase(1098, 'USD');

// Set gateway. (Charged: $10.98)
Spreedly::gateway($gatewayToken)->payment($paymentToken)->purchase(1098, 'USD');

// Specifying some custom data that spreedly allows
Spreedly::payment($paymentToken)->purchase(1098, 'USD', [
  'ip' => '127.0.0.1',
  'order_id' => '12345',
  'description' => 'test description...',
  'merchant_name_descriptor' => 'Example',
  'merchant_location_descriptor' => 'http://example.com'
]);
```

## Authorize a Payment Method

An authorize works just like a purchase; the difference being that it doesn’t actually take the funds. ```->authorize()``` accepts 2 paramaters. Param 1 is the amount to authorize. Param 2 is the currency and is optional. NOTE: ```->authorize()``` will hold funds on some payment methods, notably debit cards.

**The amount must be an integer as per required by Spreedly. E.g., 1098 for $10.98.**

```php
// Use default gateway
Spreedly::payment($paymentToken)->authorize(1098, 'USD');

// Set gateway
Spreedly::gateway($gatewayToken)->payment($paymentToken)->authorize(1098, 'USD');

// Specifying some custom data that spreedly allows
Spreedly::payment($paymentToken)->authorize(1098, 'USD', [
  'ip' => '127.0.0.1',
  'order_id' => '12345',
  'description' => 'test description...',
  'merchant_name_descriptor' => 'Example',
  'merchant_location_descriptor' => 'http://example.com'
]);
```

## Create a general credit (Add funds)

The general credit action can add funds to a credit card. This is different than a credit which refunds money. Support for this capability depends on the gateway.

**The amount must be an integer as per required by Spreedly. E.g., 1098 for $10.98.**

```php
Spreedly::payment($paymentToken)->generalCredit(1098);
```

## Transactions for a Payment Method

View all transactions of a specific payment method. The transactions returned will be sorted by created_at and then token. It returns the oldest 20 transactions.

```php
Spreedly::payment($paymentToken)->transactions();

// If you have more than 20 transactions, you can always paginate to get the remainder after the token specified.
Spreedly::payment($paymentToken)->transactions($transactionToken);
```
