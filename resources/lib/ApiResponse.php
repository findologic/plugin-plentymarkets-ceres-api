<?php

require_once __DIR__ . '/ApiResult.php';

// use FindologicApi\Components\ApiResult;
use Illuminate\Contracts\Support\Arrayable;
use FINDOLOGIC\Api\Responses\Json10\Json10Response;
use FINDOLOGIC\Api\Responses\Json10\Properties\Result;
use FINDOLOGIC\Api\Responses\Json10\Properties\Request;

class ApiResponse extends Json10Response implements Arrayable
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
    public function toArray()
    {
        return [
            'result' => (new ApiResult($this->result))->toArray(),
            'request' => array_merge((array)$this->request, ['order' => (array)$this->request->getOrder()])
        ];
    }
}
