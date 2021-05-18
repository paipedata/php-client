<?php

namespace feba\dataapi;

/**
 * Feba Data API Client.
 */
class Client
{
    private $handler;
    public $cachedServices = [];

    /**
     * Client is created with AAA url app key and secret. It handles service authorization and wraps GuzzleHttp\Client with proper JWT token valid across Feba data services.
     *
     * The following is an example of a client instance and a service request:
     *
     *     $client = new feba\dataapi\Client('https://aaa.febacapital.com', 'app key', 'app secret');
     *     $response = $client->getService('postal-code')->request('GET' '/lookup', [
     *         'query' => ['keyword' => 'av paulista']
     *     ]);
     *
     * @param string $aaaUrl Applications Authorization App (AAA) url to get the authorized services from.
     * @param string $appKey Current application key to identify the caller.
     * @param string $appSecret Application secret of appKey.
     */
    public function __construct($aaaUrl, $apiKey, $apiSecret)
    {
        $this->handler = new AAAHandler($aaaUrl, $apiKey, $apiSecret);
    }

    /**
     * Get authorized service by name.
     *
     * @param $serviceName Service name.
     *
     * @throws UnsupportedServiceException
     */
    public function getService($serviceName)
    {
        if (isset($this->cachedServices[$serviceName])) {
            $service = $this->cachedServices[$serviceName];
            if ($service->isTokenValid()) {
                return $service;
            }
        }

        $this->refreshServices();

        if (!isset($this->cachedServices[$serviceName])) {
            throw new UnsupportedServiceException("Service '$serviceName' is not supported");
        }

        return $this->cachedServices[$serviceName];
    }

    /**
     * Refresh authorized services.
     */
    public function refreshServices()
    {

        [$serviceEntries, $jwtToken, $expiration] = $this->handler->getAuthorization();

        foreach ($serviceEntries as $serviceEntry) {
            [
                'name' => $serviceName,
                'baseEndpoint' => $baseEndpoint,
                'actions' => $actions,
            ] = $serviceEntry;

            $this->cachedServices[$serviceName] = new Service($serviceName, $baseEndpoint, $actions, $jwtToken, $expiration);
        }
    }
}
