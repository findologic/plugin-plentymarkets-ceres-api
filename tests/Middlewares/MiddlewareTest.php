<?php

namespace Findologic\Tests\Middlewares;

use Findologic\Services\SearchService;
use Plenty\Log\Contracts\LoggerContract;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @var Middleware|MockObject
     */
    protected $middleware;

    public function setUp()
    {
        $this->searchService = $this->getMockBuilder(SearchService::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->eventDispatcher = $this->getMockBuilder(Dispatcher::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->pluginConfig = $this->getMockBuilder(PluginConfig::class)->disableOriginalConstructor()->setMethods([])->getMock();
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

        $this->eventDispatcher->expects($this->exactly(2))->method('listen')
            ->withConsecutive(['IO.Resources.Import'], ['Ceres.Search.Query']);

        $this->request->method('getUri')->willReturn('https://testshop.com/testpage');

        $this->runBefore();
    }

    public function testIsSearchPageAndIsNotActiveOnCatPage()
    {
        $this->pluginConfig->expects($this->any())->method('getShopKey')->willReturn('testShopKey');

        $this->searchService->expects($this->once())->method('aliveTest')->willReturn(true);

        $this->eventDispatcher->expects($this->exactly(4))->method('listen')->withConsecutive(
            ['IO.Resources.Import'],
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

        $this->searchService->expects($this->once())->method('aliveTest')->willReturn(true);

        $this->eventDispatcher->expects($this->exactly(2))->method('listen')->withConsecutive(
            ['IO.Resources.Import'],
            ['Ceres.Search.Query']
        );

        $this->request->method('getUri')->willReturn('https://testshop.com/testpage');

        $this->runBefore();
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
        $loggerMock = $this->getMockBuilder(LoggerContract::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->middleware->method('getLoggerObject')->willReturn($loggerMock);

        $this->middleware->before(
            $this->request
        );
    }
}
