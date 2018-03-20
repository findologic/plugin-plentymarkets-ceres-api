<?php

namespace Findologic\PluginPlentymarketsApi\Tests\Providers;

use Findologic\PluginPlentymarketsApi\Providers\FindologicServiceProvider;
use Findologic\PluginPlentymarketsApi\Services\SearchService;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Http\Request;

/**
 * Class FindologicServiceProviderTest
 * @package Findologic\PluginPlentymarketsApi\Tests
 */
class FindologicServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testBootPluginDisabled()
    {
        $configRepositoryMock = $this->getMockBuilder(ConfigRepository::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $configRepositoryMock->expects($this->atLeastOnce())->method('get')->with('findologic.enabled', false)->willReturn(false);
        $eventDispatcherMock = $this->getMockBuilder(Dispatcher::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $eventDispatcherMock->expects($this->never())->method('listen');
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $searchServiceMock = $this->getMockBuilder(SearchService::class)->disableOriginalConstructor()->getMock();

        /** @var FindologicServiceProvider|\PHPUnit_Framework_MockObject_MockObject $serviceProviderMock*/
        $serviceProviderMock = $this->getMockBuilder(FindologicServiceProvider::class)->disableOriginalConstructor()->setMethods(null)->getMock();
        $serviceProviderMock->boot($configRepositoryMock, $eventDispatcherMock, $requestMock, $searchServiceMock);
    }

    public function testBootPluginEnabled()
    {
        $configRepositoryMock = $this->getMockBuilder(ConfigRepository::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $configRepositoryMock->expects($this->atLeastOnce())->method('get')->with('findologic.enabled', false)->willReturn(true);
        $eventDispatcherMock = $this->getMockBuilder(Dispatcher::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $eventDispatcherMock->expects($this->exactly(2))->method('listen');
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $searchServiceMock = $this->getMockBuilder(SearchService::class)->disableOriginalConstructor()->getMock();

        /** @var FindologicServiceProvider|\PHPUnit_Framework_MockObject_MockObject $serviceProviderMock*/
        $serviceProviderMock = $this->getMockBuilder(FindologicServiceProvider::class)->disableOriginalConstructor()->setMethods(null)->getMock();
        $serviceProviderMock->boot($configRepositoryMock, $eventDispatcherMock, $requestMock, $searchServiceMock);
    }
}