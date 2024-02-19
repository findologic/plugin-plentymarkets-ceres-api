<?php

namespace Findologic\Struct;


class Pagination
{
    public const DEFAULT_LIMIT = 24;
    private ?int $limit;
    private ?int $offset;
    private ?int $total;
    public function __construct(
        ?int $limit,
        ?int $offset,
        ?int $total
    ) {
        $this->limit = $limit;
        $this->offset = $offset;
        $this->total = $total;
        $this->limit = $limit ?? self::DEFAULT_LIMIT;
        $this->offset = $offset ?? 0;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }
}