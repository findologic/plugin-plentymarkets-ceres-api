<?php

namespace Findologic\Containers;

use Findologic\Constants\Plugin;
use Findologic\Services\SearchService;
use Findologic\Api\Response\Response;
use Plenty\Modules\Category\Models\Category;
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
        $currentCategory = $searchService->getCategoryService()->getCurrentCategory();
        $showCategoryFilter = true;

        // Show category filter only in the 0 and 1 level categories
        if ($currentCategory !== null && $currentCategory->level > 1) {
            $showCategoryFilter = false;
        }

        return $twig->render(
            'Findologic::Category.Item.Partials.SearchFilters',
            [
                'resultsCount' => $searchResults->getResultsCount(),
                'facets' => $searchResults->getData(Response::DATA_FILTERS),
                'currentCategory' => null !== $currentCategory ? $currentCategory['details'] : [],
                'showCategoryFilter' => $showCategoryFilter
            ]
        );
    }
}
