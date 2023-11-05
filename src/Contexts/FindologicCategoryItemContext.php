<?php

namespace Findologic\Contexts;

use Ceres\Contexts\CategoryItemContext;
use Findologic\Api\Response\Response;
use Findologic\Services\SearchService;
use IO\Helper\ContextInterface;
use Plenty\Plugin\Log\Loggable;

class FindologicCategoryItemContext extends CategoryItemContext implements ContextInterface
{
    use Loggable;
    public function init($params)
    {
        parent::init($params);

        /** @var SearchService $searchService */
        $searchService = pluginApp(SearchService::class);
        $searchResults = $searchService->getResults();
        $this->getLogger(__METHOD__)->error('facets', ['facets' => $searchResults->getFiltersExtension()]);
        $this->facets = $searchResults->getFiltersExtension();
    }
}
