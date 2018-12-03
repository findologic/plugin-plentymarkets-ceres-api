<?php

namespace Findologic\Api\Response\Parser;

/**
 * Class FiltersParser
 * @package Findologic\Api\Response\Parser
 */
class FiltersParser
{
    /**
     * @param \SimpleXMLElement $data
     * @return array
     */
    public function parse($data)
    {
        $filters = [];

        if (!empty($data->filters) ) {
            foreach ($data->filters->filter as $filter) {
                $filterData = [
                    'name' => $filter->name->__toString(),
                    'select' => $filter->select->__toString()
                ];

                if ($filter->type) {
                    $filterData['type'] = $filter->type->__toString();
                }

                foreach ($filter->items->item as $item) {
                    $filterItem = [];
                    $this->parseFilterItem($filterItem, $item);
                    if (!empty($filterItem)) {
                        $filterData['values'][] = $filterItem;
                    }
                }

                $filters[] = $filterData;
            }
        }

        return $filters;
    }

    /**
     * @param $filterItem
     * @param \SimpleXMLElement $data
     * @return array
     */
    public function parseFilterItem(&$filterItem, $data)
    {
        if (!empty($data)) {
            $filterItem['id'] = $data->name->__toString();
            $filterItem['name'] = $data->display->__toString();
            $filterItem['position'] = $data->weight->__toString();
            $filterItem['frequency'] = $data->frequency->__toString();
            $filterItem['image'] = $data->image->__toString();

            if (!empty($data->items)) {
                foreach ($data->items->item as $item) {
                    $newItem = [];
                    $this->parseFilterItem($newItem, $item);
                    $filterItem['items'][] = $newItem;
                }
            }
        }
    }
}