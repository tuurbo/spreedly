<?php namespace Tuurbo\Spreedly;

use GuzzleHttp\ClientInterface as GuzzleInterface;
use GuzzleHttp\Message\Response as GuzzleResponse;

class Client {

	protected $response;

	protected $methods = ['get', 'post', 'put', 'options'];

	/**
	 * Set config
	 *
	 * @param  \GuzzleHttp\ClientInterface $client
	 * @param  array $config
	 * @return void
	 */
	public function __construct(GuzzleInterface $client, $config)
	{
		$this->client = $client;
		$this->config = $config;
	}

	/**
	 * Create the CURL request
	 *
	 * @param  string  $url
	 * @param  string  $method  optional
	 * @param  array   $data    optional
	 * @return object
	 */
	public function request($url, $method = 'get', array $data = null)
	{
		if (! in_array($method, $this->methods))
		{
			throw new Exceptions\InvalidRequestMethodException;
		}

		$response = $this->client->{$method}($url, $this->buildData($data));

		if (! in_array($response->getStatusCode(), [200, 201]))
		{
			if ($response->getStatusCode() == 404)
			{
				if ($response->getHeader('Content-Type') !== 'application/xml; charset=utf-8')
				{
					throw new Exceptions\NotFoundHttpException;
				}
			}

			$this->setResponse($response);

			$this->status = 'error';

			return $this;
		}

		$this->setResponse($response);

		return $this;
	}

	/**
	 * Set the response from Guzzle
	 *
	 * @param  mixed  $response
	 * @return void
	 */
	public function setResponse($response)
	{
		if ($response instanceof GuzzleResponse)
		{
			if ($response->getHeader('Content-Type') === 'application/xml; charset=utf-8')
			{
				$response = $response->xml();
			}
			else
			{
				$response = ['raw' => (string) $response->getBody()];
			}
		}

		$response = json_decode(json_encode((array) $response), true);

		$this->response = $this->cleanArray($response);

		if (isset($this->response['error']) || (isset($this->response['succeeded']) && $this->response['succeeded'] == 'false'))
		{
			$this->status = 'error';

			return $this;
		}

		$this->status = 'success';
	}

	/**
	 * Get the response from Guzzle as an array
	 *
	 * @param  string  $key
	 * @return array
	 */
	public function response($key = null)
	{
		$array = $this->response;

		if (is_null($key)) return $array;

		if (isset($this->response[$key])) return $array[$key];

		foreach (explode('.', $key) as $segment)
		{
			if ( ! is_array($array) || ! array_key_exists($segment, $array))
			{
				return null;
			}

			$array = $array[$segment];
		}

		return $array;
	}

	public function declined()
	{
		return $this->response('message');
	}

	/**
	 * Check if payment purchase has declined
	 *
	 * @return bool
	 */
	public function hasDeclined()
	{
		return !! $this->declined();
	}

	/**
	 * Get the transaction token
	 *
	 * @return string
	 */
	public function transactionToken()
	{
		return $this->response('token');
	}

	/**
	 * Get the payment token
	 *
	 * @return string
	 */
	public function paymentToken()
	{
		return $this->response('payment_method.token');
	}

	/**
	 * Get an array or string of errors
	 *
	 * @return array|string
	 */
	public function errors($string = false)
	{
		if (! isset($this->response['error']))
		{
			return null;
		}

		$errors = is_array($this->response['error']) ? $this->response['error'] : [$this->response['error']];

		if ($string == true)
		{
			return implode(', ', $errors);
		}

		return $errors;
	}

	/**
	 * Check if call returned errors
	 *
	 * @return bool
	 */
	public function hasErrors()
	{
		return isset($this->response['error']);
	}

	/**
	 * Check if call was successfull
	 *
	 * @return bool
	 */
	public function success()
	{
		return $this->status == 'success';
	}

	/**
	 * Check if call failed or purchase declined
	 *
	 * @return bool
	 */
	public function fails()
	{
		return $this->status == 'error';
	}

	protected function buildData($data)
	{
		$xml = $data ? $this->arrayToXml($data) : null;

		return [
			'auth' => [
				$this->config['key'],
				$this->config['secret']
			],
			'timeout' => isset($this->config['timeout']) ? $this->config['timeout'] : 15,
			'connect_timeout' => isset($this->config['connect_timeout']) ? $this->config['connect_timeout'] : 10,
			'exceptions' => false,
			'headers' => [
				'Content-type' => 'application/xml',
				'Content-Length' => $xml ? strlen($xml) : 0
			],
			'body' => $xml
		];
	}

	protected function arrayToXml($array)
	{
		$xml = '';

		foreach($array as $element => $value)
		{
			if (is_array($value))
			{
				$xml .= "<$element>". $this->arrayToXml($value) ."</$element>";
			}
			else if ($value == '')
			{
				$xml .= "<$element />";
			}
			else
			{
				$xml .= "<$element>". htmlentities($value) ."</$element>";
			}
		}

		return $xml;
	}

	protected function cleanArray($array)
	{
		return array_map(function($val){

			if (is_array($val))
			{
				if (key($val) == '@attributes')
					unset($val['@attributes']);

				if (empty($val))
					return null;

				return $this->cleanArray($val);
			}

			return $val;

		}, $array);
	}

}