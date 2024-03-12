<?php

namespace Findologic\Api\Response\Filter;

use Findologic\Api\Response\Json10\Filter\Values\FilterValue;

abstract class BaseFilter
{
    public const CAT_FILTER_NAME = 'cat';

    public const VENDOR_FILTER_NAME = 'vendor';

    public ?string $displayType;

    public bool $hidden = false;

    public string $id;

    public string $name;

    /** @var FilterValue[] */
    public array $values = [];

    public bool $isMain;

    public ?string $selectMode;

    public ?string $cssClass;

    public ?string $noAvailableFiltersText;

    public ?string $combinationOperation;

    public ?string $findologicFilterType;

    public function __construct(
        ?string $id = '',
        ?string $name = '',
        bool $isMain = false,
        ?string $selectMode = null,
        ?string $cssClass = null,
        ?string $noAvailableFiltersText = null,
        ?string $combinationOperation = null,
        ?string $findologicFilterType = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->isMain = $isMain;
        $this->selectMode = $selectMode;
        $this->cssClass = $cssClass;
        $this->noAvailableFiltersText = $noAvailableFiltersText;
        $this->combinationOperation = $combinationOperation;
        $this->findologicFilterType = $findologicFilterType;
    }

    public function getDisplayType(): ?string
    {
        return $this->displayType;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return FilterValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function setIsMain($isMain)
    {
        $this->isMain = $isMain;

        return $this;
    }

    public function setSelectMode($selectMode)
    {
        $this->selectMode = $selectMode;

        return $this;
    }

    public function setFindologicFilterType($findologicFilterType)
    {
        $this->findologicFilterType = $findologicFilterType;

        return $this;
    }

    public function setNoAvailableFiltersText($noAvailableFiltersText)
    {
        $this->noAvailableFiltersText = $noAvailableFiltersText;

        return $this;
    }

    public function setCombinationOperation($combinationOperation)
    {
        $this->combinationOperation = $combinationOperation;

        return $this;
    }

    public function setCssClass($cssClass)
    {
            $this->cssClass = $cssClass;

            return $this;
    }
}
