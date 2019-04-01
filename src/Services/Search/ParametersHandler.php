<?php

namespace Findologic\Services\Search;

use Ceres\Config\CeresConfig;
use Ceres\Helper\ExternalSearchOptions;
use Ceres\Helper\SearchOptions;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Plugin\Translation\Translator;
use Findologic\Constants\Plugin;

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

    /**
     * @param ExternalSearchOptions $search
     * @param HttpRequest $request
     * @return ExternalSearchOptions
     */
    public function handlePaginationAndSorting($search, $request)
    {
        /** @var CeresConfig $config */
        $config = $this->getConfig();

        $isSearch = strpos($request->getUri(), '/search') !== false;

        $defaultSort = $isSearch ? $config->sorting->defaultSortingSearch : $config->sorting->defaultSorting;

        if (!in_array($defaultSort, Plugin::API_SORT_ORDER_AVAILABLE_OPTIONS)) {
            $defaultSort = 'item.score';
        }

        $search->setSortingOptions($this->getSortingOptions($isSearch), $defaultSort);
        $search->setItemsPerPage($this->getItemsPerPage($search), $this->getCurrentItemsPerPage($request, $search));

        return $search;
    }

    /**
     * @param boolean $isSearch
     * @return array
     */
    public function getSortingOptions($isSearch)
    {
        /** @var CeresConfig $config */
        $config = $this->getConfig();

        $returnArray = [];

        foreach ($config->sorting->data as $data) {
            if (in_array($data, Plugin::API_SORT_ORDER_AVAILABLE_OPTIONS)) {
                $returnArray[$data] = 'Ceres::Template.' . SearchOptions::TRANSLATION_MAP[$data];
            }
        }

        if ($isSearch && !array_key_exists('item.score', $returnArray)) {
            $returnArray['item.score'] = 'Ceres::Template.' . SearchOptions::TRANSLATION_MAP['item.score'];
        }

        return $returnArray;
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
        $currentItemsPerPage = $request->get(Plugin::PLENTY_PARAMETER_PAGINATION_ITEMS_PER_PAGE, $search->getDefaultItemsPerPage());

        if (!$currentItemsPerPage) {
            $itemsPerPageOptions = $this->getItemsPerPage($search);
            $currentItemsPerPage = $itemsPerPageOptions[0];
        }

        return $currentItemsPerPage;
    }
}