<?php

declare(strict_types=1);

namespace FINDOLOGIC\Struct;


class Promotion
{
    public function __construct(
        private readonly string $image,
        private readonly string $link
    ) {
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getLink(): string
    {
        return $this->link;
    }
}