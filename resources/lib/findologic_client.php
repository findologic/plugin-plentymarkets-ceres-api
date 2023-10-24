<?php

require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/RequestBuilder.php';

use FINDOLOGIC\Api\Client;
use FINDOLOGIC\Api\Config;
// use FindologicApi\Components\ApiResponse;
// use FindologicApi\Components\RequestBuilder;

try {
    $config = new Config(\SdkRestApi::getParam('shop_key'));
    $findologicClient = new Client($config);

    $requestType = \SdkRestApi::getParam('requestType');
    $shopUrl = \SdkRestApi::getParam('shopUrl');
    $shopKey = \SdkRestApi::getParam('shopKey');
    $revision = \SdkRestApi::getParam('revision');
    $userIp = \SdkRestApi::getParam('userIp');
    $shopType = \SdkRestApi::getParam('shopType');
    $shopVersion = \SdkRestApi::getParam('shopVersion');
    $params = \SdkRestApi::getParam('params');
    $externalSearch = \SdkRestApi::getParam('externalSearch');
    $isTagPage = \SdkRestApi::getParam('isTagPage');
    $tagId = \SdkRestApi::getParam('tagId');
    $categoryName = \SdkRestApi::getParam('categoryName');
    $category = \SdkRestApi::getParam('category');
    $aliveRequest = \SdkRestApi::getParam('aliveRequest');
    // if ($aliveRequest) {
    //     $request = (new RequestBuilder($requestType, $shopUrl, $shopKey))->buildAliveRequest();
    // } else 
    $request = (new RequestBuilder($requestType, $shopUrl, $shopKey, $revision, $userIp, $shopType, $shopVersion, $params, $externalSearch, $isTagPage, $tagId, $categoryName, $category))->setDefaultValues()->setSearchParams();

    //$apiResponse = $findologicClient->send($request);
    //$response = new ApiResponse($apiResponse);
    return ['response' => 'no thx'];
} catch (\Throwable | \Exception $t) {
    return ['error' => (string)$t, 'request' => (array)$request];
}
