<?php

use FINDOLOGIC\Api\Responses\Json10\Properties\Item;
use FINDOLOGIC\Api\Responses\Json10\Properties\Result;
use FINDOLOGIC\Api\Responses\Json10\Properties\Variant;
use FINDOLOGIC\Api\Responses\Json10\Properties\Metadata;
use FINDOLOGIC\Api\Responses\Json10\Properties\ItemVariant;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Filter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\ColorFilter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\ImageFilter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\LabelFilter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\SelectFilter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\RangeSliderFilter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Values\FilterValue;

class ApiResult extends Result
{
    public const RATING_FILTER_NAME = 'rating';
    public const CAT_FILTER_NAME = 'cat';
    public const VENDOR_FILTER_NAME = 'vendor';

    /** @var Metadata */
    private $metadata;

    /** @var Item[] */
    private $items = [];

    /** @var Variant */
    private $variant;

    /** @var Filter[] */
    private $mainFilters;

    /** @var Filter[] */
    private $otherFilters;

    function __construct(Result $result)
    {
        $this->metadata = $result->getMetadata();
        $this->items = $result->getItems();
        $this->variant = $result->getVariant();
        $this->mainFilters = $result->getMainFilters();
        $this->otherFilters = $result->getOtherFilters();
    }
    public function __toArray()
    {
        return [
            'metadata' => [
                'searchConcept' => $this->metadata->getSearchConcept(),
                'effectiveQuery' => $this->metadata->getEffectiveQuery(),
                'totalResults' => $this->metadata->getTotalResults(),
                'currencySymbol' => $this->metadata->getCurrencySymbol(),
                'landingPage' => json_encode($this->metadata->getLandingPage()),
                'promotion' => (array) $this->metadata->getPromotion()
            ],
            'items' => array_map(fn (Item $item) => [
                'obj_vars' => $this->getItem($item),
                'highlightedName' => $item->getHighlightedName(),
                'productPlacement' => $item->getProductPlacement(),
                'pushRules' => $item->getPushRules(),
                'variants' => array_map(fn (ItemVariant $variant) => (array)$variant, $item->getVariants())
            ], $this->items),
            'variant' => (array)$this->variant,
            'mainFilters' => $this->getFilters($this->mainFilters),//array_map(fn (Filter $filter) => [...(array)$filter, ...$this->getFilterExtras($filter), 'filterValues' => array_map(fn (FilterValue $filterValue) => (array)$filterValue, $filter->getValues())], $this->mainFilters),
            'otherFilters' => $this->getFilters($this->otherFilters)//array_map(fn (Filter $filter) => [...(array)$filter, ...$this->getFilterExtras($filter), 'filterValues' => array_map(fn (FilterValue $filterValue) => (array)$filterValue, $filter->getValues())], $this->otherFilters)
        ];
    }

    private function getItem(Item $item)
    {
        $reflectionClass = new ReflectionClass(Item::class);
        return $reflectionClass->getMethods();
    }

    private function getFilters(array $filters):array
    {
        $filtered = [];

        foreach ($filters as $filter) {
            /** @var $filter Filter */
            $filtered[] = array_merge($this->getFilterExtras($filter), array_merge((array)$filter, $this->getFilterValues($filter->getValues())));

        }

        return $filtered;
    }

    private function getFilterValues(array $filterValues):array
    {
        $filtered = [];

        foreach ($filterValues as $filterValue) {
            /** @var $filter FilterValue */
            $filtered[] = (array)$filterValue;

        }

        return $filtered;
    }    

    private function getFilterExtras(Filter|RangeSliderFilter $filter):array
    {
        switch (true) {
            case $filter instanceof LabelFilter:
                if ($filter->getName() === self::CAT_FILTER_NAME) {
                    return [ 'type' => 'labelFilter'];
                }

                return [ 'type' => 'labelFilter'];
            case $filter instanceof SelectFilter:
                if ($filter->getName() === self::CAT_FILTER_NAME) {
                    return [ 'type' => 'selectFilter'];
                }

                return [ 'type' => 'selectFilter'];
            case $filter instanceof RangeSliderFilter:
                if ($filter->getName() === self::RATING_FILTER_NAME) {
                    return [ 'type' => 'rangeSliderFilter'];
                }

                return [ 'type' => 'rangeSliderFilter', 'totalRange' => (array)$filter->getTotalRange(), 'selectedRange' => (array)$filter->getSelectedRange()];
            case $filter instanceof ColorFilter:
                return [ 'type' => 'colorPickerFilter'];
            case $filter instanceof ImageFilter:
                return [ 'type' => 'vendorImageFilter'];
            default:
                throw new \Exception('The submitted filter is unknown.');
        }
    }
}
