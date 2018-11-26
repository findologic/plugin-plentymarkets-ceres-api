<?php

namespace Findologic\Tests\Api\Request;

use Findologic\Api\Request\RequestBuilder;
use Findologic\Api\Request\ParametersBuilder;
use Findologic\Api\Request\Request;
use Findologic\Constants\Plugin;
use Findologic\Services\PluginInformation;
use Ceres\Helper\ExternalSearch;
use IO\Services\CategoryService;
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
     * @var PluginInformation
     */
    protected $pluginInformation;

    /**
     * @var ParametersBuilder
     */
    protected $parametersBuilder;

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
        $this->pluginInformation = $this->getMockBuilder(PluginInformation::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->parametersBuilder = $this->getMockBuilder(ParametersBuilder::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->configRepository = $this->getMockBuilder(ConfigRepository::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->logger = $this->getMockBuilder(LoggerContract::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->loggerFactory = $this->getMockBuilder(LoggerFactory::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->loggerFactory->expects($this->any())->method('getLogger')->willReturn($this->logger);
    }

    public function providerBuildAliveRequest()
    {
        return [
            'Build alive request' => [
                'http://test.com/alivetest.php',
                [
                    'shopkey' => 'TESTSHOPKEY'
                ]
            ]
        ];
    }

    /**
     * @dataProvider providerBuildAliveRequest
     */
    public function testBuildAliveRequest($expectedUrl, $expectedParams)
    {
        $requestBuilderMock = $this->getRequestBuilderMock(['createRequestObject']);
        $requestBuilderMock->expects($this->any())->method('createRequestObject')->willReturn(new Request());

        $this->configRepository->expects($this->any())->method('get')->willReturnOnConsecutiveCalls('http://test.com', 'TESTSHOPKEY');

        /** @var Request|MockObject $result */
        $result = $requestBuilderMock->buildAliveRequest();
        $this->assertEquals($expectedUrl, $result->getUrl());
        $this->assertEquals($expectedParams, $result->getParams());
    }

    public function providerBuild()
    {
        return [
            'Build - No user ip provided' => [
                [
                    'query' => 'Query',
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'size' => ['xl'],
                    ],
                    Plugin::API_PARAMETER_SORT_ORDER => 'price DESC',
                    Plugin::API_PARAMETER_PAGINATION_ITEMS_PER_PAGE => '30',
                    Plugin::API_PARAMETER_PAGINATION_START => '60',
                    'properties' => []
                ],
                false,
                'http://test.com/index.php',
                false,
                [
                    'outputAdapter' => Plugin::API_OUTPUT_ADAPTER,
                    'shopkey' => 'TESTSHOPKEY',
                    'revision' => null
                ]
            ],
            'Category page request' => [
                [
                    'query' => 'Test',
                    Plugin::API_PARAMETER_ATTRIBUTES => [
                        'size' => ['l', 'xl']
                    ],
                    Plugin::API_PARAMETER_SORT_ORDER => 'price DESC',
                    Plugin::API_PARAMETER_PAGINATION_ITEMS_PER_PAGE => '10',
                    Plugin::API_PARAMETER_PAGINATION_START => '0',
                    'properties' => []
                ],
                '127.0.0.1',
                'http://test.com/selector.php',
                true,
                [
                    'outputAdapter' => Plugin::API_OUTPUT_ADAPTER,
                    'shopkey' => 'TESTSHOPKEY',
                    'userip' => '127.0.0.1',
                    'revision' => null
                ]
            ],
            'Search page request' => [
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
                '127.0.0.1',
                'http://test.com/index.php',
                false,
                [
                    'outputAdapter' => Plugin::API_OUTPUT_ADAPTER,
                    'shopkey' => 'TESTSHOPKEY',
                    'userip' => '127.0.0.1',
                    'revision' => null
                ]
            ]
        ];
    }

    /**
     * @dataProvider providerBuild
     */
    public function testBuild($parameters, $userIp, $expectedUrl, $category, $expectedParams)
    {
        /** @var HttpRequest|MockObject $httpRequestMock */
        $httpRequestMock = $this->getMockBuilder(HttpRequest::class)->disableOriginalConstructor()->setMethods([])->getMock();

        /** @var ExternalSearch|MockObject $searchQueryMock */
        $searchQueryMock = $this->getMockBuilder(ExternalSearch::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $searchQueryMock->searchString = 'Test';

        $this->configRepository->expects($this->any())->method('get')->willReturnOnConsecutiveCalls('http://test.com', 'TESTSHOPKEY');

        $requestBuilderMock = $this->getRequestBuilderMock(['createRequestObject', 'getUserIp']);
        $requestBuilderMock->expects($this->any())->method('createRequestObject')->willReturn(new Request());
        $requestBuilderMock->expects($this->any())->method('getUserIp')->willReturn($userIp);

        $this->parametersBuilder->expects($this->any())->method('setSearchParams')->willReturnArgument(0);

        $categoryMock = null;

        if ($category) {
            $categoryMock = $this->getMockBuilder(CategoryService::class)->disableOriginalConstructor()->setMethods([])->getMock();
        }

        /** @var Request|MockObject $result */
        $result = $requestBuilderMock->build($httpRequestMock, $categoryMock);

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
                'parametersBuilder' => $this->parametersBuilder,
                'pluginInformation' => $this->pluginInformation,
                'configRepository' => $this->configRepository,
                'loggerFactory' => $this->loggerFactory
            ])
            ->setMethods($methods)
            ->getMock();
    }
}