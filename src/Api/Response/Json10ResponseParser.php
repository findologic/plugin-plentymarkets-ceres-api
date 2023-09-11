<?php

declare(strict_types=1);

namespace Findologic\Api\Response;

use Findologic\Constants\Plugin;
use FINDOLOGIC\Struct\Promotion;
use FINDOLOGIC\Struct\LandingPage;
use FINDOLOGIC\Api\Responses\Response;
use Findologic\Components\PluginConfig;
use FINDOLOGIC\Struct\FiltersExtension;
use FINDOLOGIC\Components\SmartDidYouMean;
use FINDOLOGIC\FinSearch\Struct\Pagination;
use Symfony\Component\HttpFoundation\Request;
use FINDOLOGIC\Api\Responses\Json10\Json10Response;
use FINDOLOGIC\Api\Responses\Json10\Properties\Item;
use FINDOLOGIC\Struct\QueryInfoMessage\QueryInfoMessage;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Filter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Promotion as ApiPromotion;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Filter as ApiFilter;

class Json10ResponseParser extends Json10Response
{

    /**
     * @var PluginConfig
     */
    protected $pluginConfig;

    public function __construct(PluginConfig $pluginConfig)
    {
        $this->pluginConfig = $pluginConfig;
    }

    public function getProductIds(): array
    {
        return array_map(
            function (Item $product) {
                if ($this->pluginConfig->get(Plugin::CONFIG_USE_VARIANTS)) {
                    return count($product->getVariants()) ? $product->getVariants()[0]->getId() : $product->getId();
                } else {
                    return $product->getId();
                }
            },
            $this->getResult()->getItems()
        );
    }

    public function getSmartDidYouMeanExtension(Request $request): SmartDidYouMean
    {
        return new SmartDidYouMean(
            $this->getRequest()->getQuery(),
            $this->getResult()->getMetadata()->getEffectiveQuery(),
            $this->getResult()->getVariant()->getCorrectedQuery(),
            $this->getResult()->getVariant()->getDidYouMeanQuery(),
            $this->getResult()->getVariant()->getImprovedQuery(),
            $request->getRequestUri()
        );
    }

    public function getLandingPageExtension(): ?LandingPage
    {
        $landingPage = $this->getResult()->getMetadata()->getLandingPage();
        if ($landingPage instanceof LandingPage) {
            return new LandingPage($landingPage->getUrl());
        }

        return null;
    }

    public function getPromotionExtension(): ?Promotion
    {
        $promotion = $this->getResult()->getMetadata()->getPromotion();

        if ($promotion instanceof ApiPromotion) {
            return new Promotion($promotion->getImageUrl(), $promotion->getUrl());
        }

        return null;
    }

    public function getFiltersExtension(): FiltersExtension
    {
        $apiFilters = array_merge(
            $this->getResult()->getMainFilters() ?? [],
            $this->getResult()->getOtherFilters() ?? []
        );

        $filtersExtension = new FiltersExtension();
        foreach ($apiFilters as $apiFilter) {
            $filter = Filter::getInstance($apiFilter);

            if ($filter && count($filter->getValues()) >= 1) {
                $filtersExtension->addFilter($filter);
            }
        }

        return $filtersExtension;
    }

    public function getPaginationExtension(?int $limit, ?int $offset): Pagination
    {
        return new Pagination($limit, $offset, $this->getResult()->getMetadata()->getTotalResults());
    }

    public function getQueryInfoMessage(): QueryInfoMessage
    {
        $queryString = $this->getRequest()->getQuery() ?? '';
        $params = $event->getRequest()->query->all();

        // if ($this->hasAlternativeQuery($queryString)) {   ?????????
        //     /** @var SmartDidYouMean $smartDidYouMean */
        //     $smartDidYouMean = $event->getContext()->getExtension('flSmartDidYouMean');

        //     return $this->buildSearchTermQueryInfoMessage($smartDidYouMean->getEffectiveQuery());
        // }

        // Check for shopping guide parameter first, otherwise it will always be overridden with search or vendor query
        if ($this->isFilterSet($params, 'wizard')) {
            return $this->buildShoppingGuideInfoMessage($params);
        }

        if ($this->hasQuery($queryString)) {
            return $this->buildSearchTermQueryInfoMessage($queryString);
        }

        if ($this->isFilterSet($params, 'cat')) {
            return $this->buildCategoryQueryInfoMessage($params);
        }

        $vendorFilterValues = $this->getFilterValues($params, BaseFilter::VENDOR_FILTER_NAME);
        if (
            $vendorFilterValues &&
            count($vendorFilterValues) === 1
        ) {
            return $this->buildVendorQueryInfoMessage($params, current($vendorFilterValues));
        }

        return QueryInfoMessage::buildInstance(QueryInfoMessage::TYPE_DEFAULT);
    }

    private function buildSearchTermQueryInfoMessage(string $query): SearchTermQueryInfoMessage
    {
        /** @var SearchTermQueryInfoMessage $queryInfoMessage */
        $queryInfoMessage = QueryInfoMessage::buildInstance(
            QueryInfoMessage::TYPE_QUERY,
            $query
        );

        return $queryInfoMessage;
    }

    private function buildShoppingGuideInfoMessage(array $params): ShoppingGuideInfoMessage
    {
        /** @var ShoppingGuideInfoMessage $queryInfoMessage */
        $queryInfoMessage = QueryInfoMessage::buildInstance(
            QueryInfoMessage::TYPE_SHOPPING_GUIDE,
            $params['wizard']
        );

        return $queryInfoMessage;
    }

