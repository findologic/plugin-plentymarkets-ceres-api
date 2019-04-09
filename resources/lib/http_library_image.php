<?php

/** @var string $imageUrl */
$imageUrl = SdkRestApi::getParam('imageUrl');

try {
    $httpRequest = new \HTTP_Request2($imageUrl, 'HEAD');
    $httpRequest->setAdapter('curl');
    $httpRequest->setConfig('connect_timeout', 5);
    $httpRequest->setConfig('timeout', 10);

    $response = $httpRequest->send();
} catch (\HTTP_Request2_Exception $e) {
    return '500';
}

return $response->getStatus();