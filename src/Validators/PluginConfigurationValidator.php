<?php

namespace Findologic\Validators;

use Findologic\Constants\Plugin;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Plugin\Application;
use Plenty\Plugin\Log\Loggable;

class PluginConfigurationValidator implements ValidatorInterface
{
    use Loggable;

    /**
     * @var bool|null
     */
    private $validationStatus = null;

    /**
     * @var LoggerContract
     */
    private $logger;

    public function validate(): bool
    {
        if ($this->validationStatus !== null) {
            return $this->validationStatus;
        }

        // Shopkey validator can only be constructed if the plugin order is correct so it can't be injected.
        $shopkeyValidator = $this->getShopkeyValidator();
        if (!$shopkeyValidator->validate()) {
            $this->validationStatus = false;
            $this->getLoggerObject()->notice('Findologic shopkey is not set in the plugin configuration.');

            return false;
        }

        $this->validationStatus = true;

        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getShopkeyValidator(): ShopkeyValidator
    {
        /** @var Application $app */
        $app = pluginApp(Application::class);

        return $app->make(ShopkeyValidator::class);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getLoggerObject(): LoggerContract
    {
        if (!$this->logger) {
            $this->logger = $this->getLogger(Plugin::PLUGIN_IDENTIFIER);
        }

        return $this->logger;
    }
}
