<?php namespace spec\Tuurbo\Spreedly;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Tuurbo\Spreedly\Client;

class TransactionSpec extends ObjectBehavior {

	const GATEWAY_TOKEN = '...GATEWAY_TOKEN...';
	const PAYMENT_TOKEN = '...PAYMENT_TOKEN...';
	const TRANSACTION_TOKEN = '...TRANSACTION_TOKEN...';

	function let(Client $client)
	{
		$this->beConstructedWith($client, [], self::TRANSACTION_TOKEN);

		$this->shouldHaveType('Tuurbo\Spreedly\Transaction');
	}

	function it_requests_all_transactions_you_have_created($client)
	{
		$client->request('https://core.spreedly.com/v1/transactions.xml')
			->shouldBeCalled()
			->willReturn($client);

		$this->all()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
	}

	function it_gets_a_single_transaction($client)
	{
		$client->request('https://core.spreedly.com/v1/transactions/'.self::TRANSACTION_TOKEN.'.xml')
			->shouldBeCalled()
			->willReturn($client);

		$this->get()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
	}

	function it_makes_a_purchase($client)
	{
		$amount = 9.99;

		$data = [
			'transaction' => [
				'amount' => $amount * 100,
				'currency_code' => 'USD'
			]
		];

		$client->request('https://core.spreedly.com/v1/transactions/'.self::TRANSACTION_TOKEN.'/purchase.xml', 'post', $data)
			->shouldBeCalled()
			->willReturn($client);

		$this->purchase($amount)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
	}

	function it_throws_an_exception_when_trying_to_make_a_purchase_with_an_invalid_amount()
	{
		$this->shouldThrow('Tuurbo\Spreedly\Exceptions\InvalidAmountException')
			->duringPurchase(-1);
	}

	function it_voids_a_purchase($client)
	{
		$data = [];

		$client->request('https://core.spreedly.com/v1/transactions/'.self::TRANSACTION_TOKEN.'/void.xml', 'post', $data)
			->shouldBeCalled()
			->willReturn($client);

		$this->void()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
	}

	function it_credits_a_purchase($client)
	{
		$amount = 9.99;

		$data = [
			'transaction' => [
				'amount' => $amount * 100
			]
		];

		$client->request('https://core.spreedly.com/v1/transactions/'.self::TRANSACTION_TOKEN.'/credit.xml', 'post', $data)
			->shouldBeCalled()
			->willReturn($client);

		$this->credit($amount)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
	}

	function it_credits_a_purchase_with_no_amount_specified($client)
	{
		$client->request('https://core.spreedly.com/v1/transactions/'.self::TRANSACTION_TOKEN.'/credit.xml', 'post', [])
			->shouldBeCalled()
			->willReturn($client);

		$this->credit()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
	}

	function it_credits_a_purchase_with_no_amount_specified_and_with_extra_data($client)
	{
		$extra = [
			"order_id" => 12345
		];

		$client->request('https://core.spreedly.com/v1/transactions/'.self::TRANSACTION_TOKEN.'/credit.xml', 'post', ['transaction' => $extra])
			->shouldBeCalled()
			->willReturn($client);

		$this->credit(null, $extra)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
	}

	function it_captures_an_authorized_amount($client)
	{
		$data = [
			'transaction' => [
				'currency_code' => 'USD'
			]
		];

		$client->request('https://core.spreedly.com/v1/transactions/'.self::TRANSACTION_TOKEN.'/capture.xml', 'post', $data)
			->shouldBeCalled()
			->willReturn($client);

		$this->capture()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
	}

	function it_captures_a_specific_authorized_amount($client)
	{
		$amount = 9.99;

		$data = [
			'transaction' => [
				'amount' => $amount * 100,
				'currency_code' => 'USD'
			]
		];

		$client->request('https://core.spreedly.com/v1/transactions/'.self::TRANSACTION_TOKEN.'/capture.xml', 'post', $data)
			->shouldBeCalled()
			->willReturn($client);

		$this->capture($amount)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
	}

	function it_throws_invalid_method_exception()
	{
		$this->shouldThrow('Tuurbo\Spreedly\Exceptions\InvalidPaymentMethodException')
			->during('undefinedMethod', ['some_param']);
	}

}