<?php

namespace Findologic\Api\Response\Result;

use Findologic\Api\Response\Result\Variants;

class Item extends BaseItem
{
    private string $highlightedName;

    private ?string $productPlacement;

    /** @var string[] */
    private $pushRules = [];

    private array $variants = [];

    function __construct(array $item)
    {
        parent::__construct($item);
        $this->highlightedName = $item['highlightedName'];
        $this->productPlacement = $item['productPlacement'];
        $this->pushRules = $item['pushRules'];
        $this->variants = array_map(fn ($variant) => pluginApp(Variants::class, [$variant]), $item['variants']);
    }


    /**
     * Get the value of variants
     */ 
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * Get the value of pushRules
     */ 
    public function getPushRules()
    {
        return $this->pushRules;
    }

    /**
     * Get the value of productPlacement
     */ 
    public function getProductPlacement()
    {
        return $this->productPlacement;
    }

    /**
     * Get the value of highlightedName
     */ 
    public function getHighlightedName()
    {
        return $this->highlightedName;
    }
}
