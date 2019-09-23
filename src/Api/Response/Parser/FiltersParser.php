<?php

namespace Findologic\Api\Response\Parser;

use Findologic\Constants\Plugin;
use Findologic\Api\Services\Image;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;

/**
 * Class FiltersParser
 * @package Findologic\Api\Response\Parser
 */
class FiltersParser
{
    /**
     * @var int
     */
    protected $valueId;

    /**
     * @var Image
     */
    protected $imageService;

    /**
     * @var LibraryCallContract
     */
    protected $libraryCallContract;

    /**
     * FiltersParser constructor.
     * @param LibraryCallContract $libraryCallContract
     * @param Image $colorImageService
     */
    public function __construct(LibraryCallContract $libraryCallContract, Image $colorImageService)
    {
        $this->libraryCallContract = $libraryCallContract;
        $this->imageService = $colorImageService;
    }

    /**
     * @param \SimpleXMLElement $data
     * @return array
     */
    public function parse($data)
    {
        if (!$data) {
            return [];
        }

        $filters = [];

        if ($data->main->filter) {
            foreach ($data->main->filter as $filter) {
                $filters[] = $this->parseFilter($filter, true);
            }
        }

        if ($data->other->filter) {
            foreach ($data->other->filter as $filter) {
                $filters[] = $this->parseFilter($filter);
            }
        }

        return $filters;
    }

    /**
     * @param string $filterType
     * @param array $filterItem
     * @param \SimpleXMLElement $data
     * @param int $index
     * @return void
     */
    public function parseFilterItem($filterType, &$filterItem, $data, $index)
    {
        if (!empty($data)) {
            $filterItem['items'] = [];
            $filterItem['name'] = $data->name->__toString();
            $filterItem['position'] = $index;
            $filterItem['count'] = $data->frequency->__toString();
            $filterItem['image'] = $data->image->__toString();
            $filterItem['id'] = ++$this->valueId;
            $filterItem['selected'] = false;

            if ($filterType === 'price') {
                $filterItem['priceMin'] = $data->parameters->min;
                $filterItem['priceMax'] = $data->parameters->max;
            }

            if (isset($data->attributes()->selected) && $data->attributes()->selected->__toString() === '1') {
                $filterItem['selected'] = true;
            }

            if ($filterType === Plugin::FILTER_TYPE_IMAGE) {
                if (isset($data->image) && $data->image->__toString() !== '' && $data->image->__toString()[0] !== '/') {
                    if ($this->imageService->isImageAccessible($data->image->__toString())) {
                        $filterItem['imageUrl'] = $data->image->__toString();
                    }
                }
            }

            if ($filterType === Plugin::FILTER_TYPE_COLOR) {
                if (isset($data->image) && $data->image->__toString() !== '') {
                    $filterItem['colorImageUrl'] = null;

                    if ($this->imageService->isImageAccessible($data->image->__toString())) {
                        $filterItem['colorImageUrl'] = $data->image->__toString();
                    }
                }

                $filterItem['hexValue'] = $data->color->__toString();
            }

            if (!empty($data->items)) {
                foreach ($data->items->item as $key => $item) {
                    $newItem = [];
                    $this->parseFilterItem($filterType, $newItem, $item, $key);
                    if ($newItem['selected']) {
                        $filterItem['selected'] = true;
                    }
                    $filterItem['items'][] = $newItem;
                }
            }
        }
    }

    /**
     * @param \SimpleXMLElement $filter
     * @param bool $isMainFilter
     * @return array
     */
    protected function parseFilter($filter, $isMainFilter = false)
    {
        $filterName = $filter->name->__toString();
        $filterData = [
            'id' => $filterName,
            'name' => $filter->display->__toString(),
            'select' => $filter->select->__toString(),
            'type' => '',
            'isMain' => $isMainFilter,
            'itemCount' => $filter->itemCount ? $filter->itemCount->__toString() : 0
        ];

        $filterData['cssClass'] = $filter->cssClass ? $filter->cssClass->__toString() : '';

        if ($filter->type) {
            $filterData['type'] = $filter->type->__toString();
        }

        if ($filterName === 'price' && $filterData['type'] !== Plugin::FILTER_TYPE_RANGE_SLIDER) {
            $filterData['type'] = 'price';
        }

        if ($filterData['type'] === Plugin::FILTER_TYPE_RANGE_SLIDER) {
            $filterData['unit'] = $filter->attributes->unit->__toString();
            $filterData['minValue'] = (float)$filter->attributes->totalRange->min;
            $filterData['maxValue'] = (float)$filter->attributes->totalRange->max;
            $filterData['step'] = (float)$filter->attributes->stepSize;
        }

        foreach ($filter->items->item as $key => $item) {
            $filterItem = [];
            $this->parseFilterItem($filterData['type'], $filterItem, $item, $key);
            if (!empty($filterItem)) {
                $filterData['values'][] = $filterItem;
            }
        }

        return $filterData;
    }
}