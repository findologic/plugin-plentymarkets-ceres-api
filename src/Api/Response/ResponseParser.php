<?php

namespace Findologic\Api\Response;

use Exception;
use Findologic\Constants\Plugin;
use Findologic\Struct\Promotion;
use Findologic\Struct\LandingPage;
use Plenty\Plugin\Log\LoggerFactory;
use Findologic\Struct\SmartDidYouMean;
use Findologic\Components\PluginConfig;
use Findologic\Struct\FiltersExtension;
use Findologic\Api\Response\Result\Item;
use Plenty\Log\Contracts\LoggerContract;
// use Findologic\Api\Response\Parser\FiltersParser;
use Findologic\FinSearch\Struct\Pagination;
use Symfony\Component\HttpFoundation\Request;
use Plenty\Plugin\Http\Request as HttpRequest;
use Findologic\Api\Response\Json10\Filter\Filter;
use Findologic\Struct\QueryInfoMessage\QueryInfoMessage;
use Findologic\Struct\QueryInfoMessage\QueryInfoMessageFactory;
use Plenty\Plugin\Log\Loggable;

/**
 * Class ResponseParser
 * @package Findologic\Api\Response
 */
class ResponseParser
{
    // protected FiltersParser $filtersParser;

    protected Response $response;

    protected LoggerContract $logger;

    protected PluginConfig $pluginConfig;

    protected HttpRequest $request;

    public function __construct(
        
        LoggerFactory $loggerFactory,
        PluginConfig $pluginConfig
    ) {
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
        return $this->response->getResult()->getMetadata()->getLandingPage();
    }

    public function getPromotionExtension(): ?Promotion
    {
        return $this->response->getResult()->getMetadata()->getPromotion();
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

        $filtersExtension = pluginApp(FiltersExtension::class);
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
        return pluginApp(Pagination::class, [$limit, $offset, $this->response->getResult()->getMetadata()->getTotalResults()]);
    }

    public function getQueryInfoMessage(): QueryInfoMessage
    {
        $queryString = $this->response->getRequest()->getQuery() ?? '';
        $params = (array) $this->request->all();
        $queryInfoMessageFactory = pluginApp(QueryInfoMessageFactory::class, [$this->response, $queryString]);
        
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
            $this->logger->error('getSelectedCategoryName', ['explode' => $selectedCategory]);
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
    public function setResponse(?array $response)
    {
        if($response) $this->response = pluginApp(Response::class, $response);
        $filters = $this->getFiltersExtension();
        $filter = $filters->getFilters()[0];
        $this->logger->error('response log', $filter->getName());
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
