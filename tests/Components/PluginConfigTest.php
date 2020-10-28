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

    public function testGetShopKeyReturnsNullIfShopKeyIsEmpty()
    {
        $this->configRepository->expects($this->once())
            ->method('get')
            ->with(Plugin::CONFIG_SHOPKEY, '')
            ->willReturn('');

        $this->sessionStorageService->expects($this->never())->method('getLang');

        $this->assertSame($this->getPluginConfig()->getShopKey(), null);
    }

    public function getShopKeyProvider()
    {
        return [
            'It returns correct key based on current language' => [
                "de:ABC123\nen:DEF456",
                'de',
                'ABC123'
            ],
            'It returns null when no generic shopkey and no shopkey for current language are configured 1' => [
                "de:ABC123\njp:DEF456",
                'en',
                null
            ],
            'It returns null when no generic shopkey and no shopkey for current language are configured 2' => [
                "en:ABC123",
                'de',
                null
            ],
            'It returns generic shopkey if no shopkey for current language exists 1' => [
                "ABC123\nde:DEF456",
                'en',
                'ABC123'
            ],
            'It returns generic shopkey if no shopkey for current language exists 2' => [
                "en:ABC123\nDEF456",
                'de',
                'DEF456'
            ]
        ];
    }

    /**
     * @dataProvider getShopKeyProvider
     *
     * @param string $configRepositoryGetReturnValue
     * @param string $currentLang
     * @param string|null $returnValue
     */
    public function testGetShopKey($configRepositoryGetReturnValue, $currentLang, $returnValue)
    {
        $this->configRepository->expects($this->once())
            ->method('get')
            ->with(Plugin::CONFIG_SHOPKEY, '')
            ->willReturn($configRepositoryGetReturnValue);

        $this->sessionStorageService->expects($this->once())->method('getLang')->willReturn($currentLang);

        $this->assertSame($this->getPluginConfig()->getShopKey(), $returnValue);
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
                    'no_lang' => 'ABC123'
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
