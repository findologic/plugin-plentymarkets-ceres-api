<?php

namespace Findologic\Tests\Validators;

use Findologic\Validators\PluginConfigurationValidator;
use Findologic\Validators\PluginOrderValidator;
use Findologic\Validators\ShopkeyValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Plenty\Log\Contracts\LoggerContract;

class PluginConfigurationValidatorTest extends TestCase
{
    /**
     * @var PluginOrderValidator|MockObject
     */
    private $pluginOrderValidatorMock;

    /**
     * @var ShopkeyValidator|MockObject
     */
    private $shopKeyValidatorMock;

    /**
     * @var PluginConfigurationValidator|MockObject
     */
    private $mainValidatorMock;

    /**
     * @var LoggerContract|MockObject
     */
    private $loggerMock;

    public function setup()
    {
        $this->pluginOrderValidatorMock = $this->getMockBuilder(PluginOrderValidator::class)
            ->setMethods(['validate'])
            ->getMock();
        $this->shopKeyValidatorMock = $this->getMockBuilder(ShopkeyValidator::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();
        $this->mainValidatorMock = $this->getMockBuilder(PluginConfigurationValidator::class)
            ->setMethods(['getPluginOrderValidator', 'getShopkeyValidator', 'getLoggerObject'])
            ->getMock();
        $this->loggerMock = $this->getMockForAbstractClass(LoggerContract::class);

        $this->mainValidatorMock->method('getPluginOrderValidator')->willReturn($this->pluginOrderValidatorMock);
        $this->mainValidatorMock->method('getShopkeyValidator')->willReturn($this->shopKeyValidatorMock);
        $this->mainValidatorMock->method('getLoggerObject')->willReturn($this->loggerMock);
    }

    public function testItIsValidWhenBothSubvalidatorsAreValid()
    {
        $this->pluginOrderValidatorMock->method('validate')->willReturn(true);
        $this->shopKeyValidatorMock->method('validate')->willReturn(true);

        $this->assertTrue($this->mainValidatorMock->validate());
    }

    public function testItIsNotValidAndItDoesNotCheckShopKeyIfPluginOrderValidationFails()
    {
        $this->pluginOrderValidatorMock->method('validate')->willReturn(false);
        $this->shopKeyValidatorMock->expects($this->never())->method('validate');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('IO plugin must be loaded before the Findologic plugin. Check the plugin priorities.');

        $this->assertFalse($this->mainValidatorMock->validate());
    }

    public function testItIsNotValidIfShopkeyValidationFails()
    {
        $this->pluginOrderValidatorMock->method('validate')->willReturn(true);
        $this->shopKeyValidatorMock->method('validate')->willReturn(false);
        $this->loggerMock->expects($this->once())
            ->method('notice')
            ->with('Findologic shopkey is not set in the plugin configuration.');

        $this->assertFalse($this->mainValidatorMock->validate());
    }

    public function testItDoesNotRunValidatorsMoreThanOnce()
    {
        $this->pluginOrderValidatorMock->expects($this->once())->method('validate');

        $this->mainValidatorMock->validate();
        $this->mainValidatorMock->validate();
    }
}
