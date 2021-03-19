<?php

namespace Findologic\Validators;

use Findologic\Components\PluginConfig;

class ShopkeyValidator implements ValidatorInterface
{
    /**
     * @var PluginConfig
     */
    private $pluginConfig;

    public function __construct(PluginConfig $pluginConfig)
    {
        $this->pluginConfig = $pluginConfig;
    }

    public function validate(): bool
    {
        return (bool)$this->pluginConfig->getShopKey();
    }
}
