<?php

namespace Findologic\Services\Search;

use Ceres\Config\CeresConfig;
use Ceres\Helper\ExternalSearchOptions;
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
        $search->setSortingOptions($this->getSortingOptions(), $this->getCurrentSorting($request));
        $search->setItemsPerPage($this->getItemsPerPage($search), $this->getCurrentItemsPerPage($request, $search));

        return $search;
    }

    /**
     * @return array
     */
    public function getSortingOptions()
    {
        return [
            '' => $this->getTranslator()->trans("Findologic::search.sortRevelance"),
            'price ASC' => $this->getTranslator()->trans("Findologic::search.sortPriceAsc"),
            'price DESC' => $this->getTranslator()->trans("Findologic::search.sortPriceDesc"),
            'label ASC' => $this->getTranslator()->trans("Findologic::search.sortLabelAsc"),
            'salesfrequency DESC' => $this->getTranslator()->trans("Findologic::search.sortSalesFrequency"),
            'dateadded DESC' => $this->getTranslator()->trans("Findologic::search.sortDateAdded"),
        ];
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    public function getCurrentSorting($request)
    {
        return $request->get(Plugin::PLENTY_PARAMETER_SORT_ORDER, '');
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