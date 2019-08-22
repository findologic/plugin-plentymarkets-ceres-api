<?php

namespace Findologic\Api\Request;

use Ceres\Helper\ExternalSearch;
use Findologic\Constants\Plugin;
use Findologic\Api\Client;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Plugin\Log\LoggerFactory;
use Plenty\Plugin\Http\Request as HttpRequest;
use Findologic\Components\PluginConfig;
use IO\Services\WebstoreConfigurationService;

/**
 * Class RequestBuilder
 * @package Findologic\Api\Request
 */
class RequestBuilder
{
    const DEFAULT_REQUEST_TYPE = 'request';
    const ALIVE_REQUEST_TYPE = 'alive';
    const CATEGORY_REQUEST_TYPE = 'category';
    const SEARCH_SERVER_URL = 'https://service.findologic.com/ps/';

    /**
     * @var ParametersBuilder
     */
    protected $parametersBuilder;

    /**
     * @var PluginConfig
     */
    protected $pluginConfig;

    /**
     * @var LoggerContract
     */
    protected $logger;

    /**
     * @var Request|bool $request
     */
    protected $request;

    /**
     * @var WebstoreConfiguration
     */
    private $webstoreConfig;

    public function __construct(
        ParametersBuilder $parametersBuilder,
        PluginConfig $pluginConfig,
        LoggerFactory $loggerFactory,
        WebstoreConfigurationService $webstoreConfigurationService
    ) {
        $this->parametersBuilder = $parametersBuilder;
        $this->pluginConfig = $pluginConfig;
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
        $this->webstoreConfig = $webstoreConfigurationService->getWebstoreConfig();
    }

    /**
     * @param HttpRequest $httpRequest
     * @param ExternalSearch $externalSearch
     * @param int|null $category
     * @return bool|Request
     */
    public function build(HttpRequest $httpRequest, ExternalSearch $externalSearch, $category = null)
    {
        $requestType = $category ? self::CATEGORY_REQUEST_TYPE : self::DEFAULT_REQUEST_TYPE;

        $request = $this->createRequestObject();
        $request = $this->setDefaultValues($request, $requestType);
        $request = $this->parametersBuilder->setSearchParams($request, $httpRequest, $externalSearch, $category);

        return $request;
    }

    /**
     * @return bool|Request
     */
    public function buildAliveRequest()
    {
        $request = $this->createRequestObject();
        $request->setConfiguration(Plugin::API_CONFIGURATION_KEY_TIME_OUT, 1);
        $request->setUrl(
            $this->getUrl(self::ALIVE_REQUEST_TYPE))->setParam('shopkey', $this->pluginConfig->getShopKey()
        );

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
    public function getUrl($type = self::DEFAULT_REQUEST_TYPE)
    {
        $url = self::SEARCH_SERVER_URL . $this->getShopUrl() . '/';

        if ($type == self::ALIVE_REQUEST_TYPE) {
            $url .= 'alivetest.php';
        } else if ($type == self::CATEGORY_REQUEST_TYPE) {
            $url .= 'selector.php';
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
     * @return string
     */
    public function getPluginVersion()
    {
        return Plugin::PLUGIN_VERSION;
    }

    /**
     * @param Request $request
     * @param string $requestType
     * @return Request
     */
    protected function setDefaultValues($request, $requestType)
    {
        $request->setUrl($this->getUrl($requestType));
        $request->setParam('revision', $this->getPluginVersion());
        $request->setParam('outputAdapter', Plugin::API_OUTPUT_ADAPTER);
        $request->setParam('shopkey', $this->pluginConfig->getShopKey());
        $request->setConfiguration(Plugin::API_CONFIGURATION_KEY_CONNECTION_TIME_OUT, Client::DEFAULT_CONNECTION_TIME_OUT);

        if ($this->getUserIp()) {
            $request->setParam('userip', $this->getUserIp());
        }

        return $request;
    }

    /**
     * @return string
     */
    protected function getShopUrl()
    {
        if (!empty($this->webstoreConfig->domainSsl)) {
            return preg_replace('(^https?://)', '', $this->webstoreConfig->domainSsl);
        }

        if (!empty($this->webstoreConfig->domain)) {
            return preg_replace('(^https?://)', '', $this->webstoreConfig->domain);
        }

        return strtolower(Plugin::API_OUTPUT_ADAPTER);
    }
}