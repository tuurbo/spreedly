<?php

namespace spec\Tuurbo\Spreedly;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Tuurbo\Spreedly\Client;

class GatewaySpec extends ObjectBehavior
{
    const GATEWAY_TOKEN = '...GATEWAY_TOKEN...';
    const PAYMENT_TOKEN = '...PAYMENT_TOKEN...';

    public function let(Client $client)
    {
        $this->beConstructedWith($client, [], self::GATEWAY_TOKEN);

        $this->shouldHaveType('Tuurbo\Spreedly\Gateway');
    }

    public function it_requests_a_list_of_supported_gateways($client)
    {
        $client->get('v1/gateways.json')
            ->shouldBeCalled()
            ->willReturn($client);

        $this->all()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_requests_all_gateways_you_have_created($client)
    {
        $client->get('v1/gateways.json')
            ->shouldBeCalled()
            ->willReturn($client);

        $this->all()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_requests_a_specfic_gateway_you_have_created($client)
    {
        $client->get('v1/gateways/'.self::GATEWAY_TOKEN.'.json')
            ->shouldBeCalled()
            ->willReturn($client);

        $this->show()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_requests_all_gateways_after_a_specified_token($client)
    {
        $client->get('v1/gateways.json?since_token='.self::GATEWAY_TOKEN)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->all(self::GATEWAY_TOKEN)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_gets_a_list_of_all_gateway_transactions_for_a_single_gateway($client)
    {
        $client->get('v1/gateways/'.self::GATEWAY_TOKEN.'/transactions.json', Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn($client);

        $this->transactions()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_gets_a_list_of_all_gateway_transactions_for_a_single_gateway_and_paginates($client)
    {
        $data = [
            'order' => 'desc',
        ];

        $client->get('v1/gateways/'.self::GATEWAY_TOKEN.'/transactions.json?since_token='.self::PAYMENT_TOKEN, $data)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->transactions(self::PAYMENT_TOKEN, $data)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_creates_a_gateway($client)
    {
        $client->post('v1/gateways.json', Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn($client);

        $this->create('PAYPAL')->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_updates_a_gateway($client)
    {
        $client->put('v1/gateways/'.self::GATEWAY_TOKEN.'.json', Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn($client);

        $this->update([
                'password' => 'test',
            ])->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_disables_a_gateway($client)
    {
        $client->put('v1/gateways/'.self::GATEWAY_TOKEN.'/redact.json')
            ->shouldBeCalled()
            ->willReturn($client);

        $this->disable()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_returns_the_gateway_token($client)
    {
        $this->getToken()->shouldReturn(self::GATEWAY_TOKEN);
    }

    public function it_returns_a_payment_instance($client)
    {
        $this->payment()->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Payment');
    }
}
