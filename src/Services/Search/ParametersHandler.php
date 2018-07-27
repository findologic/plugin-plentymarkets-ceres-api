<?php

namespace Findologic\Services\Search;

use Ceres\Config\CeresConfig;
use Ceres\Helper\ExternalSearchOptions;
use Findologic\Api\Response\Response;
use Plenty\Plugin\Http\Request as HttpRequest;

/**
 * Class ParametersHandler
 * @package Findologic\Services\Search
 */
class ParametersHandler
{
    /**
     * @var CeresConfig
     */
    protected $config;

    /**
     * @var bool|array
     */
    protected $itemsPerPageOptions = [];

    /**
     * @param ExternalSearchOptions $search
     * @param Response $searchResults
     * @param HttpRequest $request
     * @return ExternalSearchOptions
     */
    public function handlePaginationAndSorting($search, $searchResults, $request)
    {
        $search->setSortingOptions($this->getSortingOptions(), $this->getCurrentSorting($request));
        $search->setItemsPerPage($search->getItemsPerPage(), $this->getCurrentItemsPerPage($request, $search));

        return $search;
    }

    /**
     * @return array
     */
    public function getSortingOptions()
    {
        //TODO: translations and maybe sort options labels should be configurable ?
        return [
            '' => 'Revelance',
            'price ASC' => 'Price ⬆',
            'price DESC' => 'Price ⬇',
            'label ASC' => 'A-Z',
            'salesfrequency DESC' => 'Top sellers',
            'dateadded DESC' => 'Newest'
        ];
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    public function getCurrentSorting($request)
    {
        return $request->get('sorting', '');
    }

    /**
     * @param HttpRequest $request
     * @param ExternalSearchOptions $search
     * @return string
     */
    public function getCurrentItemsPerPage($request, $search)
    {
        return $request->get('items', $search->getDefaultItemsPerPage());
    }
}