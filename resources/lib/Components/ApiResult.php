<?php

namespace FindologicApi\Components;

use FINDOLOGIC\Api\Responses\Json10\Properties\Item;
use FINDOLOGIC\Api\Responses\Json10\Properties\Result;
use FINDOLOGIC\Api\Responses\Json10\Properties\Variant;
use FINDOLOGIC\Api\Responses\Json10\Properties\Metadata;
use FINDOLOGIC\Api\Responses\Json10\Properties\ItemVariant;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Filter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Values\FilterValue;

class ApiResult extends Result
{

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
                'landingPage' => (array) $this->metadata->getLandingPage(),
                'promotion' => (array) $this->metadata->getPromotion()
            ],
            'items' => array_map(fn (Item $item) => [
                'highlightedName' => $item->getHighlightedName(),
                'productPlacement' => $item->getProductPlacement(),
                'pushRules' => $item->getPushRules(),
                'variants' => array_map(fn (ItemVariant $variant) => (array)$variant, $item->getVariants())
            ], $this->items),
            'variant' => (array)$this->variant,
            'mainFilters' => array_map(fn (Filter $filter) => [...(array)$filter, 'filterValues' => array_map(fn (FilterValue $filterValue) => (array)$filterValue, $filter->getValues())], $this->mainFilters),
            'otherFilters' => array_map(fn (Filter $filter) => [...(array)$filter, 'filterValues' => array_map(fn (FilterValue $filterValue) => (array)$filterValue, $filter->getValues())], $this->otherFilters)
        ];
    }
}
