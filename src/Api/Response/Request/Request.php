<?php

namespace Findologic\Api\Response\Request;

class Request
{
    private ?string $query;

    private ?int $first;

    private ?int $count;

    private ?string $serviceId;

    private ?string $usergroup;

    public function __construct(array $request = [])
    {
        $this->query = $request['query'];
        $this->first = $request['first'];
        $this->count = $request['count'];
        $this->serviceId = $request['serviceId'];
        $this->usergroup = $request['usergroup'];
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getFirst()
    {
        return $this->first;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getServiceId()
    {
        return $this->serviceId;
    }

    public function getUsergroup()
    {
        return $this->usergroup;
    }
}
