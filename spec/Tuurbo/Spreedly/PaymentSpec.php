<?php

namespace spec\Tuurbo\Spreedly;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Tuurbo\Spreedly\Client;

class PaymentSpec extends ObjectBehavior
{
    const GATEWAY_TOKEN = '...GATEWAY_TOKEN...';
    const PAYMENT_TOKEN = '...PAYMENT_TOKEN...';

    public function let(Client $client)
    {
        $this->beConstructedWith($client, [], self::PAYMENT_TOKEN, self::GATEWAY_TOKEN);

        $this->shouldHaveType('Tuurbo\Spreedly\Payment');
    }

    public function it_gets_a_list_of_all_payments($client)
    {
        $client->get('v1/payment_methods.json?since_token='.self::PAYMENT_TOKEN)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->all(self::PAYMENT_TOKEN)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_creates_a_payment_method($client)
    {
        $data = [
            'credit_card' => [
                'first_name' => 'Joe',
                'last_name' => 'Jones',
            ],
        ];

        $client->post('v1/payment_methods.json', ['payment_method' => $data])
            ->shouldBeCalled()
            ->willReturn($client);

        $this->create($data)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_updates_a_payment_method($client)
    {
        $data = [
            'first_name' => 'Joe',
            'last_name' => 'Jones',
        ];

        $client->put('v1/payment_methods/'.self::PAYMENT_TOKEN.'.json', ['payment_method' => $data])
            ->shouldBeCalled()
            ->willReturn($client);

        $this->update($data)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_retains_a_payment_method($client)
    {
        $client->put('v1/payment_methods/'.self::PAYMENT_TOKEN.'/retain.json')
            ->shouldBeCalled()
            ->willReturn($client);

        $this->retain()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_recaches_a_payment_methods_cvv($client)
    {
        $data = [
            'payment_method' => [
                'credit_card' => [
                    'verification_value' => 123,
                ],
            ],
        ];

        $client->post('v1/payment_methods/'.self::PAYMENT_TOKEN.'/recache.json', $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->recache(123)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_stores_a_payment_method($client)
    {
        $data = [
            'transaction' => [
                'payment_method_token' => self::PAYMENT_TOKEN,
            ],
        ];

        $client->post('v1/gateways/'.self::GATEWAY_TOKEN.'/store.json', $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->store()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_gets_a_single_payment_method($client)
    {
        $client->get('v1/payment_methods/'.self::PAYMENT_TOKEN.'.json')
            ->shouldBeCalled()
            ->willReturn($client);

        $this->get()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_disables_a_single_payment_method($client)
    {
        $client->put('v1/payment_methods/'.self::PAYMENT_TOKEN.'/redact.json')
            ->shouldBeCalled()
            ->willReturn($client);

        $this->disable()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_gets_a_list_of_all_transactions_for_a_single_payment_method($client)
    {
        $client->get('v1/payment_methods/'.self::PAYMENT_TOKEN.'/transactions.json', Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn($client);

        $this->transactions()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_gets_a_list_of_all_transactions_for_a_single_payment_method_and_paginates($client)
    {
        $data = [
            'order' => 'desc',
        ];

        $client->get('v1/payment_methods/'.self::PAYMENT_TOKEN.'/transactions.json?since_token='.self::PAYMENT_TOKEN, $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->transactions(self::PAYMENT_TOKEN, $data)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_makes_a_purchase($client)
    {
        $amount = 9.99;

        $data = [
            'transaction' => [
                'payment_method_token' => self::PAYMENT_TOKEN,
                'amount' => $amount,
                'currency_code' => 'USD',
            ],
        ];

        $client->post('v1/gateways/'.self::GATEWAY_TOKEN.'/purchase.json', $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->purchase($amount)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_makes_a_purchase_with_euros($client)
    {
        $amount = 9.99;
        $currency = 'EUR';

        $data = [
            'transaction' => [
                'payment_method_token' => self::PAYMENT_TOKEN,
                'amount' => $amount,
                'currency_code' => $currency,
            ],
        ];

        $client->post('v1/gateways/'.self::GATEWAY_TOKEN.'/purchase.json', $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->purchase($amount, $currency)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_makes_a_purchase_with_extra_data($client)
    {
        $amount = 9.99;
        $currency = 'USD';

        $data = [
            'transaction' => [
                'payment_method_token' => self::PAYMENT_TOKEN,
                'amount' => $amount,
                'currency_code' => $currency,
            ],
        ];

        $extra = [
            'order_id' => 12345,
            'description' => 'stuff',
        ];

        $data['transaction'] += $extra;

        $client->post('v1/gateways/'.self::GATEWAY_TOKEN.'/purchase.json', $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->purchase($amount, $currency, $extra)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_throws_an_exception_when_trying_to_make_a_purchase_with_an_invalid_amount()
    {
        $this->shouldThrow('Tuurbo\Spreedly\Exceptions\InvalidAmountException')
            ->during('purchase', [-1]);
    }

    public function it_makes_an_authorize($client)
    {
        $amount = 9.99;

        $data = [
            'transaction' => [
                'payment_method_token' => self::PAYMENT_TOKEN,
                'amount' => $amount,
                'currency_code' => 'USD',
            ],
        ];

        $client->post('v1/gateways/'.self::GATEWAY_TOKEN.'/authorize.json', $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->authorize($amount)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_makes_an_authorize_with_euros($client)
    {
        $amount = 9.99;
        $currency = 'EUR';

        $data = [
            'transaction' => [
                'payment_method_token' => self::PAYMENT_TOKEN,
                'amount' => $amount,
                'currency_code' => $currency,
            ],
        ];

        $client->post('v1/gateways/'.self::GATEWAY_TOKEN.'/authorize.json', $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->authorize($amount, $currency)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_throws_an_exception_when_trying_to_make_an_authorize_with_an_invalid_amount()
    {
        $this->shouldThrow('Tuurbo\Spreedly\Exceptions\InvalidAmountException')
            ->during('authorize', [-1]);
    }

    public function it_verifies_a_payment($client)
    {
        $data = [
            'transaction' => [
                'payment_method_token' => self::PAYMENT_TOKEN,
                'retain_on_success' => false,
                'currency_code' => 'USD',
            ],
        ];

        $client->post('v1/gateways/'.self::GATEWAY_TOKEN.'/verify.json', $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->verify(false, [
            'currency_code' => 'USD',
        ])->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_makes_a_general_credit($client)
    {
        $data = [
            'transaction' => [
                'payment_method_token' => self::PAYMENT_TOKEN,
                'amount' => 10.98,
                'currency_code' => 'USD',
            ],
        ];

        $client->post('v1/gateways/'.self::GATEWAY_TOKEN.'/general_credit.json', $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->generalCredit(10.98)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_throws_invalid_method_exception()
    {
        $this->shouldThrow('Tuurbo\Spreedly\Exceptions\InvalidPaymentMethodException')
            ->during('undefinedMethod', ['some_param']);
    }
}
