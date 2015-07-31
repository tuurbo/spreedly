<?php namespace Tuurbo\Spreedly;

class Payment {

	protected $config;
	protected $client;

	public $gatewayToken;
	public $paymentToken;

	/**
	 * Create a Guzzle instance and set tokens.
	 *
	 * @param  \GuzzleHttp\Client $client
	 * @param  array $config
	 * @param  string $paymentToken optional
	 * @param  string $gatewayToken optional
	 * @return void
	 */
	public function __construct(Client $client, $config, $paymentToken = null, $gatewayToken = null)
	{
		$this->client = $client;
		$this->gatewayToken = $gatewayToken;
		$this->paymentToken = $paymentToken;
	}

	/**
	 * Get a list of all payment methods you've created on Spreedly.
	 *
	 * @param  string $paymentToken optional
	 * @return \Tuurbo\Spreedly\Client
	 */
	public function all($paymentToken = null)
	{
		$append = '';

		if ($paymentToken)
			$append = '?since_token='.$paymentToken;

		return $this->client->request('https://core.spreedly.com/v1/payment_methods.xml'.$append);
	}

	/**
	 * Create a payment method on Spreedly.
	 *
	 * @param  array  $data
	 * @return \Tuurbo\Spreedly\Client
	 */
	public function create(array $data)
	{
		$params = [
			'payment_method' => $data
		];

		return $this->client->request('https://core.spreedly.com/v1/payment_methods.xml', 'post', $params);
	}

	/**
	 * Update a payment method on Spreedly.
	 *
	 * @param  array  $data
	 * @return \Tuurbo\Spreedly\Client
	 */
	public function update(array $data)
	{
		if (! $this->paymentToken)
			throw new Exceptions\MissingPaymentTokenException;

		$params = [
			'payment_method' => $data
		];

		return $this->client->request('https://core.spreedly.com/v1/payment_methods/'.$this->paymentToken.'.xml', 'put', $params);
	}

	/**
	 * Retain a payment method on Spreedly.
	 *
	 * @return \Tuurbo\Spreedly\Client
	 */
	public function retain()
	{
		if (! $this->paymentToken)
			throw new Exceptions\MissingPaymentTokenException;

		return $this->client->request('https://core.spreedly.com/v1/payment_methods/'.$this->paymentToken.'/retain.xml', 'put');
	}

	/**
	 * Store/Vault a payment method to a third party, like Braintree or Quickpay.
	 *
	 * @return \Tuurbo\Spreedly\Client
	 */
	public function store()
	{
		if (! $this->paymentToken)
			throw new Exceptions\MissingPaymentTokenException;

		if (! $this->gatewayToken)
			throw new Exceptions\MissingGatewayTokenException;

		$params = [
			'transaction' => [
				'payment_method_token' => $this->paymentToken
			]
		];

		return $this->client->request('https://core.spreedly.com/v1/gateways/'.$this->gatewayToken.'/store.xml', 'post', $params);
	}

	/**
	 * Get details of a payment method on Spreedly.
	 *
	 * @return \Tuurbo\Spreedly\Client
	 */
	public function get()
	{
		if (! $this->paymentToken)
			throw new Exceptions\MissingPaymentTokenException;

		return $this->client->request('https://core.spreedly.com/v1/payment_methods/'.$this->paymentToken.'.xml');
	}

	/**
	 * Disable a payment method stored on Spreedly.
	 *
	 * @return \Tuurbo\Spreedly\Client
	 */
	public function disable()
	{
		if (! $this->paymentToken)
			throw new Exceptions\MissingPaymentTokenException;

		return $this->client->request('https://core.spreedly.com/v1/payment_methods/'.$this->paymentToken.'/redact.xml', 'put');
	}

	/**
	 * Create a general credit
	 *
	 * <code>
	 *		Spreedly::payment($paymentToken)->capture();
	 * </code>
	 *
	 * @param  string|numeric $amount
	 * @param  string $currency
	 * @param  array $data
	 * @return \Tuurbo\Spreedly\Client
	 */
	public function generalCredit($amount, $currency = null, array $data = [])
	{
		$params = [
			'transaction' => [
				'payment_method_token' => $this->paymentToken,
				'amount' => $amount,
				'currency_code' => $currency ?: 'USD'
			]
		];

		$params['transaction'] += $data;

		return $this->client->request('https://core.spreedly.com/v1/gateways/'.$this->gatewayToken.'/general_credit.xml', 'post', $params);
	}

	/**
	 * Ask a gateway if a payment method is in good standing.
	 *
	 * @param  array  $params
	 * @return \Tuurbo\Spreedly\Client
	 * @link https://docs.spreedly.com/reference/api/v1/gateways/verify/
	 */
	public function verify($retain = false, array $data = null)
	{
		if (! $this->paymentToken)
			throw new Exceptions\MissingPaymentTokenException;

		if (! $this->gatewayToken)
			throw new Exceptions\MissingGatewayTokenException;

		$params = [
			'transaction' => [
				'payment_method_token' => $this->paymentToken,
				'retain_on_success' => $retain
			]
		];

		if (is_array($data))
		{
			$params['transaction'] += $data;
		}

		return $this->client->request('https://core.spreedly.com/v1/gateways/'.$this->gatewayToken.'/verify.xml', 'post', $params);
	}

	/**
	 * View all transactions of a specific payment method.
	 *
	 * @param  string $paymentToken optional
	 * @param  array $data optional
	 * @return \Tuurbo\Spreedly\Client
	 */
	public function transactions($paymentToken = null, array $data = [])
	{
		if (! $this->paymentToken)
			throw new Exceptions\MissingPaymentTokenException;

		$append = '';

		if ($paymentToken)
			$append = '?since_token='.$paymentToken;

		return $this->client->request('https://core.spreedly.com/v1/payment_methods/'.$this->paymentToken.'/transactions.xml'.$append, 'get', $data);
	}

	/**
	 * Magic Method for payment methods.
	 *
	 * Can be used to charge or authorize.
	 *
	 * <code>
	 *		// Charge a payment method on the default gateway.
	 *		Spreedly::payment($paymentToken)->purchase(1.99);
	 *
	 *		// Set currency to Euros.
	 *		Spreedly::payment($paymentToken)->purchase(1.99, 'EUR');
	 * </code>
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		if (! in_array($method, ['purchase', 'authorize']))
			throw new Exceptions\InvalidPaymentMethodException($method.' is an invalid payment method.');

		if (! $this->gatewayToken)
			throw new Exceptions\MissingGatewayTokenException;

		if (! $this->paymentToken)
			throw new Exceptions\MissingPaymentTokenException;

		if (! isset($parameters[0]) || $parameters[0] <= 0)
			throw new Exceptions\InvalidAmountException($method.' method requires an amount greater than 0.');

		$params = [
			'transaction' => [
				'payment_method_token' => $this->paymentToken,
				'amount' => $parameters[0] * 100,
				'currency_code' => isset($parameters[1]) ? $parameters[1] : 'USD'
			]
		];

		if (isset($parameters[2]) && is_array($parameters[2]))
			$params['transaction'] += $parameters[2];

		return $this->client->request('https://core.spreedly.com/v1/gateways/'.$this->gatewayToken.'/'.$method.'.xml', 'post', $params);
	}

}
