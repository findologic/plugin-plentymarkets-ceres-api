<?php

namespace Findologic\Api\Response;

use Exception;
use Findologic\Constants\Plugin;
use FINDOLOGIC\Struct\Promotion;
use FINDOLOGIC\Struct\LandingPage;
use Plenty\Plugin\Log\LoggerFactory;
use Findologic\Services\SearchService;
use Findologic\Components\PluginConfig;
use FINDOLOGIC\Struct\FiltersExtension;
use Plenty\Log\Contracts\LoggerContract;
use FINDOLOGIC\Components\SmartDidYouMean;
use FINDOLOGIC\FinSearch\Struct\Pagination;
use Symfony\Component\HttpFoundation\Request;
use Plenty\Plugin\Http\Request as HttpRequest;
use Findologic\Api\Response\Parser\FiltersParser;
use FINDOLOGIC\Api\Responses\Json10\Json10Response;
use FINDOLOGIC\Api\Responses\Json10\Properties\Item;
use FINDOLOGIC\Struct\QueryInfoMessage\QueryInfoMessage;
use FINDOLOGIC\Api\Responses\Json10\Properties\Filter\Filter;
use FINDOLOGIC\Struct\QueryInfoMessage\QueryInfoMessageFactory;
use FINDOLOGIC\Api\Responses\Json10\Properties\Promotion as ApiPromotion;

/**
 * Class ResponseParser
 * @package Findologic\Api\Response
 */
class ResponseParser
{
    protected FiltersParser $filtersParser;

    protected Json10Response $response;

    protected LoggerContract $logger;

    protected PluginConfig $pluginConfig;

    protected HttpRequest $request;

    public function __construct(
        FiltersParser $filtersParser,
        LoggerFactory $loggerFactory,
        PluginConfig $pluginConfig
    ) {
        $this->filtersParser = $filtersParser;
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
        $this->pluginConfig = $pluginConfig;
    }

    public function parseQuery() :array
    {
        $query = [];

        if ($this->response->getRequest()->getQuery()) {
            $query['query'] = $this->response->getRequest()->getQuery();
            // $query['searchedWordCount'] = $data->query->searchWordCount->__toString();
            // $query['foundWordCount'] = $data->query->foundWordCount->__toString();

            $query['first'] = $this->response->getRequest()->getFirst();
            $query['count'] = $this->response->getRequest()->getCount();
        }

        return $query;
    }

    public function getLandingPageExtension(): ?LandingPage
    {
        $landingPage = $this->response->getResult()->getMetadata()->getLandingPage();
        if ($landingPage instanceof LandingPage) {
            return new LandingPage($landingPage->getUrl());
        }

        return null;
    }

    public function getPromotionExtension(): ?Promotion
    {
        $promotion = $this->response->getResult()->getMetadata()->getPromotion();

        if ($promotion instanceof ApiPromotion) {
            return new Promotion($promotion->getImageUrl(), $promotion->getUrl());
        }

        return null;
    }

    public function parseTotalResults() :int
    {
        return $this->response->getResult()->getMetadata()->getTotalResults();
    }

    public function getProductIds() :array
    {
        return array_map(
            function (Item $product) {
                if ($this->pluginConfig->get(Plugin::CONFIG_USE_VARIANTS)) {
                    return count($product->getVariants()) ? $product->getVariants()[0]->getId() : $product->getId();
                } else {
                    return $product->getId();
                }
            },
            $this->response->getResult()->getItems()
        );
    }

    public function getFiltersExtension(): FiltersExtension
    {
        $apiFilters = array_merge(
            $this->response->getResult()->getMainFilters() ?? [],
            $this->response->getResult()->getOtherFilters() ?? []
        );

        $filtersExtension = new FiltersExtension();
        foreach ($apiFilters as $apiFilter) {
            $filter = Filter::getInstance($apiFilter);

            if ($filter && count($filter->getValues()) >= 1) {
                $filtersExtension->addFilter($filter);
            }
        }

        return $filtersExtension;
    }

    public function getPaginationExtension(?int $limit, ?int $offset): Pagination
    {
        return new Pagination($limit, $offset, $this->response->getResult()->getMetadata()->getTotalResults());
    }

    public function getQueryInfoMessage(): QueryInfoMessage
    {
        $queryString = $this->response->getRequest()->getQuery() ?? '';
        $params = (array) $this->request->all();
        $queryInfoMessageFactory = new QueryInfoMessageFactory($this->response, $queryString);
        
        return $queryInfoMessageFactory->getQueryInfoMessage($params);
    }

    protected function parseQueryInfoMessage(HttpRequest $request): array
    {

        // Not sure about this one, fix after
        // $queryStringType = isset($data->query->queryString->attributes()->type)
        //     ? $data->query->queryString->attributes()->type->__toString()
        //     : null;

        $requestParams = (array) $request->all();

        return [
            'originalQuery' => $this->response->getRequest()->getQuery(),
            'didYouMeanQuery' =>$this->response->getResult()->getVariant()->getDidYouMeanQuery(),
            'currentQuery' => $this->response->getResult()->getMetadata()->getEffectiveQuery() ,
            'queryStringType' => '',//$queryStringType,
            'selectedCategoryName' => $this->getSelectedCategoryName($requestParams),
            'selectedVendorName' => $this->getSelectedVendorName($requestParams),
            'shoppingGuide' => $this->getShoppingGuide($requestParams)
        ];
    }

    public function getSmartDidYouMeanExtension(): SmartDidYouMean
    {
        return new SmartDidYouMean(
            $this->response->getRequest()->getQuery(),
            $this->response->getResult()->getMetadata()->getEffectiveQuery(),
            $this->response->getResult()->getVariant()->getCorrectedQuery(),
            $this->response->getResult()->getVariant()->getDidYouMeanQuery(),
            $this->response->getResult()->getVariant()->getImprovedQuery(),
            $this->request->getRequestUri()
        );
    }

    /**
     * @param array $requestParams
     * @return string|null
     */
    private function getSelectedCategoryName(array $requestParams)
    {
        $selectedCategory = $requestParams['attrib']['cat'][0] ?? null;

        if (strpos($selectedCategory, '_') !== false) {
            $categories = explode('_', $selectedCategory);

            $selectedCategory = end($categories);
        }

        return $selectedCategory;
    }

    /**
     * @param array $requestParams
     * @return string|null
     */
    private function getSelectedVendorName(array $requestParams)
    {
        return $requestParams['attrib']['vendor'][0] ?? null;
    }

    /**
     * @param array $requestParams
     * @return string|null
     */
    private function getShoppingGuide(array $requestParams)
    {
        return $requestParams['attrib']['wizard'][0] ?? null;
    }

    /**
     * Get the value of response
     */
    public function getResponse(): Json10Response
    {
        return $this->response;
    }

    /**
     * Set the value of response
     *
     * @return  self
     */ 
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Set the value of request
     *
     * @return  self
     */ 
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }
}
