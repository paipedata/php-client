<?php

namespace feba\dataapi;

use Codeception\Stub\Expected;
use Codeception\Util\Debug;

class ClientTest extends \Codeception\Test\Unit
{

    public function testCachedService()
    {
        $serviceName = 'yahoo-finance';
        $service = new Service($serviceName, 'https://finance.yahoo.com', ['/news'], 'jwt-token', time() + 2000);
        $client = $this->make(new Client('aaaUrl', 'apiKey', 'apiSecret'), [
            'cachedServices' => [$serviceName => $service],
            'refreshServices' => Expected::never()
        ]);

        $foundService = $client->getService($serviceName);
        $this->assertNotNull($foundService);
        $this->assertEquals($service, $foundService);
    }

    public function testServiceNotFound()
    {
        $client = $this->make(new Client('aaaUrl', 'apiKey', 'apiSecret'), [
            'refreshServices' => Expected::once([])
        ]);
        $this->expectException(UnsupportedServiceException::class);
        $this->expectExceptionMessage("Service 'nope' is not supported");
        $client->getService('nope');
    }

    public function testGetServiceProperly()
    {
        $aaaResponseBody = json_encode([
            'app_key' => 'search_app2',
            'services' =>
            [
                [
                    'name' => 'yahoo-finance',
                    'base_endpoint' => 'https://finance.yahoo.com',
                    'authorized_actions' =>
                    [
                        'news',
                    ],
                ],
            ],
            'iat' => 1620845630,
            'exp' => time() + 6000,
            'sub' => 'search_app2',
            'jwtToken' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhcHBfa2V5Ijoic2VhcmNoX2FwcDIiLCJzZXJ2aWNlcyI6W3sibmFtZSI6InlhaG9vLWZpbmFuY2UiLCJiYXNlX2VuZHBvaW50IjoiaHR0cHM6XC9cL2ZpbmFuY2UueWFob28uY29tIiwiYXV0aG9yaXplZF9hY3Rpb25zIjpbIm5ld3MiXX1dLCJpYXQiOjE2MjA4NDU2MzAsImV4cCI6MTYyMDg0NjIzMCwic3ViIjoic2VhcmNoX2FwcDIifQ.CWp4PVdOqQ5uVRlYVYQRqTsn66MEIQX9fazIxFqOr6-PPB-FZ0yT_2olGX6juvZ3nlo2d1FuQM5wKJqmwr_IueS65R-zhz_m3ijeLZcMsrSb8Q3VIOEITjcglWyby7vaAqHzvKSTTp8qK3Qp85hUHNUyxl236Vy6EsPRp0kz4_YSfB3_pTUPbnyzdtGJV13EYoXEnzPoIml_br6hXAnSvopySSbhiMgzIR9KOhrmMJ4khUX76YJIF9mcvcBLLqKCl9LEOFSvC_B_v8SujddLokKIK2cvGD1ck2jgPRBp1-0XifJ9y54a4STmL7GLCMMg70pBJZnnp7k5cr2qpU48Cmr0GxIG1uideBXg9XZNcqiG7ihqR0SY_1MzxOp9yWFVmw53e65TIZEhfy1YpWSz6lc-7iPJelq3ihk2AKSkBbKIEKi3TQBjgQSEY72FSsx2-aMoaksFIIemTHnABwryBFJlvfHDHLaodnmToGGEc57bYKQqm1fPLU2VwFkMES2YV6wmn5j3sJIGh7kqwAmF5ysZpjoEr1WCeVri43xB1v_SrHdSz104slzC8OXy-Ni_nu8t_WwJn8kE5R1qMD2gBvRuLLutjQfarC6sealbGKHOoaVKV3dN1cr1gy6xwy8EjhgjvO8ESYZvTamngb44WOwp3OE2XQ3pfFSVKQ2q8LM'
        ]);
        $aaa = $this->make(new AAAHandler('aaaUrl', 'appKey', 'appSecret'), [
            'authorizationRequestBody' => Expected::atLeastOnce($aaaResponseBody)
        ]);

        $client = $this->make(new Client('aaaUrl', 'apiKey', 'apiSecret'), [
            'handler' => $aaa
        ]);

        $service = $client->getService('yahoo-finance');
        $this->assertNotNull($service);
    }
}
