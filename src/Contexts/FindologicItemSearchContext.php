<?php

namespace Findologic\Contexts;

use Ceres\Contexts\ItemSearchContext;
use Findologic\Api\Response\Response;
use Findologic\Services\SearchService;
use IO\Helper\ContextInterface;

class FindologicItemSearchContext extends ItemSearchContext implements ContextInterface
{
    public function init($params)
    {
        parent::init($params);

        /** @var SearchService $searchService */
        $searchService = pluginApp(SearchService::class);
        $searchResults = $searchService->getResults();
        $this->facets = $searchResults->getFiltersExtension();
    }
}
