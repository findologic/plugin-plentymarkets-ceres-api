<?php

namespace Findologic\Api\Response\Filter;

abstract class BaseFilter
{
    public const RATING_FILTER_NAME = 'rating';
    public const CAT_FILTER_NAME = 'cat';
    public const VENDOR_FILTER_NAME = 'vendor';

    protected ?string $displayType;

    protected bool $hidden = false;

    protected string $id;
    protected string $name;
    protected array $values = [];

    /**
     * @param FilterValue[] $values
     */
    public function __construct(
        string $id,
        string $name,
        array $values = [],
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->values = $values;
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
