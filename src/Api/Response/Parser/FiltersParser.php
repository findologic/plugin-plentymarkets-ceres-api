<?php

namespace Findologic\Api\Response\Parser;

use SimpleXMLElement;
use Findologic\Constants\Plugin;
use Findologic\Api\Services\Image;
use Plenty\Plugin\ConfigRepository;
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
     * @var LibraryCallContract
     */
    protected $libraryCallContract;

    /** @var ConfigRepository */
    private $configRepository;

    /**
     * FiltersParser constructor.
     * @param LibraryCallContract $libraryCallContract
     */
    public function __construct(LibraryCallContract $libraryCallContract, ConfigRepository $configRepository)
    {
        $this->libraryCallContract = $libraryCallContract;
        $this->configRepository = $configRepository;
    }

    /**
     * @param object|null $data
     */
    public function parse(?object $filters): array
    {
        if (!$filters) {
            return [];
        }

        $mapped = [];

        if ($filters->main) {
            foreach ($filters->main as $filter) {
                $filters[] = $this->parseFilter($filter, true);
            }
        }

        if ($filters->other) {
            foreach ($filters->other as $filter) {
                $filters[] = $this->parseFilter($filter);
            }
        }

        return $mapped;
    }

    /**
     * @param object|null $data
     */
    public function parseForWidgets($data): array
    {
        // if (!$data instanceof SimpleXMLElement) {
        //     return [];
        // }

        $filters = $this->parse($data);

        if (empty($filters)) {
            return [];
        }

        $parsedFilters = [];

        foreach ($filters as $filter) {
            if (isset($filter['values']) && $filter['values']) {
                switch ($filter['id']) {
                    case 'vendor':
                        $filter['type'] = 'producer';
                        break;
                    case 'cat':
                        $filter['type'] = 'category';
                        break;
                    case 'price':
                        $filter['type'] = 'price';
                        break;
                    default:
                        $filter['type'] = 'dynamic';
                        break;
                }
            }

            $parsedFilters[] = $filter;
        }

        return $parsedFilters;
    }

    /**
     * @param string $filterType
     * @param array $filterItem
     * @param object $data
     * @param int $index
     * @return void
     */
    public function parseFilterItem($filterType, &$filterItem, $data, $index)
    {
        if (!empty($data)) {
            $filterItem['items'] = [];
            $filterItem['name'] = $data->value;
            $filterItem['position'] = $index;
            $filterItem['count'] = $data->frequency;
            $filterItem['id'] = ++$this->valueId;
            $filterItem['selected'] = false;

            if ($filterType === 'price') {
                $filterItem['priceMin'] = $data->parameters->min;
                $filterItem['priceMax'] = $data->parameters->max;
            }

            if ($data->selected) {
                $filterItem['selected'] = true;
            }

            if ($filterType === Plugin::FILTER_TYPE_IMAGE) {
                if (isset($data->image)) {
                    $filterItem['imageUrl'] = $data->image;
                }
            }

            if ($filterType === Plugin::FILTER_TYPE_COLOR) {
                if (isset($data->image)) {
                    $filterItem['colorImageUrl'] = $data->image;
                }

                $filterItem['hexValue'] = $data->color;
            }

            if (!empty($data->values)) {
                foreach ($data->values as $key => $item) {
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
     * @param object $filter
     * @param bool $isMainFilter
     * @return array
     */
    protected function parseFilter(object $filter, $isMainFilter = false): array
    {
        $noAvailableFiltersText = $filter->noAvailableFiltersText ?: '';

        $filterName = $filter->name;
        $filterData = [
            'id' => $filterName,
            'name' => $filter->displayName,
            'select' => $filter->selectMode,
            'type' => '',
            'findologicFilterType' => '',
            'isMain' => $isMainFilter,
            'values' => [],
            'itemCount' => count($filter->values),
            'noAvailableFiltersText' => $noAvailableFiltersText
        ];

        $filterData['cssClass'] = $filter->cssClass ?: '';

        if ($filter->type) {
            $filterData['findologicFilterType'] = $filter->type;
        }

        if ($filterName === 'price' && $filterData['findologicFilterType'] !== Plugin::FILTER_TYPE_RANGE_SLIDER) {
            $filterData['findologicFilterType'] = 'price';
        }

        if ($filterData['findologicFilterType'] === Plugin::FILTER_TYPE_RANGE_SLIDER) {
            $filterData['unit'] = $filter->unit;
            $filterData['minValue'] = (float)$filter->totalRange->totalRange->min;
            $filterData['maxValue'] = (float)$filter->totalRange->totalRange->max;
            $filterData['step'] = $filter->stepSize ?: (float) $this->configRepository->get('Findologic.price_range_filter_step_size', '0.01');
            $filterData['useNoUISliderCSS'] = (bool) $this->configRepository->get('Findologic.load_no_ui_slider_styles_enabled', '1');
        }

        foreach ($filter->values as $key => $item) {
            $filterItem = [];
            $this->parseFilterItem($filterData['findologicFilterType'], $filterItem, $item, $key);
            if (!empty($filterItem)) {
                $filterData['values'][] = $filterItem;
            }
        }

        return $filterData;
    }
}
