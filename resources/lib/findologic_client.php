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

    if (\SdkRestApi::getParam('aliveRequest')) {
        $request = (new RequestBuilder(shopUrl: $shopUrl, requestType: $requestType, shopKey: $shopKey))->buildAliveRequest();
    } else $request = (new RequestBuilder($requestType, $shopUrl, $shopKey, $revision, $userIp, $shopType, $shopVersion, $params, $externalSearch, $isTagPage, $tagId, $categoryName, $category))->setDefaultValues()->setSearchParams();

    $response = new ApiResponse($findologicClient->send($request));
    return ['response' => (array)$response];
} catch (\Throwable $t) {
    return ['error' => (string)$t, 'request' => (array)$request];
}
