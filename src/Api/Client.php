<?php

namespace Findologic\Api;

use Exception;
use Findologic\Constants\Plugin;
use Plenty\Plugin\Log\LoggerFactory;
use Findologic\Components\PluginConfig;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\Log\Loggable;

/**
 * Class Client
 * @package Findologic\Api
 */
class Client
{
    use Loggable;

    private LibraryCallContract $libraryCall;

    protected LoggerContract $logger;

    protected PluginConfig $pluginConfig;

    public function __construct(LoggerFactory $loggerFactory, PluginConfig $pluginConfig, LibraryCallContract $libraryCallContract)
    {
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
        $this->libraryCall = $libraryCallContract;
        $this->pluginConfig = $pluginConfig;
    }

    /**
     * @param array $request
     * @return array
     */
    public function call(array $request): ?array
    {
        $response = null;

        try {
            $response = $this->libraryCall->call('Findologic::findologic_client', $request);
        } catch (Exception $e) {
            $this->logger->error('Exception while handling search query.', ['request' => $request['params']]);
            $this->logger->logException($e);
        }

        return $response;
    }
}
