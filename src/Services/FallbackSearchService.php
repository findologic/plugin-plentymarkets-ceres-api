<?php

namespace Findologic\Services;

use IO\Services\ItemSearch\SearchPresets\CategoryItems;
use IO\Services\ItemSearch\SearchPresets\Facets;
use IO\Services\ItemSearch\Services\ItemSearchService;

class FallbackSearchService implements SearchServiceInterface {

    /**
     * @param \Plenty\Plugin\Http\Request $request
     * @param \Ceres\Helper\ExternalSearchOptions $searchOptions
     */
    public function handleSearchOptions(
        \Plenty\Plugin\Http\Request $request,
        \Ceres\Helper\ExternalSearchOptions $searchOptions
    ) {
        // I'm just a lonely empty function :(
    }

    /**
     * @param \Plenty\Plugin\Http\Request $request
     * @param \Ceres\Helper\ExternalSearch $externalSearch
     * @return \Ceres\Helper\ExternalSearch
     */
    public function handleSearchQuery(
        \Plenty\Plugin\Http\Request $request,
        \Ceres\Helper\ExternalSearch $externalSearch
    ) {
        $itemListOptions = $this->createItemListOptions($request, $externalSearch);
        $defaultSearchFactory = [
            'itemList' => CategoryItems::getSearchFactory($itemListOptions),
            'facets'   => Facets::getSearchFactory($itemListOptions)
        ];

        $itemSearchService = pluginApp(ItemSearchService::class);
        return $itemSearchService->getResults($defaultSearchFactory);
    }

    /**
     * @param \Plenty\Plugin\Http\Request $request
     * @param \Ceres\Helper\ExternalSearch $externalSearch
     * @return array
     */
    private function createItemListOptions(
        \Plenty\Plugin\Http\Request $request,
        \Ceres\Helper\ExternalSearch $externalSearch
    ) {
        return [
            'page' => $externalSearch->page,
            'itemsPerPage' => $externalSearch->itemsPerPage,
            'sorting' => $externalSearch->sorting,
            'facets' => $request->get('facets', ''),
            'categoryId' => $externalSearch->categoryId,
            'query' => $externalSearch->searchString,
            'priceMin' => $request->get('priceMin', 0),
            'priceMax' => $request->get('priceMax', 0),
        ];
    }
}