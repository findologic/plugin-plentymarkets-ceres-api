<?php

use Illuminate\Contracts\Support\Arrayable;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Filter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\RangeSliderFilter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Values\FilterValue;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\ColorFilter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\ImageFilter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\LabelFilter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\SelectFilter;

class ApiFilter implements Arrayable
{
    public const RATING_FILTER_NAME = 'rating';
    public const CAT_FILTER_NAME = 'cat';
    public const VENDOR_FILTER_NAME = 'vendor';

    public function __construct(private Filter $filter)
    {
    }

    public function toArray()
    {
        $filterType = $this->getFilterExtras($this->filter);
        return array_merge($filterType, [
            'name' => $this->filter->getName(),
            'displayName' => $this->filter->getDisplayName(),
            'selectMode' => $this->filter->getSelectMode(),
            'cssClass' => $this->filter->getCssClass(),
            'noAvailableFiltersText' => $this->filter->getNoAvailableFiltersText(),
            'combinationOperation' => $this->filter->getCombinationOperation(),
            'values' => array_map(fn (FilterValue $value) => [
                'name' => $value->getName(),
                'selected' => $value->isSelected(),
                'weight' => $value->getWeight(),
                'frequency' => $value->getFrequency()
            ], $this->filter->getValues()),
        ]);
    }

    private function getFilterExtras(Filter|RangeSliderFilter $filter): array
    {
        switch (true) {
            case $filter instanceof LabelFilter:
                if ($filter->getName() === self::CAT_FILTER_NAME) {
                    return ['type' => 'labelFilter'];
                }

                return ['type' => 'labelFilter'];
            case $filter instanceof SelectFilter:
                if ($filter->getName() === self::CAT_FILTER_NAME) {
                    return ['type' => 'selectFilter'];
                }

                return ['type' => 'selectFilter'];
            case $filter instanceof RangeSliderFilter:
                if ($filter->getName() === self::RATING_FILTER_NAME) {
                    return ['type' => 'rangeSliderFilter'];
                }

                return ['type' => 'rangeSliderFilter', 'totalRange' => (array)$filter->getTotalRange(), 'selectedRange' => (array)$filter->getSelectedRange()];
            case $filter instanceof ColorFilter:
                return ['type' => 'colorPickerFilter'];
            case $filter instanceof ImageFilter:
                return ['type' => 'vendorImageFilter'];
            default:
                throw new \Exception('The submitted filter is unknown.');
        }
    }
}
