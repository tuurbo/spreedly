<?php namespace Tuurbo\Spreedly;

use GuzzleHttp\Client as Guzzle;

class Spreedly {

	protected $config;

	public function __construct($config)
	{
		$this->setConfig($config);
	}

	/**
	 * Create a Gateway instance.
	 *
	 * @param  string  $token   optional
	 * @return Spreedly\Gateway
	 */
	public function gateway($token = null)
	{
		return new Gateway($this->client(), $this->config, $token);
	}

	/**
	 * Create a Payment instance.
	 *
	 * @param  string  $token   optional
	 * @return Spreedly\Payment
	 */
	public function payment($paymentToken = null)
	{
		return new Payment($this->client(), $this->config, $paymentToken, $this->gateway()->getToken());
	}

	/**
	 * Create a Transaction instance.
	 *
	 * @param  string  $token   optional
	 * @return Spreedly\Transaction
	 */
	public function transaction($token = null)
	{
		return new Transaction($this->client(), $this->config, $token);
	}

	/**
	 * Get config and convert to object
	 *
	 * @return object
	 */
	public function setConfig(array $config = null)
	{
		$this->config = $config;

		$this->checkConfig();

		return $this;
	}

	protected function checkConfig()
	{
		if (! isset($this->config['key']))
			throw new Exceptions\InvalidConfigException;

		if (! isset($this->config['secret']))
			throw new Exceptions\InvalidConfigException;
	}

	protected function client()
	{
		return new Client(new Guzzle, $this->config);
	}

}
