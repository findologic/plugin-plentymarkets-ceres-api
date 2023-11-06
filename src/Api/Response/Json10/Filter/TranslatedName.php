<?php

declare(strict_types=1);

namespace Findologic\Api\Response\Json10\Filter;

class TranslatedName
{
    public function __construct(
        public string $name
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }
}
