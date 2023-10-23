<?php

require_once __DIR__ . '/Plugin.php';
// use FindologicApi\Constants\Plugin;
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

    public function __construct(
        private string $requestType = null, 
        private string $shopUrl = null, 
        private string $shopKey = null, 
        private string $revision = null,
        private string $userIp = null,
        private string $shopType = null,
        private string $shopVersion = null,
        private array $params = null,
        private array $externalSearch = null,
        private $isTagPage = null,
        private $tagId = null,
        private string $categoryName = null,
        private $category = null,
        )
    {
        parent::__construct();
        $this->request = Request::getInstance($this->requestType);
    }

    public function getBody()
    {
        return parent::getBody();
    }

    public function buildAliveRequest() : self {
        $this->request->setShopUrl($this->shopUrl);
        $this->request->setShopkey($this->shopKey);

        return $this;
    }

    public function setDefaultValues(): self
    {
        $this->request->setShopUrl($this->shopUrl);
        $this->request->setShopkey($this->shopKey);
        $this->request->setOutputAdapter(OutputAdapter::JSON_10);
        $this->request->setRevision($this->revision);

        if ($this->userIp) {
            $this->request->setUserIp($this->userIp);
        }
        $this->request->setShopType($this->shopType);
        $this->request->setShopVersion($this->shopVersion);

        return $this;
    }

    public function setSearchParams(
    ):self {
        $parameters = $this->params;

        $this->request->setQuery($$this->externalSearch['searchString']);
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

        if ($this->isTagPage) {
            $this->request->addIndividualParam('selected', ['cat_id' => [$this->tagId]], Request::SET_VALUE);
        }

        if ($this->category && ($categoryFullName = $this->categoryName)) {
            $this->request->addIndividualParam('selected', ['cat' => [$categoryFullName]], Request::SET_VALUE);
        }

        if ($$this->externalSearch['sorting'] !== 'item.score' &&
            in_array($$this->externalSearch['sorting'], Plugin::API_SORT_ORDER_AVAILABLE_OPTIONS)
        ) {
            $this->request->setOrder(self::SORT_MAPPING[$$this->externalSearch['sorting']]);
        }

        $this->setPagination($parameters);

        return $this;
    }

    protected function setPagination(array $parameters):void
    {
        if ($$this->externalSearch['categoryId'] !== null &&
            !array_key_exists(Plugin::API_PARAMETER_ATTRIBUTES, $parameters)
        ) {
            $this->request->setFirst(0);
            $this->request->setCount(0);
            return;
        }

        $this->request->setCount($$this->externalSearch['itemsPerPage']);

        if ($$this->externalSearch['page'] > 1) {
            $this->request->setFirst(($$this->externalSearch['page'] - 1) * $$this->externalSearch['itemsPerPage']);
        }
    }

}