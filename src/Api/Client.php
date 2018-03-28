<?php

namespace Findologic\PluginPlentymarketsApi\Api;

use Findologic\PluginPlentymarketsApi\Constants\Plugin;
use Findologic\PluginPlentymarketsApi\Api\Request\Request;
use Plenty\Plugin\Log\LoggerFactory;
use HTTP_Request2;

/**
 * Class Client
 * @package Findologic\Api
 */
class Client
{
    const DEFAULT_CONNECTION_TIME_OUT = 5;

    const DEFAULT_TIME_OUT = 10;

    /**
     * @var LoggerFactory
     */
    protected $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
    }

    /**
     * @param Request $request
     * @return bool|mixed
     */
    public function call(Request $request)
    {
        $response = false;

        try {
            $httpRequest = $this->createHttpRequest($request);
            $response = $httpRequest->send();
            $response = $response->getBody();
        } catch (\Exception $e) {
            $this->logger->warning('Exception while handling search query.');
            $this->logger->logException($e);
            return $response;
        }

        return $response;
    }

    public function createHttpRequest(Request $request)
    {
        $httpRequest =  new HTTP_Request2($request->getRequestUrl());
        $httpRequest->setAdapter('curl');

        $httpRequest->setConfig('connect_timeout', $request->getConfiguration(Plugin::API_CONFIGURATION_KEY_CONNECTION_TIME_OUT) ?? self::DEFAULT_CONNECTION_TIME_OUT);
        $httpRequest->setConfig('timeout',$request->getConfiguration(Plugin::API_CONFIGURATION_KEY_TIME_OUT) ?? self::DEFAULT_CONNECTION_TIME_OUT);

        return $httpRequest;
    }
}