<?php

declare(strict_types=1);

namespace FINDOLOGIC\Struct\QueryInfoMessage;

class SearchTermQueryInfoMessage extends QueryInfoMessage
{
    public function __construct(
        protected readonly string $query
    ) {
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}
