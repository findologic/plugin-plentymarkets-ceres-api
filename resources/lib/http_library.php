<?php

/** @var \Findologic\Api\Request\Request $request */
$request = SdkRestApi::getParam('request');

$client = new \GuzzleHttp\Client([
    'timeout' => $request['timeout'],
    'connect_timeout' => $request['connect_timeout'],
]);

$res = $client->get(
    $request['url']
);

return $res->getBody();
