<?php

require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/RequestBuilder.php';
use FINDOLOGIC\Api\Client;
use FINDOLOGIC\Api\Config;
// use FindologicApi\Components\ApiResponse;
// use FindologicApi\Components\RequestBuilder;

try {
    $config = new Config(SdkRestApi::getParam('shop_key'));
    $findologicClient = new Client($config);

    if(SdkRestApi::getParam('aliveRequest')){
        $request = (new RequestBuilder())->buildAliveRequest();
    }
    else $request = (new RequestBuilder())->setDefaultValues()->setSearchParams();

    $response = new ApiResponse($findologicClient->send($request));
    return ['response'=> (array)$response];
} catch (\Throwable $t) {
    return ['error' => (string)$t];
}