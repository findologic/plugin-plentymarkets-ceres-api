<?php

namespace Findologic\Contexts;

use IO\Helper\ContextInterface;
use Findologic\Api\Response\Response;
use Findologic\Services\SearchService;
use Ceres\Contexts\CategoryItemContext;

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
