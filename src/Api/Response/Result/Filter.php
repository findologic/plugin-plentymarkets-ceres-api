<?php

namespace Findologic\Api\Response\Result;

class Filter
{
    protected ?string $name;

    protected ?string $displayName;

    protected ?string $selectMode;

    protected ?string $cssClass;

    protected ?string $noAvailableFiltersText;

    protected ?string $combinationOperation;

    protected ?float $stepSize;

    protected ?string $unit;

    protected ?array $totalRange;

    protected ?array $selectedRange;

    protected ?int $pinnedFilterValueCount;

    protected string $type;

    /** @var FilterValue[] */
    protected $values = [];

    function __construct(array $filter)
    {
        $this->name = $filter['name'];
        $this->displayName = @$filter['displayName'];
        $this->selectMode = @$filter['selectMode'];
        $this->cssClass = @$filter['cssClass'];
        $this->noAvailableFiltersText = @$filter['noAvailableFiltersText'];
        $this->combinationOperation = @$filter['combinationOperation'];
        $this->type = @$filter['type'];
        $this->stepSize = @$filter['stepSize'];
        $this->unit = @$filter['unit'];
        $this->totalRange = @$filter['totalRange'] ?: [];
        $this->selectedRange = @$filter['selectedRange'];
        $this->pinnedFilterValueCount = @$filter['pinnedFilterValueCount'];
        $this->values = array_map(fn ($filterValue) => pluginApp(FilterValue::class, [$filterValue]), $filter['values'] ?: []);
    }

    /**
     * Get the value of name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the value of displayName
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * Get the value of selectMode
     */
    public function getSelectMode(): ?string
    {
        return $this->selectMode;
    }

    /**
     * Get the value of cssClass
     */
    public function getCssClass(): ?string
    {
        return $this->cssClass;
    }

    /**
     * Get the value of noAvailableFiltersText
     */
    public function getNoAvailableFiltersText(): ?string
    {
        return $this->noAvailableFiltersText;
    }

    /**
     * Get the value of combinationOperation
     */
    public function getCombinationOperation(): ?string
    {
        return $this->combinationOperation;
    }

    /**
     * Get the value of values
     */
    public function getValues(): ?array
    {
        return $this->values;
    }

    /**
     * Get the value of type
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Get the value of stepSize
     */
    public function getStepSize(): ?float
    {
        return $this->stepSize;
    }

    /**
     * Get the value of unit
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * Get the value of totalRange
     */
    public function getTotalRange(): ?array
    {
        return $this->totalRange;
    }

    /**
     * Get the value of selectedRange
     */
    public function getSelectedRange(): ?array
    {
        return $this->selectedRange;
    }
}
