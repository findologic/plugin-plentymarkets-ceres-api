<?php

namespace Findologic\Tests\Providers;

use Findologic\Services\SearchService;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Plugin\ConfigRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Findologic\Providers\FindologicServiceProvider;
use Findologic\Constants\Plugin;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Http\Request;

/**
 * Class SearchServiceTest
 * @package Findologic\Tests
 */
class FindologicServiceProviderTest extends TestCase
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
     * @var ConfigRepository|MockObject
     */
    protected $configRepository;

    /**
     * @var FindologicServiceProvider
     */
    protected $findologicServiceProvider;

    public function setUp()
    {
        $this->searchService = $this->getMockBuilder(SearchService::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->eventDispatcher = $this->getMockBuilder(Dispatcher::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->configRepository = $this->getMockBuilder(ConfigRepository::class)->disableOriginalConstructor()->setMethods([])->getMock();

        $this->findologicServiceProvider = $this->getMockBuilder(FindologicServiceProvider::class)->disableOriginalConstructor()->setMethods(['getLoggerObject'])->getMock();
        $loggerMock = $this->getMockBuilder(LoggerContract::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->findologicServiceProvider->method('getLoggerObject')->willReturn($loggerMock);
    }

    public function testBootShopKeyNotSet()
    {
        $this->configRepository->expects($this->once())->method('get')->willReturn('');

        $this->searchService->expects($this->never())->method('aliveTest');

        $this->eventDispatcher->expects($this->never())->method('listen');

        $this->runBoot();
    }

    public function testBootShopKeySetAndAliveTestFails()
    {
        $this->configRepository->expects($this->once())->method('get')->willReturn('testShopKey');

        $this->searchService->expects($this->once())->method('aliveTest')->willThrowException(new \Exception());

        $this->eventDispatcher->expects($this->never())->method('listen');

        $this->runBoot();
    }

    public function testIsNotSearchPageAndIsNotActiveOnCatPage()
    {
        $this->configRepository->expects($this->any())->method('get')->willReturnMap([
            [
                Plugin::CONFIG_SHOPKEY, false, 'testShopKey'
            ]
        ]);

        $this->searchService->expects($this->once())->method('aliveTest')->willReturn(true);

        $this->eventDispatcher->expects($this->exactly(2))->method('listen')
            ->withConsecutive(['IO.Resources.Import'], ['Ceres.Search.Query']);

        $this->request->method('getUri')->willReturn('https://testshop.com/testpage');

        $this->runBoot();
    }

    public function testIsSearchPageAndIsNotActiveOnCatPage()
    {
        $this->configRepository->expects($this->any())->method('get')->willReturnMap([
            [
                Plugin::CONFIG_SHOPKEY, false, 'testShopKey'
            ]
        ]);

        $this->searchService->expects($this->once())->method('aliveTest')->willReturn(true);

        $this->eventDispatcher->expects($this->exactly(4))->method('listen')->withConsecutive(
            ['IO.Resources.Import'],
            ['Ceres.Search.Options'],
            ['IO.Component.Import'],
            ['Ceres.Search.Query']
        );

        $this->request->method('getUri')->willReturn('https://testshop.com/search');

        $this->runBoot();
    }

    public function testIsNotSearchPageAndIsActiveOnCatPage()
    {
        $this->configRepository->expects($this->any())->method('get')->willReturn('testConfigValue');

        $this->searchService->expects($this->once())->method('aliveTest')->willReturn(true);

        $this->eventDispatcher->expects($this->exactly(4))->method('listen')->withConsecutive(
            ['IO.Resources.Import'],
            ['Ceres.Search.Options'],
            ['IO.Component.Import'],
            ['Ceres.Search.Query']
        );

        $this->request->method('getUri')->willReturn('https://testshop.com/testpage');

        $this->runBoot();
    }

    protected function runBoot()
    {
        $this->findologicServiceProvider->boot(
            $this->configRepository,
            $this->eventDispatcher,
            $this->request,
            $this->searchService
        );
    }
}