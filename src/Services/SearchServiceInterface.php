<?php

namespace Findologic\Services;

use Ceres\Helper\ExternalSearch;
use Ceres\Helper\ExternalSearchOptions;
use Plenty\Plugin\Http\Request;

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
