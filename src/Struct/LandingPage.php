<?php

namespace Findologic\Struct;

class LandingPage
{
    protected ?string $link;

    public function __construct(?string $link, string $name)
    {
        $this->link = $link;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }
}
