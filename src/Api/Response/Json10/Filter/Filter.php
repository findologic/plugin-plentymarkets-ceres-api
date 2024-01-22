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

    /**
     * Builds a new filter instance. May return null for unsupported filter types. Throws an exception for unknown
     * filter types.
     */
    public static function getInstance(ResultFilter $filter, bool $isMain): ?Filter
    {
        switch ($filter->getType()) {
            case 'labelFilter':
                if ($filter->getName() === BaseFilter::CAT_FILTER_NAME) {
                    return static::handleCategoryFilter($filter, $isMain);
                }

                return static::handleLabelTextFilter($filter, $isMain);
            case 'selectFilter':
                if ($filter->getName() === BaseFilter::CAT_FILTER_NAME) {
                    return static::handleCategoryFilter($filter, $isMain);
                }

                return static::handleSelectDropdownFilter($filter, $isMain);
            case 'rangeSliderFilter':
                if ($filter->getName() === BaseFilter::RATING_FILTER_NAME) {
                    return static::handleRatingFilter($filter, $isMain);
                }

                return static::handleRangeSliderFilter($filter, $isMain);
            case 'colorPickerFilter':
                return static::handleColorPickerFilter($filter, $isMain);
            case 'vendorImageFilter':
                return static::handleVendorImageFilter($filter, $isMain);
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
            if ($value->getTranslated()->getName() === $needle) {
                return $value;
            }
        }

        return null;
    }

    private static function handleLabelTextFilter(ResultFilter $filter, bool $isMain): LabelTextFilter
    {
        $customFilter = pluginApp(LabelTextFilter::class, [$filter->getName(), $filter->getDisplayName(), $isMain, $filter->getSelectMode(), $filter->getCssClass(), $filter->getNoAvailableFiltersText(), $filter->getCombinationOperation(), $filter->getType()]);

        foreach ($filter->getValues() as $item) {
            $customFilter->addValue(pluginApp(FilterValue::class, [$filter, $item]));
        }

        return $customFilter;
    }

    private static function handleSelectDropdownFilter(ResultFilter $filter, bool $isMain): SelectDropdownFilter
    {
        $customFilter = pluginApp(SelectDropdownFilter::class, [$filter->getName(), $filter->getDisplayName(), $isMain, $filter->getSelectMode(), $filter->getCssClass(), $filter->getNoAvailableFiltersText(), $filter->getCombinationOperation(), $filter->getType()]);

        foreach ($filter->getValues() as $item) {
            $customFilter->addValue(pluginApp(FilterValue::class, [$filter, $item]));
        }

        return $customFilter;
    }

    private static function handleRangeSliderFilter(ResultFilter $filter, bool $isMain): RangeSliderFilter
    {
        $customFilter = pluginApp(RangeSliderFilter::class, [$filter, $isMain]);
        $unit = $filter->getUnit();
        $step = $filter->getStepSize();

        if ($unit !== null) {
            $customFilter->setUnit($unit);
        }

        if ($step !== null) {
            $customFilter->setStep($step);
        }
        else{
            $customFilter->setStep(0.01);
        }

        if ($filter->getSelectedRange()) {
            $customFilter->setSelectedRange([
                self::FILTER_RANGE_MIN => $filter->getSelectedRange()['min'],
                self::FILTER_RANGE_MAX => $filter->getSelectedRange()['max'],
            ]);
        }

        foreach ($filter->getValues() as $item) {
            $customFilter->addValue(pluginApp(FilterValue::class, [$filter, $item]));
        }

        if ($filter->getTotalRange()['min'] && $filter->getTotalRange()['max']) {
            $customFilter->setMin($filter->getTotalRange()['min']);
            $customFilter->setMax($filter->getTotalRange()['max']);
        } else {
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

    private static function handleColorPickerFilter(ResultFilter $filter, bool $isMain): ColorPickerFilter
    {
        $customFilter = pluginApp(ColorPickerFilter::class, [$filter->getName(), $filter->getDisplayName(), $isMain, $filter->getSelectMode(), $filter->getCssClass(), $filter->getNoAvailableFiltersText(), $filter->getCombinationOperation(), $filter->getType()]);

        /** @var ResultFilterValue $item */
        foreach ($filter->getValues() as $item) {
            $imageUrls[$item->getName()] = $item->getImage();

            $filterValue = pluginApp(ColorFilterValue::class, [null, $item]);
            $filterValue->setColorHexCode($item->getColor());

            self::setColorPickerDisplayType($item, $filterValue);

            $media = pluginApp(Media::class, [$item->getImage()]);
            $filterValue->setMedia($media);

            $customFilter->addValue($filterValue);
        }

        return $customFilter;
    }

    private static function handleVendorImageFilter(ResultFilter $filter, bool $isMain): VendorImageFilter
    {
        $customFilter = pluginApp(VendorImageFilter::class, [$filter->getName(), $filter->getDisplayName(), $isMain, $filter->getSelectMode(), $filter->getCssClass(), $filter->getNoAvailableFiltersText(), $filter->getCombinationOperation(), $filter->getType()]);

        foreach ($filter->getValues() as $item) {
            $imageUrls[$item->getName()] = $item->getImage();
            
            $filterValue = pluginApp(ImageFilterValue::class, [null, $item]);
            $media = pluginApp(Media::class, [$item->getImage()]);
            $filterValue->setMedia($media);
            $customFilter->addValue($filterValue);
            $filterValue->setDisplayType('media');
        }

        return $customFilter;
    }

    private static function handleCategoryFilter(ResultFilter $filter, bool $isMain): CategoryFilter
    {
        $categoryFilter = pluginApp(CategoryFilter::class, [$filter->getName(), $filter->getDisplayName(), $isMain, $filter->getSelectMode(), $filter->getCssClass(), $filter->getNoAvailableFiltersText(), $filter->getCombinationOperation(), $filter->getType()]);

        foreach ($filter->getValues() as $item) {
            if (!$item->getName()) {
                continue;
            }
            $levels = explode('_', $item->getName());
            $currentValue = $categoryFilter;

            foreach ($levels as $level) {
                if (!$foundValue = $currentValue->searchValue($level)) {
                    $foundValue = pluginApp(CategoryFilterValue::class, [null, $item]);
                    $foundValue->setTranslated(pluginApp(TranslatedName::class, [$level]));
                    $foundValue->setUuid(sprintf('%s%s', $level, FilterValue::DELIMITER));
                    $foundValue->setSelected($item->isSelected());
                    $foundValue->setFrequency($item->getFrequency());

                    $currentValue->addValue($foundValue);
                }

                $currentValue = $foundValue;
            }
        }

        return $categoryFilter;
    }

    private static function handleRatingFilter(ResultFilter $filter, bool $isMain): ?RatingFilter
    {
        $totalRange = $filter->getTotalRange();
        if ($totalRange && $totalRange['min'] === $totalRange['max']) {
            return null;
        }

        $customFilter = pluginApp(RatingFilter::class, [$filter->getName(), $filter->getDisplayName(), $isMain, $filter->getSelectMode(), $filter->getCssClass(), $filter->getNoAvailableFiltersText(), $filter->getCombinationOperation(), $filter->getType()]);

        if ($totalRange && $totalRange['max']) {
            $customFilter->setMaxPoints(ceil($totalRange['max']));
        }

        foreach ($filter->getValues() as $item) {
            $customFilter->addValue(pluginApp(FilterValue::class, [null, $item]));
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
