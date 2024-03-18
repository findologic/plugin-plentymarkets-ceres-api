<?php

declare(strict_types=1);

namespace Findologic\Api\Response\Json10\Filter;

class Media
{
    public function __construct(
        public ?string $url
    ) {
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
}
