<?php

namespace Findologic\Containers;

use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Templates\Twig;
use Findologic\Services\SearchService;
use Findologic\Api\Response\Response;

/**
 * Class PromotionContainer
 * @package Findologic\Containers
 */
class PromotionContainer
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
        $this->getLogger(__METHOD__)->debug('searchResults', $searchResults);
        throw new \Exception(json_encode($searchResults));
        return $twig->render(
            'Findologic::Category.Item.Partials.Promotion',
            [
                'promotion' => $searchResults->getData(Response::DATA_PROMOTION)
            ]
        );
    }
}
