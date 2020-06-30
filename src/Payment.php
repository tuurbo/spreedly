<?php

namespace Tuurbo\Spreedly;

class Payment
{
    protected $config;
    protected $client;
    protected $gatewayToken;
    protected $paymentToken;

    /**
     * Create a Guzzle instance and set tokens.
     *
     * @param \GuzzleHttp\Client $client
     * @param array              $config
     * @param string             $paymentToken optional
     * @param string             $gatewayToken optional
     */
    public function __construct(Client $client, $config, $paymentToken = null, $gatewayToken = null)
    {
        $this->client = $client;
        $this->config = $config;
        $this->gatewayToken = $gatewayToken;
        $this->paymentToken = $paymentToken;
    }

    /**
     * Get a list of all payment methods you've created on Spreedly.
     *
     * @param string $paymentToken optional
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function all($paymentToken = null)
    {
        $append = '';

        if ($paymentToken) {
            $append = '?since_token='.$paymentToken;
        }

        return $this->client->get('v1/payment_methods.json'.$append);
    }

    /**
     * Create a payment method on Spreedly.
     *
     * @param array $data
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function create(array $data)
    {
        $params = [
            'payment_method' => $data,
        ];

        return $this->client->post('v1/payment_methods.json', $params);
    }

    /**
     * Update a payment method on Spreedly.
     *
     * @param array $data
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function update(array $data)
    {
        if (!$this->paymentToken) {
            throw new Exceptions\MissingPaymentTokenException();
        }

        $params = [
            'payment_method' => $data,
        ];

        return $this->client->put('v1/payment_methods/'.$this->paymentToken.'.json', $params);
    }

    /**
     * Retain a payment method on Spreedly.
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function retain()
    {
        if (!$this->paymentToken) {
            throw new Exceptions\MissingPaymentTokenException();
        }

        return $this->client->put('v1/payment_methods/'.$this->paymentToken.'/retain.json');
    }

    /**
     * Update a credit card’s verification value.
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function recache($cvv)
    {
        if (!$this->paymentToken) {
            throw new Exceptions\MissingPaymentTokenException();
        }

        $params = [
            'payment_method' => [
                'credit_card' => [
                    'verification_value' => $cvv,
                ],
            ],
        ];

        return $this->client->post('v1/payment_methods/'.$this->paymentToken.'/recache.json', $params);
    }

    /**
     * Store/Vault a payment method to a third party, like Braintree or Quickpay.
     *
     * @param string $currency optional
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function store($currency = null)
    {
        if (!$this->paymentToken) {
            throw new Exceptions\MissingPaymentTokenException();
        }

        if (!$this->gatewayToken) {
            throw new Exceptions\MissingGatewayTokenException();
        }

        $params = [
            'transaction' => [
                'payment_method_token' => $this->paymentToken,
            ],
        ];

        if ($currency) {
            $params['transaction']['currency_code'] = $currency;
        }

        return $this->client->post('v1/gateways/'.$this->gatewayToken.'/store.json', $params);
    }

    /**
     * Get details of a payment method on Spreedly.
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function get()
    {
        if (!$this->paymentToken) {
            throw new Exceptions\MissingPaymentTokenException();
        }

        return $this->client->get('v1/payment_methods/'.$this->paymentToken.'.json');
    }

    /**
     * Disable a payment method stored on Spreedly.
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function disable()
    {
        if (!$this->paymentToken) {
            throw new Exceptions\MissingPaymentTokenException();
        }

        return $this->client->put('v1/payment_methods/'.$this->paymentToken.'/redact.json');
    }

    /**
     * Create a general credit.
     *
     * <code>
     *		Spreedly::payment($paymentToken)->capture();
     * </code>
     *
     * @param int    $amount
     * @param string $currency
     * @param array  $data
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function generalCredit($amount, $currency = 'USD', array $data = [])
    {
        return $this->createTransaction('general_credit', $amount, $currency, $data);
    }

    /**
     * Ask a gateway if a payment method is in good standing.
     *
     * @param array $params
     *
     * @return \Tuurbo\Spreedly\Client
     *
     * @link https://docs.spreedly.com/reference/api/v1/gateways/verify/
     */
    public function verify($retain = false, array $data = [])
    {
        if (!$this->paymentToken && !isset($data['credit_card']['number'])) {
            throw new Exceptions\MissingPaymentTokenException();
        }

        if (!$this->gatewayToken) {
            throw new Exceptions\MissingGatewayTokenException();
        }

        $params = [
            'transaction' => [
                'payment_method_token' => $this->paymentToken,
                'retain_on_success' => $retain,
            ],
        ];

        if ($data) {
            $params['transaction'] += $data;
        }

        return $this->client->post('v1/gateways/'.$this->gatewayToken.'/verify.json', $params);
    }

    /**
     * View all transactions of a specific payment method.
     *
     * @param string $paymentToken optional
     * @param array  $data         optional
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function transactions($paymentToken = null, array $data = [])
    {
        if (!$this->paymentToken) {
            throw new Exceptions\MissingPaymentTokenException();
        }

        $append = '';

        if ($paymentToken) {
            $append = '?since_token='.$paymentToken;
        }

        return $this->client->get('v1/payment_methods/'.$this->paymentToken.'/transactions.json'.$append, $data);
    }

    /**
     * Can be used to authorize.
     *
     * <code>
     *		// Authorize a payment method on the default gateway.
     *		Spreedly::payment($paymentToken)->authorize(1.99);
     *
     *		// Set currency to Euros.
     *		Spreedly::payment($paymentToken)->authorize(1.99, 'EUR');
     * </code>
     *
     * @param int    $amount
     * @param string $currency
     * @param array  $data
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function authorize($amount, $currency = 'USD', array $data = [])
    {
        return $this->createTransaction('authorize', $amount, $currency, $data);
    }

    /**
     * Can be used to charge.
     *
     * <code>
     *		// Charge a payment method on the default gateway.
     *		Spreedly::payment($paymentToken)->purchase(1.99);
     *
     *		// Set currency to Euros.
     *		Spreedly::payment($paymentToken)->purchase(1.99, 'EUR');
     * </code>
     *
     * @param int    $amount
     * @param string $currency
     * @param array  $data
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function purchase($amount, $currency = 'USD', array $data = [])
    {
        return $this->createTransaction('purchase', $amount, $currency, $data);
    }

    /**
     * Can be used to charge or authorize.
     *
     * @param string $method
     * @param int    $amount
     * @param string $currency
     * @param array  $data
     *
     * @return \Tuurbo\Spreedly\Client
     *
     * @throws Exceptions\InvalidAmountException
     * @throws Exceptions\MissingGatewayTokenException
     * @throws Exceptions\MissingPaymentTokenException
     */
    protected function createTransaction($method, $amount, $currency, array $data)
    {
        if (!$this->gatewayToken) {
            throw new Exceptions\MissingGatewayTokenException();
        }

        if (!$this->paymentToken) {
            throw new Exceptions\MissingPaymentTokenException();
        }

        if ($amount <= 0) {
            throw new Exceptions\InvalidAmountException($method.' method requires an amount greater than 0.');
        }

        $params = [
            'transaction' => [
                'payment_method_token' => $this->paymentToken,
                'amount' => $amount,
                'currency_code' => $currency,
            ],
        ];

        if ($data) {
            $params['transaction'] += $data;
        }

        return $this->client->post('v1/gateways/'.$this->gatewayToken.'/'.$method.'.json', $params);
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
