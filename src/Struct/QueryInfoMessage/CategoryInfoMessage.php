<?php

namespace Findologic\Struct\QueryInfoMessage;

class CategoryInfoMessage extends QueryInfoMessage
{
    protected string $filterName;
    protected string $filterValue;

    public function __construct(
        string $filterName,
        string $filterValue
    ) {
        $this->filterName = $filterName;
        $this->filterValue = $filterValue;
    }

    public function getFilterName(): string
    {
        return $this->filterName;
    }

    public function getFilterValue(): string
    {
        return $this->filterValue;
    }
}
