# Transactions

## List all Transactions on your account

The transactions returned will be sorted by created_at and then token. It returns the oldest 20 transactions.

```
Spreedly::transaction()->all();

// If you have more than 20 transactions, you can always paginate to get more like so.
Spreedly::transaction()->all($transactionToken);
```

## Get the details of a Transaction

Get the details of a specific transaction.

```
Spreedly::transaction($transactionToken)->get();
```

## List of all transactions referencing a specific transaction.

If you used a transaction token to Purchase, Authorization, Void, or Credit, this will list all the transactions referencing that transaction. NOTE: This call could take a while if there are a lot of referencing transactions because it has to make a seperate call for each referenced transaction.

```
Spreedly::transaction($transactionToken)->referencing();

// If there are a lot of referencing transactions, you may want to limit how many transactions are returned.
// This for example would return 5 results, starting with the first transaction.
Spreedly::transaction($transactionToken)->referencing(0, 5);

// This for example would return 5 results, starting with the sixth transaction.
Spreedly::transaction($transactionToken)->referencing(5, 5);
```

## Get a Transactions transcript

This allows you to see the full conversation Spreedly had with the payment gateway for a given transaction.

```
Spreedly::transaction($transactionToken)->transcript();
```

## Purchase, Void, and Credit using a previous Transaction

No reason to set the gateway before these methods, since the transactions gateway can't be changed.

```
// A purchase call immediately takes funds (assuming the transaction succeeds).
Spreedly::transaction($transactionToken)->purchase('...AMOUNT...', '...CURRENCY(optional)...');

// Void is used to cancel out authorizations and, with some gateways, to cancel actual payment transactions within the first 24 hours.
Spreedly::transaction($transactionToken)->void();

// A credit is like a void, except it reverses a charge instead of just cancelling a charge that hasn’t yet been made. You can pass in an amount to only credit a portion of the original transaction.
Spreedly::transaction($transactionToken)->credit('...AMOUNT(optional)...');

// Example: Specifying some custom data that spreedly allows
Spreedly::transaction($transactionToken)->purchase(10.98, 'USD', [
  'ip' => '127.0.0.1',
  'order_id' => '12345',
  'description' => 'test description...',
  'merchant_name_descriptor' => 'Example',
  'merchant_location_descriptor' => 'http://example.com'
]);
```

## Capture using a previous Transaction

No reason to set the gateway, since the transactions gateway can't be changed.

```
// A capture will take the funds previously that were reserved via an authorization.
Spreedly::transaction($transactionToken)->capture(10.98);
Spreedly::transaction($transactionToken)->capture(10.98, 'EUR');
Spreedly::transaction($transactionToken)->capture(10.98, 'USD', [
  'ip' => '127.0.0.1',
  'order_id' => '12345',
  'description' => 'test description...',
  'merchant_name_descriptor' => 'Example',
  'merchant_location_descriptor' => 'http://example.com'
]);
```