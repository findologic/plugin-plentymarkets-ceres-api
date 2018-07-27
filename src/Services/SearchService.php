<?php

namespace Findologic\Services;

use Findologic\Api\Request\RequestBuilder;
use Findologic\Api\Response\Response;
use Findologic\Api\Response\ResponseParser;
use Findologic\Api\Client;
use Findologic\Constants\Plugin;
use Findologic\Exception\AliveException;
use Findologic\Services\Search\ParametersHandler;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Plugin\Log\LoggerFactory;
use Plenty\Log\Contracts\LoggerContract;
use Ceres\Helper\ExternalSearch;
use Ceres\Helper\ExternalSearchOptions;

/**
 * Class SearchService
 * @package Findologic\Services
 */
class SearchService implements SearchServiceInterface
{
    CONST DEFAULT_ITEMS_PER_PAGE = 25;

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
     * @var ParametersHandler
     */
    protected $searchParametersHandler;

    /**
     * @var LoggerContract
     */
    protected $logger;

    protected $results;

    public function __construct(
        Client $client,
        RequestBuilder $requestBuilder,
        ResponseParser $responseParser,
        ParametersHandler $searchParametersHandler,
        LoggerFactory $loggerFactory
    ) {
        $this->client = $client;
        $this->requestBuilder = $requestBuilder;
        $this->responseParser = $responseParser;
        $this->searchParametersHandler = $searchParametersHandler;
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
    }

    /**
     * @param ExternalSearch $searchQuery
     * @param HttpRequest $request
     */
    public function handleSearchQuery($searchQuery, $request)
    {
        try {
            $results = $this->search($request);
            $productsIds = $results->getProductMainVariationsIds();

            //TODO: remove, used for testing during development
            if ($request->get('productIds', false)) {
                $productsIds = explode('-', $request->get('productIds'));
            }

            if (!empty($productsIds) && is_array($productsIds)) {
                //TODO: remove after testing
                $this->logger->error('Set results', $productsIds);

                $searchQuery->setResults($productsIds);
            }

            //TODO: how to handle no results ?
        } catch (\Exception $e) {
            $this->logger->error('Exception while handling search query.');
            $this->logger->logException($e);
        }
    }

    /**
     * @param ExternalSearchOptions $searchOptions
     * @param HttpRequest $request
     */
    public function handleSearchOptions($searchOptions, $request)
    {
        try {
            $results = $this->search($request);

            $searchOptions = $this->searchParametersHandler->handlePaginationAndSorting($searchOptions, $results, $request);

            //TODO: set filters
        } catch (\Exception $e) {
            $this->logger->error('Exception while handling search options.');
            $this->logger->logException($e);
        }
    }

    /**
     * @param HttpRequest $request
     * @return \Findologic\Api\Response\Response
     */
    protected function search($request)
    {
        if ($this->results instanceof  Response) {
            return $this->results;
        }

        try {
            $this->aliveTest();

            $apiRequest = $this->requestBuilder->build($request);
            $this->results = $this->responseParser->parse($this->client->call($apiRequest));
        } catch (AliveException $e) {
            $this->logger->error('Findologic server did not responded to alive request. ' . $e->getMessage());
            throw $e;
        }

        return $this->results;
    }

    /**
     * @throws AliveException
     */
    protected function aliveTest()
    {
        $request = $this->requestBuilder->buildAliveRequest();
        $response = $this->client->call($request);

        if ($response !== Plugin::API_ALIVE_RESPONSE_BODY) {
            throw new AliveException('Server is not alive!');
        }
    }
}