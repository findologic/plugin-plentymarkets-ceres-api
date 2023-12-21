<?php

require_once __DIR__ . '/Arrayable.php';


use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Filter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\ColorFilter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\ImageFilter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\LabelFilter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\SelectFilter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\RangeSliderFilter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Values\FilterValue;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Values\ColorFilterValue;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Values\ImageFilterValue;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Values\RangeSliderValue;

class ApiFilter implements Arrayable
{
    public const RATING_FILTER_NAME = 'rating';
    public const CAT_FILTER_NAME = 'cat';
    public const VENDOR_FILTER_NAME = 'vendor';

    public function __construct(private Filter|RangeSliderFilter $filter)
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
            'stepSize' => method_exists($this->filter, 'getStepSize') ? $this->filter->getStepSize() : null,
            'unit' => method_exists($this->filter, 'getUnit') ? $this->filter->getUnit() : null,
            'values' => array_map(fn (FilterValue|ImageFilterValue|ColorFilterValue|RangeSliderValue $value) => [
                'name' => $value->getName(),
                'selected' => $value->isSelected(),
                'weight' => $value->getWeight(),
                'frequency' => $value->getFrequency(),
                'image' => method_exists($value, 'getImage') ? $value->getImage() : null,
                'color' => method_exists($value, 'getColor') ? $value->getColor() : null,
                'min' => method_exists($value, 'getMin') ? $value->getMin() : null,
                'max' => method_exists($value, 'getMax') ? $value->getMax() : null
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

                $totalRange = $filter->getTotalRange();
                $selectedRange = $filter->getSelectedRange();
                return [
                    'type' => 'rangeSliderFilter',
                    'totalRange' =>[
                        'min' => $totalRange->getMin(),
                        'max' => $totalRange->getMax()
                    ],
                    'selectedRange' =>[
                        'min' => $selectedRange->getMin(),
                        'max' => $selectedRange->getMax()
                    ]
                ];
            case $filter instanceof ColorFilter:
                return ['type' => 'colorPickerFilter'];
            case $filter instanceof ImageFilter:
                return ['type' => 'vendorImageFilter'];
            default:
                throw new \Exception('The submitted filter is unknown.');
        }
    }
}
