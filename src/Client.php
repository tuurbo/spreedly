<?php

namespace Tuurbo\Spreedly;

use GuzzleHttp\ClientInterface as GuzzleInterface;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Exception\ConnectException as GuzzleConnectException;

class Client
{
    const BASE_URL = 'https://core.spreedly.com/';
    const TIMEOUT = 64;
    const CONNECT_TIMEOUT = 10;

    protected $client;
    protected $config;
    protected $response;
    protected $status;
    protected $key;

    /**
     * Set config.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param array                       $config
     */
    public function __construct(GuzzleInterface $client, $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    public function get($url, array $data = null)
    {
        return $this->request($url, 'get', $data);
    }

    public function post($url, array $data = null)
    {
        return $this->request($url, 'post', $data);
    }

    public function put($url, array $data = null)
    {
        return $this->request($url, 'put', $data);
    }

    /**
     * Create the CURL request.
     *
     * @param string $url
     * @param string $method optional
     * @param array  $data   optional
     *
     * @return Tuurbo\Spreedly\Client
     */
    protected function request($url, $method, array $data = null)
    {
        try {
            $response = $this->client->{$method}(self::BASE_URL.$url, $this->buildData($data));

            if (!in_array($response->getStatusCode(), [200, 201])) {
                $contentType = $response->getHeader('Content-Type');
                $notJson = array_shift($contentType) !== 'application/json; charset=utf-8';

                if ($response->getStatusCode() == 404 && $notJson) {
                    throw new Exceptions\NotFoundHttpException();
                }

                if ($response->getStatusCode() == 408) {
                    throw new Exceptions\TimeoutException();
                }

                $this->setResponse($response);
                $this->status = 'error';
            } else {
                $this->setResponse($response);
            }
        } catch (GuzzleConnectException $e) {
            throw new Exceptions\TimeoutException();
        }

        return $this;
    }

    /**
     * Set the response from Guzzle.
     *
     * @param mixed $response
     *
     * @return Tuurbo\Spreedly\Client
     */
    public function setResponse($response)
    {
        if ($response instanceof GuzzleResponse) {
            $contentType = $response->getHeader('Content-Type');

            if (array_shift($contentType) === 'application/json; charset=utf-8') {
                $response = $response->getBody();
                $response = json_decode($response, true);
            } else {
                $response = ['raw' => (string) $response->getBody()];
            }
        }

        $this->response = $response;

        if ($this->response('error') ||
            $this->response('errors') ||
            $this->response('succeeded') === false) {
            $this->status = 'error';
        } else {
            $this->status = 'success';
        }

        return $this;
    }

    /**
     * Get the response from Guzzle as an array.
     *
     * @param string $key
     *
     * @return array
     */
    public function response($key = null)
    {
        $array = $this->response;

        if (!$array) {
            return;
        }

        $this->key = key($array);

        $array = $array[$this->key];

        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Get the transaction message.
     *
     * @return string
     */
    public function message()
    {
        return $this->response('message');
    }

    /**
     * Check if payment purchase has declined.
     *
     * @return bool
     */
    public function hasDeclined()
    {
        return $this->response('succeeded') !== null && $this->response('succeeded') == false;
    }

    /**
     * Get the transaction token.
     *
     * @return string
     */
    public function transactionToken()
    {
        if ($this->key == 'payment_method') {
            return;
        }

        return $this->response('token');
    }

    /**
     * Get the payment token.
     *
     * @return string
     */
    public function paymentToken()
    {
        if ($this->key == 'payment_method') {
            return $this->response('token');
        }

        return $this->response('payment_method.token');
    }

    /**
     * Get an array or string of errors.
     *
     * @return array|string
     */
    public function errors($string = false)
    {
        if (!isset($this->response['errors'])) {
            return;
        }

        $errors = is_array($this->response['errors']) ? $this->response['errors'] : [$this->response['errors']];

        if ($string == true) {
            $errors = array_map(function ($error) {
                return $error['message'];
            }, $errors);

            return implode(' ', $errors);
        }

        return $errors;
    }

    /**
     * Check if call returned errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return isset($this->response['errors']);
    }

    /**
     * Check if call was successfull.
     *
     * @return bool
     */
    public function success()
    {
        return $this->status == 'success';
    }

    /**
     * Check if call failed or purchase declined.
     *
     * @return bool
     */
    public function fails()
    {
        return $this->status == 'error';
    }

    /**
     * Alias for fails method.
     *
     * @return bool
     */
    public function failed()
    {
        return $this->fails();
    }

    protected function buildData($data)
    {
        return [
            'auth' => [
                $this->config['key'],
                $this->config['secret'],
            ],
            'timeout' => isset($this->config['timeout']) ? $this->config['timeout'] : self::TIMEOUT,
            'connect_timeout' => isset($this->config['connect_timeout']) ? $this->config['connect_timeout'] : self::CONNECT_TIMEOUT,
            'exceptions' => false,
            'headers' => [
                'Content-type' => 'application/json',
            ],
            'body' => $data ? json_encode($data) : null,
        ];
    }
}
