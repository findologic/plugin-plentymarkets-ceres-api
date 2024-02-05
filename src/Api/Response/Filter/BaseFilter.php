<?php

namespace Findologic\Api\Response\Filter;

abstract class BaseFilter
{
    public const RATING_FILTER_NAME = 'rating';
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
    /**
     * @param FilterValue[] $values
     */
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

    /**
     * Set the value of isMain
     *
     * @return  self
     */ 
    public function setIsMain($isMain)
    {
        $this->isMain = $isMain;

        return $this;
    }

    /**
     * Set the value of selectMode
     *
     * @return  self
     */ 
    public function setSelectMode($selectMode)
    {
        $this->selectMode = $selectMode;

        return $this;
    }

    /**
     * Set the value of findologicFilterType
     *
     * @return  self
     */ 
    public function setFindologicFilterType($findologicFilterType)
    {
        $this->findologicFilterType = $findologicFilterType;

        return $this;
    }

    /**
     * Set the value of noAvailableFiltersText
     *
     * @return  self
     */ 
    public function setNoAvailableFiltersText($noAvailableFiltersText)
    {
        $this->noAvailableFiltersText = $noAvailableFiltersText;

        return $this;
    }

    /**
     * Set the value of combinationOperation
     *
     * @return  self
     */ 
    public function setCombinationOperation($combinationOperation)
    {
        $this->combinationOperation = $combinationOperation;

        return $this;
    }

        /**
         * Set the value of cssClass
         *
         * @return  self
         */ 
        public function setCssClass($cssClass)
        {
                $this->cssClass = $cssClass;

                return $this;
        }
}
