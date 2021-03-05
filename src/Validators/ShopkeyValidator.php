<?php

namespace Findologic\Validators;

use Findologic\Components\PluginConfig;

class ShopkeyValidator
{
    /**
     * @var PluginConfig
     */
    private $pluginConfig;

    public function __construct(PluginConfig $pluginConfig)
    {
        $this->pluginConfig = $pluginConfig;
    }

    /**
     * @return bool
     */
    public function validate()
    {
        /** @var PluginConfig $pluginConfig */
        if (!$this->pluginConfig->getShopKey()) {
            return false;
        }

        return true;
    }
}
