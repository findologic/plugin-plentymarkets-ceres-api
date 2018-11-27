<?php

namespace Findologic\Containers;

use Findologic\Services\SearchService;
use Plenty\Plugin\Templates\Twig;

/**
 * Class SearchFilterContainer
 * @package Findologic\Containers
 */
class SearchFilterContainer
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function call(Twig $twig):string
    {
        return $twig->render('Findologic::content.filters');
    }
}