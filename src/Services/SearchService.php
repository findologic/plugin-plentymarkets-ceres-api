<?php

namespace Findologic\Services;

use Findologic\Api\Request\RequestBuilder;
use Findologic\Api\Response\Response;
use Findologic\Api\Response\ResponseParser;
use Findologic\Api\Client;
use Findologic\Constants\Plugin;
use Findologic\Exception\AliveException;
use Findologic\Services\Search\ParametersHandler;
use Ceres\Helper\ExternalSearch;
use Ceres\Helper\ExternalSearchOptions;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Plugin\Log\LoggerFactory;
use Plenty\Log\Contracts\LoggerContract;
use IO\Services\CategoryService;
use IO\Services\ItemSearch\Services\ItemSearchService;
use IO\Services\ItemSearch\SearchPresets\VariationList;

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

    /**
     * @var FallbackSearchService
     */
    protected $fallbackSearchService;

    public function __construct(
        Client $client,
        RequestBuilder $requestBuilder,
        ResponseParser $responseParser,
        ParametersHandler $searchParametersHandler,
        LoggerFactory $loggerFactory,
        FallbackSearchService $fallbackSearchService
    ) {
        $this->client = $client;
        $this->requestBuilder = $requestBuilder;
        $this->responseParser = $responseParser;
        $this->searchParametersHandler = $searchParametersHandler;
        $this->logger = $loggerFactory->getLogger(
            Plugin::PLUGIN_NAMESPACE,
            Plugin::PLUGIN_IDENTIFIER
        );
        $this->fallbackSearchService = $fallbackSearchService;
    }

    /**
     * @return CategoryService
     */
    public function getCategoryService()
    {
        if (!$this->categoryService) {
            $this->categoryService = pluginApp(CategoryService::class);
        }

        return $this->categoryService;
    }

    /**
     * @return Response|null
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param Response $results
     * @param ExternalSearch $externalSearch
     */
    public function doSearch(Response $results, ExternalSearch $externalSearch) {
        $productsIds = $this->filterInvalidVariationIds($results->getVariationIds());

        /** @var ExternalSearch $searchQuery */
        $externalSearch->setResults($productsIds, $results->getResultsCount());
    }

    /**
     * @param HttpRequest $request
     * @param ExternalSearch $externalSearch
     */
    public function doNavigation(HttpRequest $request, ExternalSearch $externalSearch) {
        $searchResults = $this->fallbackSearchService->handleSearchQuery($request, $externalSearch);

        $getIdsFromSearchResultItemsDocuments = function ($document) {
            return $document['id'];
        };

        $this->logger->error('data', $searchResults['itemList']['documents']);
        $this->logger->error('data:ids', array_map(
            $getIdsFromSearchResultItemsDocuments,
            $searchResults['itemList']['documents']
        ));
        $this->logger->error('count', $searchResults['itemList']['total']);
        $externalSearch->setResults(
            array_map(
                $getIdsFromSearchResultItemsDocuments,
                $searchResults['itemList']['documents']
            ),
            $searchResults['itemList']['total']
        );

        $this->createSearchDataProducts($searchResults['itemList']['documents']);
        $this->createSearchDataResults($searchResults['itemList']['documents']);
    }

    /**
     * @param array $searchResults
     */
    public function createSearchDataProducts(array $searchResults) {
        $getObjectFromSearchResultItemsDocuments = function($document) {
            return [
                'id' => $document['id'],
                'relevance' => $document['score'],
                'direct' => '0',
            ];
        };
        $products = array_map(
            $getObjectFromSearchResultItemsDocuments,
            $searchResults
        );

        $this->results->setData(
            Response::DATA_PRODUCTS,
            $products
        );
    }

    /**
     * @param array $searchResults
     */
    public function createSearchDataResults(array $searchResults) {
        $count = [];
        $count['count'] = (string)count($searchResults);
        $this->results->setData(Response::DATA_RESULTS, $count);
    }

    /**
     * @inheritdoc
     */
    public function handleSearchQuery(HttpRequest $request, ExternalSearch $externalSearch)
    {
        try {
            $results = $this->search($request, $externalSearch);
            $this->logger->error('catId', $externalSearch->categoryId);
            $this->logger->error('attrib', $request->get('attrib'));
            if ($externalSearch->categoryId !== null && $request->get('attrib') === null){
                $this->doNavigation($request, $externalSearch);
            } else {
                $this->logger->error('its a search ?');
                $this->doSearch($results, $externalSearch);
            }
        } catch (\Exception $e) {
            $this->logger->error('Exception while handling search query.');
            $this->logger->logException($e);
        }

        return $this->results;
    }

    /**
     * @inheritdoc
     */
    public function handleSearchOptions(HttpRequest $request, ExternalSearchOptions $searchOptions)
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
     * @param ExternalSearch $externalSearch
     * @return \Findologic\Api\Response\Response
     * @throws AliveException
     */
    public function search(HttpRequest $request, ExternalSearch $externalSearch)
    {
        try {
            $this->aliveTest();

            /** @var CategoryService $category */
            $category = $this->getCategoryService() ?? null;

            $apiRequest = $this->requestBuilder->build(
                $request,
                $externalSearch,
                $category ? $category->getCurrentCategory() : null
            );
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

    private function filterInvalidVariationIds(array $ids)
    {
        $externalSearchFactories = [];
        $itemSearchService = pluginApp(ItemSearchService::class);

        foreach ($ids as $id) {
            $externalSearchFactories[$id] = VariationList::getSearchFactory([
                'variationIds'      => [$id],
                'excludeFromCache'  => true
            ]);
        }

        // Return only the variation IDs which actually yielded a result.
        return array_keys(array_filter($itemSearchService->getResults($externalSearchFactories), function ($result) {
            return $result['total'] > 0;
        }));
    }
}