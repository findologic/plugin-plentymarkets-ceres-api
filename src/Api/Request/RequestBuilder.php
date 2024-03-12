<?php

namespace Findologic\Api\Request;

use Findologic\Helpers\Tags;
use Ceres\Helper\ExternalSearch;
use Findologic\Constants\Plugin;
use Plenty\Plugin\Log\LoggerFactory;
use Findologic\Components\PluginConfig;
use Plenty\Log\Contracts\LoggerContract;
use Findologic\Services\PluginInfoService;
use Plenty\Modules\Category\Models\Category;
use IO\Services\WebstoreConfigurationService;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Modules\System\Models\WebstoreConfiguration;

/**
 * Class RequestBuilder
 * @package Findologic\Api\Request
 */
class RequestBuilder
{
    public const TYPE_SEARCH = 0;
    public const TYPE_NAVIGATION = 1;
    public const TYPE_SUGGEST_V3 = 2;
    public const TYPE_ALIVETEST = 3;
    public const TYPE_ITEM_UPDATE = 4;
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

    private $request = [];

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
     * @param Category|null $category
     * @return array
     */
    public function build(HttpRequest $httpRequest, ExternalSearch $externalSearch, $category = null)
    {
        $this->request['requestType'] = $this->getRequestType($httpRequest, $category);
        $this->setDefaultValues($this->getRequestType($httpRequest, $category));
        $this->request = array_merge($this->request, $this->parametersBuilder->setSearchParams($httpRequest, $externalSearch, $category));

        return $this->request;
    }

    /**
     * @return array
     */
    public function buildAliveRequest()
    {
        $this->request['shopUrl'] = $this->getShopUrl();
        $this->request['shopKey'] = $this->pluginConfig->getShopKey();
        $this->request['aliveRequest'] = true;
        return $this->request;
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
    public function getPluginVersion(): string
    {
        return Plugin::PLUGIN_VERSION;
    }

    /**
     * @return string
     */
    private function getShopUrl(): string
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
     * @param string $requestType
     * @return RequestBuilder
     */
    protected function setDefaultValues(string $requestType): self
    {
        $this->request['shopUrl'] = $this->getShopUrl();
        $this->request['shopKey'] = $this->pluginConfig->getShopKey();
        $this->request['revision'] = $this->getPluginVersion();

        if ($this->getUserIp()) {
            $this->request['userIp'] = $this->getUserIp();
        }
        $this->request['shopType'] = self::SHOPTYPE;
        $this->request['shopVersion'] = $this->pluginInfoService->getPluginVersion('ceres');

        return $this;
    }

    /**
     * @param HttpRequest $httpRequest
     * @param Category|null $category
     * @return string
     */
    protected function getRequestType(HttpRequest $httpRequest, ?Category $category = null): string
    {
        $requestType = $category ? self::TYPE_NAVIGATION : self::TYPE_SEARCH;

        if ($this->tagsHelper->isTagPage($httpRequest)) {
            $requestType = self::TYPE_NAVIGATION;
        }

        return $requestType;
    }
}
