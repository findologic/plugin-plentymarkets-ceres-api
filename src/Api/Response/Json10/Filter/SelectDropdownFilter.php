<?php

declare(strict_types=1);

namespace Findologic\Api\Response\Json10\Filter;

use Findologic\Api\Response\Result\Filter as ResultFilter;

class SelectDropdownFilter extends Filter
{
    public int $pinnedFilterValueCount = 0;
    public function __construct(ResultFilter $filter, bool $isMain = false)
    {
        parent::__construct(
            $filter->getName(),
            $filter->getDisplayName(),
            $isMain, $filter->getSelectMode(),
            $filter->getCssClass(),
            $filter->getNoAvailableFiltersText(),
            $filter->getCombinationOperation(),
            $filter->getType()
        );
        $this->pinnedFilterValueCount = $filter->getPinnedFilterValueCount() ?: 0;
    }
}
