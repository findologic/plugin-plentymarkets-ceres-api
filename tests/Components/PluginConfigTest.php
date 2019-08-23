<?php

namespace Findologic\Tests\Components;

use IO\Services\SessionStorageService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Findologic\Components\PluginConfig;
use Plenty\Plugin\ConfigRepository;
use Findologic\Constants\Plugin;

/**
 * Class PluginConfigTest
 * @package Findologic\Tests
 */
class PluginConfigTest extends TestCase
{
    /**
     * @var ConfigRepository|MockObject
     */
    private $configRepository;

    /**
     * @var SessionStorageService|MockObject
     */
    private $sessionStorageService;

    public function setUp()
    {
        $this->configRepository = $this->getMockBuilder(ConfigRepository::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->sessionStorageService = $this->getMockBuilder(SessionStorageService::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
    }

    public function testHasIsForwardedToConfigRepository()
    {
        $this->configRepository->expects($this->once())->method('has')->with('test');

        $this->getPluginConfig()->has('test');
    }

    public function testSetIsForwardedToConfigRepository()
    {
        $this->configRepository->expects($this->once())
            ->method('set')
            ->with('testKey', 'testValue');

        $this->getPluginConfig()->set('testKey', 'testValue');
    }

    public function testGetIsForwardedToConfigRepository()
    {
        $this->configRepository->expects($this->once())
            ->method('get')
            ->with('testKey');

        $this->getPluginConfig()->get('testKey');
    }

    public function testPrependIsForwardedToConfigRepository()
    {
        $this->configRepository->expects($this->once())
            ->method('prepend')
            ->with('testKey', 'testValue');

        $this->getPluginConfig()->prepend('testKey', 'testValue');
    }

    public function testPushIsForwardedToConfigRepository()
    {
        $this->configRepository->expects($this->once())
            ->method('push')
            ->with('testKey', 'testValue');

        $this->getPluginConfig()->push('testKey', 'testValue');
    }

    public function testGetShopKeyReturnsCorrectKeyBasedOnCurrentLanguage()
    {
        $this->configRepository->expects($this->once())
            ->method('get')
            ->with(Plugin::CONFIG_SHOPKEY, '')
            ->willReturn("de:ABC123\nen:DEF456");

        $this->sessionStorageService->expects($this->once())->method('getLang')->willReturn('de');

        $this->assertSame($this->getPluginConfig()->getShopKey(), 'ABC123');
    }

    public function testGetShopKeyReturnsGermanLanguageKeyIfNoKeyIsSetForCurrentLanguageInSession()
    {
        $this->configRepository->expects($this->once())
            ->method('get')
            ->with(Plugin::CONFIG_SHOPKEY, '')
            ->willReturn("de:ABC123\nen:DEF456");

        $this->sessionStorageService->expects($this->once())->method('getLang')->willReturn('jp');

        $this->assertSame($this->getPluginConfig()->getShopKey(), 'ABC123');
    }

    public function testGetShopKeyReturnsNullIfShopKeyIsEmpty()
    {
        $this->configRepository->expects($this->once())
            ->method('get')
            ->with(Plugin::CONFIG_SHOPKEY, '')
            ->willReturn('');

        $this->sessionStorageService->expects($this->never())->method('getLang');

        $this->assertSame($this->getPluginConfig()->getShopKey(), null);
    }

    public function parseShopKeysProvider()
    {
        return [
            'Shopkey is an empty string' => [
                '',
                []
            ],
            'Shopkey is only empty lines' => [
                "\n\n\n",
                []
            ],
            'Shopkey is configured for German' => [
                "de:ABC123\n",
                [
                    'de' => 'ABC123'
                ]
            ],
            'Shopkey is configured for German and English' => [
                "de:ABC123\nen:DEF456",
                [
                    'de' => 'ABC123',
                    'en' => 'DEF456'
                ]
            ],
            'Generic shopkey is configured' => [
                'ABC123',
                [
                    'ABC123'
                ]
            ],
            'Shopkey contains whitespaces' => [
                "de:   ABC123    \n",
                [
                    'de' => 'ABC123'
                ]
            ],
            'Language key contains whitespaces' => [
                "  de  :ABC123\n",
                [
                    'de' => 'ABC123'
                ]
            ],
        ];
    }

    /**
     * @dataProvider parseShopKeysProvider
     *
     * @param string $shopkeyConfig
     * @param array $shopKeysArray
     */
    public function testParseShopKeys($shopkeyConfig, $shopKeysArray)
    {
        $this->configRepository->expects($this->once())
            ->method('get')
            ->with(Plugin::CONFIG_SHOPKEY, '')
            ->willReturn($shopkeyConfig);

        $this->sessionStorageService->method('getLang')->willReturn('de');

        $pluginConfig = $this->getPluginConfig();

        $pluginConfig->getShopKey();

        $this->assertSame($pluginConfig->getShopkeys(), $shopKeysArray);
    }

    /**
     * @param array|null $methods
     * @return MockObject
     */
    protected function getPluginConfig($methods = null)
    {
        return $this->getMockBuilder(PluginConfig::class)
            ->setConstructorArgs(
                [
                    'configRepository' => $this->configRepository,
                    'sessionStorageService' => $this->sessionStorageService
                ]
            )
            ->setMethods($methods)
            ->getMock();
    }
}
