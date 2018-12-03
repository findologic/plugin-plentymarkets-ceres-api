<?php

namespace Findologic\Containers;

use Findologic\Services\SearchService;
use Findologic\Api\Response\Response;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\Http\Request;

/**
 * Class SearchFilterContainer
 * @package Findologic\Containers
 */
class SearchFilterContainer
{
    public function call(Twig $twig, SearchService $searchService, Request $request):string
    {
        $searchResults = $searchService->search($request);

        return $twig->render(
            'Findologic::content.filters',
            [
                'facets' => $searchResults->getData(Response::DATA_FILTERS)
            ]
        );
    }
}