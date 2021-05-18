<?php

namespace feba\dataapi;

use Codeception\Util\Debug;

class ServiceTest extends \Codeception\Test\Unit
{
    protected $service;

    public function testValidExpiration()
    {
        $service = new Service('yahoo-finance', 'https://finance.yahoo.com', ['/news'], 'jwt-token', time() + 1000);
        $this->assertTrue($service->isTokenValid());
    }

    public function testInvalidExpiration()
    {
        $service = new Service('yahoo-finance', 'https://finance.yahoo.com', ['/news'], 'jwt-token', time() - 2000);

        $this->assertFalse($service->isTokenValid());
    }

    public function testRequestWithExpiredToken()
    {
        $service = new Service('yahoo-finance', 'https://finance.yahoo.com', ['/news'], 'jwt-token', time() - 2000);

        $this->expectException(TokenExpiredException::class);
        $service->request('POST', '/news');
    }

    public function testRequestWithUnsupportedAction()
    {
        $service = new Service('yahoo-finance', 'https://finance.yahoo.com', ['/news'], 'jwt-token', time() + 2000);

        $this->expectException(UnsupportedServiceActionException::class);
        $this->expectExceptionMessage('Action /newsx is not supported by the service yahoo-finance');
        $service->request('GET', '/newsx');
    }
}
