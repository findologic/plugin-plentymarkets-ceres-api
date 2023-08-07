<?php

namespace Findologic\Api\Response;

use Exception;
use FINDOLOGIC\Api\Responses\Response as ApiResponse;
use FINDOLOGIC\Api\Responses\Json10\Json10Response;
use Findologic\Api\Response\Parser\FiltersParser;
use Findologic\Constants\Plugin;
use Findologic\Services\SearchService;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Plugin\Log\LoggerFactory;
use Plenty\Plugin\Http\Request as HttpRequest;

/**
 * Class ResponseParser
 * @package Findologic\Api\Response
 */
class ResponseParser
{
    /**
     * @var FiltersParser
     */
    protected $filtersParser;

    /**
     * @var Json10Response
     */
    private $response;

    /**
     * @var LoggerContract
     */
    protected $logger;

    public function __construct(
        FiltersParser $filtersParser,
        LoggerFactory $loggerFactory,
        Json10Response $response
    ) {
        $this->filtersParser = $filtersParser;
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
        $this->response = $response;
    }

    /**
     * @return array
     */
    protected function parseQuery()
    {
        $query = [];

        if ($this->response->getRequest()->getQuery()) {
            $query['query'] = $this->response->getRequest()->getQuery();
            $query['searchedWordCount'] = $data->query->searchWordCount->__toString();
            $query['foundWordCount'] = $data->query->foundWordCount->__toString();

            $query['first'] = $this->response->getRequest()->getFirst();
            $query['count'] = $this->response->getRequest()->getCount();
        }

        return $query;
    }

    /**
     * @return string|null
     */
    protected function parseLandingPage()
    {
        $landingPage = $this->response->getResult()->getMetadata()->getLandingPage();
        return $landingPage ? $landingPage->getUrl() : null;
    }

    /**
     * @return array
     */
    protected function parsePromotion()
    {
        $promotion = [];
        $responsePromotion = $this->response->getResult()->getMetadata()->getPromotion();

        if ($responsePromotion) {
            $promotion['image'] = $this->response->getResult()->getMetadata()->getPromotion()->getImageUrl();
            $promotion['link'] = $this->response->getResult()->getMetadata()->getPromotion()->getUrl();
        }

        return $promotion;
    }

    /**
     * @return int
     */
    protected function parseTotalResults()
    {
        return $this->response->getResult()->getMetadata()->getTotalResults();
    }

    /**
     * @return array
     */
    protected function parseProducts()
    {
        $products = [];

        if (!empty($data->products)) {
            foreach ($data->products->product as $product) {
                $productData = [
                    'id' => $product['id']->__toString(),
                    'relevance' => $product['relevance']->__toString(),
                ];

                foreach ($product->properties->property as $property) {
                    $productData['properties'][$property['name']->__toString()] = $property->__toString();
                }

                $products[] = $productData;
            }
        }

        return $products;
    }

    protected function parseQueryInfoMessage(HttpRequest $request): array
    {

        // Not sure about this one, fix after
        $queryStringType = isset($data->query->queryString->attributes()->type)
            ? $data->query->queryString->attributes()->type->__toString()
            : null;

        $requestParams = (array) $request->all();

        return [
            'originalQuery' => $this->response->getRequest()->getQuery(),
            'didYouMeanQuery' =>$this->response->getResult()->getVariant()->getDidYouMeanQuery(),
            'currentQuery' => $this->response->getResult()->getMetadata()->getEffectiveQuery() ,
            'queryStringType' => $queryStringType,
            'selectedCategoryName' => $this->getSelectedCategoryName($requestParams),
            'selectedVendorName' => $this->getSelectedVendorName($requestParams),
            'shoppingGuide' => $this->getShoppingGuide($requestParams)
        ];
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
}
