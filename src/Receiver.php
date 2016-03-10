<?php

namespace Tuurbo\Spreedly;

use GuzzleHttp\Client;

class Receiver {

    protected $config;
    protected $client;
    public $receiverToken;

    /**
     * Create a Guzzle instance and set tokens.
     *
     * @param \GuzzleHttp\Client $client
     * @param array              $config
     * @param string             $receiverToken optional
     */

    public function __construct(Client $client, $config, $receiverToken = null)
    {
        $this->client = $client;
        $this->config = $config;
        $this->receiverToken = $receiverToken;
    }

    /**
     * Get a list of all Receivers you've created on Spreedly.
     *
     * @param  string $receiverToken optional
     * @return \Tuurbo\Spreedly\Client
     */
    public function all($receiverToken = null)
    {
        $append = '';

        if ($receiverToken) {
            $append = '?since_token='.$receiverToken;
        }

        return $this->client->request('v1/receivers.json'.$append);
    }

    /**
     * Create a new receiver on Spreedly.
     *
     * @param  string $type
     * @param  array  $credentials  optional
     * @param  string|array $hostnames
     * @return \Tuurbo\Spreedly\Client
     */
    public function create($type, array $credentials = null, $hostnames = null)
    {
        $params = [
            'receiver' => [
                'receiver_type' => $type
            ]
        ];

        if($type == 'test') {
            if(is_array($hostnames)) {
                $hostnames = implode(',', $hostnames);
            }
            $params['receiver']['hostnames'] = $hostnames;
        }

        if(count($credentials)) {
            foreach($credentials as $k => $v) {
                $params['receiver']['credentials']['credential'][] = [
                    'name' => $k,
                    'value' => $v
                ];
            }
        }
        return $this->client->post('v1/receivers.json', $params);
    }

    /**
     * Update a receiver
     *
     * @param array $credentials
     * @return \Tuurbo\Spreedly\Client
     * @throws Exceptions\MissingReceiverTokenException
     */
    public function update(array $credentials)
    {
        if (! $this->receiverToken) {
            throw new Exceptions\MissingReceiverTokenException;
        }

        $params = [];
        if(count($credentials)) {
            foreach($credentials as $k => $v) {
                $params['receiver']['credentials']['credential'][] = [
                    'name' => $k,
                    'value' => $v
                ];
            }
        }

        return $this->client->put('v1/receivers/'.$this->receiverToken.'.json', $params);
    }

    /**
     * Get details of the receiver
     *
     * @return \Tuurbo\Spreedly\Client
     * @throws Exceptions\MissingReceiverTokenException
     */
    public function get()
    {
        if (! $this->receiverToken) {
            throw new Exceptions\MissingReceiverTokenException;
        }
        return $this->client->get('v1/receivers/'.$this->receiverToken.'.json');
    }

    /**
     * Disable a receiver on Spreedly.
     *
     * @return \Tuurbo\Spreedly\Client
     * @throws Exceptions\MissingReceiverTokenException
     */
    public function disable()
    {
        if (! $this->receiverToken) {
            throw new Exceptions\MissingReceiverTokenException;
        }
        return $this->client->put('v1/receivers/'.$this->receiverToken.'/redact.json');
    }

    /**
     * Deliver payment method to receiver
     *
     * TODO: This needs to be updated
     *
     * @param $paymentMethod
     * @param $endpoint
     * @param array $headers
     * @param null $body
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws Exceptions\MissingReceiverTokenException
     */
    public function deliver($paymentMethod, $endpoint, array $headers, $body = null) {
        if (! $this->receiverToken) {
            throw new Exceptions\MissingReceiverTokenException;
        }
        $params = [
            'delivery'=>[
                'payment_method_token'  => $paymentMethod,
                'url'                   => $endpoint,
            ]
        ];
        if(count($headers)) {
            $headerLines = [];
            foreach($headers as $key => $value) {
                $headerLines[] = "$key: $value";
            }
            $headerString = implode("/n", $headerLines);
            $params['delivery']['headers'] = $headerString;
        }

        if($body) {
            $params['delivery']['body'] = $body;
        }

        return $this->client->post('v1/receivers/'.$this->receiverToken.'/deliver.json', $params);
    }

    /**
     * @param $method
     * @param $parameters
     * @throws Exceptions\InvalidReceiverMethodException
     */
    public function __call($method, $parameters)
    {
        throw new Exceptions\InvalidReceiverMethodException($method.' is an invalid method.');
    }
}