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
        $this->items = array_map(fn ($item) => pluginApp(Item::class, $item), [$result['items']]);
        $this->variant = pluginApp(Variant::class, [$result['variant']]);
        $this->mainFilters = array_map(fn ($mainFilter) => pluginApp(Filter::class, [$mainFilter]), $result['mainFilters']);
        $this->otherFilters = array_map(fn ($otherFilter) => pluginApp(Filter::class, [$otherFilter]), $result['otherFilters']);
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
