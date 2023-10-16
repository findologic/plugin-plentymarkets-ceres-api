<?php

namespace Findologic\Api\Response\Result;

use Findologic\Struct\Promotion;
use Findologic\Struct\LandingPage;

class Metadata
{
    /** @var LandingPage|null */
    private $landingPage;

    /** @var Promotion|null */
    private $promotion;

    /** @var string */
    private $searchConcept;

    /** @var string */
    private $effectiveQuery;

    /** @var int */
    private $totalResults;

    /** @var string */
    private $currencySymbol;

    function __construct(array $metadata)
    {
        $this->searchConcept = $metadata['searchConcept'];
        $this->effectiveQuery = $metadata['effectiveQuery'];
        $this->totalResults = $metadata['totalResults'];
        $this->currencySymbol = $metadata['currencySymbol'];
        $this->landingPage = pluginApp(LandingPage::class, [$metadata['landingPage']['url'], $metadata['landingPage']['name']]);
        $this->promotion = pluginApp(Promotion::class, [$metadata['promotion']['url'], $metadata['promotion']['imageUrl']]);
    }

    /**
     * Get the value of currencySymbol
     */ 
    public function getCurrencySymbol()
    {
        return $this->currencySymbol;
    }

    /**
     * Get the value of totalResults
     */ 
    public function getTotalResults()
    {
        return $this->totalResults;
    }

    /**
     * Get the value of effectiveQuery
     */ 
    public function getEffectiveQuery()
    {
        return $this->effectiveQuery;
    }

    /**
     * Get the value of searchConcept
     */ 
    public function getSearchConcept()
    {
        return $this->searchConcept;
    }

    /**
     * Get the value of promotion
     */ 
    public function getPromotion()
    {
        return $this->promotion;
    }

    /**
     * Get the value of landingPage
     */ 
    public function getLandingPage()
    {
        return $this->landingPage;
    }
}
