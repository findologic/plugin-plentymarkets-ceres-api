<?php

namespace Findologic\PluginPlentymarketsApi\Api;

use Findologic\PluginPlentymarketsApi\Api\Request\Request;
use Plenty\Plugin\ConfigRepository;

/**
 * Class Client
 * @package Findologic\Api
 */
class Client
{
    const DEFAULT_CONNECTION_TIMEOUT = 100;

    const DEFAULT_TIMEOUT = 50;

    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
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
            curl_setopt($ch, CURLOPT_URL, $request->getUrl());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::DEFAULT_CONNECTION_TIMEOUT);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::DEFAULT_TIMEOUT);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getParams());
            curl_setopt($ch, CURLOPT_HTTPHEADER, $request->getHeaders());
            $response = curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            //TODO: log
            return $response;
        }

        return $response;
    }
}