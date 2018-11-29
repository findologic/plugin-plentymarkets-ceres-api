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
     * @param ExternalSearchOptions|null $searchOptions
     */
    public function handleSearchOptions($request, $searchOptions = null);

    /**
     * @param Request $request
     * @param ExternalSearch|null $searchQuery
     */
    public function handleSearchQuery($request, $searchQuery = null);
}