<?php

namespace spec\Tuurbo\Spreedly;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

class ClientSpec extends ObjectBehavior
{
    const GATEWAY_TOKEN = '...GATEWAY_TOKEN...';
    const PAYMENT_TOKEN = '...PAYMENT_TOKEN...';
    const BASE_URL = 'https://core.spreedly.com/';
    const END_POINT = 'v1/fake_url';

    public function let(Client $client)
    {
        $config = [
            'key' => '12345',
            'secret' => '67890',
        ];

        $this->beConstructedWith($client, $config);
    }

    public function letGo()
    {
        $this->shouldReturnAnInstanceOf('Tuurbo\Spreedly\Client');
    }

    public function it_returns_an_array()
    {
        $array = [
            'gateway' => [
                'paypal' => [
                    'test' => 2,
                ],
            ],
        ];

        $this->setResponse($array);

        $this->response()->shouldReturn($array['gateway']);
    }

    public function it_returns_an_array_without_any_keys_containing_an_at_symbol_attribute()
    {
        $this->setResponse([
            'gateway' => [
                'paypal' => [
                    'test' => 2,
                ],
            ],
        ]);

        $this->response()->shouldReturn([
            'paypal' => [
                'test' => 2,
            ],
        ]);
    }

    public function it_returns_an_array_of_errors($client)
    {
        $errors = [
            'errors' => [
                [
                    'key' => 'broken',
                    'message' => 'something went wrong',
                ],
            ],
        ];

        $this->setResponse($errors);

        $this->errors()
            ->shouldReturn($errors['errors']);
    }

    public function it_returns_a_string_of_errors($client)
    {
        $errors = [
            'errors' => [
                [
                    'key' => 'broken',
                    'message' => 'something went wrong',
                ],
            ],
        ];

        $this->setResponse($errors);

        $errors = array_map(function ($error) {
            return $error['message'];
        }, $errors['errors']);

        $this->errors(true)
            ->shouldReturn(implode(', ', $errors));
    }

    public function it_return_an_instance_of_itself($client)
    {
        $client->get(self::BASE_URL.self::END_POINT, Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn(new ClientStub200());

        $this->get(self::END_POINT)->shouldReturn($this);
    }

    public function it_throws_an_exception_if_http_response_is_404($client)
    {
        $client->get(self::BASE_URL.self::END_POINT, Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn(new ClientStub404());

        $this->shouldThrow('Tuurbo\Spreedly\Exceptions\NotFoundHttpException')
            ->duringGet(self::END_POINT);
    }

    public function it_sets_status_to_success_if_transaction_succeeds($client)
    {
        $client->get(self::BASE_URL.self::END_POINT, Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn(new ClientStub200());

        $this->get(self::END_POINT)
            ->success()
            ->shouldReturn(true);
    }

    public function it_sets_status_to_error_if_transaction_fails($client)
    {
        $client->get(self::BASE_URL.self::END_POINT, Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn(new ClientStub500());

        $this->get(self::END_POINT)
            ->fails()
            ->shouldReturn(true);
    }

    public function it_throws_an_exception_if_the_config_is_invalid($client)
    {
        $this->beConstructedWith($client, []);

        $this->shouldThrow('Exception')
            ->duringGet(self::END_POINT);
    }

    public function it_should_allow_overriding_the_base_url($client)
    {
        // for this test, reconstruct the client with the new base url
        $overrideBaseUrl = 'https://otherdomain.com/';

        $config = [
            'key' => '12345',
            'secret' => '67890',
            'baseUrl' => $overrideBaseUrl
        ];

        $this->beConstructedWith($client, $config);

        // check that client really did use the overriden base url
        $client->post($overrideBaseUrl.self::END_POINT, Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn(new ClientStub200());

        $this->post(self::END_POINT)
            ->success()
            ->shouldReturn(true);
    }
}

class ClientStub200 extends GuzzleResponse
{
    public function getStatusCode()
    {
        return 200;
    }

    public function getHeader($header)
    {
        return ['application/json; charset=utf-8'];
    }

    public function getBody()
    {
        return json_encode([]);
    }
}

class ClientStub404 extends GuzzleResponse
{
    public function getStatusCode()
    {
        return 404;
    }

    public function getHeader($header)
    {
        return ['application/text; charset=utf-8'];
    }

    public function json()
    {
        return json_encode([]);
    }
}

class ClientStub500 extends GuzzleResponse
{
    public function getStatusCode()
    {
        return 500;
    }

    public function getHeader($header)
    {
        return ['application/text; charset=utf-8'];
    }

    public function json()
    {
        return json_encode([]);
    }
}
