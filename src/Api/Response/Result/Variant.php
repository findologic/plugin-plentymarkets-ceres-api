<?php

namespace Findologic\Api\Response\Result;

class Variant
{
    private string $name;

    private ?string $correctedQuery;

    private ?string $improvedQuery;

    private ?string $didYouMeanQuery;

    function __construct(array $variant)
    {
        $this->name = $variant['name'];
        $this->correctedQuery = $variant['correctedQuery'];
        $this->improvedQuery = $variant['improvedQuery'];
        $this->didYouMeanQuery = $variant['didYouMeanQuery'];
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of correctedQuery
     */ 
    public function getCorrectedQuery()
    {
        return $this->correctedQuery;
    }

    /**
     * Get the value of improvedQuery
     */ 
    public function getImprovedQuery()
    {
        return $this->improvedQuery;
    }

    /**
     * Get the value of didYouMeanQuery
     */ 
    public function getDidYouMeanQuery()
    {
        return $this->didYouMeanQuery;
    }
}
