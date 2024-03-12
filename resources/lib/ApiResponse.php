<?php

require_once __DIR__ . '/ApiResult.php';
require_once __DIR__ . '/Arrayable.php';

use FINDOLOGIC\Api\Responses\Json10\Json10Response;
use FINDOLOGIC\Api\Responses\Json10\Properties\Result;
use FINDOLOGIC\Api\Responses\Json10\Properties\Request;

class ApiResponse extends Json10Response implements Arrayable
{

    public Request $request;

    public Result $result;

    public function __construct(Json10Response $response)
    {
        $this->request = $response->getRequest();
        $this->result = $response->getResult();
    }

    public function toArray()
    {
        return [
            'result' => (new ApiResult($this->result))->toArray(),
            'request' => array_merge((array)$this->request, ['order' => (array)$this->request->getOrder()])
        ];
    }
}
