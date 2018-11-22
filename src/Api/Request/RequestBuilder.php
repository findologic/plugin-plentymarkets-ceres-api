<?php

namespace Findologic\Api\Request;

use Findologic\Constants\Plugin;
use Findologic\Api\Client;
use Findologic\Services\PluginInformation;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Plugin\Log\LoggerFactory;

/**
 * Class RequestBuilder
 * @package Findologic\Api\Request
 */
class RequestBuilder
{

    /**
     * @var PluginInformation
     */
    protected $pluginInformation;

    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * @var LoggerContract
     */
    protected $logger;

    /**
     * @var Request|bool $request
     */
    protected $request;

    public function __construct(
        PluginInformation $pluginInformation,
        ConfigRepository $configRepository,
        LoggerFactory $loggerFactory
    ) {
        $this->pluginInformation = $pluginInformation;
        $this->configRepository = $configRepository;
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
    }

    /**
     * @param HttpRequest $httpRequest
     * @return bool|Request
     */
    public function build($httpRequest)
    {
        $request = $this->createRequestObject();
        $request = $this->setDefaultValues($request);
        $request = $this->setSearchParams($request, $httpRequest);

        return $request;
    }

    /**
     * @return bool|Request
     */
    public function buildAliveRequest()
    {
        $request = $this->createRequestObject();

        $request->setUrl($this->getCleanShopUrl('alive'))->setParam('shopkey', $this->configRepository->get(Plugin::CONFIG_SHOPKEY));
        $request->setConfiguration(Plugin::API_CONFIGURATION_KEY_TIME_OUT, 1);

        return $request;
    }

    /**
     * @return Request
     */
    public function createRequestObject()
    {
        return pluginApp(Request::class);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getCleanShopUrl($type = 'request')
    {
        $url = ltrim($this->configRepository->get(Plugin::CONFIG_URL), '/') . '/';

        if ($type == 'alive') {
            $url .= 'alivetest.php';
        } else {
            $url .= 'index.php';
        }

        return $url;
    }

    /**
     * @return string|bool
     */
    public function getUserIp()
    {
        if ($_SERVER['HTTP_CLIENT_IP']) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ($_SERVER['HTTP_X_FORWARDED']) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif ($_SERVER['HTTP_FORWARDED_FOR']) {
            $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif ($_SERVER['HTTP_FORWARDED']) {
            $ipAddress = $_SERVER['HTTP_FORWARDED'];
        } elseif ($_SERVER['REMOTE_ADDR']) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipAddress = false;
        }

        return $ipAddress;
    }

    /**
     * @param Request $request
     * @return Request
     */
    public function setDefaultValues($request)
    {
        $pluginVersion = $this->pluginInformation->getPluginVersion();

        $request->setUrl($this->getCleanShopUrl());
        $request->setParam('revision', $pluginVersion);
        $request->setParam('outputAdapter', Plugin::API_OUTPUT_ADAPTER);
        $request->setParam('shopkey', $this->configRepository->get(Plugin::CONFIG_SHOPKEY));
        $request->setConfiguration(Plugin::API_CONFIGURATION_KEY_CONNECTION_TIME_OUT, Client::DEFAULT_CONNECTION_TIME_OUT);

        if ($this->getUserIp()) {
            $request->setParam('userip', $this->getUserIp());
        }

        return $request;
    }

    /**
     * @param Request $request
     * @param HttpRequest $httpRequest
     * @return Request
     */
    public function setSearchParams($request, $httpRequest)
    {
        $parameters = $httpRequest->all();

        //TODO: remove after testing
        $this->logger->error('Parameters ', $parameters);

        $request->setParam('query', $parameters['query'] ?? '');
        $request->setPropertyParam(Plugin::API_PROPERTY_MAIN_VARIATION_ID);

        if (isset($parameters[Plugin::API_PARAMETER_ATTRIBUTES])) {
            $attributes = $parameters[Plugin::API_PARAMETER_ATTRIBUTES];
            foreach ($attributes as $key => $value) {
                $request->setAttributeParam($key, $value);
            }
        }

        if (isset($parameters['sorting']) && in_array($parameters['sorting'], Plugin::API_SORT_ORDER_AVAILABLE_OPTIONS)) {
            $request->setParam(Plugin::API_PARAMETER_SORT_ORDER, $parameters['sorting']);
        }

        $request = $this->setPagination($request, $parameters);

        return $request;
    }

    /**
     * @param Request $request
     * @param array $parameters
     * @return Request
     */
    protected function setPagination($request, $parameters)
    {
        $pageSize = intval($parameters['items'] ?? 0);

        if ($pageSize > 0) {
            $request->setParam(Plugin::API_PARAMETER_PAGINATION_ITEMS_PER_PAGE, $pageSize);
        }

        $paginationStart = intval($parameters['page'] ?? 0) * $pageSize;

        if ($paginationStart > 0) {
            $request->setParam(Plugin::API_PARAMETER_PAGINATION_START, $paginationStart);
        }

        return $request;
    }
}