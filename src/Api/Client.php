<?php

namespace Findologic\Api;

use Findologic\Constants\Plugin;
use Findologic\Api\Request\Request;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\Log\Loggable;
use Plenty\Log\Contracts\LoggerContract;

/**
 * Class Client
 * @package Findologic\Api
 */
class Client
{
    use Loggable;

    const DEFAULT_CONNECTION_TIME_OUT = 5;

    const DEFAULT_TIME_OUT = 10;

    /**
     * @var LoggerContract
     */
    protected $logger;

    /**
     * @var LibraryCallContract
     */
    protected $libraryCallContract;

    public function __construct(LibraryCallContract $libraryCallContract)
    {
        $this->logger = $this->getLogger(Plugin::PLUGIN_IDENTIFIER);
        $this->libraryCallContract = $libraryCallContract;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function call(Request $request)
    {
        $response = false;

        try {
            $response = $this->libraryCallContract->call(
                'Findologic::http_library',
                ['request' => $this->requestToArray($request)]
            );
        } catch (\Exception $e) {
            $this->logger->error('Exception while handling search query.');
            $this->logger->logException($e);
            return $response;
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
        $requestArray['connect_timeout'] = $request->getConfiguration(Plugin::API_CONFIGURATION_KEY_CONNECTION_TIME_OUT) ?? self::DEFAULT_CONNECTION_TIME_OUT;
        $requestArray['timeout'] = $request->getConfiguration(Plugin::API_CONFIGURATION_KEY_TIME_OUT) ?? self::DEFAULT_CONNECTION_TIME_OUT;

        return $requestArray;
    }
}