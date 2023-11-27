<?php

namespace Findologic\Api\Response\Result;

class Result
{
    /** @var Metadata */
    private Metadata $metadata;

    /** @var Item[] */
    private $items = [];

    private Variant $variant;

    /** @var Filter[] */
    private $mainFilters;

    /** @var Filter[] */
    private $otherFilters;

    public function __construct(array $result = [])
    {
        $this->metadata = pluginApp(Metadata::class, [$result['metadata']]);
        if(!empty($result['items'])) $this->items = array_map(fn ($item) => pluginApp(Item::class, $item), [$result['items']]);
        $this->variant = pluginApp(Variant::class, [$result['variant']]);
        $filterValueId = 0;
        $this->mainFilters = array_map(function($mainFilter) use (&$filterValueId){
            return pluginApp(Filter::class, [$mainFilter, ++$filterValueId]);
        }, $result['mainFilters']);
        $this->otherFilters = array_map(function ($otherFilter) use(&$filterValueId){
            return pluginApp(Filter::class, [$otherFilter, ++$filterValueId]);
        }, $result['otherFilters']);
    }

    /**
     * Get the value of metadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Get the value of items
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get the value of variant
     */
    public function getVariant()
    {
        return $this->variant;
    }

    /**
     * Get the value of mainFilters
     */
    public function getMainFilters()
    {
        return $this->mainFilters;
    }

    /**
     * Get the value of otherFilters
     */
    public function getOtherFilters()
    {
        return $this->otherFilters;
    }
}
