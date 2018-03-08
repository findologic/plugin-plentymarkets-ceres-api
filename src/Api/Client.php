<?php

namespace Findologic\PluginPlentymarketsApi\Api;

use Findologic\PluginPlentymarketsApi\Constants\Plugin;
use Findologic\PluginPlentymarketsApi\Api\Request\Request;
use Plenty\Plugin\Log\LoggerFactory;

/**
 * Class Client
 * @package Findologic\Api
 */
class Client
{
    const DEFAULT_CONNECTION_TIMEOUT = 100;

    const DEFAULT_TIMEOUT = 50;

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
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_URL, $request->getRequestUrl());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::DEFAULT_CONNECTION_TIMEOUT);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::DEFAULT_TIMEOUT);
            curl_setopt($ch, CURLOPT_POST, true);
            $response = curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            $this->logger->warning('Exception while handling search query.');
            $this->logger->logException($e);
            return $response;
        }

        return $response;
    }
}