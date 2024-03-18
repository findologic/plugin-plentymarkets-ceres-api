<?php

namespace Findologic\Api;

use Exception;
use Findologic\Constants\Plugin;
use Findologic\Api\Request\Request;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Plugin\Log\LoggerFactory;

/**
 * Class Client
 * @package Findologic\Api
 */
class Client
{
    const DEFAULT_CONNECTION_TIME_OUT = 5;

    const DEFAULT_TIME_OUT = 10;

    /**
     * @var LibraryCallContract
     */
    protected $libraryCallContract;

    /**
     * @var LoggerContract
     */
    protected $logger;

    public function __construct(LibraryCallContract $libraryCallContract, LoggerFactory $loggerFactory)
    {
        $this->libraryCallContract = $libraryCallContract;
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function call(Request $request)
    {
        $requestArray = [];
        $response = false;

        try {
            $requestArray = $this->requestToArray($request);
            $response = $this->libraryCallContract->call(
                'Findologic::http_library',
                ['request' => $requestArray]
            );
            $response['error'] = 'An error occured';
            if (is_array($response) && array_key_exists('error', $response) && $response['error']) {
                $this->logger->error('Exception while handling search query.', ['response' => $response]);
            }
        } catch (Exception $e) {
            $this->logger->error('Exception while handling search query.', ['request' => $requestArray]);
            $this->logger->logException($e);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function requestToArray($request)
    {
        $requestArray = [];

        $requestArray['url'] = $request->getRequestUrl();

        $connectTimeout = $request->getConfiguration(Plugin::API_CONFIGURATION_KEY_CONNECTION_TIME_OUT);
        $requestArray['connect_timeout'] = $connectTimeout ?? self::DEFAULT_CONNECTION_TIME_OUT;

        $timeout = $request->getConfiguration(Plugin::API_CONFIGURATION_KEY_TIME_OUT);
        $requestArray['timeout'] = $timeout ?? self::DEFAULT_CONNECTION_TIME_OUT;

        return $requestArray;
    }
}
