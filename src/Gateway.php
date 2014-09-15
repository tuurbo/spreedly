<?php namespace Tuurbo\Spreedly;

class Gateway {

	protected $config;
	protected $client;

	public $gatewayToken;

	/**
	 * Create Curl instance and set token.
	 *
	 * @param  string $gatewayToken optional
	 * @return void
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
	 * @return mixed
	 */
	public function setup()
	{
		return $this->client->request('https://core.spreedly.com/v1/gateways.xml', 'options');
	}

	/**
	 * Get a list of all gateways you've created on Spreedly.
	 *
	 * <code>
	 *		Spreedly::gateway()->all();
	 * </code>
	 *
	 * @param  string $gatewayToken optional
	 * @return mixed
	 */
	public function all($gatewayToken = null)
	{
		$append = '';

		if ($gatewayToken)
			$append = '?since_token='.$gatewayToken;

		return $this->client->request('https://core.spreedly.com/v1/gateways.xml'.$append);
	}

	/**
	 * Create a new gateway on Spreedly.
	 *
	 * <code>
	 *		// Example gateway for testing
	 *		Spreedly::gateway()->create('test');
	 * </code>
	 *
	 * @param  string $gateway
	 * @param  array  $data    optional
	 * @return mixed
	 */
	public function create($gateway, array $data = null)
	{
		$params = [
			'gateway' => [
				'gateway_type' => $gateway
			]
		];

		if (is_array($data))
		{
			$params['gateway'] += $data;
		}

		return $this->client->request('https://core.spreedly.com/v1/gateways.xml', 'post', $params);
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
	 * @param  array  $data
	 * @return mixed
	 */
	public function update(array $data)
	{
		if (! $this->gatewayToken)
			throw new Exceptions\MissingGatewayTokenException;

		$params = [
			'gateway' => $data
		];

		return $this->client->request('https://core.spreedly.com/v1/gateways/'.$this->gatewayToken.'.xml', 'put', $params);
	}

	/**
	 * Disable a gateway on Spreedly.
	 *
	 * <code>
	 *		Spreedly::gateway($gatewayToken)->disable();
	 * </code>
	 *
	 * @return mixed
	 */
	public function disable()
	{
		if (! $this->gatewayToken)
			throw new Exceptions\MissingGatewayTokenException;

		return $this->client->request('https://core.spreedly.com/v1/gateways/'.$this->gatewayToken.'/redact.xml', 'put');
	}

	public function getToken()
	{
		return $this->gatewayToken;
	}

	/**
	 * Magic Method for calling Spreedly/Payment instance.
	 *
	 * Useful when you don't want to use the default gateway.
	 *
	 * <code>
	 *		// Charge a payment method on a non-default gateway.
	 *		Spreedly::gateway($gatewayToken)->payment($paymentToken)->purchase();
	 * </code>
	 */
	public function __call($method, $params)
	{
		if ($method == 'payment')
		{
			if (! $this->gatewayToken)
				throw new Exceptions\MissingGatewayTokenException;

			return new Payment($this->client, $this->config, isset($params[0]) ? $params[0] : null, $this->gatewayToken);
		}

		if (! in_array($method, ['payment']))
			throw new Exceptions\InvalidPaymentMethodException($method.' is an invalid method.');
	}

}