<?php

namespace Findologic\Tests\Validators;

use Findologic\Validators\PluginConfigurationValidator;
use Findologic\Validators\ShopkeyValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Plenty\Log\Contracts\LoggerContract;

class PluginConfigurationValidatorTest extends TestCase
{
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
        $this->shopKeyValidatorMock = $this->getMockBuilder(ShopkeyValidator::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();
        $this->mainValidatorMock = $this->getMockBuilder(PluginConfigurationValidator::class)
            ->setMethods(['getShopkeyValidator', 'getLoggerObject'])
            ->getMock();
        $this->loggerMock = $this->getMockForAbstractClass(LoggerContract::class);

        $this->mainValidatorMock->method('getShopkeyValidator')->willReturn($this->shopKeyValidatorMock);
        $this->mainValidatorMock->method('getLoggerObject')->willReturn($this->loggerMock);
    }

    public function testItIsValidWhenBothSubvalidatorsAreValid()
    {
        $this->shopKeyValidatorMock->method('validate')->willReturn(true);

        $this->assertTrue($this->mainValidatorMock->validate());
    }

    public function testItIsNotValidIfShopkeyValidationFails()
    {
        $this->shopKeyValidatorMock->method('validate')->willReturn(false);
        $this->loggerMock->expects($this->once())
            ->method('notice')
            ->with('Findologic shopkey is not set in the plugin configuration.');

        $this->assertFalse($this->mainValidatorMock->validate());
    }

    public function testItDoesNotRunValidatorsMoreThanOnce()
    {
        $this->shopKeyValidatorMock->expects($this->once())->method('validate');

        $this->mainValidatorMock->validate();
        $this->mainValidatorMock->validate();
    }
}
