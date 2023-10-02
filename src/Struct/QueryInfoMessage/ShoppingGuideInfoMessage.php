<?php

namespace Findologic\Struct\QueryInfoMessage;

class ShoppingGuideInfoMessage extends QueryInfoMessage
{
    protected string $shoppingGuide;

    public function __construct(string $shoppingGuide)
    {
        $this->shoppingGuide = $shoppingGuide;
    }

    public function getShoppingGuide(): string
    {
        return $this->shoppingGuide;
    }
}
