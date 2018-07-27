<?php

namespace Findologic\Tests\Api\Request;

use Findologic\Api\Request\RequestBuilder;
use Findologic\Api\Request\Request;
use Findologic\Constants\Plugin;
use Ceres\Helper\ExternalSearch;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Plugin\Log\LoggerFactory;
use Plenty\Log\Contracts\LoggerContract;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class RequestBuilderTest
 * @package Findologic\Tests\Api\Request
 */
class RequestBuilderTest extends TestCase
{
    /**
     * @var ConfigRepository|MockObject
     */
    protected $configRepository;

    /**
     * @var LoggerFactory|MockObject
     */
    protected $loggerFactory;

    /**
     * @var LoggerContract|MockObject
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
                [
                    'query' => 'Test',
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'color' => ['red', 'blue']
                    ],
                    Plugin::API_PARAMETER_SORT_ORDER => 'price ASC',
                    Plugin::API_PARAMETER_PAGINATION_ITEMS_PER_PAGE => '20',
                    Plugin::API_PARAMETER_PAGINATION_START => '10',
                    'properties' => []
                ],
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
                    Plugin::API_PARAMETER_PAGINATION_START => '10',
                    'properties' => [
                        0 => 'main_variation_id'
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider providerBuild
     */
    public function testBuild($parameters, $expectedUrl, $expectedParams)
    {
        /** @var HttpRequest|MockObject $httpRequestMock */
        $httpRequestMock = $this->getMockBuilder(HttpRequest::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $httpRequestMock->expects($this->once())->method('all')->willReturn($parameters);

        /** @var ExternalSearch|MockObject $searchQueryMock */
        $searchQueryMock = $this->getMockBuilder(ExternalSearch::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $searchQueryMock->searchString = 'Test';

        $this->configRepository->expects($this->any())->method('get')->willReturnOnConsecutiveCalls('http://test.com', 'TESTSHOPKEY');

        $requestBuilderMock = $this->getRequestBuilderMock(['createRequestObject']);
        $requestBuilderMock->expects($this->any())->method('createRequestObject')->willReturn(new Request());

        /** @var Request|MockObject $result */
        $result = $requestBuilderMock->build($httpRequestMock, $searchQueryMock);
        $this->assertEquals($expectedUrl, $result->getUrl());
        $this->assertEquals($expectedParams, $result->getParams());
    }

    /**
     * @param array|null $methods
     * @return RequestBuilder|MockObject
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