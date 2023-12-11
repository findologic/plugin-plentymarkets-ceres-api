<?php

require_once __DIR__ . '/ApiResult.php';
require_once __DIR__ . '/Arrayable.php';

use FINDOLOGIC\Api\Responses\Json10\Json10Response;
use FINDOLOGIC\Api\Responses\Json10\Properties\Result;
use FINDOLOGIC\Api\Responses\Json10\Properties\Request;
use FINDOLOGIC\Api\Responses\Xml21\Xml21Response;

class ApiResponse extends Json10Response implements Arrayable
{
    /** @var Request */
    private $request;

    /** @var Result */
    private $result;

    public function __construct(Json10Response|Xml21Response $response)
    {
        if ($response instanceof Json10Response){
            $this->request = $response->getRequest();
            $this->result = $response->getResult();
        }
        else {
            $this->result = $response->getResults();
        }
        
    }
    public function toArray()
    {
        return [
            'result' => (new ApiResult($this->result))->toArray(),
            'request' => array_merge((array)$this->request, ['order' => (array)$this->request->getOrder()])
        ];
    }
}
