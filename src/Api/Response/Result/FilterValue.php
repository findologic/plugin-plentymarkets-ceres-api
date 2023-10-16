<?php

namespace Findologic\Api\Response\Result;

class FilterValue
{
    protected string $name;

    protected bool $selected;

    protected float $weight;

    protected ?int $frequency;

    function __construct(array $filterValue)
    {
        $this->name = $filterValue['name'];
        $this->selected = $filterValue['selected'];
        $this->weight = $filterValue['weight'];
        $this->frequency = $filterValue['frequency'];
    }


    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of selected
     */ 
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * Get the value of weight
     */ 
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Get the value of frequency
     */ 
    public function getFrequency()
    {
        return $this->frequency;
    }
}
