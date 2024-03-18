<?php

namespace Findologic\Containers;

use Plenty\Plugin\Templates\Twig;
use Findologic\Services\SearchService;

/**
 * Class PromotionContainer
 * @package Findologic\Containers
 */
class PromotionContainer
{
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

        return $twig->render(
            'Findologic::Category.Item.Partials.Promotion',
            [
                'promotion' => $searchResults->getPromotionExtension()
            ]
        );
    }
}
