<?php

namespace Findologic\Containers;

use Findologic\Constants\Plugin;
use Findologic\Services\SearchService;
use Findologic\Api\Response\Response;
use Plenty\Modules\Category\Models\Category;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Templates\Twig;

/**
 * Class SearchFilterContainer
 * @package Findologic\Containers
 */
class SearchFilterContainer
{
    use Loggable;
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

        $filtersExtension = $searchResults->getFiltersExtension();
        $filters = $filtersExtension->getFilters();
        $this->getLogger(__METHOD__)->error('facets', ['facets container' => $filters]);
        // throw new \Exception(json_encode($filters));
        return $twig->render(
            'Findologic::Category.Item.Partials.SearchFilters',
            [
                'resultsCount' => $searchResults->parseTotalResults(),
                'facets' => $filters,
                'currentCategory' => null !== $currentCategory ? $currentCategory->details : [],
                'showCategoryFilter' => $showCategoryFilter
            ]
        );
    }
}