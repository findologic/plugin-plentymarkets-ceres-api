<?php

namespace Findologic\Struct;

use Findologic\Api\Response\Filter\BaseFilter;


class FiltersExtension
{
    private array $filters = [];

    // /**
    //  * @param BaseFilter[] $filters
    //  */
    public function __construct(
        array $filters = []
    ) {
        $this->filters = $filters;
    }

    public function addFilter($filter): self
    {
        $this->filters[] = (array)$filter;

        return $this;
    }

    // /**
    //  * @return BaseFilter[]
    //  */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getFilter(string $id): ?BaseFilter
    {
        if (!isset($this->filters[$id])) {
            return null;
        }

        return $this->filters[$id];
    }
}
