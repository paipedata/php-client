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
     *     $client = new feba\dataapi\Client([
     *       'aaaUrl' => 'https://aaa.febacapital.com', 
     *       'appKey' => 'app key', 
     *       'appSecret' => 'app secret']);
     * 
     *     $response = $client->getService('postal-code')->request('GET' '/lookup', [
     *         'query' => ['keyword' => 'av paulista']
     *     ]);
     * Client configuration settings include the following options:
     * 
     * - aaaUrl: Applications Authorization App (AAA) url to get the authorized services from.
     * - appKey: Current application key to identify the caller.
     * - appSecret: Application secret of appKey.
     */
    public function __construct(array $config = [])
    {
        $this->handler = new AAAHandler($config);
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
            $serviceName = $serviceEntry['name'];
            $serviceEntry['jwtToken'] = $jwtToken;
            $serviceEntry['expiration'] = $expiration;
            $this->cachedServices[$serviceName] = new Service($serviceEntry);
        }
    }
}
