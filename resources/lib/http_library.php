<?php

use Findologic\Constants\Plugin;

/** @var \Findologic\Api\Request\Request $request */
$request = SdkRestApi::getParam('request');

$method = $request['method'] ?? 'GET';
$httpRequest = new \HTTP_Request2($request['url'], $method);

$httpRequest->setAdapter('curl');

$httpRequest->setConfig('connect_timeout', $request['connect_timeout']);
$httpRequest->setConfig('timeout', $request['timeout']);

$response = $httpRequest->send();

return $response->getBody();