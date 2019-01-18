<?php

namespace Findologic\Tests\Api\Request;

use Findologic\Api\Request\Request;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class RequestTest
 * @package Findologic\Tests\Api\Request
 */
class RequestTest extends TestCase
{
    public function providerGetRequestUrl()
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
     * @dataProvider providerGetRequestUrl
     */
    public function testGetRequestUrl($url, $params, $expectedResult)
    {
        $requestMock = $this->getRequestMock();
        $requestMock->setUrl($url)->setParams($params);

        $this->assertEquals($expectedResult, $requestMock->getRequestUrl());
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