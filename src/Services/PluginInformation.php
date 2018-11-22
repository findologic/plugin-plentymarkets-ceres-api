<?php

namespace Findologic\Services;

use Findologic\Constants\Plugin as PluginConstants;
use Plenty\Modules\Plugin\Models\Plugin;
use Plenty\Modules\Plugin\Contracts\PluginRepositoryContract;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Plugin\Log\LoggerFactory;

/**
 * Class Plugin
 * @package Findologic\Services
 */
class PluginInformation
{
    /**
     * @var bool|Plugin
     */
    protected $pluginInformation = false;

    /**
     * @var LoggerContract
     */
    protected $logger;

    public function __construct(
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->getLogger(PluginConstants::PLUGIN_NAMESPACE, PluginConstants::PLUGIN_IDENTIFIER);
    }

    /**
     * @return mixed
     */
    public function getPluginVersion()
    {
        if (!$this->pluginInformation) {
            $this->loadPluginInformation();
        }

        if (!$this->pluginInformation instanceof Plugin) {
            return false;
        }

        return $this->pluginInformation->version;
    }

    /**
     * @return PluginRepositoryContract|null
     */
    public function getPluginRepositoryContract()
    {
        return pluginApp(PluginRepositoryContract::class);
    }

    protected function loadPluginInformation()
    {
        $pluginRepository = $this->getPluginRepositoryContract();

        if (!$pluginRepository) {
            return;
        }

        $plugins = $pluginRepository->searchPlugins(['name' => 'Findologic']);

        if ($plugins->getTotalCount() < 1) {
            return;
        }

        $this->pluginInformation = $plugins->getResult()[0] ?? false;
    }
}