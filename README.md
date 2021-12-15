# Paipe API Client for PHP

Paipe PHP client provides an easy interface for Feba data services, it handles authentication from Paipe Auth, pre-authorize services and validate authorized actions.


## Installation

```
composer require paipe/phpclient
```
## Usage

Firstly get instantiate a client then get a service from it. 
In this example, we're going to use the config:

- `aaaUrl` the Auth endpoint as `https://auth.paipe.com.br` 
- `appKey` the app key as `app-key` 
- `appSecret` the app secret as `app-secret` 

In order to get the client done, above parameter should be shared to the application.

Example to perform GET with query string:

```php
$client = new paipe\phpclient\Client([
  'aaaUrl' => 'https://auth.paipe.com.br',
  'appKey' => 'app-key',
  'appSecret' => 'app-secret'
];


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
© [Paipe](https://www.paipe.com.br/)
