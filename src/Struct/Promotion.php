<?php

namespace Findologic\Struct;


class Promotion
{
    private ?string $image;
    private ?string $link;

    public function __construct(
        string $link,
        string $image
    ) {
        $this->image = $image;
        $this->link = $link;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }
}
