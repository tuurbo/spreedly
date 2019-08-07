<?php

namespace spec\Tuurbo\Spreedly;

use PhpSpec\ObjectBehavior;
use Tuurbo\Spreedly\Client;

class TransactionSpec extends ObjectBehavior
{
    const GATEWAY_TOKEN = '...GATEWAY_TOKEN...';
    const PAYMENT_TOKEN = '...PAYMENT_TOKEN...';
    const TRANSACTION_TOKEN = '...TRANSACTION_TOKEN...';

    public function let(Client $client)
    {
        $this->beConstructedWith($client, [], self::TRANSACTION_TOKEN);

        $this->shouldHaveType('Tuurbo\Spreedly\Transaction');
    }

    public function it_requests_all_transactions_you_have_created($client)
    {
        $client->get('v1/transactions.json')
            ->shouldBeCalled()
            ->willReturn($client);

        $this->all()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_gets_a_single_transaction($client)
    {
        $client->get('v1/transactions/'.self::TRANSACTION_TOKEN.'.json')
            ->shouldBeCalled()
            ->willReturn($client);

        $this->get()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_makes_a_purchase($client)
    {
        $amount = 9.99;

        $data = [
            'transaction' => [
                'amount' => $amount,
                'currency_code' => 'USD',
            ],
        ];

        $client->post('v1/transactions/'.self::TRANSACTION_TOKEN.'/purchase.json', $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->purchase($amount)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_throws_an_exception_when_trying_to_make_a_purchase_with_an_invalid_amount()
    {
        $this->shouldThrow('Tuurbo\Spreedly\Exceptions\InvalidAmountException')
            ->duringPurchase(-1);
    }

    public function it_voids_a_purchase($client)
    {
        $data = [];

        $client->post('v1/transactions/'.self::TRANSACTION_TOKEN.'/void.json', $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->void()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_credits_a_purchase($client)
    {
        $amount = 9.99;

        $data = [
            'transaction' => [
                'amount' => $amount,
                'currency_code' => 'USD',
            ],
        ];

        $client->post('v1/transactions/'.self::TRANSACTION_TOKEN.'/credit.json', $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->credit($amount)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_credits_a_purchase_with_no_amount_specified($client)
    {
        $client->post('v1/transactions/'.self::TRANSACTION_TOKEN.'/credit.json', [])
            ->shouldBeCalled()
            ->willReturn($client);

        $this->credit()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_credits_a_purchase_with_no_amount_specified_and_with_extra_data($client)
    {
        $extra = [
            'order_id' => 12345,
        ];

        $client->post('v1/transactions/'.self::TRANSACTION_TOKEN.'/credit.json', ['transaction' => $extra])
            ->shouldBeCalled()
            ->willReturn($client);

        $this->credit(null, null, $extra)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_captures_an_authorized_amount($client)
    {
        $data = [
            'transaction' => [],
        ];

        $client->post('v1/transactions/'.self::TRANSACTION_TOKEN.'/capture.json', $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->capture()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_captures_a_specific_authorized_amount($client)
    {
        $amount = 9.99;

        $data = [
            'transaction' => [
                'amount' => $amount,
            ],
        ];

        $client->post('v1/transactions/'.self::TRANSACTION_TOKEN.'/capture.json', $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->capture($amount)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_captures_a_specific_authorized_amount_and_currency($client)
    {
        $amount = 9.99;
        $currencyCode = 'USD';

        $data = [
            'transaction' => [
                'amount' => $amount,
                'currency_code' => $currencyCode,
            ],
        ];

        $client->post('v1/transactions/'.self::TRANSACTION_TOKEN.'/capture.json', $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->capture($amount, $currencyCode)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_completes_a_3ds2_transaction($client)
    {
        $client->post('v1/transactions/'.self::TRANSACTION_TOKEN.'/complete.json')
            ->shouldBeCalled()
            ->willReturn($client);

        $this->complete()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_throws_invalid_method_exception()
    {
        $this->shouldThrow('Tuurbo\Spreedly\Exceptions\InvalidPaymentMethodException')
            ->during('undefinedMethod', ['some_param']);
    }
}
