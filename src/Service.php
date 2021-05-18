<?php

namespace feba\dataapi;

use GuzzleHttp;

/**
 * Service represents an interface of a Data API service.
 */
class Service
{
    /**
     * It's an optmist offset to help delay on token expiration time validation.
     */
    const TOKEN_EXPIRATION_SALT = 60;

    protected $name;
    protected $baseEndpoint;
    protected $actions;
    protected $jwtToken;
    protected $expiration;

    /**
     * Service configuration settings include the following options:
     * - name:          Service name.
     * - baseEndpoint:  Base service endpoint.
     * - actions:       The list(string) of supported actions.
     * - jwtToken:      JWT Token provided by AAA.
     * - expiration:    When the jwtToken will expire.
     */
    public function __construct($config)
    {
        $this->name = $config['name'];
        $this->baseEndpoint = $config['baseEndpoint'];
        $this->actions = $config['actions'];
        $this->jwtToken = $config['jwtToken'];
        $this->expiration = $config['expiration'];
    }

    /**
     * Checks if the token is valid.
     *
     * @return bool
     */
    public function isTokenValid()
    {
        return time() <= ($this->expiration - self::TOKEN_EXPIRATION_SALT);
    }

    /**
     * Performs the request with JWT token as Bearer Authorization header.
     * It has similar syntax of GuzzleHttp\Client, request method.
     * Prefer to use services from feba\dataapi\Client which handles expiration tokens accordantly.
     *
     * Example to perform GET with query string:
     *     $client = new feba\dataapi\Client([
     *       'aaaUrl' => 'https://aaa.febacapital.com', 
     *       'appKey' => 'app key', 
     *       'appSecret' => 'app secret']);
     * 
     *     $response = $client->getService('postal-code')->request('GET' '/lookup', [
     *         'query' => ['keyword' => 'av paulista']
     *     ]);
     * To post to data service as json:
     *     $service = $client->getService('postal-code')
     *     $resp = $service->request('POST' '/search', [
     *         'json' => ['foo' => 'data']
     *     ]);
     * Injecting a custom header:
     *     $service = $client->getService('postal-code')
     *     $resp = $service->request('POST' '/search', [
     *         'headers' => ['X-My-Header' => 'nice header'],
     *         'json' => ['foo' => 'data']
     *     ]);
     *
     * @param string              $method  HTTP method
     * @param string              $action  Action name/path.
     * @param array               $options Request options to apply. See \Psr\Http\Message\MessageInterface.
     *
     * @return Psr\Http\Message\ResponseInterface
     * @throws GuzzleHttp\Exception\GuzzleException
     * @throws TokenExpiredException
     * @throws UnsupportedServiceActionException
     */
    public function request($method, $action, $options = [])
    {
        if (!$this->isTokenValid()) {
            throw new TokenExpiredException('Token is not longer valid');
        }

        if (!in_array($action, $this->actions)) {
            throw new UnsupportedServiceActionException("Action $action is not supported by the service " . $this->name);
        }

        if (!isset($options['headers'])) {
            $options['headers'] = [];
        }

        $options['headers']['Authorization'] = 'Bearer ' . $this->jwtToken;

        $serviceClient = new GuzzleHttp\Client(['base_uri' => $this->baseEndpoint]);

        return $serviceClient->request($method, $action, $options);
    }
}
