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
    protected $itemsPerPage = [];

    public function getConfig()
    {
        if (!$this->config) {
            $this->config = pluginApp(CeresConfig::class);
        }

        return $this->config;
    }

    /**
     * @param ExternalSearchOptions $search
     * @param Response $searchResults
     * @param HttpRequest $request
     * @return ExternalSearchOptions
     */
    public function handlePaginationAndSorting($search, $searchResults, $request)
    {
        $search->setSortingOptions($this->getSortingOptions(), $this->getCurrentSorting($request));
        $search->setItemsPerPage($this->getItemsPerPage($search), $this->getCurrentItemsPerPage($request, $search));

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
     * @param ExternalSearchOptions $search
     * @return array
     */
    public function getItemsPerPage($search)
    {
        if (!empty($search->getItemsPerPage())) {
            return $search->getItemsPerPage();
        }

        if (empty($this->itemsPerPage)) {
            foreach ($this->getConfig()->pagination->rowsPerPage as $rowPerPage) {
                $this->itemsPerPage[] = $rowPerPage * $this->getConfig()->pagination->columnsPerPage;
            }
        }

        return $this->itemsPerPage;
    }

    /**
     * @param HttpRequest $request
     * @param ExternalSearchOptions $search
     * @return string
     */
    public function getCurrentItemsPerPage($request, $search)
    {
        $currentItemsPerPage = $request->get('items', $search->getDefaultItemsPerPage());

        if (!$currentItemsPerPage) {
            $itemsPerPageOptions = $this->getItemsPerPage($search);
            $currentItemsPerPage = $itemsPerPageOptions[0];
        }

        return $currentItemsPerPage;
    }
}