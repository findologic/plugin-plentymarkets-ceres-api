<?php

require_once __DIR__ . '/ApiResponse.php';
use FINDOLOGIC\Api\Client;
use FINDOLOGIC\Api\Config;
use FINDOLOGIC\Api\Requests\Request;
use FINDOLOGIC\Api\Definitions\OutputAdapter;
use FINDOLOGIC\Api\Requests\SearchNavigation\SearchNavigationRequest;

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
    $parameters = \SdkRestApi::getParam('parameters');

    $request->setQuery(\SdkRestApi::getParam('externalSearch')['searchString']);
    $request->addProperty('variation_id');

    if (isset($parameters['attrib'])) {
        $attributes = $parameters['attrib'];

        foreach ($attributes as $key => $attrib) {
            if($key === 'price'){
                $request->addAttribute($key, $attrib['min'], 'min');
                $request->addAttribute($key, $attrib['max'], 'max');
            }
            else {
                foreach($attrib as $attributeValue){
                    $request->addAttribute($key, $attributeValue);
                }
            }
        }
    }

    if (
        isset($parameters['forceOriginalQuery'])
        && $parameters['forceOriginalQuery'] != false
    ) {
        $request->setForceOriginalQuery();
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
    $requestUrl = $request->buildRequestUrl($config);

    $apiResponse = $findologicClient->send($request);
    $response = new ApiResponse($apiResponse);
    return ['response' => $response->toArray(), 'requestUrl' => $requestUrl, 'request' => $request->getParams()];
} catch (\Throwable | \Exception $t) {
    return ['error' => (string)$t, 'request' => (array)$request];
}
