<?php

namespace Findologic\Traits;

use Findologic\Constants\Plugin;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Plugin\Log\Loggable as PlentyLogger;

class Logger
{
    use PlentyLogger;

    public function __construct(
        private string $identifier
    ) {
    }

    public function debug(string $message, mixed $value, array $reference = []): void
    {
        $logger = $this->getLogger($this->identifier);
        $this->addReference($logger, $reference);
        $logger->debug(Plugin::PLUGIN_NAMESPACE . '::' . $message, $value);
    }

    public function info(string $message, mixed $value, array $reference = []): void
    {
        $logger = $this->getLogger($this->identifier);
        $this->addReference($logger, $reference);
        $logger->info(Plugin::PLUGIN_NAMESPACE . '::' . $message, $value);
    }

    public function error(string $message, mixed $value, array $reference = []): void
    {
        $logger = $this->getLogger($this->identifier);
        $this->addReference($logger, $reference);
        $logger->error($message, $value);
    }

    public function notice(string $message, mixed $value, array $reference = []): void
    {
        $logger = $this->getLogger($this->identifier);
        $this->addReference($logger, $reference);
        $logger->notice(Plugin::PLUGIN_NAMESPACE . '::' . $message, $value);
    }

    public function warning(string $message, mixed $value, array $reference = []): void
    {
        $logger = $this->getLogger($this->identifier);
        $this->addReference($logger, $reference);
        $logger->warning(Plugin::PLUGIN_NAMESPACE . '::' . $message, $value);
    }

    private function addReference(LoggerContract $logger, array $reference): void
    {
        if (!empty($reference)) {
            $logger->addReference(array_keys($reference)[0], array_values($reference)[0]);
        }
    }
}
