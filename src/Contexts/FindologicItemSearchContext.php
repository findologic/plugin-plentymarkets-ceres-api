<?php

namespace Findologic\Contexts;

use Ceres\Contexts\ItemSearchContext;
use Findologic\Api\Response\Response;
use Findologic\Services\SearchService;
use IO\Helper\ContextInterface;
use Plenty\Plugin\Log\Loggable;

class FindologicItemSearchContext extends ItemSearchContext implements ContextInterface
{
    use Loggable;
    public function init($params)
    {
        parent::init($params);

        /** @var SearchService $searchService */
        $searchService = pluginApp(SearchService::class);
        $searchResults = $searchService->getResults();
        $this->getLogger(__METHOD__)->error('facets', ['facets' => json_decode(json_encode($searchResults->getFiltersExtension()), true)]);
        $this->facets = $searchResults->getFiltersExtension();
    }
}
