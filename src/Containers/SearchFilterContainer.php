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
        $searchResults = $searchService->getResults();

        return $twig->render(
            'Findologic::content.FiltersContainer',
            [
                'resultsCount' => $searchResults->getResultsCount(),
                'facets' => $searchResults->getData(Response::DATA_FILTERS)
            ]
        );
    }
}