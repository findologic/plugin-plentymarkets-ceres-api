<?php

namespace Findologic\Containers;

use Findologic\Services\SearchService;
use Findologic\Api\Response\Response;
use Plenty\Plugin\Templates\Twig;

/**
 * Class SearchFilterContainer
 * @package Findologic\Containers
 */
class SearchFilterContainer
{
    public function call(Twig $twig, SearchService $searchService):string
    {
        if (!$searchService->getResults()) {
            return '';
        }

        $searchResults = $searchService->getResults();

        return $twig->render(
            'Findologic::Category.Item.Partials.SearchFilters',
            [
                'resultsCount' => $searchResults->getResultsCount(),
                'facets' => $searchResults->getData(Response::DATA_FILTERS)
            ]
        );
    }
}