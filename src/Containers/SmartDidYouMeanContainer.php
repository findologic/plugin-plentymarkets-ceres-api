<?php

namespace Findologic\Containers;

use Findologic\Traits\Loggable;
use Plenty\Plugin\Templates\Twig;
use Findologic\Services\SearchService;

/**
 * Class SmartDidYouMeanContainer
 * @package Findologic\Containers
 */
class SmartDidYouMeanContainer
{
    use Loggable;
    /**
     * @param Twig $twig
     * @param SearchService $searchService
     * @return string
     */
    public function call(Twig $twig, SearchService $searchService): string
    {
        if (!$searchService->getResults()) {
            return '';
        }

        $searchResults = $searchService->getResults();
        $this->getLogger(__METHOD__)->debug('log.debuglog', ['query_info_message' => $searchResults->getQueryInfoMessage() ]);
        return $twig->render(
            'Findologic::Category.Item.Partials.SmartDidYouMean',
            [
                'query_info_message' => $searchResults->getQueryInfoMessage(),
                'smart_did_you_mean' => $searchResults->getSmartDidYouMean()
            ]
        );
    }
}
