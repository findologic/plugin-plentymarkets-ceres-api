<?php

namespace Findologic\Services\Search;

use Ceres\Config\CeresConfig;
use Ceres\Helper\SearchOptions;
use Findologic\Constants\Plugin;
use IO\Extensions\Constants\ShopUrls;
use Ceres\Helper\ExternalSearchOptions;
use IO\Helper\RouteConfig;
use Plenty\Plugin\Translation\Translator;
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
     * @var Translator
     */
    protected $translator;

    /**
     * @var ShopUrls
     */
    protected $shopUrls;

    /**
     * @var bool|array
     */
    protected $itemsPerPage = [];

    /**
     * @return CeresConfig
     */
    public function getConfig()
    {
        if (!$this->config) {
            $this->config = pluginApp(CeresConfig::class);
        }

        return $this->config;
    }

    /**
     * @return Translator
     */
    public function getTranslator()
    {
        if (!$this->translator) {
            $this->translator = pluginApp(Translator::class);
        }

        return $this->translator;
    }

    public function getShopUrls()
    {
        if (!$this->shopUrls) {
            $this->shopUrls = pluginApp(ShopUrls::class);
        }

        return $this->shopUrls;
    }

    /**
     * @param ExternalSearchOptions $search
     * @param HttpRequest $request
     * @return ExternalSearchOptions
     */
    public function handlePaginationAndSorting($search, $request)
    {
        /** @var CeresConfig $config */
        $config = $this->getConfig();
        $searchUrls = $this->getShopUrls();

        $isSearch = $searchUrls->is(RouteConfig::SEARCH);
        $isFiltersSet = array_key_exists('attrib', $request->all());

        $defaultSort = $isSearch ? $config->sorting->defaultSortingSearch : $config->sorting->defaultSorting;
        if (!in_array($defaultSort, Plugin::API_SORT_ORDER_AVAILABLE_OPTIONS) && ($isSearch || $isFiltersSet)) {
            $defaultSort = 'item.score';
        }

        $search->setSortingOptions($this->getSortingOptions($isSearch, $isFiltersSet), $defaultSort);
        $search->setItemsPerPage($this->getItemsPerPage($search), $this->getCurrentItemsPerPage($request, $search));

        return $search;
    }

    public function getSortingOptions(bool $isSearch, bool $isFiltersSet): array
    {
        if (!$isSearch && !$isFiltersSet) {
            return $this->getPlentymarketsSortingOptions();
        }

        return $this->getFindologicSortingOptions($isSearch);
    }

    public function getPlentymarketsSortingOptions(): array
    {
        /** @var CeresConfig $config */
        $config = $this->getConfig();

        $sortingOptions = [];

        foreach ($config->sorting->data as $data) {
            $sortingOptions[$data] = 'Ceres::Template.' . SearchOptions::TRANSLATION_MAP[$data];
        }

        return $sortingOptions;
    }

    public function getFindologicSortingOptions(bool $isSearch): array
    {
        /** @var CeresConfig $config */
        $config = $this->getConfig();

        $sortingOptions = [];

        foreach ($config->sorting->data as $data) {
            if (in_array($data, Plugin::API_SORT_ORDER_AVAILABLE_OPTIONS)) {
                $sortingOptions[$data] = 'Ceres::Template.' . SearchOptions::TRANSLATION_MAP[$data];
            }
        }

        if ($isSearch && !array_key_exists('item.score', $sortingOptions)) {
            $sortingOptions['item.score'] = 'Ceres::Template.' . SearchOptions::TRANSLATION_MAP['item.score'];
        }

        return $sortingOptions;
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

        /** @var CeresConfig $config */
        $config = $this->getConfig();

        if (empty($this->itemsPerPage)) {
            foreach ($config->pagination->rowsPerPage as $rowPerPage) {
                $this->itemsPerPage[] = $rowPerPage * $config->pagination->columnsPerPage;
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
        $currentItemsPerPage = $request->get(
            Plugin::PLENTY_PARAMETER_PAGINATION_ITEMS_PER_PAGE,
            $search->getDefaultItemsPerPage()
        );

        if (!$currentItemsPerPage) {
            $itemsPerPageOptions = $this->getItemsPerPage($search);
            $currentItemsPerPage = $itemsPerPageOptions[0];
        }

        return $currentItemsPerPage;
    }
}
