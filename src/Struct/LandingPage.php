<?php

namespace Findologic\Struct;

class LandingPage
{
    protected string $link;

    public function __construct(string $link)
    {
        $this->link = $link;
    }

    public function getLink(): string
    {
        return $this->link;
    }
}
