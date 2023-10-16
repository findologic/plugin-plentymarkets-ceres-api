<?php

namespace Findologic\Api\Response;

use Findologic\Api\Response\Result\Result;
use Findologic\Api\Response\Request\Request;

class Response{
    private Request $request;

    private Result $result;

    public function __construct(array $response)
    {
        $this->request = pluginApp(Request::class, $response['request']);
        $this->result = pluginApp(Result::class, $response['result']);;
    }

    /**
     * Get the value of request
     */ 
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the value of result
     */ 
    public function getResult()
    {
        return $this->result;
    }
}