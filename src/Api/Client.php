<?php

namespace Findologic\Api;

use Exception;
use FINDOLOGIC\Api\Client as FindologicClient;
use FINDOLOGIC\Api\Config;
use Findologic\Constants\Plugin;
use FINDOLOGIC\Api\Requests\Request;
use Plenty\Plugin\Log\LoggerFactory;
use FINDOLOGIC\Api\Responses\Response;
use Findologic\Components\PluginConfig;
use Plenty\Log\Contracts\LoggerContract;

/**
 * Class Client
 * @package Findologic\Api
 */
class Client
{

    /**
     * @var FindologicClient
     */
    private $findologicClient;
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerContract
     */
    protected $logger;

    public function __construct(LoggerFactory $loggerFactory, PluginConfig $pluginConfig)
    {
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
        $this->config = new Config($pluginConfig->getShopKey());
        $this->findologicClient = new FindologicClient($this->config);
    }

    /**
     * @param Request $request
     * @return Response|null
     */
    public function call(Request $request): ?Response
    {
        $response = null;

        try {
            $response = $this->findologicClient->send($request);
        } catch (Exception $e) {
            $this->logger->error('Exception while handling search query.', ['request' => $request->getParams()]);
            $this->logger->logException($e);
        }

        return $response;
    }
}
