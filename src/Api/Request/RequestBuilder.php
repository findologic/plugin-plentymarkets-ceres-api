<?php

namespace Findologic\Api\Request;

use Ceres\Helper\ExternalSearch;
use Findologic\Constants\Plugin;
use Findologic\Api\Client;
use Findologic\Helpers\Tags;
use Findologic\Services\PluginInfoService;
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
    const SHOPTYPE = 'Plentymarkets';

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

    /**
     * @var Tags
     */
    private $tagsHelper;

    /**
     * @var PluginInfoService
     */
    private $pluginInfoService;

    public function __construct(
        ParametersBuilder $parametersBuilder,
        PluginConfig $pluginConfig,
        LoggerFactory $loggerFactory,
        WebstoreConfigurationService $webstoreConfigurationService,
        Tags $tagsHelper,
        PluginInfoService $pluginInfoService
    ) {
        $this->parametersBuilder = $parametersBuilder;
        $this->pluginConfig = $pluginConfig;
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
        $this->webstoreConfig = $webstoreConfigurationService->getWebstoreConfig();
        $this->tagsHelper = $tagsHelper;
        $this->pluginInfoService = $pluginInfoService;
    }

    /**
     * @param HttpRequest $httpRequest
     * @param ExternalSearch $externalSearch
     * @param int|null $category
     * @return bool|Request
     */
    public function build(HttpRequest $httpRequest, ExternalSearch $externalSearch, $category = null)
    {
        $request = $this->createRequestObject();
        $request = $this->setDefaultValues($request, $this->getRequestType($httpRequest, $category));
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
            $this->getUrl(self::ALIVE_REQUEST_TYPE)
        )->setParam('shopkey', $this->pluginConfig->getShopKey());

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
        } elseif ($type == self::CATEGORY_REQUEST_TYPE) {
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
     * @return string
     */
    public function getShopUrl()
    {
        if (!empty($this->webstoreConfig->domainSsl)) {
            return preg_replace('(^https?://)', '', $this->webstoreConfig->domainSsl);
        }

        if (!empty($this->webstoreConfig->domain)) {
            return preg_replace('(^https?://)', '', $this->webstoreConfig->domain);
        }

        return strtolower(Plugin::API_OUTPUT_ADAPTER);
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
        $request->setConfiguration(
            Plugin::API_CONFIGURATION_KEY_CONNECTION_TIME_OUT,
            Client::DEFAULT_CONNECTION_TIME_OUT
        );

        if ($this->getUserIp()) {
            $request->setParam('userip', $this->getUserIp());
        }
        $request->setParam('shopType', self::SHOPTYPE);
        $request->setParam('shopVersion', $this->pluginInfoService->getPluginVersion('ceres'));

        return $request;
    }

    /**
     * @param HttpRequest $httpRequest
     * @param int|null $category
     * @return string
     */
    protected function getRequestType($httpRequest, $category = null)
    {
        $requestType = $category ? self::CATEGORY_REQUEST_TYPE : self::DEFAULT_REQUEST_TYPE;

        if ($this->tagsHelper->isTagPage($httpRequest)) {
            $requestType = self::CATEGORY_REQUEST_TYPE;
        }

        return $requestType;
    }
}
