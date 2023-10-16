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


    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of score
     */ 
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Get the value of url
     */ 
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of ordernumbers
     */ 
    public function getOrdernumbers()
    {
        return $this->ordernumbers;
    }

    /**
     * Get the value of matchingOrdernumber
     */ 
    public function getMatchingOrdernumber()
    {
        return $this->matchingOrdernumber;
    }

    /**
     * Get the value of price
     */ 
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Get the value of attributes
     */ 
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get the value of properties
     */ 
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Get the value of imageUrl
     */ 
    public function getImageUrl()
    {
        return $this->imageUrl;
    }
}