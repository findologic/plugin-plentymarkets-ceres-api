<?php

namespace Findologic\Api\Response\Result;

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
