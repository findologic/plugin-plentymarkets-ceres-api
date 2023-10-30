<?php

namespace Findologic\Struct;

class LandingPage
{
    protected ?string $link;
    protected ?string $name;

    public function __construct(?string $link, ?string $name)
    {
        $this->link = $link;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getName()
    {
        return $this->name;
    }
}
