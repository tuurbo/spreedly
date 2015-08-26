<?php namespace Tuurbo\Spreedly;

class Transaction {

	protected $curl;

	public $transactionToken;

	/**
	 * Create a Guzzle instance and set token.
	 *
	 * @param  \GuzzleHttp\Client $client
	 * @param  array $config
	 * @param  string $transactionToken optional
	 * @return void
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
	 * @param  string $transactionToken optional
	 * @return \Tuurbo\Spreedly\Client
	 */
	public function all($transactionToken = null)
	{
		$append = '';

		if ($transactionToken)
			$append = '?since_token='.$transactionToken;

		return $this->client->request('https://core.spreedly.com/v1/transactions.xml'.$append);
	}

	/**
	 * Get a list of all referencing transactions from a specific transaction.
	 *
	 * <code>
	 *		Spreedly::transaction($transactionToken)->referencing();
	 * </code>
	 *
	 * @param  string $transactionToken optional
	 * @return \Tuurbo\Spreedly\Client
	 */
	public function referencing($offset = null, $count = null, $reverse = false)
	{
		if (! $this->transactionToken)
			throw new Exceptions\MissingTransactionTokenException;

		$response = $this->get();

		if ($response->success())
		{
			$response = $response->response();

			if (isset($response['api_urls']['referencing_transaction']))
			{
				$urls = $response['api_urls']['referencing_transaction'];

				if ($reverse)
				{
					$urls = array_reverse($urls);
				}

				$transactions = [];

				if (is_array($urls))
				{
					if (isset($offset) || isset($count))
					{
						$urls = array_slice($urls, $offset, $count);
					}

					foreach ($urls as $url)
					{
						$transactions[] = $this->client->request($url)->response();
					}

					$this->client->setResponse($transactions);

					return $this->client;
				}
				else if ($url = $urls)
				{
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
		if (! $this->transactionToken)
			throw new Exceptions\MissingTransactionTokenException;

		return $this->client->request('https://core.spreedly.com/v1/transactions/'.$this->transactionToken.'.xml');
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
		if (! $this->transactionToken)
			throw new Exceptions\MissingTransactionTokenException;

		return $this->client->request('https://core.spreedly.com/v1/transactions/'.$this->transactionToken.'/transcript');
	}

	/**
	 * Capture an authorization
	 *
	 * <code>
	 *		Spreedly::transaction($transactionToken)->capture();
	 * </code>
	 *
	 * @param  string|numeric $amount
	 * @param  string $currency
	 * @param  array $data
	 * @return \Tuurbo\Spreedly\Client
	 */
	public function capture($amount = null, $currency = null, array $data = [])
	{
		$params = [
			'transaction' => [
				'currency_code' => $currency ?: 'USD'
			]
		];

		if ($amount > 0)
			$params['transaction']['amount'] = $amount * 100;

		$params['transaction'] += $data;

		return $this->client->request('https://core.spreedly.com/v1/transactions/'.$this->transactionToken.'/capture.xml', 'post', $params);
	}

	/**
	 * Handle dynamic calls for referencing a transaction.
	 *
	 * Can be used to purchase, void, credit.
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
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		if (! in_array($method, ['purchase', 'void', 'credit']))
			throw new Exceptions\InvalidPaymentMethodException($method.' is an invalid payment method.');

		if (! $this->transactionToken)
			throw new Exceptions\MissingTransactionTokenException;

		if ($method == 'purchase' && ! isset($parameters[0]) || (isset($parameters[0]) && $parameters[0] <= 0))
			throw new Exceptions\InvalidAmountException($method.' method requires an amount greater than 0.');

		$params = [];

		if (in_array($method, ['purchase', 'credit']))
		{

			if(isset($parameters[0])) {
				$params = [
					'transaction' => [
						'amount' => $parameters[0] * 100
					]
				];
			}

			if ($method == 'purchase')
				$params['transaction']['currency_code'] = isset($parameters[1]) ? $parameters[1] : 'USD';

			if (isset($parameters[2]) && is_array($parameters[2]))
				$params += $parameters[2];
		}

		return $this->client->request('https://core.spreedly.com/v1/transactions/'.$this->transactionToken.'/'.$method.'.xml', 'post', $params);
	}

}
