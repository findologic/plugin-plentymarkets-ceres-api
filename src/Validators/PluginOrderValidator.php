<?php

namespace Findologic\Validators;

use Plenty\Modules\Plugin\Contracts\PluginRepositoryContract;
use Plenty\Modules\Plugin\PluginSet\Contracts\PluginSetRepositoryContract;

class PluginOrderValidator implements ValidatorInterface
{
    public function validate(): bool
    {
        $pluginSetRepository = $this->getPluginSetRepository();
        $currentPluginSet = $pluginSetRepository->getCurrentPluginSetId();

        $pluginRepository = $this->getPluginRepository();
        $ioPlugin = $pluginRepository->getPluginByName('io');
        $findologicPlugin = $pluginRepository->getPluginByName('findologic');

        $ioPosition = null;
        $findologicPosition = null;

        foreach (($ioPlugin->pluginSetEntries ?? []) as $pluginSetEntry) {
            if ($pluginSetEntry->pluginSetId == $currentPluginSet) {
                $ioPosition = (int)$pluginSetEntry->position;
            }
        }

        foreach (($findologicPlugin->pluginSetEntries ?? []) as $pluginSetEntry) {
            if ($pluginSetEntry->pluginSetId == $currentPluginSet) {
                $findologicPosition = (int)$pluginSetEntry->position;
            }
        }

        if ($findologicPosition === null || $ioPosition === null || $findologicPosition <= $ioPosition) {
            return false;
        }

        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getPluginRepository(): PluginRepositoryContract
    {
        return pluginApp(PluginRepositoryContract::class);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getPluginSetRepository(): PluginSetRepositoryContract
    {
        return pluginApp(PluginSetRepositoryContract::class);
    }
}
