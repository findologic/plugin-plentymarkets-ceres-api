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
use IO\Services\CategoryService;

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

    /**
     * @var CategoryService
     */
    protected $categoryService;

    /**
     * @var Response
     */
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
     * @return CategoryService|null
     */
    public function getCategoryService()
    {
        if (!$this->categoryService) {
            $this->categoryService = pluginApp(CategoryService::class);
        }

        return $this->categoryService;
    }

    /**
     * @param ExternalSearch $searchQuery
     * @param HttpRequest $request
     */
    public function handleSearchQuery($searchQuery, $request)
    {
        try {
            $this->logger->info('FINDOLOGIC Pre-Search');

            $results = $this->search($request);
            $productsIds = $results->getProductMainVariationsIds();

            if (!empty($productsIds) && is_array($productsIds)) {
                $searchQuery->setResults([$productsIds[0]], $results->getResultsCount());
            }

            $this->logger->info('FINDOLOGIC Search');
            $this->logger->info(implode(',', $productsIds));

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
            $this->searchParametersHandler->handlePaginationAndSorting($searchOptions, $request);
        } catch (\Exception $e) {
            $this->logger->error('Exception while handling search options.');
            $this->logger->logException($e);
        }
    }

    /**
     * @param HttpRequest $request
     * @return \Findologic\Api\Response\Response
     * @throws AliveException
     */
    protected function search($request)
    {
        try {
            $this->aliveTest();

            $category = $this->getCategoryService() ?? null;

            $apiRequest = $this->requestBuilder->build($request, $category ? $category->getCurrentCategory() : null);
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