<?php

namespace Findologic\Tests\Overrides;

use PHPUnit\Framework\TestCase;
use Plenty\Plugin\Log\LoggerFactory;
use Findologic\Tests\Overrides\FakeLogger;

class BaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('GITLAB_CI', false) === false) {
            $logger = pluginApp(FakeLogger::class);
            $loggerFactory = $this->createMock(LoggerFactory::class);
            $loggerFactory->method('getLogger')->willReturn($logger);
            replaceInstanceByMock(LoggerFactory::class, $loggerFactory);
        } else {
            replaceInstanceByMock(LoggerFactory::class, $this->createMock(LoggerFactory::class));
        }
    }
}
