<?php

namespace Findologic\Api\Response;

use Exception;
use Plenty\Plugin\Log\Loggable;
use Findologic\Constants\Plugin;
use Findologic\Struct\Promotion;
use Findologic\Struct\LandingPage;
use Plenty\Plugin\Log\LoggerFactory;
use Findologic\Struct\SmartDidYouMean;
use Findologic\Struct\FiltersExtension;
use Findologic\Api\Response\Result\Item;
use Plenty\Log\Contracts\LoggerContract;
use Findologic\FinSearch\Struct\Pagination;
use Symfony\Component\HttpFoundation\Request;
use Plenty\Plugin\Http\Request as HttpRequest;
use Findologic\Api\Response\Json10\Filter\Filter;
use Findologic\Struct\QueryInfoMessage\QueryInfoMessage;
use Findologic\Struct\QueryInfoMessage\QueryInfoMessageFactory;

/**
 * Class ResponseParser
 * @package Findologic\Api\Response
 */
class ResponseParser
{
    use Loggable;

    protected Response $response;

    protected LoggerContract $logger;


    protected HttpRequest $request;

    public function __construct(
        
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
    }

    public function parseQuery() :array
    {
        $query = [];

        if ($this->response->getRequest()->getQuery()) {
            $query['query'] = $this->response->getRequest()->getQuery();
            $query['first'] = $this->response->getRequest()->getFirst();
            $query['count'] = $this->response->getRequest()->getCount();
        }

        return $query;
    }

    public function getLandingPageExtension(): ?LandingPage
    {
        return $this->response->getResult()->getMetadata()->getLandingPage();
    }

    public function getPromotionExtension(): ?Promotion
    {
        return $this->response->getResult()->getMetadata()->getPromotion();
    }

    public function parseTotalResults() :int
    {
        return $this->response->getResult()->getMetadata()->getTotalResults() ?: 0;
    }

    public function getProductIds() :array
    {
        return array_map(
            function (Item $product) {
                if (count($product->getVariants())) {
                    return  $product->getVariants()[0]->getId();
                } 
                else if(array_key_exists('variation_id', $product->getProperties())){
                    return $product->getProperties()['variation_id'];
                }
                else {
                    return $product->getId();
                }
            },
            $this->response->getResult()->getItems()
        );
    }

    public function getFiltersExtension(): FiltersExtension
    {
        $mainFilters = $this->response->getResult()->getMainFilters();
        $otherFilters = $this->response->getResult()->getOtherFilters();
        $filtersExtension = pluginApp(FiltersExtension::class);
        foreach ($mainFilters as $mainFilter) {
            $filter = Filter::getInstance($mainFilter, true);
            if ($filter && count($filter->getValues()) >= 1) {
                $filtersExtension->addFilter($filter);
            }
        }

        foreach ($otherFilters as $otherFilter) {
            $filter = Filter::getInstance($otherFilter, false);
            if ($filter && count($filter->getValues()) >= 1) {
                $filtersExtension->addFilter($filter);
            }
        }

        return $filtersExtension;
    }

    public function getPaginationExtension(?int $limit, ?int $offset): Pagination
    {
        return pluginApp(Pagination::class, [$limit, $offset, $this->response->getResult()->getMetadata()->getTotalResults()]);
    }

    public function getQueryInfoMessage(): QueryInfoMessage
    {
        $queryString = $this->response->getRequest()->getQuery() ?? '';
        $params = (array) $this->request->all();
        $count = $this->parseTotalResults();
        $queryInfoMessageFactory = pluginApp(QueryInfoMessageFactory::class, [$this->response, $queryString, $count]);
        
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
        return pluginApp(SmartDidYouMean::class,[
            $this->response->getRequest()->getQuery(),
            $this->response->getResult()->getMetadata()->getEffectiveQuery(),
            $this->response->getResult()->getVariant()->getCorrectedQuery(),
            $this->response->getResult()->getVariant()->getDidYouMeanQuery(),
            $this->response->getResult()->getVariant()->getImprovedQuery(),
            $this->request->getRequestUri()]
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
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Set the value of response
     *
     * @return  self
     */ 
    public function setResponse(array $response)
    {
        if($response) $this->response = pluginApp(Response::class, [$response['response']]);

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
