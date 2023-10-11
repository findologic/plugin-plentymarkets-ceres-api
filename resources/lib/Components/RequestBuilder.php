<?php

namespace FindologicApi\Components;

require_once __DIR__ . '/Constants/Plugin.php';
use FindologicApi\Constants\Plugin;
use FINDOLOGIC\Api\Requests\Request;
use FINDOLOGIC\Api\Definitions\OutputAdapter;
use FINDOLOGIC\Api\Requests\SearchNavigation\SearchNavigationRequest;

class RequestBuilder extends Request
{
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

    private Request|SearchNavigationRequest $request;

    public function __construct()
    {
        $this->request = Request::getInstance(\SdkRestApi::getParam('requestType'));
    }

    public function getBody()
    {
        return parent::getBody();
    }

    public function buildAliveRequest() : self {
        $this->request->setShopUrl(\SdkRestApi::getParam('shopUrl'));
        $this->request->setShopkey(\SdkRestApi::getParam('shopKey'));

        return $this;
    }

    public function setDefaultValues(): self
    {
        $this->request->setShopUrl(\SdkRestApi::getParam('shopUrl'));
        $this->request->setShopkey(\SdkRestApi::getParam('shopKey'));
        $this->request->setOutputAdapter(OutputAdapter::JSON_10);
        $this->request->setRevision(\SdkRestApi::getParam('revision'));

        if (\SdkRestApi::getParam('userIp')) {
            $this->request->setUserIp(\SdkRestApi::getParam('userIp'));
        }
        $this->request->setShopType(\SdkRestApi::getParam('shopType'));
        $this->request->setShopVersion(\SdkRestApi::getParam('shopVersion'));

        return $this;
    }

    public function setSearchParams(
    ):self {
        $parameters = \SdkRestApi::getParam('params');

        $externalSearch = \SdkRestApi::getParam('externalSearch');
        $this->request->setQuery($externalSearch['searchString']);
        $this->request->addProperty(Plugin::API_PROPERTY_VARIATION_ID);

        if (isset($parameters[Plugin::API_PARAMETER_ATTRIBUTES])) {
            $attributes = $parameters[Plugin::API_PARAMETER_ATTRIBUTES];
            foreach ($attributes as $filterName => $value) {
                $this->request->addAttribute($filterName, $value);
            }
        }

        if (isset($parameters[Plugin::API_PARAMETER_FORCE_ORIGINAL_QUERY])
            && $parameters[Plugin::API_PARAMETER_FORCE_ORIGINAL_QUERY] != false
        ) {
            $this->request->setForceOriginalQuery(true);
        }

        if (\SdkRestApi::getParam('isTagPage')) {
            $this->request->addIndividualParam('selected', ['cat_id' => [\SdkRestApi::getParam('tagId')]], Request::SET_VALUE);
        }

        if (\SdkRestApi::getParam('category') && ($categoryFullName = \SdkRestApi::getParam('categoryName'))) {
            $this->request->addIndividualParam('selected', ['cat' => [$categoryFullName]], Request::SET_VALUE);
        }

        if ($externalSearch->sorting !== 'item.score' &&
            in_array($externalSearch->sorting, Plugin::API_SORT_ORDER_AVAILABLE_OPTIONS)
        ) {
            $this->request->setOrder(self::SORT_MAPPING[$externalSearch['sorting']]);
        }

        $this->setPagination($externalSearch, $parameters);

        return $this;
    }

    protected function setPagination(array $externalSearch, array $parameters):void
    {
        if ($externalSearch['categoryId'] !== null &&
            !array_key_exists(Plugin::API_PARAMETER_ATTRIBUTES, $parameters)
        ) {
            $this->request->setFirst(0);
            $this->request->setCount(0);
            return;
        }

        $this->request->setCount($externalSearch['itemsPerPage']);

        if ($externalSearch['page'] > 1) {
            $this->request->setFirst(($externalSearch['page'] - 1) * $externalSearch['itemsPerPage']);
        }
    }

}