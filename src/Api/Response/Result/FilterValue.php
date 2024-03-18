<?php

namespace Findologic\Api\Response\Result;

class FilterValue
{
    protected ?string $id;
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
        $this->id = $filterValue['name'];
        $this->name = $filterValue['name'];
        $this->selected = $filterValue['selected'];
        $this->weight = $filterValue['weight'];
        $this->frequency = $filterValue['frequency'];
        $this->min = $filterValue['min'];
        $this->max = $filterValue['max'];
        $this->color = $filterValue['color'];
        $this->image = $filterValue['image'];
    }

    public function getName():?string
    {
        return $this->name;
    }

    public function isSelected():?bool
    {
        return $this->selected;
    }
    public function getWeight():?float
    {
        return $this->weight;
    }
    public function getFrequency():?int
    {
        return $this->frequency;
    }
    public function getMin():?float
    {
        return $this->min;
    }
    public function getMax():?float
    {
        return $this->max;
    }
    public function getColor():?string
    {
        return $this->color;
    }
    public function getImage():?string
    {
        return $this->image;
    }
    public function getId():string
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
