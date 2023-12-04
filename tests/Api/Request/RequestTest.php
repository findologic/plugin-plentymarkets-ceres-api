<?php

namespace Findologic\Tests\Api\Request;

use Findologic\Api\Request\RequestBuilder;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use FINDOLOGIC\Api\Requests\Request;
use FINDOLOGIC\Api\Config;
require_once __DIR__.'/../../../resources/lib/findologic_client.php';
require_once __DIR__.'/SdkRestApi.php';
/**
 * Class RequestTest
 * @package Findologic\Tests\Api\Request
 */
class RequestTest extends TestCase
{
    public function getRequestUrlProvider()
    {
        return [
            [
                'http://test.com/index.php',
                ['query' => 'test', 'attrib' => ['color' => ['red', 'blue']]],
                'http://test.com/index.php?query=test&attrib%5Bcolor%5D%5B0%5D=red&attrib%5Bcolor%5D%5B1%5D=blue'
            ]
        ];
    }

    /**
     * @dataProvider getRequestUrlProvider
     *
     * @param string $url
     * @param array $params
     * @param string $expectedResult
     */
    public function testGetRequestUrl(
        string $url,
        array $params,
        string $expectedResult
    ) {
        $mockApiRequest = new \SdkRestApi();
        $mockApiRequest::$params = [
            'shopUrl'=> $url,
            'externalSearch' => [
                'searchString' => $params['query'],
                'sorting' => 'ASC',
                'categoryId' => null,
                'itemsPerPage' => 20,
                'page' => 1
            ],
            'parameters' => [
                'attrib' => $params['attrib']
            ]
        ];
        $request = Request::getInstance(RequestBuilder::TYPE_NAVIGATION);
        // replaceInstanceByMock(\SdkRestApi::class, $mockApiRequest);
    $request = setDefaultValues($request);
    $config = new Config('2913E20746E3F762ABFC4AFAFE964609');

    $requestUrl = $request->buildRequestUrl($config);
    print_r($requestUrl);
    
        // $requestMock = $this->getRequestMock();
        // $requestMock->setUrl($url)->setParams($params);

        // $this->assertEquals($expectedResult, $requestMock->getRequestUrl());
    }

    /**
     * @param null $methods
     * @return Request|MockObject
     */
    protected function getRequestMock($methods = null)
    {
        return $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods($methods)->getMock();
    }
}

// class MockSdkRestApi
// {
//     public static $params = [];
//     public static function getParam(string $param)
//     {   print_r('call');
//         return isset(self::$params[$param]) ? self::$params[$param] : null;
//     }
// }
