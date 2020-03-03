<?php

namespace Findologic\Tests\Api\Request;

use Findologic\Api\Request\RequestBuilder;
use Findologic\Api\Request\ParametersBuilder;
use Findologic\Api\Request\Request;
use Ceres\Helper\ExternalSearch;
use Findologic\Helpers\Tags;
use IO\Services\CategoryService;
use IO\Services\WebstoreConfigurationService;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Plugin\Log\LoggerFactory;
use Plenty\Log\Contracts\LoggerContract;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Findologic\Components\PluginConfig;
use Findologic\Constants\Plugin;

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
     * @var PluginConfig|MockObject
     */
    protected $pluginConfig;

    /**
     * @var LoggerFactory|MockObject
     */
    protected $loggerFactory;

    /**
     * @var LoggerContract|MockObject
     */
    protected $logger;

    /**
     * @var WebstoreConfigurationService|MockObject
     */
    protected $webstoreConfigurationService;

    /**
     * @var Tags
     */
    protected $tagsHelper;

    public function setUp()
    {
        $this->parametersBuilder = $this->getMockBuilder(ParametersBuilder::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->pluginConfig = $this->getMockBuilder(PluginConfig::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->logger = $this->getMockBuilder(LoggerContract::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->webstoreConfigurationService = $this->getMockBuilder(WebstoreConfigurationService::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->loggerFactory = $this->getMockBuilder(LoggerFactory::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->loggerFactory->expects($this->any())->method('getLogger')->willReturn($this->logger);
        $this->tagsHelper = $this->getMockBuilder(Tags::class)->disableOriginalConstructor()->setMethods()->getMock();
    }

    public function buildAliveRequestProvider()
    {
        return [
            'Build alive request' => [
                'https://service.findologic.com/ps/xml_2.1/alivetest.php',
                [
                    'shopkey' => 'TESTSHOPKEY'
                ]
            ]
        ];
    }

    /**
     * @dataProvider buildAliveRequestProvider
     *
     * @param string $expectedUrl
     * @param array $expectedParams
     */
    public function testBuildAliveRequest(string $expectedUrl, array $expectedParams)
    {
        $requestBuilderMock = $this->getRequestBuilderMock(['createRequestObject']);
        $requestBuilderMock->expects($this->any())->method('createRequestObject')->willReturn(new Request());

        $this->pluginConfig->expects($this->once())->method('getShopKey')->willReturn('TESTSHOPKEY');

        /** @var Request|MockObject $result */
        $result = $requestBuilderMock->buildAliveRequest();
        $this->assertEquals($expectedUrl, $result->getUrl());
        $this->assertEquals($expectedParams, $result->getParams());
    }

    public function buildProvider()
    {
        return [
            'Build - No user ip provided' => [
                false,
                'https://service.findologic.com/ps/xml_2.1/index.php',
                false,
                [
                    'outputAdapter' => Plugin::API_OUTPUT_ADAPTER,
                    'shopkey' => 'TESTSHOPKEY',
                    'revision' => '0.0.1'
                ]
            ],
            'Category page request' => [
                '127.0.0.1',
                'https://service.findologic.com/ps/xml_2.1/selector.php',
                true,
                [
                    'outputAdapter' => Plugin::API_OUTPUT_ADAPTER,
                    'shopkey' => 'TESTSHOPKEY',
                    'userip' => '127.0.0.1',
                    'revision' => '0.0.1'
                ]
            ],
            'Search page request' => [
                '127.0.0.1',
                'https://service.findologic.com/ps/xml_2.1/index.php',
                false,
                [
                    'outputAdapter' => Plugin::API_OUTPUT_ADAPTER,
                    'shopkey' => 'TESTSHOPKEY',
                    'userip' => '127.0.0.1',
                    'revision' => '0.0.1'
                ]
            ]
        ];
    }

    /**
     * @dataProvider buildProvider
     *
     * @param string $userIp
     * @param string $expectedUrl
     * @param bool $category
     * @param array $expectedParams
     */
    public function testBuild(
        string $userIp,
        string $expectedUrl,
        bool $category,
        array $expectedParams
    ) {
        /** @var HttpRequest|MockObject $httpRequestMock */
        $httpRequestMock = $this->getMockBuilder(HttpRequest::class)->disableOriginalConstructor()->setMethods([])->getMock();

        /** @var ExternalSearch|MockObject $searchQueryMock */
        $searchQueryMock = $this->getMockBuilder(ExternalSearch::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $searchQueryMock->searchString = 'Test';

        $this->pluginConfig->expects($this->once())->method('getShopKey')->willReturn('TESTSHOPKEY');

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

    public function getUrlProvider()
    {
        return [
            'Secure domain is configured' => [
                'https://example.com',
                '',
                'https://service.findologic.com/ps/example.com/index.php'
            ],
            'Domain is configured' => [
                '',
                'http://example.com',
                'https://service.findologic.com/ps/example.com/index.php'
            ],
            'No domain is configured' => [
                '',
                '',
                'https://service.findologic.com/ps/xml_2.1/index.php'
            ]
        ];
    }

    /**
     * @dataProvider getUrlProvider
     *
     * @param string $domainSsl
     * @param string $domain
     * @param string $generatedUrl
     */
    public function testGetUrl(string $domainSsl, string $domain, string $generatedUrl)
    {
        $webstoreConfigMock = $this->getMockBuilder(WebstoreConfiguration::class)->disableOriginalConstructor()->getMock();
        $webstoreConfigMock->domainSsl = $domainSsl;
        $webstoreConfigMock->domain = $domain;

        $this->webstoreConfigurationService->expects($this->once())->method('getWebstoreConfig')->willReturn(
            $webstoreConfigMock
        );

        $requestBuilderMock = $this->getRequestBuilderMock();

        $this->assertEquals($generatedUrl, $requestBuilderMock->getUrl());
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
                'pluginConfig' => $this->pluginConfig,
                'loggerFactory' => $this->loggerFactory,
                'webstoreConfigurationService' => $this->webstoreConfigurationService,
                'tagsHelper' => $this->tagsHelper
            ])
            ->setMethods($methods)
            ->getMock();
    }
}
