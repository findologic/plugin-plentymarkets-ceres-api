<?php

namespace Findologic\Tests\Services;

use Findologic\Services\PluginInfoService;
use IO\Services\TemplateConfigService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Plenty\Modules\Plugin\Contracts\PluginRepositoryContract;
use Plenty\Modules\Plugin\Models\Plugin;
use Plenty\Modules\Plugin\PluginSet\Contracts\PluginSetRepositoryContract;
use Plenty\Plugin\CachingRepository;

class PluginInfoServiceTest extends TestCase
{
    /**
     * @var MockObject|PluginRepositoryContract
     */
    private $pluginRepositoryMock;

    /**
     * @var MockObject|PluginSetRepositoryContract
     */
    private $pluginSetRepositoryMock;

    /**
     * @var MockObject|CachingRepository
     */
    private $cacheMock;

    /**
     * @var MockObject|Plugin
     */
    private $pluginMock;

    /**
     * @var PluginInfoService
     */
    private $pluginInfoService;

    /**
     * @var TemplateConfigService
     */
    private $templateConfigServiceMock;

    public function setUp()
    {
        $this->pluginRepositoryMock = $this->getMockForAbstractClass(PluginRepositoryContract::class);
        $this->pluginSetRepositoryMock = $this->getMockForAbstractClass(PluginSetRepositoryContract::class);
        $this->cacheMock = $this->getMockForAbstractClass(CachingRepository::class);
        $this->pluginMock = $this->getMockForAbstractClass(Plugin::class);
        $this->templateConfigServiceMock = $this->getMockBuilder(TemplateConfigService::class);

        $this->pluginInfoService = new PluginInfoService(
            $this->pluginRepositoryMock,
            $this->pluginSetRepositoryMock,
            $this->cacheMock,
            $this->templateConfigServiceMock
        );
    }

    public function testItReturnsCachedPluginVersionIfExists()
    {
        $this->cacheMock
            ->method('get')
            ->with(PluginInfoService::PLUGIN_VERSION_CACHE_KEY_PREFIX . 'pluginname')
            ->willReturn('1.0.0');

        $this->pluginRepositoryMock->expects($this->never())->method('getPluginByName');
        $this->cacheMock->expects($this->never())->method('put');
        $this->assertEquals('1.0.0', $this->pluginInfoService->getPluginVersion('pluginname'));
    }

    public function testPluginVersionGetterDoesNotDecoratePluginIfItAlreadyHasRequiredInfo()
    {
        $this->cacheMock->method('get')->willReturn(null);

        $this->pluginMock->versionProductive = '1.0.0';
        $this->pluginRepositoryMock->method('getPluginByName')->willReturn($this->pluginMock);

        $this->pluginRepositoryMock->expects($this->never())->method('decoratePlugin');
        $this->cacheMock
            ->expects($this->once())
            ->method('put')
            ->with(PluginInfoService::PLUGIN_VERSION_CACHE_KEY_PREFIX . 'pluginname', '1.0.0', 60*24);
        $this->assertEquals('1.0.0', $this->pluginInfoService->getPluginVersion('pluginname'));
    }

    public function testPluginVersionGetterCallsPluginDecoratorIfPluginDoesNotContainVersionInfo()
    {
        $this->cacheMock->method('get')->willReturn(null);

        $this->pluginMock->versionProductive = '';

        $decoratedPlugin = $this->getMockForAbstractClass(Plugin::class);
        $decoratedPlugin->versionProductive = '1.0.0';

        $this->pluginRepositoryMock->expects($this->once())->method('decoratePlugin')->willReturn($decoratedPlugin);
        $this->cacheMock
            ->expects($this->once())
            ->method('put')
            ->with(PluginInfoService::PLUGIN_VERSION_CACHE_KEY_PREFIX . 'pluginname', '1.0.0', 60*24);
        $this->pluginInfoService->getPluginVersion('pluginname');
    }
}
