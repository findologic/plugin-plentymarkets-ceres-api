<?php

namespace Findologic\Api\Response\Result;

class FilterValue
{
    protected ?string $name;

    protected ?bool $selected;

    protected ?float $weight;

    protected ?int $frequency;

    protected ?float $min;

    protected ?float $max;

    protected ?string $color;

    protected ?string $image;

    function __construct(array $filterValue = [])
    {
        $this->name = $filterValue['name'];
        $this->selected = $filterValue['selected'];
        $this->weight = $filterValue['weight'];
        $this->frequency = $filterValue['frequency'];
        $this->min = $filterValue['min'];
        $this->max = $filterValue['max'];
        $this->color = $filterValue['color'];
        $this->image = $filterValue['image'];
    }


    /**
     * Get the value of name
     */
    public function getName():string
    {
        return $this->name;
    }

    /**
     * Get the value of selected
     */
    public function isSelected():bool
    {
        return $this->selected;
    }

    /**
     * Get the value of weight
     */
    public function getWeight():float
    {
        return $this->weight;
    }

    /**
     * Get the value of frequency
     */
    public function getFrequency():?int
    {
        return $this->frequency;
    }

    /**
     * Get the value of min
     */
    public function getMin():?float
    {
        return $this->min;
    }

    /**
     * Get the value of max
     */
    public function getMax():?float
    {
        return $this->max;
    }

    /**
     * Get the value of color
     */ 
    public function getColor():?string
    {
        return $this->color;
    }

    /**
     * Get the value of image
     */ 
    public function getImage():?string
    {
        return $this->image;
    }
}
