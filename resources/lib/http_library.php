<?php

use Findologic\Constants\Plugin;

/** @var \Findologic\Api\Request\Request $request */
$request = SdkRestApi::getParam('request');

$httpRequest = new \HTTP_Request2($request['url'], 'GET');
$httpRequest->setAdapter('curl');
$httpRequest->setConfig('connect_timeout', $request['connect_timeout']);
$httpRequest->setConfig('timeout', $request['timeout']);
throw new \Exception($request['url']);
$response = $httpRequest->send();

return $response->getBody();