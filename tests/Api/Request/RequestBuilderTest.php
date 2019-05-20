<?php

namespace Findologic\Tests\Api\Request;

use Findologic\Api\Request\RequestBuilder;
use Findologic\Api\Request\ParametersBuilder;
use Findologic\Api\Request\Request;
use Findologic\Constants\Plugin;
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
                'https://service.findologic.com/ps/xml_2.0/alivetest.php',
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

        $this->configRepository->expects($this->once())->method('get')->with('Findologic.shopkey')->willReturn('TESTSHOPKEY');

        /** @var Request|MockObject $result */
        $result = $requestBuilderMock->buildAliveRequest();
        $this->assertEquals($expectedUrl, $result->getUrl());
        $this->assertEquals($expectedParams, $result->getParams());
    }

    public function providerBuild()
    {
        return [
            'Build - No user ip provided' => [
                false,
                'https://service.findologic.com/ps/xml_2.0/index.php',
                false,
                [
                    'shopkey' => 'TESTSHOPKEY',
                    'revision' => '0.0.1'
                ]
            ],
            'Category page request' => [
                '127.0.0.1',
                'https://service.findologic.com/ps/xml_2.0/selector.php',
                true,
                [
                    'shopkey' => 'TESTSHOPKEY',
                    'userip' => '127.0.0.1',
                    'revision' => '0.0.1'
                ]
            ],
            'Search page request' => [
                '127.0.0.1',
                'https://service.findologic.com/ps/xml_2.0/index.php',
                false,
                [
                    'shopkey' => 'TESTSHOPKEY',
                    'userip' => '127.0.0.1',
                    'revision' => '0.0.1'
                ]
            ]
        ];
    }

    /**
     * @dataProvider providerBuild
     */
    public function testBuild($userIp, $expectedUrl, $category, $expectedParams)
    {
        /** @var HttpRequest|MockObject $httpRequestMock */
        $httpRequestMock = $this->getMockBuilder(HttpRequest::class)->disableOriginalConstructor()->setMethods([])->getMock();

        /** @var ExternalSearch|MockObject $searchQueryMock */
        $searchQueryMock = $this->getMockBuilder(ExternalSearch::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $searchQueryMock->searchString = 'Test';

        $this->configRepository->expects($this->once())->method('get')->with('Findologic.shopkey')->willReturn('TESTSHOPKEY');

        $requestBuilderMock = $this->getRequestBuilderMock(['createRequestObject', 'getUserIp', 'getPluginVersion']);
        $requestBuilderMock->expects($this->any())->method('createRequestObject')->willReturn(new Request());
        $requestBuilderMock->expects($this->any())->method('getUserIp')->willReturn($userIp);
        $requestBuilderMock->expects($this->any())->method('getPluginVersion')->willReturn('0.0.1');

        $this->parametersBuilder->expects($this->any())->method('setSearchParams')->willReturnArgument(0);

        $categoryMock = null;

        if ($category) {
            $categoryMock = $this->getMockBuilder(CategoryService::class)->disableOriginalConstructor()->setMethods([])->getMock();
        }

        /** @var Request|MockObject $result */
        $result = $requestBuilderMock->build($httpRequestMock, $searchQueryMock, $categoryMock);

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
                'configRepository' => $this->configRepository,
                'loggerFactory' => $this->loggerFactory
            ])
            ->setMethods($methods)
            ->getMock();
    }
}