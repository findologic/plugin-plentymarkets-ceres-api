<?php

namespace Findologic\Services;

use IO\Services\TemplateConfigService;
use Plenty\Modules\Plugin\Contracts\PluginRepositoryContract;
use Plenty\Modules\Plugin\Models\Plugin;
use Plenty\Modules\Plugin\PluginSet\Contracts\PluginSetRepositoryContract;
use Plenty\Plugin\CachingRepository;

class PluginInfoService
{
    const PLUGIN_VERSION_CACHE_KEY_PREFIX = 'plugin_version_';
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
     * @var TemplateConfigService
     */
    private $templateConfigService;

    /**
     * @var Plugin[]
     */
    private $plugins = [];

    public function __construct(
        PluginRepositoryContract $pluginRepository,
        PluginSetRepositoryContract $pluginSetRepository,
        CachingRepository $cache,
        TemplateConfigService $templateConfigService
    ) {
        $this->pluginRepository = $pluginRepository;
        $this->pluginSetRepository = $pluginSetRepository;
        $this->cache = $cache;
        $this->templateConfigService = $templateConfigService;
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
     * @return bool
     */
    public function isOptionShowPleaseSelectEnabled()
    {
        return $this->templateConfigService->getBoolean('item.show_please_select');
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
