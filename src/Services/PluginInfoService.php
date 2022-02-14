<?php

namespace Findologic\Services;

use Plenty\Modules\Plugin\Contracts\PluginRepositoryContract;
use Plenty\Modules\Plugin\Models\Plugin;
use Plenty\Modules\Plugin\PluginSet\Contracts\PluginSetRepositoryContract;
use Plenty\Plugin\CachingRepository;

class PluginInfoService
{
    const PLUGIN_VERSION_CACHE_KEY_PREFIX = 'plugin_version_';

    const OPTION_SHOW_PLEASE_SELECT_CACHE_KEY_PREFIX = 'show_please_select_';

    const DEFAULT_PLUGIN_VERSION = '0.0.0';

    /**
     * @var PluginRepositoryContract
     */
    private $pluginRepository;

    /**
     * @var PluginSetRepositoryContract
     */
    private $pluginSetRepository;

    /**
     * @var CachingRepository
     */
    private $cache;

    /**
     * @var Plugin[]
     */
    private $plugins = [];

    public function __construct(
        PluginRepositoryContract $pluginRepository,
        PluginSetRepositoryContract $pluginSetRepository,
        CachingRepository $cache
    ) {
        $this->pluginRepository = $pluginRepository;
        $this->cache = $cache;
        $this->pluginSetRepository = $pluginSetRepository;
    }

    /**
     * @return string|null
     */
    public function getPluginVersion(string $pluginName)
    {
        $cachedVersion = $this->cache->get(self::PLUGIN_VERSION_CACHE_KEY_PREFIX . $pluginName);
        if ($cachedVersion !== null) {
            return $cachedVersion;
        }

        if (!$plugin = $this->getPlugin($pluginName)) {
            return null;
        }

        if (!$plugin->versionProductive || $plugin->versionProductive == self::DEFAULT_PLUGIN_VERSION) {
            $pluginSetId = $this->pluginSetRepository->getCurrentPluginSetId();
            $plugin = $this->pluginRepository->decoratePlugin($plugin, $pluginSetId);
        }

        $this->cache->put(self::PLUGIN_VERSION_CACHE_KEY_PREFIX . $pluginName, $plugin->versionProductive, 60*24);

        return $plugin->versionProductive;
    }

    /**
     * @return bool|null
     */
    public function isOptionShowPleaseSelectEnabled(string $pluginName)
    {
        $cachedOption = $this->cache->get(self::OPTION_SHOW_PLEASE_SELECT_CACHE_KEY_PREFIX . $pluginName);
        if ($cachedOption !== null) {
            return $cachedOption;
        }

        if (!$plugin = $this->getPlugin($pluginName)) {
            return null;
        }

        if (!$plugin->versionProductive || $plugin->versionProductive == self::DEFAULT_PLUGIN_VERSION) {
            $pluginSetId = $this->pluginSetRepository->getCurrentPluginSetId();
            $plugin = $this->pluginRepository->decoratePlugin($plugin, $pluginSetId);
        }

        $value = false;

        foreach ($plugin->configurations as $configuration) {
            if ($configuration->key !== 'item.show_please_select') {
                continue;
            }

            $value = filter_var($configuration->value, FILTER_VALIDATE_BOOLEAN);

            break;
        }

        $this->cache->put(
            self::OPTION_SHOW_PLEASE_SELECT_CACHE_KEY_PREFIX . $pluginName,
            $value,
            60*24
        );

        return $value;
    }

    /**
     * @return Plugin|null
     */
    protected function getPlugin(string $pluginName)
    {
        if (!isset($this->plugins[$pluginName])) {
            $this->plugins[$pluginName] = $this->pluginRepository->getPluginByName($pluginName);
        }

        return $this->plugins[$pluginName];
    }
}
