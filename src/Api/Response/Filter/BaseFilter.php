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

    /**
     * @param FilterValue[] $values
     */
    public function __construct(
        string $id,
        string $name,
        bool $isMain = false
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->isMain = $isMain;
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
}
