<?php

namespace Findologic\Services;

use Plenty\Plugin\Http\Request;
use Ceres\Helper\ExternalSearch;
use Ceres\Helper\ExternalSearchOptions;

/**
 * Interface SearchServiceInterface
 * @package Findologic\Services
 */
interface SearchServiceInterface
{
    /**
     * @param Request $request
     * @param ExternalSearchOptions $searchOptions
     */
    public function handleSearchOptions(Request $request, ExternalSearchOptions $searchOptions);

    /**
     * @param Request $request
     * @param ExternalSearch $externalSearch
     */
    public function handleSearchQuery(Request $request, ExternalSearch $externalSearch);
}
