<?php

declare(strict_types=1);

namespace Findologic\Api\Response\Json10\Filter\Values;

use Findologic\Api\Response\Result\Filter as ResultFilter;
use Findologic\Api\Response\Result\FilterValue as ResultFilterValue;

class FilterValue
{
    public ?string $id;

    public ?int $frequency;

    public ?bool $selected;

    public ?float $weight;

    public ?string $name;

    public function __construct(
        ?ResultFilter $filter,
        ResultFilterValue $filterValue
    ) {
        $this->name = $filterValue->getName();
        $this->frequency = $filterValue->getFrequency();
        $this->selected = $filterValue->isSelected();
        $this->weight = $filterValue->getWeight();
        $this->id = $filterValue->getId();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFrequency(): ?int
    {
            return $this->frequency;
    }

    public function setTranslated($translated): self
    {
        $this->translated = $translated;

        return $this;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setFrequency($frequency): self
    {
        if ($frequency === null) {
            $frequency = 0;
        }

        $this->frequency = $frequency;

        return $this;
    }

    public function setSelected($selected): self
    {
        $this->selected = $selected;

        return $this;
    }

    public function setWeight($weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
