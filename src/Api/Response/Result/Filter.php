<?php

namespace Findologic\Api\Response\Result;

class Filter
{
    protected string $name;

    protected string $displayName;

    protected string $selectMode;

    protected ?string $cssClass;

    protected ?string $noAvailableFiltersText;

    protected ?string $combinationOperation;

    /** @var FilterValue[] */
    protected $values = [];

    function __construct(array $filter)
    {
        $this->name = $filter['name'];
        $this->displayName = $filter['displayName'];
        $this->selectMode = $filter['selectMode'];
        $this->cssClass = $filter['cssClass'];
        $this->noAvailableFiltersText = $filter['noAvailableFiltersText'];
        $this->combinationOperation = $filter['combinationOperation'];
        $this->values = array_map(fn ($filterValue) => pluginApp(FilterValue::class, $filterValue), $filter['values']);
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of displayName
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Get the value of selectMode
     */
    public function getSelectMode()
    {
        return $this->selectMode;
    }

    /**
     * Get the value of cssClass
     */
    public function getCssClass()
    {
        return $this->cssClass;
    }

    /**
     * Get the value of noAvailableFiltersText
     */
    public function getNoAvailableFiltersText()
    {
        return $this->noAvailableFiltersText;
    }

    /**
     * Get the value of combinationOperation
     */
    public function getCombinationOperation()
    {
        return $this->combinationOperation;
    }

    /**
     * Get the value of values
     */
    public function getValues()
    {
        return $this->values;
    }
}
