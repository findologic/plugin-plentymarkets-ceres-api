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
use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Plugin\Log\LoggerFactory;
use Plenty\Log\Contracts\LoggerContract;
use IO\Services\CategoryService;
use IO\Services\ItemSearch\Services\ItemSearchService;

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

    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    public function __construct(
        Client $client,
        RequestBuilder $requestBuilder,
        ResponseParser $responseParser,
        ParametersHandler $searchParametersHandler,
        LoggerFactory $loggerFactory,
        FallbackSearchService $fallbackSearchService,
        ConfigRepository $configRepository
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
        $this->configRepository = $configRepository;
    }

    /**
     * @return ItemSearchService
     */
    public function getItemSearchService()
    {
        return pluginApp(ItemSearchService::class);
    }

    public function getSearchFactory(array $ids): VariationSearchFactory
    {
        $searchFactory = pluginApp( VariationSearchFactory::class);

        $searchFactory->hasVariationIds($ids);

        return $searchFactory;
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
     * @param HttpRequest $request
     * @param ExternalSearch $externalSearch
     * @throws AliveException
     */
    public function doSearch(HttpRequest $request, ExternalSearch $externalSearch)
    {
        $results = $this->search($request, $externalSearch);
        $productsIds = $this->filterInvalidVariationIds($results->getVariationIds());

        if ($this->shouldRedirectToProductDetailPage($productsIds, $request)) {
            if ($redirectUrl = $this->getProductDetailUrl($productsIds[0])) {
                $this->handleProductRedirectUrl($redirectUrl);
            }
        }

        /** @var ExternalSearch $searchQuery */
        $externalSearch->setResults($productsIds, $results->getResultsCount());
    }

    /**
     * @param HttpRequest $request
     * @param ExternalSearch $externalSearch
     * @throws AliveException
     */
    public function doNavigation(HttpRequest $request, ExternalSearch $externalSearch)
    {
        $response = $this->fallbackSearchService->handleSearchQuery($request, $externalSearch);

        $externalSearch->setResults(
            $response->getVariationIds(),
            $response->getResultsCount()
        );

        if ($this->configRepository->get(Plugin::CONFIG_NAVIGATION_ENABLED)) {
            $this->search($request, $externalSearch);
            $this->results->setData(Response::DATA_RESULTS, $response->getData(Response::DATA_RESULTS));
            $this->results->setData(Response::DATA_PRODUCTS, $response->getData(Response::DATA_PRODUCTS));
        } else {
            $this->results = $response;
        }
    }

    /**
     * @inheritdoc
     */
    public function handleSearchQuery(HttpRequest $request, ExternalSearch $externalSearch)
    {
        $isCategoryPage = $externalSearch->categoryId !== null ? true : false;
        $hasSelectedFilters = $request->get('attrib') !== null ? true : false;

        try {
            if ($isCategoryPage && (!$hasSelectedFilters || !$this->configRepository->get(Plugin::CONFIG_NAVIGATION_ENABLED))) {
                $this->doNavigation($request, $externalSearch);
            } else {
                $this->doSearch($request, $externalSearch);
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
        /** @var CategoryService $category */
        $category = $this->getCategoryService() ?? null;

        $apiRequest = $this->requestBuilder->build(
            $request,
            $externalSearch,
            $category ? $category->getCurrentCategory() : null
        );
        $this->results = $this->responseParser->parse($request, $this->client->call($apiRequest));

        return $this->results;
    }

    /**
     * @returns bool
     */
    public function aliveTest()
    {
        $request = $this->requestBuilder->buildAliveRequest();
        $response = $this->client->call($request);

        return $response === Plugin::API_ALIVE_RESPONSE_BODY;
    }

    public function handleProductRedirectUrl(string $url)
    {
        header('Location: ' . $url);
    }

    private function filterInvalidVariationIds(array $ids): array
    {
        $results = $this->getItemSearchService()->getResult(
            $this->getSearchFactory($ids)
        );

        $variationIds = [];

        if ($results['success'] && $results['total'] > 0) {
            foreach ($results['documents'] as $document) {
                $variationIds[] = $document['id'];
            }
        }

        return $variationIds;
    }

    private function shouldRedirectToProductDetailPage(array $productsIds, HttpRequest $request): bool
    {
        if (count($productsIds) !== 1) {
            return false;
        }

        $parameters = $request->all();
        if (isset($parameters[Plugin::API_PARAMETER_ATTRIBUTES])) {
            return false;
        }

        $dataQueryInfoMessage = $this->getResults()->getData(Response::DATA_QUERY_INFO_MESSAGE);

        $type = !empty($dataQueryInfoMessage['didYouMeanQuery'])
            ? 'did-you-mean' : $dataQueryInfoMessage['queryStringType'];

        return $type !== 'corrected' && $type !== 'improved';
    }

    /**
     * @return string|null
     */
    private function getProductDetailUrl(int $productId)
    {
        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = $this->getItemSearchService();

        $result = $itemSearchService->getResult(
            $this->getSearchFactory([$productId])
        );

        if (!$result['success'] || empty($result['documents'][0])) {
            return null;
        }

        $productData = $result['documents'][0]['data'];

        $urlPath = $productData['texts']['urlPath'];
        $itemId = $productData['item']['id'];
        $variationId = $productId;

        return sprintf('%s/%s_%s_%s', $this->requestBuilder->getShopUrl(), $urlPath, $itemId, $variationId);
    }
}
