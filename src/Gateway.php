<?php

namespace Tuurbo\Spreedly;

class Gateway
{
    protected $config;
    protected $client;
    protected $gatewayToken;

    /**
     * Create a Guzzle instance and set token.
     *
     * @param \GuzzleHttp\Client $client
     * @param array              $config
     * @param string             $gatewayToken optional
     */
    public function __construct(Client $client, $config, $gatewayToken = null)
    {
        $this->client = $client;
        $this->config = $config;
        $this->gatewayToken = $gatewayToken ?: $config['gateway'];
    }

    /**
     * Get a list of gateways supported by Spreedly.
     *
     * <code>
     *		Spreedly::gateway()->setup();
     * </code>
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function setup()
    {
        return $this->client->get('v1/gateways_options.json');
    }

    /**
     * Get a list of all gateways you've created on Spreedly.
     *
     * <code>
     *		Spreedly::gateway()->all();
     * </code>
     *
     * @param string $gatewayToken optional
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function all($gatewayToken = null)
    {
        $append = '';

        if ($gatewayToken) {
            $append = '?since_token='.$gatewayToken;
        }

        return $this->client->get('v1/gateways.json'.$append);
    }

    /**
     * Get a specific gateway on Spreedly.
     *
     * <code>
     *		Spreedly::gateway($gatewayToken)->show();
     * </code>
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function show()
    {
        return $this->client->get('v1/gateways/'.$this->gatewayToken.'.json');
    }

    /**
     * Create a new gateway on Spreedly.
     *
     * <code>
     *		// Example gateway for testing
     *		Spreedly::gateway()->create('test');
     * </code>
     *
     * @param string $gateway
     * @param array  $data    optional
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function create($gateway, array $data = null)
    {
        $params = [
            'gateway' => [
                'gateway_type' => $gateway,
            ],
        ];

        if (is_array($data)) {
            $params['gateway'] += $data;
        }

        return $this->client->post('v1/gateways.json', $params);
    }

    /**
     * Update a gateway on Spreedly.
     *
     * <code>
     *		// Example
     *		Spreedly::gateway($gatewayToken)->update(array('
     *			password' => '12345'
     *		));
     * </code>
     *
     * @param array $data
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function update(array $data)
    {
        if (!$this->gatewayToken) {
            throw new Exceptions\MissingGatewayTokenException();
        }

        $params = [
            'gateway' => $data,
        ];

        return $this->client->put('v1/gateways/'.$this->gatewayToken.'.json', $params);
    }

    /**
     * Disable a gateway on Spreedly.
     *
     * <code>
     *		Spreedly::gateway($gatewayToken)->disable();
     * </code>
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function disable()
    {
        if (!$this->gatewayToken) {
            throw new Exceptions\MissingGatewayTokenException();
        }

        return $this->client->put('v1/gateways/'.$this->gatewayToken.'/redact.json');
    }

    /**
     * Handle dynamic calls for \Tuurbo\Spreedly\Payment.
     *
     * Useful when you don't want to use the default gateway.
     *
     * <code>
     *		// Charge a payment method on a non-default gateway.
     *		Spreedly::gateway($gatewayToken)->payment($paymentToken)->purchase();
     * </code>
     *
     * @param string $paymentToken optional
     *
     * @return Payment
     */
    public function payment($paymentToken = null)
    {
        if (!$this->gatewayToken) {
            throw new Exceptions\MissingGatewayTokenException();
        }

        return new Payment($this->client, $this->config, $paymentToken, $this->gatewayToken);
    }

    /**
     * View all transactions of a specific gateway.
     *
     * <code>
     *		Spreedly::gateway($gatewayToken)->transactions();
     *
     *		// Paginate
     *		Spreedly::gateway($gatewayToken)->transactions($transactionToken);
     *
     *		// Paginate and sort
     *		Spreedly::gateway($gatewayToken)->transactions($transactionToken, ['order' => 'desc']);
     * </code>
     *
     * @param string $paymentToken optional
     * @param array  $data         optional
     *
     * @link https://docs.spreedly.com/reference/api/v1/gateways/transactions/
     *
     * @return \Tuurbo\Spreedly\Client
     */
    public function transactions($paymentToken = null, array $data = [])
    {
        if (!$this->gatewayToken) {
            throw new Exceptions\MissingGatewayTokenException();
        }

        $append = '';

        if ($paymentToken) {
            $append = '?since_token='.$paymentToken;
        }

        return $this->client->get('v1/gateways/'.$this->gatewayToken.'/transactions.json'.$append, $data);
    }

    /**
     * Retrieve the gateway token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->gatewayToken;
    }
}
