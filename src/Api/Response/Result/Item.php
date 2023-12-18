<?php

namespace Findologic\Api\Response\Result;

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

class Variants {

    public string $id;
    public float $score;
    public string $url;
    public ?string $name;
    public array $ordernumbers;
    public ?string $matchingOrdernumber;
    public float $price;
    public ?string $summary;
    public array $attributes;
    public array $properties;
    public string $imageUrl;

    function __construct(array $variant)
    {
        $this->id = $variant['id'];
        $this->score = $variant['score'];
        $this->url = $variant['url'];
        $this->name = $variant['name'];
        $this->ordernumbers = $variant['ordernumbers'];
        $this->matchingOrdernumber = $variant['matchingOrdernumber'];
        $this->price = $variant['price'];
        $this->summary = $variant['summary'];
        $this->attributes = $variant['attributes'];
        $this->properties = $variant['properties'];
        $this->imageUrl = $variant['imageUrl'];
    }

    public function getId()
    {
        return $this->id;
    }
}