<?php

namespace Findologic\Services;

use Plenty\Plugin\Http\Request;

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
     * @param $searchOptions
     */
    public function handleSearchQuery($externalSearch);
}