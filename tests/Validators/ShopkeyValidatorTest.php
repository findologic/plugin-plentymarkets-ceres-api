<?php

namespace Findologic\Tests\Validators;

use Findologic\Components\PluginConfig;
use Findologic\Validators\ShopkeyValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShopkeyValidatorTest extends TestCase
{
    /**
     * @var PluginConfig|MockObject
     */
    private $pluginConfigMock;

    /**
     * @var ShopkeyValidator
     */
    private $shopkeyValidator;

    public function setup(): void
    {
        $this->pluginConfigMock = $this->getMockBuilder(PluginConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShopKey'])
            ->getMock();

        $this->shopkeyValidator = new ShopkeyValidator($this->pluginConfigMock);
    }

    public function testItIsValidWhenShopKeyIsSet()
    {
        $this->pluginConfigMock->method('getShopKey')->willReturn('asdfghjg');

        $this->assertTrue($this->shopkeyValidator->validate());
    }

    public function testItIsInvalidWhenShopKeyIsNotSet()
    {
        $this->pluginConfigMock->method('getShopKey')->willReturn(null);

        $this->assertFalse($this->shopkeyValidator->validate());
    }
}
