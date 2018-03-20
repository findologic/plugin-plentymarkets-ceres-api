<?php

namespace Findologic\PluginPlentymarketsApi\Tests\Api\Request;

use Findologic\PluginPlentymarketsApi\Api\Request\RequestBuilder;
use Findologic\PluginPlentymarketsApi\Api\Request\Request;
use Findologic\PluginPlentymarketsApi\Constants\Plugin;
use Ceres\Helper\ExternalSearch;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Plugin\Log\LoggerFactory;
use Plenty\Log\Contracts\LoggerContract;

/**
 * Class RequestBuilderTest
 * @package Findologic\PluginPlentymarketsApi\Tests\Api\Request
 */
class RequestBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configRepository;

    /**
     * @var LoggerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerFactory;

    /**
     * @var LoggerContract|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    public function setUp()
    {
        $this->configRepository = $this->getMockBuilder(ConfigRepository::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->logger = $this->getMockBuilder(LoggerContract::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->loggerFactory = $this->getMockBuilder(LoggerFactory::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->loggerFactory->expects($this->any())->method('getLogger')->willReturn($this->logger);
    }

    public function providerBuild()
    {
        return [
            [
                'http://test.com/index.php?query=test&attrib%5Bcolor%5D%5B0%5D=red&attrib%5Bcolor%5D%5B1%5D=blue&'
                    . Plugin::API_PARAMETER_SORT_ORDER . '=price+ASC&'
                    . Plugin::API_PARAMETER_PAGINATION_ITEMS_PER_PAGE . '=20&'
                    . Plugin::API_PARAMETER_PAGINATION_START . '=10',
                'http://test.com/index.php',
                [
                    'query' => 'Test',
                    'outputAdapter' => Plugin::API_OUTPUT_ADAPTER,
                    'shopkey' => 'TESTSHOPKEY',
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'color' => ['red', 'blue']
                    ],
                    Plugin::API_PARAMETER_SORT_ORDER => 'price ASC',
                    Plugin::API_PARAMETER_PAGINATION_ITEMS_PER_PAGE => '20',
                    Plugin::API_PARAMETER_PAGINATION_START => '10'
                ]
            ]
        ];
    }

    /**
     * @dataProvider providerBuild
     */
    public function testBuild($uri, $expectedUrl, $expectedParams)
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $httpRequestMock */
        $httpRequestMock = $this->getMockBuilder(HttpRequest::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $httpRequestMock->expects($this->once())->method('getUri')->willReturn($uri);

        /** @var ExternalSearch|\PHPUnit_Framework_MockObject_MockObject $searchQueryMock */
        $searchQueryMock = $this->getMockBuilder(ExternalSearch::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $searchQueryMock->searchString = 'Test';

        $this->configRepository->expects($this->any())->method('get')->willReturnOnConsecutiveCalls('http://test.com', 'TESTSHOPKEY');

        $requestBuilderMock = $this->getRequestBuilderMock(['createRequestObject']);
        $requestBuilderMock->expects($this->any())->method('createRequestObject')->willReturn(new Request());

        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $result */
        $result = $requestBuilderMock->build($httpRequestMock, $searchQueryMock);
        $this->assertEquals($expectedUrl, $result->getUrl());
        $this->assertEquals($expectedParams, $result->getParams());
    }

    /**
     * @param array|null $methods
     * @return RequestBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRequestBuilderMock($methods = null)
    {
        return $this->getMockBuilder(RequestBuilder::class)
            ->setConstructorArgs([
                'configRepository' => $this->configRepository,
                'loggerFactory' => $this->loggerFactory
            ])
            ->setMethods($methods)
            ->getMock();
    }
}