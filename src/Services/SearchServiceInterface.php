<?php

namespace Findologic\PluginPlentymarketsApi\Services;

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
     * @param $request
     */
    public function handleSearchQuery($searchQuery, $request);
}