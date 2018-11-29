<?php

namespace Findologic\Containers;

use Findologic\Services\SearchService;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\Http\Request;

/**
 * Class SearchFilterContainer
 * @package Findologic\Containers
 */
class SearchFilterContainer
{
    /**
     * @var SearchService
     */
    protected $searchService;

    /**
     * @var Request
     */
    protected $request;

    public function __construct(
        SearchService $searchService,
        Request $request
    ) {
        $this->searchService = $searchService;
        $this->request = $request;
    }

    public function call(Twig $twig):string
    {
        $searchResults = $this->searchService->search($this->request);


        return $twig->render('Findologic::content.filters');
    }
}