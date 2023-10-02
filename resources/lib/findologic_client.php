<?php

// $client = new \GuzzleHttp\Client();
$config = new \FINDOLOGIC\Api\Config(SdkRestApi::getParam('shop_key'));
$findologicClient = new \FINDOLOGIC\Api\Client($config);

return $findologicClient->send(SdkRestApi::getParam('request'));