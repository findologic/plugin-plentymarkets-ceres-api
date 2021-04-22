<?php

namespace Findologic\Services;

use Plenty\Modules\Plugin\Contracts\PluginRepositoryContract;
use Plenty\Modules\Plugin\Models\Plugin;
use Plenty\Modules\Plugin\PluginSet\Contracts\PluginSetRepositoryContract;
use Plenty\Plugin\CachingRepository;

class PluginInfoService
{
    const PLUGIN_VERSION_CACHE_KEY_PREFIX = 'plugin_version_';

    /**
     * @var PluginRepositoryContract|null
     */
    private $pluginRepository;

    /**
     * @var CachingRepository
     */
    private $cache;

    /**
     * @var Plugin|null[]
     */
    private $plugins = [];

    /**
     * @return string|null
     */
    public function getPluginVersion(string $pluginName)
    {
        $cachedVersion = $this->getCache()->get(self::PLUGIN_VERSION_CACHE_KEY_PREFIX . $pluginName);
        if ($cachedVersion !== null) {
            return $cachedVersion;
        }

        if (!$plugin = $this->getPlugin($pluginName)) {
            return null;
        }

        if (!$plugin->versionProductive || $plugin->versionProductive == '0.0.0') {
            $pluginSetId = $this->getCurrentPluginSetId();
            $plugin = $this->getPluginRepository()->decoratePlugin($plugin, $pluginSetId);
        }

        $this->getCache()->put(self::PLUGIN_VERSION_CACHE_KEY_PREFIX . $pluginName, $plugin->versionProductive, 60*24);

        return $plugin->versionProductive;
    }

    /**
     * @return Plugin|null
     */
    private function getPlugin(string $pluginName)
    {
        if (!isset($this->plugins[$pluginName])) {
            $this->plugins[$pluginName] = $this->getPluginRepository()->getPluginByName($pluginName);
        }

        return $this->plugins[$pluginName];
    }

    private function getCurrentPluginSetId(): int
    {
        /** @var PluginSetRepositoryContract $contract */
        $contract = pluginApp(PluginSetRepositoryContract::class);

        return $contract->getCurrentPluginSetId();
    }

    private function getPluginRepository(): PluginRepositoryContract
    {
        if ($this->pluginRepository === null) {
            $this->pluginRepository = pluginApp(PluginRepositoryContract::class);
        }

        return $this->pluginRepository;
    }

    private function getCache()
    {
        if ($this->cache === null) {
            $this->cache = pluginApp(CachingRepository::class);
        }

        return $this->cache;
    }
}
