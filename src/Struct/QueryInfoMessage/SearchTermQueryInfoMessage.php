<?php

namespace Findologic\Struct\QueryInfoMessage;

class SearchTermQueryInfoMessage extends QueryInfoMessage
{
    public string $query;

    public function __construct(
        string $query
    ) {
        $this->query = $query;
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}
