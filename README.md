# Feba Data API Client for PHP

DataAPI client provides an easy interface for Feba data services, it handles authentication from [AAA](https://git.febacapital.com/feba/data-api/aaa/-/tree/master/src), pre-authorize services and validate authorized actions.


## Installation

TODO: Check how to distribute it.
```
composer install feba-data-api-client.phar
```
## Usage

Firstly get instantiate a client then get a service from it. 
In this example, we're going to use:

- `aaaUrl` the AAA endpoint as `https://aaa.febacapital.com` 
- `appKey` the app key as `app-key` 
- `appSecret` the app secret as `app-secret` 

In order to get the client done, above parameter should be shared to the application.

Example to perform GET with query string:

```php
$client = new Feba\DataAPI\Client('https://aaa.febacapital.com', 'app-key', 'app-secret');

$response = $client->getService('cep')->request('GET' '/lookup', [ 
    'query' => ['keyword' => 'av paulista']
]);
```
The response comes as [Psr\Http\Message\MessageInterface](https://github.com/php-fig/http-message/blob/master/docs/PSR7-Interfaces.md#psrhttpmessagemessageinterface-methods), then you call common functions and the `$options` parameter are same as [GuzzleHttp\RequestOptions](https://docs.guzzlephp.org/en/stable/request-options.html)


To post to data service as json:

```php
$service = $client->getService('postal-code')
$resp = $service->request('POST' '/search', [ 
    'json' => ['foo' => 'data']
]);
```

Injecting a custom header:

```php
$service = $client->getService('postal-code')
$resp = $service->request('POST' '/search', [ 
    'headers' => ['X-My-Header' => 'nice header'],
    'json' => ['foo' => 'data']
]);
```

###
© [Feba Capital](https://www.febacapital.com/)
