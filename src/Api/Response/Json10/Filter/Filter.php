<?php

declare(strict_types=1);

namespace Findologic\Api\Response\Json10\Filter;

use InvalidArgumentException;
use Findologic\Api\Response\Filter\BaseFilter;
use Findologic\Api\Response\Result\Filter as ResultFilter;
use Findologic\Api\Response\Json10\Filter\Values\FilterValue;
use Findologic\Api\Response\Json10\Filter\Values\ColorFilterValue;
use Findologic\Api\Response\Json10\Filter\Values\ImageFilterValue;
use Findologic\Api\Response\Json10\Filter\Values\CategoryFilterValue;
use Findologic\Api\Response\Result\FilterValue as ResultFilterValue;

abstract class Filter extends BaseFilter
{
    private const FILTER_RANGE_MIN = 'min';
    private const FILTER_RANGE_MAX = 'max';

    /** @var FilterValue[] */
    protected array $values;

    /**
     * Builds a new filter instance. May return null for unsupported filter types. Throws an exception for unknown
     * filter types.
     */
    public static function getInstance(ResultFilter $filter): ?Filter
    {
        switch ($filter->getType()) {
            case 'labelFilter':
                if ($filter->getName() === BaseFilter::CAT_FILTER_NAME) {
                    return static::handleCategoryFilter($filter);
                }

                return static::handleLabelTextFilter($filter);
            case 'selectFilter':
                if ($filter->getName() === BaseFilter::CAT_FILTER_NAME) {
                    return static::handleCategoryFilter($filter);
                }

                return static::handleSelectDropdownFilter($filter);
            case 'rangeSliderFilter':
                if ($filter->getName() === BaseFilter::RATING_FILTER_NAME) {
                    return static::handleRatingFilter($filter);
                }

                return static::handleRangeSliderFilter($filter);
            case 'colorPickerFilter':
                return static::handleColorPickerFilter($filter);
            case 'vendorImageFilter':
                return static::handleVendorImageFilter($filter);
            default:
                throw new InvalidArgumentException('The submitted filter is unknown.');
        }
    }

    public function addValue(FilterValue $filterValue): self
    {
        $this->values[] = $filterValue;

        return $this;
    }

    public function searchValue(string $needle): ?FilterValue
    {
        foreach ($this->values as $value) {
            if ($value->getName() === $needle) {
                return $value;
            }
        }

        return null;
    }

    private static function handleLabelTextFilter(ResultFilter $filter): LabelTextFilter
    {
        $customFilter = new LabelTextFilter($filter->getName(), $filter->getDisplayName());

        foreach ($filter->getValues() as $item) {
            $customFilter->addValue(new FilterValue($item->getName(), $item->getName(), $filter->getName()));
        }

        return $customFilter;
    }

    private static function handleSelectDropdownFilter(ResultFilter $filter): SelectDropdownFilter
    {
        $customFilter = new SelectDropdownFilter($filter->getName(), $filter->getDisplayName());

        foreach ($filter->getValues() as $item) {
            $customFilter->addValue(new FilterValue($item->getName(), $item->getName(), $filter->getName()));
        }

        return $customFilter;
    }

