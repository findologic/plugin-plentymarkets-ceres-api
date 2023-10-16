<?php

namespace Findologic\Api\Response\Request;

class Request
{

    private ?string $query;

    private int $first;

    private int $count;

    private string $serviceId;

    private ?string $usergroup;

    private array $order;

    public function __construct(array $request)
    {
        $this->query = $request['query'];
        $this->first = $request['first'];
        $this->count = $request['count'];
        $this->serviceId = $request['serviceId'];
        $this->usergroup = $request['usergroup'];
        $this->order = pluginApp(Order::class, $request['order']);
    }

    /**
     * Get the value of query
     */ 
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get the value of first
     */ 
    public function getFirst()
    {
        return $this->first;
    }

    /**
     * Get the value of count
     */ 
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Get the value of serviceId
     */ 
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Get the value of usergroup
     */ 
    public function getUsergroup()
    {
        return $this->usergroup;
    }

    /**
     * Get the value of order
     */ 
    public function getOrder()
    {
        return $this->order;
    }
}
