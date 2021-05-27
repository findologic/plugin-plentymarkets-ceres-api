<?php

namespace Findologic\Tests\Middlewares;

use Findologic\Constants\Plugin;
use Findologic\Services\SearchService;
use Findologic\Validators\PluginConfigurationValidator;
use IO\Services\SessionStorageService;
use Plenty\Log\Contracts\LoggerContract;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Http\Request;
use Findologic\Components\PluginConfig;
use Findologic\Middlewares\Middleware;

/**
 * Class MiddlewareTest
 * @package Findologic\Tests
 */
class MiddlewareTest extends TestCase
{
    /**
     * @var SearchService|MockObject
     */
    protected $searchService;

    /**
     * @var Request|MockObject
     */
    protected $request;

    /**
     * @var Dispatcher|MockObject
     */
    protected $eventDispatcher;

    /**
     * @var PluginConfig|MockObject
     */
    protected $pluginConfig;

    /**
     * @var PluginConfigurationValidator|MockObject
     */
    protected $pluginConfigurationValidatorMock;

    /**
     * @var Middleware|MockObject
     */
    protected $middleware;

    public function setUp()
    {
        $this->searchService = $this->getMockBuilder(SearchService::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->eventDispatcher = $this->getMockBuilder(Dispatcher::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->pluginConfig = $this->getMockBuilder(PluginConfig::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->pluginConfigurationValidatorMock = $this->getMockBuilder(PluginConfigurationValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pluginConfigurationValidatorMock->expects($this->any())
            ->method('validate')
            ->willReturn(true);

        global $classInstances;
        $classInstances[PluginConfigurationValidator::class] = $this->pluginConfigurationValidatorMock;
    }

    public function testBootShopKeyNotSet()
    {
        $this->pluginConfig->expects($this->once())->method('getShopKey')->willReturn('');

        $this->searchService->expects($this->never())->method('aliveTest');

        $this->eventDispatcher->expects($this->never())->method('listen');

        $this->runBefore();
    }

    public function testBootShopKeySetAndAliveTestFails()
    {
        $this->pluginConfig->expects($this->once())->method('getShopKey')->willReturn('testShopKey');

        $this->eventDispatcher->expects($this->never())->method('listen');

        $this->runBefore();
    }

    public function testIsNotSearchPageAndIsNotActiveOnCatPage()
    {
        $this->pluginConfig->expects($this->any())->method('getShopKey')->willReturn('testShopKey');

        $this->searchService->expects($this->once())->method('aliveTest')->willReturn(true);

        $this->eventDispatcher->expects($this->once())->method('listen')->with('IO.Resources.Import');

        $this->runBefore();
    }

    public function testIsSearchPageAndIsNotActiveOnCatPage()
    {
        $this->pluginConfig->expects($this->any())->method('getShopKey')->willReturn('testShopKey');

        $this->searchService->expects($this->once())->method('aliveTest')->willReturn(true);

        $this->eventDispatcher->expects($this->exactly(6))->method('listen')->withConsecutive(
            ['IO.Resources.Import'],
            ['IO.ctx.search'],
            ['IO.ctx.category.item'],
            ['Ceres.Search.Options'],
            ['IO.Component.Import'],
            ['Ceres.Search.Query']
        );

        $this->request->method('getUri')->willReturn('https://testshop.com/search');

        $this->runBefore();
    }

    public function testIsNotSearchPageAndIsActiveOnCatPage()
    {
        $this->pluginConfig->expects($this->any())->method('getShopKey')->willReturn('testConfigValue');
        $this->pluginConfig->expects($this->once())
            ->method('get')
            ->with(Plugin::CONFIG_NAVIGATION_ENABLED)
            ->willReturn(true);

        $this->searchService->expects($this->once())->method('aliveTest')->willReturn(true);

        $this->eventDispatcher->expects($this->exactly(6))->method('listen')->withConsecutive(
            ['IO.Resources.Import'],
            ['IO.ctx.search'],
            ['IO.ctx.category.item'],
            ['Ceres.Search.Options'],
            ['IO.Component.Import'],
            ['Ceres.Search.Query']
        );

        $this->request->method('getUri')->willReturn('https://testshop.com/testpage');

        $this->runBefore();
    }

    public function shopkeyConfigProvider(): array
    {
        return [
            'no shopkey configured' => [
                'lang' => 'de',
                'rawShopkeyConfig' => '',
                'isAlive' => true
            ],
            'shopkey for antoher language is configured' => [
                'lang' => 'de',
                'rawShopkeyConfig' => 'fr: ABCDABCDABCDABCDABCDABCDABCDABCD',
                'isAlive' => true
            ],
            'invalid shopkey for current language is configured' => [
                'lang' => 'de',
                'rawShopkeyConfig' => "de: invalid shopkey :)\nfr: ABCDABCDABCDABCDABCDABCDABCDABCD",
                'isAlive' => false // Alivetest fails as there is no such shopkey.
            ]
        ];
    }

    /**
     * @dataProvider shopkeyConfigProvider
     */
    public function testPlentymarketsSearchIsUsedWhenNoOrWrongShopkeyIsConfiguredForTheCurrentLanguage(
        string $lang,
        string $rawShopkeyConfig,
        bool $isAlive
    ) {
        $configRepositoryMock = $this->getMockBuilder(ConfigRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configRepositoryMock->expects($this->once())
            ->method('get')
            ->with(Plugin::CONFIG_SHOPKEY)
            ->willReturn($rawShopkeyConfig);

        $sessionStorageServiceMock = $this->getMockBuilder(SessionStorageService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sessionStorageServiceMock->expects($this->any())
            ->method('getLang')
            ->with()
            ->willReturn($lang);

        $pluginConfig = new PluginConfig($configRepositoryMock, $sessionStorageServiceMock);

        $searchServiceMock = $this->getMockBuilder(SearchService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $searchServiceMock->expects($this->any())
            ->method('aliveTest')
            ->willReturn($isAlive);

        $eventDispatcherMock = $this->getMockBuilder(Dispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $middleware = new Middleware($pluginConfig, $searchServiceMock, $eventDispatcherMock);

        // Ensure Findologic is not triggered.
        $eventDispatcherMock->expects($this->never())->method('listen');

        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $middleware->before($requestMock);
    }

    public function testPlentymarketsIsTriggeredOnNavigationPagesIfConfigIsDisabled()
    {
        $configRepositoryMock = $this->getMockBuilder(ConfigRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configRepositoryMock->expects($this->any())
            ->method('get')
            ->withConsecutive([Plugin::CONFIG_SHOPKEY], [Plugin::CONFIG_NAVIGATION_ENABLED])
            ->willReturn('de: ABCDABCDABCDABCDABCDABCDABCDABCD', false);

        $sessionStorageServiceMock = $this->getMockBuilder(SessionStorageService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sessionStorageServiceMock->expects($this->any())
            ->method('getLang')
            ->with()
            ->willReturn('de');

        $pluginConfig = new PluginConfig($configRepositoryMock, $sessionStorageServiceMock);

        $searchServiceMock = $this->getMockBuilder(SearchService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $searchServiceMock->expects($this->once())
            ->method('aliveTest')
            ->willReturn(true);

        $eventDispatcherMock = $this->getMockBuilder(Dispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $middleware = new Middleware($pluginConfig, $searchServiceMock, $eventDispatcherMock);

        // Ensure snippets get loaded but Findologic is not triggered.
        $eventDispatcherMock->expects($this->once())->method('listen')->with('IO.Resources.Import');

        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $requestMock->expects($this->once())
            ->method('getUri')
            ->willReturn('https://your-shop.com/chairs-and-stools');

        $middleware->before($requestMock);
    }

    public function properConfigProvider(): array
    {
        return [
            'search shopkey for current language is configured' => [
                'lang' => 'de',
                'rawShopkeyConfig' => 'de: ABCDABCDABCDABCDABCDABCDABCDABCD',
                'currentPage' => 'https://your-shop.com/search?query=blub'
            ],
            'navigation shopkey for current language is configured' => [
                'lang' => 'de',
                'rawShopkeyConfig' => 'de: ABCDABCDABCDABCDABCDABCDABCDABCD',
                'currentPage' => 'https://your-shop.com/chairs-and-stools'
            ],
            'search shopkey for current language and another language is configured' => [
                'lang' => 'fr',
                'rawShopkeyConfig' => "de: ABCDABCDABCDABCDABCDABCDABCDABCD\nfr: 12341234123412341234123412341234",
                'currentPage' => 'https://your-shop.com/search?query=blub'
            ],
            'navigation shopkey for current language and another language is configured' => [
                'lang' => 'fr',
                'rawShopkeyConfig' => "de: ABCDABCDABCDABCDABCDABCDABCDABCD\nfr: 12341234123412341234123412341234",
                'currentPage' => 'https://your-shop.com/chairs-and-stools'
            ],
        ];
    }

    /**
     * @dataProvider properConfigProvider
     */
    public function testFindologicIsTriggeredIfProperlyConfigured(
        string $lang,
        string $rawShopkeyConfig,
        string $currentPage
    ) {
        $configRepositoryMock = $this->getMockBuilder(ConfigRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configRepositoryMock->expects($this->any())
            ->method('get')
            ->withConsecutive([Plugin::CONFIG_SHOPKEY], [Plugin::CONFIG_NAVIGATION_ENABLED])
            ->willReturn($rawShopkeyConfig, true);

        $sessionStorageServiceMock = $this->getMockBuilder(SessionStorageService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sessionStorageServiceMock->expects($this->any())
            ->method('getLang')
            ->with()
            ->willReturn($lang);

        $pluginConfig = new PluginConfig($configRepositoryMock, $sessionStorageServiceMock);

        $searchServiceMock = $this->getMockBuilder(SearchService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $searchServiceMock->expects($this->once())
            ->method('aliveTest')
            ->willReturn(true);

        $eventDispatcherMock = $this->getMockBuilder(Dispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $middleware = new Middleware($pluginConfig, $searchServiceMock, $eventDispatcherMock);

        // Ensure Findologic is triggered.
        $eventDispatcherMock->expects($this->exactly(6))
            ->method('listen')
            ->withConsecutive(
                ['IO.Resources.Import'],
                ['IO.ctx.search'],
                ['IO.ctx.category.item'],
                ['Ceres.Search.Options'],
                ['IO.Component.Import'],
                ['Ceres.Search.Query']
            );

        $requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $requestMock->expects($this->once())
            ->method('getUri')
            ->willReturn($currentPage);

        $middleware->before($requestMock);
    }

    protected function runBefore()
    {
        $this->middleware = $this->getMockBuilder(Middleware::class)
            ->setConstructorArgs([
                'pluginConfig' => $this->pluginConfig,
                'searchService' => $this->searchService,
                'eventDispatcher' => $this->eventDispatcher
            ])
            ->setMethods(
                [
                    'getLoggerObject'
                ]
            )
            ->getMock();
        $loggerMock = $this->getMockBuilder(LoggerContract::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->middleware->method('getLoggerObject')->willReturn($loggerMock);

        $this->middleware->before(
            $this->request
        );
    }
}
