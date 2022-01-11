<?php

namespace Findologic\Services;

use Exception;
use Findologic\Api\Request\Request;
use Findologic\Api\Request\RequestBuilder;
use Findologic\Api\Response\Response;
use Findologic\Api\Response\ResponseParser;
use Findologic\Api\Client;
use Findologic\Constants\Plugin;
use Findologic\Exception\AliveException;
use Findologic\Services\Search\ParametersHandler;
use Ceres\Helper\ExternalSearch;
use Ceres\Helper\ExternalSearchOptions;
use IO\Helper\Utils;
use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use Plenty\Modules\Plugin\Contracts\PluginRepositoryContract;
use Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Plugin\Log\Loggable;
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
    use Loggable;

    const DEFAULT_ITEMS_PER_PAGE = 25;
    const MAX_RETRIES = 2;

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

    /**
     * @var bool|null
     */
    protected $aliveTestResult;

    /**
     * @var PluginInfoService
     */
    protected $pluginInfoService;

    public function __construct(
        Client $client,
        RequestBuilder $requestBuilder,
        ResponseParser $responseParser,
        ParametersHandler $searchParametersHandler,
        LoggerFactory $loggerFactory,
        FallbackSearchService $fallbackSearchService,
        ConfigRepository $configRepository,
        PluginInfoService $pluginInfoService
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
        $this->pluginInfoService = $pluginInfoService;
    }

    /**
     * @return ItemSearchService
     */
    public function getItemSearchService()
    {
        return pluginApp(ItemSearchService::class);
    }

    public function getSearchFactory(): VariationSearchFactory
    {
        return pluginApp(VariationSearchFactory::class);
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
        if ($landingPageUrl = $results->getLandingPage()) {
            $this->doPageRedirect($landingPageUrl);
        }

        if ($results->getResultsCount() == 0) {
            return;
        }

        if ($this->shouldFilterInvalidProducts()) {
            $variationIds = $this->filterInvalidVariationIds($results->getVariationIds());
        } else {
            $variationIds = $results->getVariationIds();
        }

        if ($redirectUrl = $this->getRedirectUrl($request, $results, $variationIds)) {
            $this->doPageRedirect($redirectUrl);
        }

        /** @var ExternalSearch $searchQuery */
        $externalSearch->setResults($variationIds, $results->getResultsCount());
    }

    /**
     * In case a redirect should happen, this will return the redirect URL. Redirects may be caused when
     * a search only yields a single result.
     *
     * @return string|null
     */
    public function getRedirectUrl(HttpRequest $request, Response $response, array $variationIds)
    {
        if ($this->shouldRedirectToProductDetailPage($variationIds, $request)) {
            return $this->getProductDetailUrl($response);
        }

        return null;
    }

    /**
     * @param HttpRequest $request
     * @param ExternalSearch $externalSearch
     * @throws AliveException
     */
    public function doNavigation(HttpRequest $request, ExternalSearch $externalSearch)
    {
        $fallbackSearchResult = $this->fallbackSearchService->getSearchResults($request, $externalSearch);
        $response = $this->fallbackSearchService->createResponseFromSearchResult($fallbackSearchResult);

        if ($this->configRepository->get(Plugin::CONFIG_NAVIGATION_ENABLED)) {
            $this->search($request, $externalSearch);
            $this->results->setData(Response::DATA_PRODUCTS, $response->getData(Response::DATA_PRODUCTS));
        } else {
            $this->results = $response;
        }

        $parameters = (array) $request->all();
        if (isset($parameters[Plugin::API_PARAMETER_ATTRIBUTES])) {
            $total = $this->results->getData(Response::DATA_RESULTS)['count'];
        } else {
            $total = $response->getData(Response::DATA_RESULTS)['count'];
        }

        $externalSearch->setDocuments($fallbackSearchResult['itemList']['documents'], $total);
    }

    /**
     * @inheritdoc
     */
    public function handleSearchQuery(HttpRequest $request, ExternalSearch $externalSearch)
    {
        $isCategoryPage = $externalSearch->categoryId !== null ? true : false;
        $hasSelectedFilters = $request->get('attrib') !== null ? true : false;

        try {
            if ($isCategoryPage &&
                (!$hasSelectedFilters || !$this->configRepository->get(Plugin::CONFIG_NAVIGATION_ENABLED))
            ) {
                $this->doNavigation($request, $externalSearch);
            } else {
                $this->doSearch($request, $externalSearch);
            }
        } catch (Exception $e) {
            $this->logger->error('Exception while handling search query.', ['URL' => $request->getRequestUri()]);
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
        } catch (Exception $e) {
            $this->logger->error('Exception while handling search options.', [
                'URL' => $request->getRequestUri(),
                'searchOptions' => [
                    'itemsPerPage' => $searchOptions->getItemsPerPage(),
                    'defaultItemsPerPage' => $searchOptions->getDefaultItemsPerPage(),
                    'sortingOptions' => $searchOptions->getSortingOptions(),
                    'defaultSortingOptions' => $searchOptions->getDefaultSortingOption()
                ]
            ]);
            $this->logger->logException($e);
        }
    }

    /**
     * @param HttpRequest $request
     * @param ExternalSearch $externalSearch
     * @return Response
     * @throws AliveException
     */
    public function search(HttpRequest $request, ExternalSearch $externalSearch)
    {
        $categoryService = null;
        if ($this->getCategoryService()) {
            $categoryId = $this->configRepository->get(Plugin::CONFIG_IO_CATEGORY_SEARCH);
            $isConfiguredSearchCategory = $this->getCategoryService()->getCurrentCategory()->id == $categoryId;

            /** @var CategoryService|null $categoryService */
            $categoryService = !$isConfiguredSearchCategory ? $this->getCategoryService() : null;
        }

        $apiRequest = $this->requestBuilder->build(
            $request,
            $externalSearch,
            $categoryService ? $categoryService->getCurrentCategory() : null
        );

        $this->results = $this->responseParser->parse($request, $this->requestWithRetries($apiRequest));

        return $this->results;
    }

    /**
     * @returns bool
     */
    public function aliveTest()
    {
        if ($this->aliveTestResult === null) {
            $request = $this->requestBuilder->buildAliveRequest();
            $response = $this->client->call($request);

            $this->aliveTestResult = ($response === Plugin::API_ALIVE_RESPONSE_BODY);
        }

        return $this->aliveTestResult;
    }

    public function doPageRedirect(string $url)
    {
        header('Location: ' . $url);
    }

    /**
     * Since Ceres version 5.0.3 invalid ids are filtered by default.
     * @return bool
     */
    public function shouldFilterInvalidProducts(): bool
    {
        if (!$ceresVersion = $this->getCeresVersion()) {
            return true;
        }

        /** Custom version checking as Plentymarkets forbids using the version_compare function */
        $versionParts = explode('.', $ceresVersion);

        if ($versionParts[0] > 5) {
            return false;
        }

        if ($versionParts[0] == 5 && $versionParts[1] > 0) {
            return false;
        }

        if ($versionParts[0] == 5 && $versionParts[1] == 0 && $versionParts[2] > 2) {
            return false;
        }

        return true;
    }

    private function filterInvalidVariationIds(array $ids): array
    {
        $results = $this->getItemSearchService()->getResult(
            $this->getSearchFactory()->hasVariationIds($ids)->isActive()
        );

        $variationIds = [];

        if ($results['success'] && $results['total'] > 0) {
            foreach ($results['documents'] as $document) {
                $variationIds[] = $document['id'];
            }
        }

        return $this->removeInvalidVariationIds($ids, $variationIds);
    }

    private function removeInvalidVariationIds(array $findologicIds, array $plentymarketsIds): array
    {
        if (empty($findologicIds) || empty($plentymarketsIds)) {
            return [];
        }

        foreach ($findologicIds as $key => $value) {
            if (!in_array($value, $plentymarketsIds)) {
                unset($findologicIds[$key]);
                array_values($findologicIds);
            }
        }

        return $findologicIds;
    }

    protected function shouldRedirectToProductDetailPage(array $productsIds, HttpRequest $request): bool
    {
        if (count($productsIds) !== 1) {
            return false;
        }

        $parameters = $request->all();
        $attributesSet = isset($parameters[Plugin::API_PARAMETER_ATTRIBUTES]);
        if ($attributesSet || (isset($parameters['page']) && $parameters['page'] > 1)) {
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
    private function getProductDetailUrl(Response $response)
    {
        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = $this->getItemSearchService();

        $productId = $response->getProductsIds()[0];

        if (strpos($productId, '_')) {
            $productId = explode('_', $productId)[0];
        }

        $result = $itemSearchService->getResult(
            $this->getSearchFactory()->hasItemId($productId)
        );

        if (!$result['success'] || empty($result['documents'][0])) {
            return null;
        }

        $query = $response->getData(Response::DATA_QUERY)['query'];

        $productData = $result['documents'][0]['data'];
        $variationId = $this->getVariationIdForRedirect($query, $result['documents']);
        if ($variationId !== $productId) {
            $productData['variation']['id'] = $variationId;
        }

        return $this->buildItemURL($productData, false);
    }

    /**
     * Returns a variation id to be used for redirecting in searches with single result.
     * If a variation has an identifier matching the query, its id is returned. Otherwise the main variation is used.
     *
     * @param string $query
     * @param array $documents
     * @return string
     */
    private function getVariationIdForRedirect(string $query, array $documents)
    {
        $lowercasedQuery = strtolower($query);
        $mainVariationId = null;
        foreach ($documents as $document) {
            $variation = $document['data']['variation'];
            $barcodes = $document['data']['barcodes'] ?? [];

            if (strtolower($variation['number']) == $lowercasedQuery ||
                strtolower($variation['model']) == $lowercasedQuery ||
                strtolower($variation['order']) == $lowercasedQuery
            ) {
                return $variation['id'];
            }

            foreach ($barcodes as $barcode) {
                if (strtolower($barcode['code']) == $lowercasedQuery) {
                    return $variation['id'];
                }
            }

            if ($variation['isMain'] === true) {
                $mainVariationId = $variation['id'];
            }
        }

        return $mainVariationId;
    }

    /**
     * @return string|null
     */
    private function getCeresVersion()
    {
        return $this->pluginInfoService->getPluginVersion('ceres');
    }

    /**
     * @see \IO\Extensions\Filters\URLFilter::buildItemURL (source)
     */
    private function buildItemURL($itemData, $withVariationId = true): string
    {
        $itemId = $itemData['item']['id'];
        $variationId = $itemData['variation']['id'];

        if ($itemId === null || $itemId <= 0) {
            return '';
        }

        /** @var UrlBuilderRepositoryContract $urlBuilderRepository */
        $urlBuilderRepository = pluginApp(UrlBuilderRepositoryContract::class);

        $includeLanguage = Utils::getLang() !== Utils::getDefaultLang();
        if ($variationId === null || $variationId <= 0) {
            return $urlBuilderRepository->buildItemUrl($itemId)->toRelativeUrl($includeLanguage);
        } else {
            $url = $urlBuilderRepository->buildVariationUrl($itemId, $variationId);

            return $url->append(
                $urlBuilderRepository->getSuffix($itemId, $variationId, $withVariationId)
            )->toRelativeUrl($includeLanguage);
        }
    }

    /**
     * @return mixed
     */
    private function requestWithRetries(Request $request)
    {
        $i = 0;
        do {
            $responseData = $this->client->call($request);
            
            $error = $this->validateResponse($responseData);
            if (!$error) {
                return $responseData;
            }

            $logLine = sprintf('%s - Retry %d/%d takes place', $error, $i + 1, self::MAX_RETRIES);
            $this->logger->error($logLine, ['response' => $responseData]);

            $i++;
        } while ($i <= self::MAX_RETRIES);

        return $responseData;
    }

    /**
     * @param mixed $responseData
     * @return string|null
     */
    private function validateResponse($responseData)
    {
        $errorMsg = null;
        if (is_array($responseData) && array_key_exists('error', $responseData) && $responseData['error'] === true) {
            $errorMsg = 'Plentymarkets SDK returned an error response';
        } elseif (!is_string($responseData)) {
            $errorMsg = 'Invalid response received from server';
        } elseif ($responseData === '') {
            $errorMsg = 'Plentymarkets SDK returned empty response';
        }

        return $errorMsg;
    }
}
