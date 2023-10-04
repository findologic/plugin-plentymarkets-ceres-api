<?php

function createRequestObject(): \FINDOLOGIC\Api\Requests\Request
{
    return \FINDOLOGIC\Api\Requests\Request::getInstance(SdkRestApi::getParam('requestType'));
}

function setDefaultValues(\FINDOLOGIC\Api\Requests\Request|\FINDOLOGIC\Api\Requests\SearchNavigation\SearchNavigationRequest &$request):void
{
    $request->setShopUrl(SdkRestApi::getParam('shopUrl'));
    $request->setShopkey(SdkRestApi::getParam('shopKey'));
    $request->setOutputAdapter(SdkRestApi::getParam('outputAdapter'));
    $request->setRevision(SdkRestApi::getParam('revision'));

    if (SdkRestApi::getParam('userIp')) {
        $request->setUserIp(SdkRestApi::getParam('userIp'));
    }
    $request->setShopType(SdkRestApi::getParam('shopType'));
    $request->setShopVersion(SdkRestApi::getParam('shopVersion'));
}

function setSearchParams(){
    
}

$request = createRequestObject();
setDefaultValues($request);

return $request;
