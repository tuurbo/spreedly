<?php

namespace spec\Tuurbo\Spreedly;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Tuurbo\Spreedly\Client;

class ReceiverSpec extends ObjectBehavior
{
    const RECEIVER_TOKEN = '...RECEIVER_TOKEN...';

    public function let(Client $client)
    {
        $this->beConstructedWith($client, [], self::RECEIVER_TOKEN);

        $this->shouldHaveType('Tuurbo\Spreedly\Receiver');
    }

    public function it_gets_a_list_of_all_receivers($client)
    {
        $client->get('v1/receivers.json?since_token='.self::RECEIVER_TOKEN)
            ->shouldBeCalled()
            ->willReturn($client);

        $this->all(self::RECEIVER_TOKEN)->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_throws_invalid_method_exception()
    {
        $this->shouldThrow('Tuurbo\Spreedly\Exceptions\InvalidReceiverMethodException')
            ->during('undefinedMethod', ['some_param']);
    }
}
