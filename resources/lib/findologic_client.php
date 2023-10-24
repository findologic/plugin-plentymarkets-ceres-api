<?php

// require_once __DIR__ . '/RequestBuilder.php';

use FINDOLOGIC\Api\Client;
use FINDOLOGIC\Api\Config;
use FINDOLOGIC\Api\Requests\Request;
use FINDOLOGIC\Api\Definitions\OutputAdapter;
use FINDOLOGIC\Api\Requests\SearchNavigation\SearchNavigationRequest;
// use FindologicApi\Components\ApiResponse;
// use FindologicApi\Components\RequestBuilder;
const SORT_MAPPING = [
    'sorting.price.avg_asc' => 'price ASC',
    'sorting.price.avg_desc' => 'price DESC',
    'texts.name1_asc' => 'label ASC',
    'texts.name1_desc' => 'label DESC',
    'variation.createdAt_desc' => 'dateadded DESC',
    'variation.createdAt_asc' => 'dateadded ASC',
    'variation.position_asc' => 'salesfrequency ASC',
    'variation.position_desc' => 'salesfrequency DESC'
];

function setDefaultValues(Request|SearchNavigationRequest $request): Request
{
    $request->setShopUrl(\SdkRestApi::getParam('shopUrl'));
    $request->setShopkey(\SdkRestApi::getParam('shopKey'));
    $request->setOutputAdapter(OutputAdapter::JSON_10);
    $request->setRevision(\SdkRestApi::getParam('revision'));

    if (\SdkRestApi::getParam('userIp')) {
        $request->setUserIp(\SdkRestApi::getParam('userIp'));
    }
    $request->setShopType(\SdkRestApi::getParam('shopType'));
    $request->setShopVersion(\SdkRestApi::getParam('shopVersion'));
    $request = setSearchParams($request);

    return $request;
}

function setSearchParams(Request|SearchNavigationRequest $request): Request
{
    $parameters = \SdkRestApi::getParam('params');

    $request->setQuery(\SdkRestApi::getParam('externalSearch')['searchString']);
    $request->addProperty('variation_id');

    if (isset($parameters['attrib'])) {
        $attributes = $parameters['attrib'];
        foreach ($attributes as $filterName => $value) {
            $request->addAttribute($filterName, $value);
        }
    }

    if (
        isset($parameters['forceOriginalQuery'])
        && $parameters['forceOriginalQuery'] != false
    ) {
        $request->setForceOriginalQuery(true);
    }

    if (\SdkRestApi::getParam('isTagPage')) {
        $request->addIndividualParam('selected', ['cat_id' => [\SdkRestApi::getParam('tagId')]], Request::SET_VALUE);
    }

    if (\SdkRestApi::getParam('category') && ($categoryFullName = \SdkRestApi::getParam('categoryName'))) {
        $request->addIndividualParam('selected', ['cat' => [$categoryFullName]], Request::SET_VALUE);
    }

    if (
        \SdkRestApi::getParam('externalSearch')['sorting'] !== 'item.score' &&
        in_array(\SdkRestApi::getParam('externalSearch')['sorting'], [
            'sorting.price.avg_asc',
            'sorting.price.avg_desc',
            'texts.name1_asc',
            'texts.name1_desc',
            'variation.createdAt_desc',
            'variation.createdAt_asc',
            'item.score',
            'variation.position_asc',
            'variation.position_desc'
        ])
    ) {
        $request->setOrder(SORT_MAPPING[\SdkRestApi::getParam('externalSearch')['sorting']]);
    }

    $request = setPagination($request, $parameters);

    return $request;
}

function setPagination(Request|SearchNavigationRequest $request, array $parameters): Request
{
    if (
        \SdkRestApi::getParam('externalSearch')['categoryId'] !== null &&
        !array_key_exists('attrib', $parameters)
    ) {
        $request->setFirst(0);
        $request->setCount(0);
        return $request;
    }

    $request->setCount(\SdkRestApi::getParam('externalSearch')['itemsPerPage']);

    if (\SdkRestApi::getParam('externalSearch')['page'] > 1) {
        $request->setFirst((\SdkRestApi::getParam('externalSearch')['page'] - 1) * \SdkRestApi::getParam('externalSearch')['itemsPerPage']);
    }

    return $request;
}
try {
    $config = new Config(\SdkRestApi::getParam('shop_key'));
    $findologicClient = new Client($config);

    $requestType = \SdkRestApi::getParam('requestType');

    $request = Request::getInstance($requestType);
    $request = setDefaultValues($request);

    // $shopUrl = \SdkRestApi::getParam('shopUrl');
    // $shopKey = \SdkRestApi::getParam('shopKey');
    // $revision = \SdkRestApi::getParam('revision');
    // $userIp = \SdkRestApi::getParam('userIp');
    // $shopType = \SdkRestApi::getParam('shopType');
    // $shopVersion = \SdkRestApi::getParam('shopVersion');
    // $params = \SdkRestApi::getParam('params');
    // $externalSearch = \SdkRestApi::getParam('externalSearch');
    // $isTagPage = \SdkRestApi::getParam('isTagPage');
    // $tagId = \SdkRestApi::getParam('tagId');
    // $categoryName = \SdkRestApi::getParam('categoryName');
    // $category = \SdkRestApi::getParam('category');
    // $aliveRequest = \SdkRestApi::getParam('aliveRequest');
    // if ($aliveRequest) {
    //     $request = (new RequestBuilder($requestType, $shopUrl, $shopKey))->buildAliveRequest();
    // } else 
    //$request = (new RequestBuilder($requestType, $shopUrl, $shopKey, $revision, $userIp, $shopType, $shopVersion, $params, $externalSearch, $isTagPage, $tagId, $categoryName, $category))->setDefaultValues()->setSearchParams();

    $apiResponse = $findologicClient->send($request);
    $response = new ApiResponse($apiResponse);
    return ['response' => (array)$response];
} catch (\Throwable | \Exception $t) {
    return ['error' => (string)$t, 'request' => (array)$request];
}
