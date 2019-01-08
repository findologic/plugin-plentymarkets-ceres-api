<?php

namespace Findologic\Services;

use Ceres\Helper\ExternalSearch;

/**
 * Interface SearchServiceInterface
 * @package Findologic\Services
 */
interface SearchServiceInterface
{
    /**
     * @param $searchOptions
     * @param $request
     */
    public function handleSearchOptions($searchOptions, $request);

    /**
     * @param ExternalSearch $externalSearch
     */
    public function handleSearchQuery(ExternalSearch $externalSearch);
}