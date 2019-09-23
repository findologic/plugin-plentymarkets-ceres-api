<?php

namespace Findologic\Containers;

use Plenty\Plugin\Templates\Twig;

/**
 * Class SearchBarContainer
 * @package Findologic\Containers
 */
class SearchBarContainer
{
    /**
     * @param Twig $twig
     * @return string
     */
    public function call(Twig $twig):string
    {
        return $twig->render(
            'Findologic::Category.Item.Partials.ItemSearch'
        );
    }
}
