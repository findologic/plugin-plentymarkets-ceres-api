<?php

require_once __DIR__ . '/Arrayable.php';

use FINDOLOGIC\Api\Responses\Json10\Properties\Item;
use FINDOLOGIC\Api\Responses\Json10\Properties\BaseItem;
use FINDOLOGIC\Api\Responses\Json10\Properties\ItemVariant;

/**
 * Holds data of an item/product.
 */
class ApiItem implements Arrayable
{
    public function __construct(private Item $item)
    {
    }

    public function toArray()
    {
        $baseItem = $this->getBaseItemProperties($this->item);
        return array_merge($baseItem, [
            'highlightedName' => $this->item->getHighlightedName(),
            'productPlacement' => $this->item->getProductPlacement(),
            'pushRules' => $this->item->getPushRules(),
            'variants' => array_map(fn (ItemVariant $variant) => $this->getBaseItemProperties($variant), $this->item->getVariants()),
        ]);
    }

    private function getBaseItemProperties(BaseItem $item)
    {
        return [
            'id' => $item->getId(),
            'score' => $item->getScore(),
            'url' => $item->getUrl(),
            'name' => $item->getName(),
            'ordernumbers' => $item->getOrdernumbers(),
            'matchingOrdernumber' => $item->getMatchingOrdernumber(),
            'price' => $item->getPrice(),
            'summary' => $item->getSummary(),
            'attributes' => $item->getAttributes(),
            'properties' => $item->getProperties(),
            'imageUrl' => $item->getImageUrl()
        ];
    }
}
