<?php

namespace Findologic\Containers;

use Plenty\Plugin\Templates\Twig;
use Findologic\Services\SearchService;
use Findologic\Api\Response\Response;

/**
 * Class SmartDidYouMeanContainer
 * @package Findologic\Containers
 */
class SmartDidYouMeanContainer
{
    /**
     * @param Twig $twig
     * @param SearchService $searchService
     * @return string
     */
    public function call(Twig $twig, SearchService $searchService):string
    {
        if (!$searchService->getResults()) {
            return '';
        }

        $searchResults = $searchService->getResults();

        return $twig->render(
            'Findologic::Category.Item.Partials.SmartDidYouMean',
            [
                'query_info_message' => $searchResults->getQueryInfoMessage(),
                'smart_did_you_mean' => $searchResults->getSmartDidYouMean()
            ]
        );
    }
}
