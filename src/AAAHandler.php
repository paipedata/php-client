<?php

namespace feba\dataapi;

use Exception;
use GuzzleHttp;

/**
 * Creates a handler to get authorization from AAA.
 */
class AAAHandler
{
    private $aaaUrl;
    private $appKey;
    private $appSecret;
    private $aaa;
    const DEFAULT_AAA_URL = 'https://feba.dataapi.com';

    /** 
     * AAA Handler configuration settings include the following options:
     * - aaaUrl: Applications Authorization App (AAA) url to get the authorized services from.
     * - appKey: Current application key to identify the caller.
     * - appSecret: Application secret of appKey.
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['aaaUrl'])) {
            $this->aaaUrl = self::DEFAULT_AAA_URL;
        } else {
            $this->aaaUrl = $config['aaaUrl'];
        }

        $this->appKey = $config['appKey'];
        $this->appSecret = $config['appSecret'];
        $this->aaa = new GuzzleHttp\Client(['base_uri' => $this->aaaUrl]);
    }

    /**
     * Get authorization for the app.
     *
     * @return array - [$services (array), $jwtToken (string), $tokenExpirationTime (int)]
     */
    public function getAuthorization()
    {
        $body = $this->authorizationRequestBody();

        return $this->handleResponse($body);
    }

    /**
     * Get parsed response body from authorization.
     *
     * @return array
     */
    public function authorizationRequestBody()
    {
        return $this->aaa->request('GET', '/token', [
            'query' => [
                'app_key' => $this->appKey,
                'app_secret' => $this->appSecret,
            ]
        ])->getBody();
    }

    private function handleResponse($responseBody)
    {
        $jsonBody = json_decode($responseBody, true);
        $services = [];

        foreach ($jsonBody['services'] as $serviceEntry) {
            $actions = [];

            foreach ($serviceEntry['authorized_actions'] as $actionEntry) {
                $actions[] = '/' . $actionEntry;
            }

            $services[] = [
                'name' => $serviceEntry['name'],
                'baseEndpoint' => $serviceEntry['base_endpoint'],
                'actions' => $actions
            ];
        }

        return [$services, $jsonBody['jwtToken'], $jsonBody['exp']];
    }
}
