<?php

namespace Findologic\Api;

use Exception;
use Findologic\Constants\Plugin;
use FINDOLOGIC\Api\Requests\Request;
use Plenty\Plugin\Log\LoggerFactory;
use FINDOLOGIC\Api\Responses\Response;
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
        // $this->config = pluginApp(ApiConfig::class, $pluginConfig->getShopKey());
        // $this->findologicClient = pluginApp(FindologicClient::class,$this->config);
    }

    /**
     * @param Request $request
     * @return Response|null
     */
    public function call(Request $request): ?Response
    {
        $response = null;

        try {
            $response = $this->libraryCall->call('Findologic::findologic_client', [ 'request' => $request, 'shop_key' => $this->pluginConfig->getShopKey()]);
            $this->getLogger(__METHOD__)->error('response', $response);
        } catch (Exception $e) {
            $this->logger->error('Exception while handling search query.', ['request' => $request->getParams()]);
            $this->logger->logException($e);
        }

        return $response;
    }
}
