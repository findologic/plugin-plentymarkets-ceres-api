<?php

namespace Findologic\Struct;


class Promotion
{
    public ?string $image;
    public ?string $link;

    public function __construct(
        ?string $link,
        ?string $image
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
