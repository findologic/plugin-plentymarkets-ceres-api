<?php

namespace Findologic\Services;

use Ceres\Helper\ExternalSearch;
use Ceres\Helper\ExternalSearchOptions;
use Findologic\Api\Response\ResponseParser;
use Findologic\Constants\Plugin;
use IO\Services\ItemSearch\SearchPresets\CategoryItems;
use IO\Services\ItemSearch\SearchPresets\Facets;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Http\Request;
use Findologic\Api\Response\Response;

class FallbackSearchService implements SearchServiceInterface
{

    /**
     * @var ResponseParser
     */
    protected $responseParser;

    public function __construct(
        ResponseParser $responseParser
    )
    {
        $this->responseParser = $responseParser;
    }

    /**
     * @param Request $request
     * @param ExternalSearchOptions $searchOptions
     */
    public function handleSearchOptions(
        Request $request,
        ExternalSearchOptions $searchOptions
    ) {
        // Search options are always provided by FINDOLOGIC,
        // therefore no actual implementation is needed.
    }

    /**
     * @param Request $request
     * @param ExternalSearch $externalSearch
     * @return Response
     */
    public function handleSearchQuery(Request $request, ExternalSearch $externalSearch)
    {
        $searchResults = $this->getSearchResults($request, $externalSearch);

        $response = $this->responseParser->createResponseObject();
        $this->setSearchDataProducts($searchResults['itemList']['documents'], $response);
        $this->setSearchDataResults($searchResults['itemList']['total'], $response);
        $this->setFilters($searchResults['facets'], $response);

        return $response;
    }

    /**
     * @param Request $request
     * @param ExternalSearch $externalSearch
     * @return array
     */
    private function getSearchResults(Request $request, ExternalSearch $externalSearch)
    {
        $itemListOptions = [
            'page' => $externalSearch->page,
            'itemsPerPage' => $externalSearch->itemsPerPage,
            'sorting' => $externalSearch->sorting,
            'facets' => $request->get('facets', ''),
            'categoryId' => $externalSearch->categoryId,
            'query' => $externalSearch->searchString,
            'priceMin' => $request->get('priceMin', 0),
            'priceMax' => $request->get('priceMax', 0),
        ];

        $defaultSearchFactory = [
            'itemList' => CategoryItems::getSearchFactory($itemListOptions),
            'facets'   => Facets::getSearchFactory($itemListOptions)
        ];

        $itemSearchService = pluginApp(ItemSearchService::class);
        return $itemSearchService->getResults($defaultSearchFactory);
    }

    /**
     * @param array $searchResults
     * @param Response $response
     */
    private function setSearchDataProducts(array $searchResults, Response $response)
    {
        $getObjectFromSearchResultItemsDocuments = function($document) {
            return [
                'id' => $document['id'],
                'relevance' => $document['score'],
                'direct' => '0',
                'properties' => [
                    Plugin::API_PROPERTY_VARIATION_ID => $document['id']
                ]
            ];
        };
        $products = array_map(
            $getObjectFromSearchResultItemsDocuments,
            $searchResults
        );

        $response->setData(
            Response::DATA_PRODUCTS,
            $products
        );
    }

    /**
     * @param string $dataResults
     * @param Response $response
     */
    private function setSearchDataResults(string $dataResults, Response $response)
    {
        $count = [];
        $count['count'] = (string)$dataResults;
        $response->setData(Response::DATA_RESULTS, $count);
    }

    /**
     * @param array $dataResults
     * @param Response $response
     */
    private function setFilters(array $dataResults, Response $response) {
        $response->setData(Response::DATA_FILTERS, $dataResults);
    }
}
