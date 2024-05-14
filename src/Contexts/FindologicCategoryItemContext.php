<?php

namespace Findologic\Contexts;

use Ceres\Contexts\CategoryItemContext;
use Findologic\Api\Response\Response;
use Findologic\Services\SearchService;
use IO\Helper\ContextInterface;

class FindologicCategoryItemContext extends CategoryItemContext implements ContextInterface
{
    public function init($params)
    {
        parent::init($params);

        /** @var SearchService $searchService */
        $searchService = pluginApp(SearchService::class);
        $searchResults = $searchService->getResults();
        $this->facets = $searchResults->getData(Response::DATA_FILTERS_WIDGETS);
    }
}
