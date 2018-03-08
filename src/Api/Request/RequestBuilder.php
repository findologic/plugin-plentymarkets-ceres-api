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
     * @return mixed|null
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
        $this->request->setUrl($this->configRepository->get(Plugin::CONFIG_URL));
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
    }
}