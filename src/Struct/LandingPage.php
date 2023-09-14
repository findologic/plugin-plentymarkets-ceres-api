<?php

declare(strict_types=1);

namespace FINDOLOGIC\Struct;

class LandingPage
{
    public function __construct(
        protected readonly string $link
    ) {
    }

    public function getLink(): string
    {
        return $this->link;
    }
}