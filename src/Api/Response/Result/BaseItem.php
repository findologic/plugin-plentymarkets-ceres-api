<?php

namespace Findologic\Api\Response\Result;

class BaseItem
{
    private string $id;

    private float $score;

    private ?string $url;

    private ?string $name;

    /** @var string[] */
    private $ordernumbers = [];

    private ?string $matchingOrdernumber;

    private float $price;

    private ?string $summary;

    /** @var array<string, array<string>> */
    private $attributes = [];

    /** @var array<string, string> */
    private $properties = [];

    private ?string $imageUrl;

    function __construct(array $item)
    {
        $this->id = $item['id'];
        $this->score = $item['score'];
        $this->url = $item['url'];
        $this->name = $item['name'];
        $this->ordernumbers = $item['ordernumbers'];
        $this->matchingOrdernumber = $item['matchingOrdernumber'];
        $this->price = $item['price'];
        $this->summary = $item['summary'];
        $this->attributes = $item['attributes'];
        $this->properties = $item['properties'];
        $this->imageUrl = $item['imageUrl'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getScore()
    {
        return $this->score;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOrdernumbers()
    {
        return $this->ordernumbers;
    }

    public function getMatchingOrdernumber()
    {
        return $this->matchingOrdernumber;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getImageUrl()
    {
        return $this->imageUrl;
    }
}