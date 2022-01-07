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
    public function call(Twig $twig, SearchService $searchService): string
    {
        if (!$searchService->getResults()) {
            return '';
        }

        $searchResults = $searchService->getResults();

        $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $parsedUrl = parse_url($url);

        return $twig->render(
            'Findologic::Category.Item.Partials.SearchFilters',
            [
                'resultsCount' => $searchResults->getResultsCount(),
                'facets' => $searchResults->getData(Response::DATA_FILTERS),
                'queryParams' => $parsedUrl['query'],
            ]
        );
    }
}
