<?php

namespace Findologic\Tests\Providers;

use Findologic\Providers\FindologicServiceProvider;
use Findologic\Services\SearchService;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Http\Request;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class FindologicServiceProviderTest
 * @package Findologic\Tests
 */
class FindologicServiceProviderTest extends TestCase
{
    public function testBootPluginDisabled()
    {
        $configRepositoryMock = $this->getMockBuilder(ConfigRepository::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $configRepositoryMock->expects($this->at(0))->method('get')->with('Findologic.enabled', false)->willReturn(false);
        $eventDispatcherMock = $this->getMockBuilder(Dispatcher::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $eventDispatcherMock->expects($this->never())->method('listen');
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $searchServiceMock = $this->getMockBuilder(SearchService::class)->disableOriginalConstructor()->getMock();

        /** @var FindologicServiceProvider|MockObject $serviceProviderMock*/
        $serviceProviderMock = $this->getMockBuilder(FindologicServiceProvider::class)->disableOriginalConstructor()->setMethods(null)->getMock();
        $serviceProviderMock->boot($configRepositoryMock, $eventDispatcherMock, $requestMock, $searchServiceMock);
    }

    public function testBootPluginEnabled()
    {
        $configRepositoryMock = $this->getMockBuilder(ConfigRepository::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $configRepositoryMock->expects($this->at(0))->method('get')->with('Findologic.enabled', false)->willReturn(true);
        $configRepositoryMock->expects($this->at(1))->method('get')->with('Findologic.shopkey', false)->willReturn('SHOPKEY');
        $eventDispatcherMock = $this->getMockBuilder(Dispatcher::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $eventDispatcherMock->expects($this->exactly(3))->method('listen');
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $searchServiceMock = $this->getMockBuilder(SearchService::class)->disableOriginalConstructor()->getMock();

        /** @var FindologicServiceProvider|MockObject $serviceProviderMock*/
        $serviceProviderMock = $this->getMockBuilder(FindologicServiceProvider::class)->disableOriginalConstructor()->setMethods(null)->getMock();
        $serviceProviderMock->boot($configRepositoryMock, $eventDispatcherMock, $requestMock, $searchServiceMock);
    }
}