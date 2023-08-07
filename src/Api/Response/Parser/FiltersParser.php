<?php

namespace Findologic\Api\Response\Parser;

use FINDOLOGIC\Api\Responses\Json10\Json10Response;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Filter;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\RangeSliderFilter;
use Findologic\Constants\Plugin;
use Findologic\Api\Services\Image;
use Plenty\Plugin\ConfigRepository;

/**
 * Class FiltersParser
 * @package Findologic\Api\Response\Parser
 */
class FiltersParser
{
    /** @var int */
    protected $valueId;

    /** @var Json10Response|null */
    private $response;

    /** @var ConfigRepository */
    private $configRepository;

    /**
     * FiltersParser constructor.
     * @param Json10Response $response
     */
    public function __construct(ConfigRepository $configRepository, ?Json10Response $response)
    {
        $this->configRepository = $configRepository;
        $this->response = $response;
    }

    /**
     * @return array
     */
    public function parse(): array
    {
        if (!$this->response) {
            return [];
        }

        $filters = array_merge(
            $this->response->getResult()->getMainFilters() ?? [],
            $this->response->getResult()->getOtherFilters() ?? []
        );
        
        foreach ($this->response->getResult()->getMainFilters() as $filter) {
            $filters[] = $this->parseFilter($filter, true);
        }

        foreach ($this->response->getResult()->getOtherFilters() as $filter) {
            $filters[] = $this->parseFilter($filter);
        }

        return $filters;
    }

    /**
     * @return array
     */
    public function parseForWidgets(): array
    {
        if (!$this->response) {
            return [];
        }

        $filters = $this->parse();

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
     * @param int $index
     * @return void
     */
    public function parseFilterItem($filterType, &$filterItem, $index)
    {
        if (!empty($data)) {
            $filterItem['items'] = [];
            $filterItem['name'] = $data->name->__toString();
            $filterItem['position'] = $index;
            $filterItem['count'] = $data->frequency->__toString();
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
                    $filterItem['imageUrl'] = $data->image->__toString();
                }
            }

            if ($filterType === Plugin::FILTER_TYPE_COLOR) {
                if (isset($data->image) && $data->image->__toString() !== '') {
                    $filterItem['colorImageUrl'] = $data->image->__toString();
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
     * @param Filter|RangeSliderFilter $filter
     * @param bool $isMainFilter
     * @return array
     */
    protected function parseFilter(Filter $filter, bool $isMainFilter = false)
    {
        $filterName = $filter->getName();
        $filterData = [
            'id' => $filterName,
            'name' => $filter->getDisplayName(),
            'select' => $filter->getSelectMode(),
            'type' => '',
            'findologicFilterType' => '',
            'isMain' => $isMainFilter,
            'values' => [],
            'itemCount' => $filter->itemCount ? $filter->itemCount->__toString() : 0,
            'noAvailableFiltersText' => $filter->getNoAvailableFiltersText() ?: '',
            'cssClass' => $filter->getCssClass() ?: ''
        ];

        if ($filter->type) {
            $filterData['findologicFilterType'] = $filter->type->__toString();
        }

        if ($filterName === 'price' && $filterData['findologicFilterType'] !== Plugin::FILTER_TYPE_RANGE_SLIDER) {
            $filterData['findologicFilterType'] = 'price';
        }

        if ($filterData['findologicFilterType'] === Plugin::FILTER_TYPE_RANGE_SLIDER) {
            $filterData['unit'] = $filter->getUnit();
            $filterData['minValue'] = (float)$filter->getTotalRange()->getMin();
            $filterData['maxValue'] = (float)$filter->getTotalRange()->getMax();
            $filterData['step'] = (float) $this->configRepository->get('Findologic.price_range_filter_step_size', '0.01');
            $filterData['useNoUISliderCSS'] = (bool) $this->configRepository->get('Findologic.load_no_ui_slider_styles_enabled', '1');
        }

        foreach ($filter->items->item as $key => $item) {
            $filterItem = [];
            $this->parseFilterItem($filterData['findologicFilterType'], $filterItem, $item, $key);
            if (!empty($filterItem)) {
                $filterData['values'][] = $filterItem;
            }
        }

        return $filterData;
    }
}
