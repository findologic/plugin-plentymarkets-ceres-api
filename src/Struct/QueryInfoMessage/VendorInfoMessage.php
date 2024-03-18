<?php

namespace Findologic\Struct\QueryInfoMessage;

class VendorInfoMessage extends QueryInfoMessage
{
    public string $filterName;
    public string $filterValue;

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
