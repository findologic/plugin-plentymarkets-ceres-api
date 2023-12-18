<?php

require_once __DIR__ . '/ApiItem.php';
require_once __DIR__ . '/ApiFilter.php';
require_once __DIR__ . '/Arrayable.php';

use FINDOLOGIC\Api\Responses\Json10\Properties\Item;
use FINDOLOGIC\Api\Responses\Json10\Properties\Result;
use FINDOLOGIC\Api\Responses\Json10\Properties\Variant;
use FINDOLOGIC\Api\Responses\Json10\Properties\Metadata;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Filter;

class ApiResult extends Result implements Arrayable
{
    /** @var Metadata */
    public $metadata;

    /** @var Item[] */
    public $items = [];

    /** @var Variant */
    public $variant;

    /** @var Filter[] */
    public $mainFilters;

    /** @var Filter[] */
    public $otherFilters;

    function __construct(Result $result)
    {
        $this->metadata = $result->getMetadata();
        $this->items = $result->getItems();
        $this->variant = $result->getVariant();
        $this->mainFilters = $result->getMainFilters();
        $this->otherFilters = $result->getOtherFilters();
    }
    public function toArray()
    {
        $promotion = $this->metadata->getPromotion();
        $landingPage = $this->metadata->getLandingPage();
        return [
            'metadata' => [
                'searchConcept' => $this->metadata->getSearchConcept(),
                'effectiveQuery' => $this->metadata->getEffectiveQuery(),
                'totalResults' => $this->metadata->getTotalResults(),
                'currencySymbol' => $this->metadata->getCurrencySymbol(),
                'landingPage' => [
                    'name' => $landingPage ? $landingPage->getName() : null,
                    'url' => $landingPage ? $landingPage->getUrl() : null
                ],
                'promotion' => [
                    'name' => $promotion ? $promotion->getName() : null,
                    'url' => $promotion ? $promotion->getUrl() : null,
                    'imageUrl' => $promotion ? $promotion->getImageUrl() : null,
                ],
            ],
            'items' => array_map(fn (Item $item) => (new ApiItem($item))->toArray(), $this->items),
            'variant' => [
                'name' => $this->variant->getName(),
                'correctedQuery' => $this->variant->getCorrectedQuery(),
                'improvedQuery' => $this->variant->getImprovedQuery(),
                'didYouMeanQuery' => $this->variant->getDidYouMeanQuery(),
            ],
            'mainFilters' => array_map(fn (Filter $filter) => (new ApiFilter($filter))->toArray(), $this->mainFilters ?? []),
            'otherFilters' => array_map(fn (Filter $filter) => (new ApiFilter($filter))->toArray(), $this->otherFilters ?? [])
        ];
    }
}