    private static function handleRangeSliderFilter(ResultFilter $filter): RangeSliderFilter
    {
        $customFilter = new RangeSliderFilter($filter->getName(), $filter->getDisplayName());
        $unit = $filter->getUnit();
        $step = $filter->getStepSize();

        if ($unit !== null) {
            $customFilter->setUnit($unit);
        }

        if ($step !== null) {
            $customFilter->setStep($step);
        }

        if ($filter->getTotalRange()) {
            $customFilter->setTotalRange([
                self::FILTER_RANGE_MIN => $filter->getTotalRange()['min'],
                self::FILTER_RANGE_MAX => $filter->getTotalRange()['max'],
            ]);
        }

        if ($filter->getSelectedRange()) {
            $customFilter->setSelectedRange([
                self::FILTER_RANGE_MIN => $filter->getSelectedRange()['min'],
                self::FILTER_RANGE_MAX => $filter->getSelectedRange()['max'],
            ]);
        }

        foreach ($filter->getValues() as $item) {
            $customFilter->addValue(new FilterValue($item->getName(), $item->getName(), $filter->getName()));
        }

        if ($filter->getTotalRange()['min'] && $filter->getTotalRange()['max']) {
            $customFilter->setMin($filter->getTotalRange()['min']);
            $customFilter->setMax($filter->getTotalRange()['max']);
        } else {
            /** @var ApiRangeSliderValue[] $filterItems */
            $filterItems = array_values($filter->getValues());

            $firstFilterItem = current($filterItems);
            if ($firstFilterItem?->getMin()) {
                $customFilter->setMin($firstFilterItem->getMin());
            }

            $lastFilterItem = end($filterItems);
            if ($lastFilterItem?->getMax()) {
                $customFilter->setMax($lastFilterItem->getMax());
            }
        }

        return $customFilter;
    }

    private static function handleColorPickerFilter(ResultFilter $filter): ColorPickerFilter
    {
        $customFilter = new ColorPickerFilter($filter->getName(), $filter->getDisplayName());

        /** @var ResultFilterValue $item */
        foreach ($filter->getValues() as $item) {
            $imageUrls[$item->getName()] = $item->getImage();

            $filterValue = new ColorFilterValue($item->getName(), $item->getName(), $filter->getName());
            $filterValue->setColorHexCode($item->getColor());

            self::setColorPickerDisplayType($item, $filterValue);

            $media = new Media($item->getImage());
            $filterValue->setMedia($media);

            $customFilter->addValue($filterValue);
        }

        return $customFilter;
    }

    private static function handleVendorImageFilter(ResultFilter $filter): VendorImageFilter
    {
        $customFilter = new VendorImageFilter($filter->getName(), $filter->getDisplayName());

        /** @var ApiImageFilterValue $item */
        foreach ($filter->getValues() as $item) {
            $imageUrls[$item->getName()] = $item->getImage();
            $filterValue = new ImageFilterValue($item->getName(), $item->getName(), $filter->getName());
            $media = new Media($item->getImage());
            $filterValue->setMedia($media);
            $customFilter->addValue($filterValue);
            $filterValue->setDisplayType('media');
        }

        return $customFilter;
    }

    private static function handleCategoryFilter(ResultFilter $filter): CategoryFilter
    {
        $categoryFilter = new CategoryFilter($filter->getName(), $filter->getDisplayName());

        foreach ($filter->getValues() as $item) {
            $levels = explode('_', $item->getName());
            $currentValue = $categoryFilter;

            foreach ($levels as $level) {
                if (!$foundValue = $currentValue->searchValue($level)) {
                    $foundValue = new CategoryFilterValue($level, $level);
                    $foundValue->setSelected($item->isSelected());
                    $foundValue->setFrequency($item->getFrequency());

                    $currentValue->addValue($foundValue);
                }

                $currentValue = $foundValue;
            }
        }

        return $categoryFilter;
    }

    private static function handleRatingFilter(ResultFilter $filter): ?RatingFilter
    {
        $totalRange = $filter->getTotalRange();
        if ($totalRange['min'] === $totalRange['max']) {
            return null;
        }

        $customFilter = new RatingFilter($filter->getName(), $filter->getDisplayName());

        if ($totalRange['max']) {
            $customFilter->setMaxPoints(ceil($totalRange['max']));
        }

        /** @var ApiRangeSliderValue $item */
        foreach ($filter->getValues() as $item) {
            $customFilter->addValue(new FilterValue($item->getName(), $item->getName()));
        }

        return $customFilter;
    }

    private static function setColorPickerDisplayType(ResultFilterValue $item, ColorFilterValue $filterValue): void
    {
        if ($item->getImage() && trim($item->getImage()) !== '') {
            $filterValue->setDisplayType('media');
        } elseif ($item->getColor() && trim($item->getColor()) !== '') {
            $filterValue->setDisplayType('color');
        } else {
            $filterValue->setDisplayType('none');
        }
    }
}
