<?php

namespace Findologic\PluginPlentymarketsApi\Api\Request;

use Ceres\Helper\ExternalSearch;
use Findologic\PluginPlentymarketsApi\Constants\Plugin;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Plugin\Log\LoggerFactory;

/**
 * Class RequestBuilder
 * @package Findologic\Api\Request
 */
class RequestBuilder
{
    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * @var \Plenty\Log\Contracts\LoggerContract
     */
    protected $logger;

    /**
     * @var Request|bool $request
     */
    protected $request;

    public function __construct(ConfigRepository $configRepository, LoggerFactory $loggerFactory)
    {
        $this->configRepository = $configRepository;
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
    }

    /**
     * @param HttpRequest $request
     * @param null $searchQuery
     * @return bool|Request
     */
    public function build($request, $searchQuery = null)
    {
        $this->createRequestObject();
        $this->setDefaultValues();
        $this->setSearchParams($request, $searchQuery);

        $request = $this->request;
        $this->request = false;

        return $request;
    }

    /**
     * @return bool|Request
     */
    public function buildAliveRequest()
    {
        $this->createRequestObject();

        $request = $this->request;
        $request->setUrl($this->getCleanShopUrl('alive'))->setParam('shopkey', $this->configRepository->get(Plugin::CONFIG_SHOPKEY));
        $this->request = false;

        return $request;
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

    protected function createRequestObject()
    {
        if (!$this->request) {
            $this->request = pluginApp(Request::class);
        }
    }

    /**
     * @param Request $request
     */
    protected function setDefaultValues()
    {
        $this->request->setUrl($this->getCleanShopUrl());
        $this->request->setParam('outputAdapter', Plugin::API_OUTPUT_ADAPTER);
        $this->request->setParam('shopkey', $this->configRepository->get(Plugin::CONFIG_SHOPKEY));
    }

    /**
     * @param HttpRequest $request
     * @param ExternalSearch $searchQuery
     */
    protected function setSearchParams($request, $searchQuery)
    {
        if ($searchQuery->searchString) {
            $this->request->setParam('query', $searchQuery->searchString);
        }

        parse_str($request->getUri(), $parameters);

        if (isset($parameters[Plugin::API_PARAMETER_ATTRIBUTES])) {
            $attributes = $parameters[Plugin::API_PARAMETER_ATTRIBUTES];
            foreach ($attributes as $key => $value) {
                $this->request->setAttributeParam($key, $value);
            }
        }

        if (isset($parameters[Plugin::API_PARAMETER_SORT_ORDER]) && in_array($parameters[Plugin::API_PARAMETER_SORT_ORDER], Plugin::API_SORT_ORDER_AVAILABLE_OPTIONS)) {
            $this->request->setParam(Plugin::API_PARAMETER_SORT_ORDER, $parameters[Plugin::API_PARAMETER_SORT_ORDER]);
        }

        $pageSize = $parameters[Plugin::API_PARAMETER_PAGINATION_ITEMS_PER_PAGE] ?? 0;

        if (intval($pageSize) > 0) {
            $this->request->setParam(Plugin::API_PARAMETER_PAGINATION_ITEMS_PER_PAGE, $pageSize);
        }

        $paginationStart = $parameters[Plugin::API_PARAMETER_PAGINATION_START] ?? 0;

        if (intval($paginationStart) > 0) {
            $this->request->setParam(Plugin::API_PARAMETER_PAGINATION_START, $paginationStart);
        }
    }
}