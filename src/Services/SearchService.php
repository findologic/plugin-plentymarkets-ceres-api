<?php

namespace Findologic\PluginPlentymarketsApi\Services;

use Findologic\PluginPlentymarketsApi\Constants\Plugin;
use Findologic\PluginPlentymarketsApi\Api\Request\RequestBuilder;
use Findologic\PluginPlentymarketsApi\Api\Response\ResponseParser;
use Findologic\PluginPlentymarketsApi\Api\Client;
use Ceres\Helper\ExternalSearch;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Log\LoggerFactory;

/**
 * Class SearchService
 * @package Findologic\Services
 */
class SearchService implements SearchServiceInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var RequestBuilder
     */
    protected $requestBuilder;

    /**
     * @var ResponseParser
     */
    protected $responseParser;

    /**
     * @var \Plenty\Log\Contracts\LoggerContract
     */
    protected $logger;

    public function __construct(
        Client $client,
        RequestBuilder $requestBuilder,
        ResponseParser $responseParser,
        LoggerFactory $loggerFactory
    ) {
        $this->client = $client;
        $this->requestBuilder = $requestBuilder;
        $this->responseParser = $responseParser;
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
    }

    /**
     * @param ExternalSearch $searchQuery
     * @param Request $request
     */
    public function handleSearchQuery($searchQuery, $request)
    {
        try {
            $apiRequest = $this->requestBuilder->build($request, $searchQuery);
            $results = $this->responseParser->parse($this->client->call($apiRequest));
            $productsIds = $results->getProductsIds();

            $searchQuery->setResults($productsIds);
        } catch (\Exception $e) {
            $this->logger->warning('Exception while handling search query.');
            $this->logger->logException($e);
        }
    }

    public function handleSearchOptions($searchOptions, $request)
    {
        // TODO: Implement handleSearchOptions() method.
    }
}