<?php

namespace FindologicApi\Components;

use FINDOLOGIC\Api\Responses\Json10\Properties\Item;
use FINDOLOGIC\Api\Responses\Json10\Properties\Result;
use FINDOLOGIC\Api\Responses\Json10\Properties\Variant;
use FINDOLOGIC\Api\Responses\Json10\Properties\Metadata;
use FINDOLOGIC\Api\Responses\Json10\Properties\ItemVariant;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Filter;

class ApiResult extends Result{

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

    function __construct(Result $result) {
        $this->metadata = $result->getMetadata();
        $this->items = $result->getItems();
        $this->variant = $result->getVariant();
        $this->mainFilters = $result->getMainFilters();
        $this->otherFilters = $result->getOtherFilters();
    }
    public function __toArray(){
        return [
            'metadata' => [
                'search_concept' => $this->metadata->getSearchConcept(),
                'effective_query' => $this->metadata->getEffectiveQuery(),
                'total_results' => $this->metadata->getTotalResults(),
                'currency_symbol' => $this->metadata->getCurrencySymbol()
            ],
            'items' => array_map(fn(Item $item)=>[
                'highlighted_name' => $item->getHighlightedName(),
                'product_placement' => $item->getProductPlacement(),
                'push_rules' => $item->getPushRules(),
                'variants' => array_map(fn(ItemVariant $variant)=>(array)$variant, $item->getVariants())
            ], $this->items),
            'variant' => (array)$this->variant,
            'main_filters' => array_map(fn(Filter $filter)=>(array)$filter, $this->mainFilters),
            'other_filters' => array_map(fn(Filter $filter)=>(array)$filter, $this->otherFilters)
        ];
    }
}