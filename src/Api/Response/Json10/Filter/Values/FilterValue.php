<?php

declare(strict_types=1);

namespace Findologic\Api\Response\Json10\Filter\Values;

use Findologic\Api\Response\Json10\Filter\TranslatedName;
use Findologic\Api\Response\Result\Filter as ResultFilter;
use Findologic\Api\Response\Result\FilterValue as ResultFilterValue;

class FilterValue
{
    public const DELIMITER = '>';
    public ?string $uuid;
    public TranslatedName $translated;
    public string $id;
    public ?int $frequency;
    public ?bool $selected;
    public ?float $weight;

    public function __construct(
        ?ResultFilter $filter,
        ResultFilterValue $filterValue
    ) {
        $this->translated = pluginApp(TranslatedName::class, [$filterValue->getName()]);
        $this->frequency = $filterValue->getFrequency();
        $this->selected = $filterValue->isSelected();
        $this->weight = $filterValue->getWeight();
        $this->id = $filterValue->getId();
        
        if($filter){
            $filterName = $filter->getName();
        }else{
            $filterName = $filterValue->getName();
        }
        if ($filterName !== null) {
            $this->uuid = sprintf('%s%s', $filterName, self::DELIMITER);
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTranslated(): TranslatedName
    {
        return $this->translated;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }
    public function getFrequency()
    {
            return $this->frequency;
    }

    /**
     * Set the value of translated
     *
     * @return  self
     */ 
    public function setTranslated($translated)
    {
        $this->translated = $translated;

        return $this;
    }

    /**
     * Set the value of uuid
     *
     * @return  self
     */ 
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }
}
