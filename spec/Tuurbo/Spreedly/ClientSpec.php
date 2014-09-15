<?php namespace spec\Tuurbo\Spreedly;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GuzzleHttp\Client;

class ClientSpec extends ObjectBehavior {

	const GATEWAY_TOKEN = '...GATEWAY_TOKEN...';
	const PAYMENT_TOKEN = '...PAYMENT_TOKEN...';
	const HTTP_URL = 'http://example.com';

	function let(Client $client)
	{
		$config = [
			'key' => '12345',
			'secret' => '67890'
		];

		$this->beConstructedWith($client, $config);

		$this->shouldHaveType('Tuurbo\Spreedly\Client');
	}

	function it_returns_an_array()
	{
		$array = [
			'gateway' => [
				'paypal' => [
					'test' => 2
				]
			]
		];

		$this->setResponse($array);

		$this->response()->shouldReturn($array);
	}

	function it_returns_an_array_without_any_keys_containing_an_at_symbol_attribute()
	{
		$this->setResponse([
			'gateway' => [
				'paypal' => [
					'@attributes' => 1,
					'test' => 2
				]
			]
		]);

		$this->response()->shouldReturn([
			'gateway' => [
				'paypal' => [
					'test' => 2
				]
			]
		]);
	}

	function it_return_an_instance_of_itself($client)
	{
		$client->get(self::HTTP_URL, Argument::type('array'))
			->shouldBeCalled()
			->willReturn(new ClientStub200);

		$this->request(self::HTTP_URL, 'get')->shouldReturn($this);
	}

	function it_throws_an_exception_if_http_response_is_404($client)
	{
		$client->get(self::HTTP_URL, Argument::type('array'))
			->shouldBeCalled()
			->willReturn(new ClientStub404);

		$this->shouldThrow('Tuurbo\Spreedly\Exceptions\NotFoundHttpException')
			->duringRequest(self::HTTP_URL, 'get');
	}

	function it_throws_an_exception_if_the_config_is_invalid($client)
	{
		$this->beConstructedWith($client, []);

		$this->shouldThrow('Exception')
			->duringRequest(self::HTTP_URL, 'get');
	}

	function it_throws_an_exception_if_given_an_invalid_http_method()
	{
		$this->shouldThrow('Tuurbo\Spreedly\Exceptions\InvalidRequestMethodException')
			->duringRequest(self::HTTP_URL, 'INVALID_METHOD');
	}

}

class ClientStub200 {

	function getStatusCode()
	{
		return 200;
	}

	function getHeader()
	{
		return 'application/xml; charset=utf-8';
	}

	function xml()
	{
		return [];
	}

}

class ClientStub404 {

	function getStatusCode()
	{
		return 404;
	}

	function getHeader()
	{
		return 'application/text; charset=utf-8';
	}

	function xml()
	{
		return [];
	}

}