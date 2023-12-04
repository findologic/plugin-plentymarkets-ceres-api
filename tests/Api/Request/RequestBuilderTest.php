<?php

namespace Findologic\Tests\Api\Request;

use Findologic\Helpers\Tags;
use PHPUnit\Framework\TestCase;
use Ceres\Helper\ExternalSearch;
use Findologic\Constants\Plugin;
use IO\Services\CategoryService;
use Findologic\Api\Request\Request;
use Plenty\Plugin\Log\LoggerFactory;
use Findologic\Components\PluginConfig;
use Plenty\Log\Contracts\LoggerContract;
use Findologic\Api\Request\RequestBuilder;
use Findologic\Services\PluginInfoService;
use PHPUnit\Framework\MockObject\MockObject;
use Plenty\Modules\Category\Models\Category;
use Findologic\Api\Request\ParametersBuilder;
use IO\Services\WebstoreConfigurationService;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Modules\System\Models\WebstoreConfiguration;

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

    /**
     * @var PluginInfoService|MockObject
     */
    protected $pluginInfoService;

    public function setUp(): void
    {
        $this->pluginConfig = $this->getMockBuilder(PluginConfig::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerContract::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->webstoreConfigurationService = $this->getMockBuilder(WebstoreConfigurationService::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->loggerFactory = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->loggerFactory->expects($this->any())->method('getLogger')->willReturn($this->logger);
        $this->tagsHelper = $this->getMockBuilder(Tags::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['isTagPage', 'getTagIdFromUri'])
            ->getMock();
        $this->pluginInfoService = $this->getMockBuilder(PluginInfoService::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
            $this->parametersBuilder = $this->getMockBuilder(ParametersBuilder::class)
            ->setConstructorArgs([
                'loggerFactory' => $this->loggerFactory,
                'tagsHelper' => $this->tagsHelper,
            ])
            ->setMethodsExcept(['setSearchParams', 'getCategoryName', 'getCategoryTree'])
            ->getMock();
    }

    public function buildAliveRequestProvider()
    {
        return [
            'Build alive request' => [
                'https://service.findologic.com/ps/json_1.0/alivetest.php',
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
        $requestBuilderMock = $this->getRequestBuilderMock(['buildAliveRequest', 'getUrl', 'getShopUrl']);
        $this->pluginConfig->expects($this->once())->method('getShopKey')->willReturn('TESTSHOPKEY');

        /** @var Request|MockObject $result */
        $result = $requestBuilderMock->buildAliveRequest();

        $this->assertEquals($expectedUrl, $result['shopUrl']);
        $this->assertEquals($expectedParams['shopkey'], $result['shopKey']);
    }

    public function buildProvider()
    {
        return [
            'Build - No user ip provided' => [
                false,
                'https://service.findologic.com/ps/json_1.0/index.php',
                false,
                [
                    'shopKey' => 'TESTSHOPKEY',
                    'revision' => '0.0.1',
                    'shopType' => 'Plentymarkets',
                    'shopVersion' => '5.0.30'
                ]
            ],
            'Category page request' => [
                '127.0.0.1',
                'https://service.findologic.com/ps/json_1.0/selector.php',
                true,
                [
                    'shopKey' => 'TESTSHOPKEY',
                    'userIp' => '127.0.0.1',
                    'revision' => '0.0.1',
                    'shopType' => 'Plentymarkets',
                    'shopVersion' => '5.0.30'
                ]
            ],
            'Search page request' => [
                '127.0.0.1',
                'https://service.findologic.com/ps/json_1.0/index.php',
                false,
                [
                    'shopKey' => 'TESTSHOPKEY',
                    'userIp' => '127.0.0.1',
                    'revision' => '0.0.1',
                    'shopType' => 'Plentymarkets',
                    'shopVersion' => '5.0.30'
                ]
            ]
        ];
    }

    /**
     * @dataProvider buildProvider
     *
     * @param string|bool $userIp
     * @param string $expectedUrl
     * @param bool $category
     * @param array $expectedParams
     */
    public function testBuild(
        $userIp,
        string $expectedUrl,
        bool $category,
        array $expectedParams
    ) {
        /** @var HttpRequest|MockObject $httpRequestMock */
        $httpRequestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var ExternalSearch|MockObject $searchQueryMock */
        $searchQueryMock = $this->getMockBuilder(ExternalSearch::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $searchQueryMock->searchString = 'Test';

        $this->pluginConfig->expects($this->once())->method('getShopKey')->willReturn('TESTSHOPKEY');

        $requestBuilderMock = $this->getRequestBuilderMock(['build', 'getUrl', 'getShopUrl', 'setDefaultValues']);
        $requestBuilderMock->expects($this->any())->method('getUserIp')->willReturn($userIp);
        $requestBuilderMock->expects($this->any())->method('getPluginVersion')->willReturn('0.0.1');

        $this->pluginInfoService->method('getPluginVersion')->with('ceres')->willReturn('5.0.30');

        $categoryMock = null;

        if ($category) {
            $categoryMock = $this->createMock(Category::class);
        }

        /** @var array|MockObject $result */
        $result = $requestBuilderMock->build(RequestBuilder::TYPE_SEARCH, $httpRequestMock, $searchQueryMock, $categoryMock);

        $this->assertEquals($expectedUrl, $result['shopUrl']);

        foreach ($expectedParams as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }
        
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
                'https://service.findologic.com/ps/json_1.0/index.php'
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
        $webstoreConfigMock = $this->getMockBuilder(WebstoreConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $webstoreConfigMock->domainSsl = $domainSsl;
        $webstoreConfigMock->domain = $domain;

        $this->webstoreConfigurationService->expects($this->once())->method('getWebstoreConfig')->willReturn(
            $webstoreConfigMock
        );

        $requestBuilderMock = $this->getRequestBuilderMock(['getUrl', 'getShopUrl']);

        $this->assertEquals($generatedUrl, $requestBuilderMock->getUrl());
    }

    /**
     * @param array|null $methods
     * @return RequestBuilder|MockObject
     */
    protected function getRequestBuilderMock($methods)
    {
        return $this->getMockBuilder(RequestBuilder::class)
        ->setConstructorArgs([
            'parametersBuilder' => $this->parametersBuilder,
            'pluginConfig' => $this->pluginConfig,
            'loggerFactory' => $this->loggerFactory,
            'webstoreConfigurationService' => $this->webstoreConfigurationService,
            'tagsHelper' => $this->tagsHelper,
            'pluginInfoService' => $this->pluginInfoService
        ])
        ->setMethodsExcept($methods)
        ->getMock();
    }
}
