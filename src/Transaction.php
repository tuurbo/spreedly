<?php

namespace Tuurbo\Spreedly;

class Transaction
{
    protected $client;

    public $transactionToken;

    /**
     * Create a Guzzle instance and set token.
     *
     * @param \GuzzleHttp\Client $client
     * @param array              $config
     * @param string             $transactionToken optional
     */
    public function __construct(Client $client, $config, $transactionToken = null)
    {
        $this->client = $client;
        $this->transactionToken = $transactionToken;
    }

    /**
     * Get a list of all tranasctions on your Spreedly account.
     *
     * <code>
     *		Spreedly::transaction()->all();
     *
     *		// Returns list of all transactions for entire Spreedly account and paginates.
     *		Spreedly::transaction()->all($transactionToken);
     * </code>
     *
     * @param string $transactionToken optional
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function all($transactionToken = null)
    {
        $append = '';

        if ($transactionToken) {
            $append = '?since_token='.$transactionToken;
        }

        return $this->client->get('v1/transactions.json'.$append);
    }

    /**
     * Get a list of all referencing transactions from a specific transaction.
     *
     * <code>
     *		Spreedly::transaction($transactionToken)->referencing();
     * </code>
     *
     * @param string $transactionToken optional
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function referencing($offset = null, $count = null, $reverse = false)
    {
        if (!$this->transactionToken) {
            throw new Exceptions\MissingTransactionTokenException();
        }

        $response = $this->get();

        if ($response->success()) {
            if ($urls = $response->response('api_urls.0.referencing_transaction')) {
                if ($reverse) {
                    $urls = array_reverse($urls);
                }

                $transactions = [];

                if (is_array($urls)) {
                    if (isset($offset) || isset($count)) {
                        $urls = array_slice($urls, $offset, $count);
                    }

                    foreach ($urls as $url) {
                        $transactions[] = $this->client->get(str_replace(Client::BASE_URL, '', $url))->response();
                    }

                    $this->client->setResponse([
                        'transactions' => $transactions
                    ]);

                    return $this->client;
                } elseif ($url = $urls) {
                    $transactions = $this->client->request($url)->response();

                    $this->client->setResponse($transactions);

                    return $this->client;
                }
            }

            $this->client->setResponse([]);

            return $this->client;
        }
    }

    /**
     * Get details of a transaction on Spreedly.
     *
     * <code>
     *		Spreedly::transaction($transactionToken)->get();
     * </code>
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function get()
    {
        if (!$this->transactionToken) {
            throw new Exceptions\MissingTransactionTokenException();
        }

        return $this->client->get('v1/transactions/'.$this->transactionToken.'.json');
    }

    /**
     * Get the transcript of a transaction on Spreedly.
     *
     * <code>
     *		Spreedly::transaction($transactionToken)->transcript();
     * </code>
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function transcript()
    {
        if (!$this->transactionToken) {
            throw new Exceptions\MissingTransactionTokenException();
        }

        return $this->client->get('v1/transactions/'.$this->transactionToken.'/transcript');
    }

    /**
     * Capture an authorization.
     *
     * <code>
     *		Spreedly::transaction($transactionToken)->capture();
     * </code>
     *
     * @param int|null $amount
     * @param string   $currency
     * @param array    $data
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function capture($amount = null, $currency = null, array $data = [])
    {
        $params = [
            'transaction' => [],
        ];

        if ($currency !== null) {
            $params['transaction']['currency_code'] = $currency;
        }

        if ($amount > 0) {
            $params['transaction']['amount'] = $amount;
        }

        $params['transaction'] += $data;

        return $this->client->post('v1/transactions/'.$this->transactionToken.'/capture.json', $params);
    }

    /**
     * Can be used to credit a transaction.
     * See docs for more information.
     *
     * <code>
     *		// Reverse a charge.
     *		Spreedly::transaction($transactionToken)->credit();
     *		// Reverse part of a charge by specifying the amount.
     *		Spreedly::transaction($transactionToken)->credit(1.99);
     * </code>
     *
     * @param int|null $amount
     * @param array    $data
     *
     * @return mixed
     *
     * @throws Exceptions\MissingTransactionTokenException
     */
    public function credit($amount = null, $currency = 'USD', array $data = [])
    {
        if (!$this->transactionToken) {
            throw new Exceptions\MissingTransactionTokenException();
        }

        $params = [];

        if ($amount > 0) {
            $params = [
                'transaction' => [
                    'amount' => $amount,
                    'currency_code' => $currency,
                ],
            ];

            $params['transaction'] += $data;
        } elseif ($data) {
            $params['transaction'] = $data;
        }

        return $this->client->post('v1/transactions/'.$this->transactionToken.'/credit.json', $params);
    }

    /**
     * Can be used to make a purchase referencing a transaction.
     * See docs for more information.
     *
     * <code>
     *		// Charge a payment method on the default gateway.
     *		Spreedly::transaction($transactionToken)->purchase(1.99);
     *
     *		// Charge a payment method on the default gateway.
     *		// And in Euros
     *		Spreedly::transaction($transactionToken)->purchase(1.99, 'EUR');
     * </code>
     *
     * @param int    $amount
     * @param string $currency
     * @param array  $data
     *
     * @return mixed
     *
     * @throws Exceptions\MissingTransactionTokenException
     * @throws Exceptions\InvalidAmountException
     */
    public function purchase($amount, $currency = 'USD', array $data = [])
    {
        if (!$this->transactionToken) {
            throw new Exceptions\MissingTransactionTokenException();
        }

        if ($amount <= 0) {
            throw new Exceptions\InvalidAmountException('purchase method requires an amount greater than 0.');
        }

        $params = [
            'transaction' => [
                'amount' => $amount,
                'currency_code' => $currency,
            ],
        ];

        $params['transaction'] += $data;

        return $this->client->post('v1/transactions/'.$this->transactionToken.'/purchase.json', $params);
    }

    /**
     * Can be used to void a transaction.
     * See docs for more information.
     *
     * <code>
     *		// Void a transaction.
     *		Spreedly::transaction($transactionToken)->void();
     * </code>
     *
     * @param array $data
     *
     * @return mixed
     *
     * @throws Exceptions\MissingTransactionTokenException
     */
    public function void(array $data = [])
    {
        if (!$this->transactionToken) {
            throw new Exceptions\MissingTransactionTokenException();
        }

        return $this->client->post('v1/transactions/'.$this->transactionToken.'/void.json', $data);
    }

    /**
     * Can be used to Completes a 3DS 2 transaction in the device fingerprint stage
     * See docs for more information.
     *
     * <code>
     *		// Completes a 3DS 2 transaction in the device fingerprint stage.
     *		Spreedly::transaction($transactionToken)->complete();
     * </code>
     *
     *
     * @return mixed
     *
     * @throws Exceptions\MissingTransactionTokenException
     */
    public function complete()
    {
        if (!$this->transactionToken) {
            throw new Exceptions\MissingTransactionTokenException();
        }

        return $this->client->post('v1/transactions/'.$this->transactionToken.'/complete.json');
    }

    /**
     * @param string $method
     * @param array  $parameters
     *
     * @throws Exceptions\InvalidPaymentMethodException
     */
    public function __call($method, $parameters)
    {
        throw new Exceptions\InvalidPaymentMethodException($method.' is an invalid payment method.');
    }
}
