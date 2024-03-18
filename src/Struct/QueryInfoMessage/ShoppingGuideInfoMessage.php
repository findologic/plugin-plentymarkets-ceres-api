<?php

namespace Findologic\Struct\QueryInfoMessage;

class ShoppingGuideInfoMessage extends QueryInfoMessage
{
    public string $shoppingGuide;

    public function __construct(string $shoppingGuide)
    {
        $this->shoppingGuide = $shoppingGuide;
    }

    public function getShoppingGuide(): string
    {
        return $this->shoppingGuide;
    }
}