    private function buildCategoryQueryInfoMessage(array $params): CategoryInfoMessage
    {
        /** @var ApiFilter[] $filters */
        $filters = array_merge(
            $this->getResult()->getMainFilters() ?? [],
            $this->getResult()->getOtherFilters() ?? []
        );

        $categories = explode('_', $params['cat']);
        $category = end($categories);

        $catFilter = array_filter(
            $filters,
            static fn (ApiFilter $filter) => $filter->getName() === Filter::CAT_FILTER_NAME
        );

        if ($catFilter && count($catFilter) === 1) {
            $filterName = array_values($catFilter)[0]->getDisplayName();
        } else {
            $filterName = $this->serviceConfigResource->getSmartSuggestBlocks($this->config->getShopkey())['cat'];
        }

        /** @var CategoryInfoMessage $categoryInfoMessage */
        $categoryInfoMessage = QueryInfoMessage::buildInstance(
            QueryInfoMessage::TYPE_CATEGORY,
            null,
            $filterName,
            $category
        );

        return $categoryInfoMessage;
    }

    private function buildVendorQueryInfoMessage(array $params, string $value): VendorInfoMessage
    {
        /** @var ApiFilter[] $filters */
        $filters = array_merge(
            $this->getResult()->getMainFilters() ?? [],
            $this->getResult()->getOtherFilters() ?? []
        );

        $vendorFilter = array_filter(
            $filters,
            static fn (ApiFilter $filter) => $filter->getName() === BaseFilter::VENDOR_FILTER_NAME
        );

        if ($vendorFilter && count($vendorFilter) === 1) {
            $filterName = array_values($vendorFilter)[0]->getDisplayName();
        } else {
            $filterName = $this->serviceConfigResource->getSmartSuggestBlocks($this->config->getShopkey())['vendor'];
        }

        /** @var VendorInfoMessage $vendorInfoMessage */
        $vendorInfoMessage = QueryInfoMessage::buildInstance(
            QueryInfoMessage::TYPE_VENDOR,
            null,
            $filterName,
            $value
        );

        return $vendorInfoMessage;
    }

    private function hasAlternativeQuery(?string $queryString): bool
    {
        $correctedQuery = $this->getResult()->getVariant()->getCorrectedQuery();
        $improvedQuery = $this->getResult()->getVariant()->getImprovedQuery();
        $didYouMeanQuery = $this->getResult()->getVariant()->getDidYouMeanQuery();

        return !empty($queryString) && ($correctedQuery || $improvedQuery || $didYouMeanQuery);
    }

    private function hasQuery(?string $queryString): bool
    {
        return !empty($queryString);
    }

    private function isFilterSet(array $params, string $name): bool
    {
        return isset($params[$name]) && !empty($params[$name]);
    }

    /**
     * @param array $params
     * @param string $name
     * @return string[]|null
     */
    private function getFilterValues(array $params, string $name): ?array
    {
        if (!$this->isFilterSet($params, $name)) {
            return null;
        }

        $filterValues = [];
        $joinedFilterValues = explode(FilterHandler::FILTER_DELIMITER, $params[$name]);

        foreach ($joinedFilterValues as $joinedFilterValue) {
            $filterValues[] = str_contains($joinedFilterValue, FilterValue::DELIMITER)
                ? explode(FilterValue::DELIMITER, $joinedFilterValue)[1]
                : $joinedFilterValue;
        }

        return $filterValues;
    }

    // public function getFiltersWithSmartSuggestBlocks(
    //     FiltersExtension $flFilters,
    //     array $smartSuggestBlocks,
    //     array $params
    // ): FiltersExtension {
    //     $hasCategoryFilter = $hasVendorFilter = false;

    //     foreach ($flFilters->getFilters() as $filter) {
    //         if ($filter instanceof CategoryFilter) {
    //             $hasCategoryFilter = true;
    //         }
    //         if ($filter->getId() === 'vendor') {
    //             $hasVendorFilter = true;
    //         }
    //     }

    //     $allowCatHiddenFilter = !$hasCategoryFilter && array_key_exists('cat', $smartSuggestBlocks);
    //     $allowVendorHiddenFilter = !$hasVendorFilter && array_key_exists('vendor', $smartSuggestBlocks);

    //     if ($allowCatHiddenFilter && $this->isFilterSet($params, 'cat')) {
    //         $customFilter = $this->buildHiddenFilter($smartSuggestBlocks, $params, 'cat');
    //         if ($customFilter) {
    //             $flFilters->addFilter($customFilter);
    //         }
    //     }

    //     if ($allowVendorHiddenFilter && $this->isFilterSet($params, BaseFilter::VENDOR_FILTER_NAME)) {
    //         $customFilter = $this->buildHiddenFilter($smartSuggestBlocks, $params, 'vendor');
    //         if ($customFilter) {
    //             $flFilters->addFilter($customFilter);
    //         }
    //     }

    //     return $flFilters;
    // }

    // /**
    //  * @param string[] $smartSuggestBlocks
    //  * @param string[] $params
    //  * @param string $filterName
    //  *
    //  * @return BaseFilter|null
    //  */
    // private function buildHiddenFilter(array $smartSuggestBlocks, array $params, string $filterName): ?BaseFilter
    // {
    //     $display = $smartSuggestBlocks[$filterName];
    //     $value = $params[$filterName];

    //     switch ($filterName) {
    //         case 'cat':
    //             $customFilter = new CategoryFilter($filterName, $display);
    //             break;
    //         case 'vendor':
    //             $customFilter = new VendorImageFilter($filterName, $display);
    //             break;
    //         default:
    //             return null;
    //     }

    //     $filterValue = new FilterValue($value, $value, $filterName);
    //     $customFilter->addValue($filterValue);
    //     $customFilter->setHidden(true);

    //     return $customFilter;
    // }
}
