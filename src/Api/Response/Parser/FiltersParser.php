<?php

namespace Findologic\PluginPlentymarketsApi\Api\Response\Parser;

/**
 * Class FiltersParser
 * @package Findologic\PluginPlentymarketsApi\Api\Response\Parser
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
                        $filterData['items'][] = $filterItem;
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
            $filterItem['name'] = $data->name->__toString();
            $filterItem['weight'] = $data->weight->__toString();
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