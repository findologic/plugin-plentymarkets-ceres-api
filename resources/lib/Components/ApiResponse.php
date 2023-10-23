<?php

namespace FindologicApi\Components;

require_once __DIR__ . '/ApiResult.php';

// use FindologicApi\Components\ApiResult;
use FINDOLOGIC\Api\Responses\Json10\Json10Response;
use FINDOLOGIC\Api\Responses\Json10\Properties\Request;
use FINDOLOGIC\Api\Responses\Json10\Properties\Result;

class ApiResponse extends Json10Response
{
    /** @var Request */
    private $request;

    /** @var Result */
    private $result;

    public function __construct(Json10Response $response)
    {
        $this->request = $response->getRequest();
        $this->result = $response->getResult();
    }
    public function __toArray()
    {
        return [
            'result' => (array)new ApiResult($this->result),
            'request' => [...(array)$this->request, 'order' => (array)$this->request->getOrder()]
        ];
    }
}
