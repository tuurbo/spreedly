<?php

namespace spec\Tuurbo\Spreedly;

use PhpSpec\ObjectBehavior;

class SpreedlySpec extends ObjectBehavior
{
    const GATEWAY_TOKEN = '...GATEWAY_TOKEN...';
    const PAYMENT_TOKEN = '...PAYMENT_TOKEN...';

    public function let()
    {
        $config = [
            'key' => '...key...',
            'secret' => '...secret...',
            'gateway' => null,
        ];

        $this->beConstructedWith($config);

        $this->shouldHaveType('Tuurbo\Spreedly\Spreedly');
    }

    public function it_returns_a_gateway_instance()
    {
        $this->gateway()
            ->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Gateway');
    }

    public function it_returns_a_payment_instance()
    {
        $this->payment()
            ->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Payment');
    }

    public function it_returns_a_transaction_instance()
    {
        $this->transaction()
            ->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Transaction');
    }

    public function it_returns_a_receiver_instance()
    {
        $this->receiver()
            ->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Receiver');
    }

    public function it_throws_an_exception_if_config_is_invalid()
    {
        $config = [
            'key' => null,
            'secret' => null,
            'gateway' => null,
        ];

        $this->shouldThrow('Tuurbo\Spreedly\Exceptions\InvalidConfigException')
            ->duringSetConfig($config);
    }

    public function it_throws_an_exception_if_gateway_token_is_not_set()
    {
        $this->payment(self::PAYMENT_TOKEN)
            ->shouldThrow('Tuurbo\Spreedly\Exceptions\MissingGatewayTokenException')
            ->duringPurchase(9.00);
    }

    public function it_throws_an_exception_if_payment_token_is_not_passed()
    {
        $this->gateway(self::GATEWAY_TOKEN)
            ->payment()
            ->shouldThrow('Tuurbo\Spreedly\Exceptions\MissingPaymentTokenException')
            ->duringPurchase(9.00);
    }
}
