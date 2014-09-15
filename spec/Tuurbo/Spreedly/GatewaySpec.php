<?php namespace spec\Tuurbo\Spreedly;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Tuurbo\Spreedly\Client;

class GatewaySpec extends ObjectBehavior {

	const GATEWAY_TOKEN = '...GATEWAY_TOKEN...';
	const PAYMENT_TOKEN = '...PAYMENT_TOKEN...';

	function let(Client $client)
	{
		$this->beConstructedWith($client, [], self::GATEWAY_TOKEN);

		$this->shouldHaveType('Tuurbo\Spreedly\Gateway');
	}

	function it_requests_a_list_of_supported_gateways($client)
	{
		$client->request('https://core.spreedly.com/v1/gateways.xml')
			->shouldBeCalled()
			->willReturn($client);

		$this->all()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
	}

	function it_requests_all_gateways_you_have_created($client)
	{
		$client->request('https://core.spreedly.com/v1/gateways.xml')
			->shouldBeCalled()
			->willReturn($client);

		$this->all()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
	}

	function it_requests_all_gateways_after_a_specified_token($client)
	{
		$client->request('https://core.spreedly.com/v1/gateways.xml?since_token='.self::GATEWAY_TOKEN)
			->shouldBeCalled()
			->willReturn($client);

		$this->all(self::GATEWAY_TOKEN)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
	}

	function it_creates_a_gateway($client)
	{
		$client->request('https://core.spreedly.com/v1/gateways.xml', 'post', Argument::type('array'))
			->shouldBeCalled()
			->willReturn($client);

		$this->create('PAYPAL')->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
	}

	function it_updates_a_gateway($client)
	{
		$client->request('https://core.spreedly.com/v1/gateways/'.self::GATEWAY_TOKEN.'.xml', 'put', Argument::type('array'))
			->shouldBeCalled()
			->willReturn($client);

		$this->update([
				'password' => 'test'
			])->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
	}

	function it_disables_a_gateway($client)
	{
		$client->request('https://core.spreedly.com/v1/gateways/'.self::GATEWAY_TOKEN.'/redact.xml', 'put')
			->shouldBeCalled()
			->willReturn($client);

		$this->disable()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
	}

	function it_returns_the_gateway_token($client)
	{
		$this->getToken()->shouldReturn(self::GATEWAY_TOKEN);
	}

	function it_returns_a_payment_instance($client)
	{
		$this->payment()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Payment');
	}

}