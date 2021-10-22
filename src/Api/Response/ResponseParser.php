<?php

namespace Findologic\Api\Response;

use Exception;
use Findologic\Api\Response\Parser\FiltersParser;
use Findologic\Constants\Plugin;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Plugin\Log\LoggerFactory;
use SimpleXMLElement;
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
     * @var LoggerContract
     */
    protected $logger;

    public function __construct(
        FiltersParser $filtersParser,
        LoggerFactory $loggerFactory
    ) {
        $this->filtersParser = $filtersParser;
        $this->logger = $loggerFactory->getLogger(Plugin::PLUGIN_NAMESPACE, Plugin::PLUGIN_IDENTIFIER);
    }

    public function parse(HttpRequest $request, $responseData): Response
    {
        /** @var Response $response */
        $response = $this->createResponseObject();

        if (!is_string($responseData)) {
            $this->logger->error('Invalid response received from server.', ['response' => $responseData]);

            return $response;
        }

        try {
            $data = $this->loadXml($responseData);

            $response->setData(Response::DATA_LANDING_PAGE, $this->parseLandingPage($data));
            $response->setData(Response::DATA_SERVERS, $this->parseServers($data));
            $response->setData(Response::DATA_QUERY, $this->parseQuery($data));
            $response->setData(Response::DATA_PROMOTION, $this->parsePromotion($data));
            $response->setData(Response::DATA_RESULTS, $this->parseResults($data));
            $response->setData(Response::DATA_PRODUCTS, $this->parseProducts($data));
            $response->setData(Response::DATA_FILTERS, $this->filtersParser->parse($data->filters));
            $response->setData(Response::DATA_FILTERS_WIDGETS, $this->filtersParser->parseForWidgets($data->filters));
            $response->setData(Response::DATA_QUERY_INFO_MESSAGE, $this->parseQueryInfoMessage($request, $data));
        } catch (Exception $e) {
            $this->logger->warning('Could not parse response from server.');
            throw $e;
        }

        return $response;
    }

    /**
     * @param string $xmlString
     * @return SimpleXMLElement
     * @throws Exception
     */
    public function loadXml($xmlString = '')
    {
        $parsedXml = simplexml_load_string($xmlString);
        if (!$parsedXml) {
            throw new Exception('Error while parsing xmlString to xmlElement');
        }

        return $parsedXml;
    }

    /**
     * @return Response
     */
    public function createResponseObject()
    {
        return pluginApp(Response::class);
    }

    /**
     * @param SimpleXMLElement $data
     * @return array
     */
    protected function parseServers(SimpleXMLElement $data)
    {
        $servers = [];

        if (!empty($data->servers)) {
            $servers['frontend'] = $data->servers->frontend->__toString();
            $servers['backend'] = $data->servers->backend->__toString();
        }

        return $servers;
    }

    /**
     * @param SimpleXMLElement $data
     * @return array
     */
    protected function parseQuery(SimpleXMLElement $data)
    {
        $query = [];

        if (!empty($data->query)) {
            $query['query'] = $data->query->queryString->__toString();
            $query['searchedWordCount'] = $data->query->searchWordCount->__toString();
            $query['foundWordCount'] = $data->query->foundWordCount->__toString();

            $query['first'] = $data->query->limit['first']->__toString();
            $query['count'] = $data->query->limit['count']->__toString();
        }

        return $query;
    }

    /**
     * @param SimpleXMLElement $data
     * @return string|null
     */
    protected function parseLandingPage(SimpleXMLElement $data)
    {
        if (!isset($data->landingPage)
            || empty($data->landingPage->attributes())
            || !isset($data->landingPage->attributes()->link)
        ) {
            return null;
        }

        return $data->landingPage->attributes()->link->__toString();
    }

    /**
     * @param SimpleXMLElement $data
     * @return array
     */
    protected function parsePromotion(SimpleXMLElement $data)
    {
        $promotion = [];

        if (isset($data->promotion) && !empty($data->promotion->attributes())) {
            $promotion['image'] = $data->promotion->attributes()->image->__toString();
            $promotion['link'] = $data->promotion->attributes()->link->__toString();
        }

        return $promotion;
    }

    /**
     * @param SimpleXMLElement $data
     * @return array
     */
    protected function parseResults(SimpleXMLElement $data)
    {
        $results = [];

        if (!empty($data->results)) {
            $results['count'] = $data->results->count->__toString();
        }

        return $results;
    }

    /**
     * @param SimpleXMLElement $data
     * @return array
     */
    protected function parseProducts(SimpleXMLElement $data)
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

    protected function parseQueryInfoMessage(HttpRequest $request, SimpleXMLElement $data): array
    {
        if (empty($data->query)) {
            return [];
        }

        $originalQuery = isset($data->query->originalQuery) ? $data->query->originalQuery->__toString() : null;
        $didYouMeanQuery = isset($data->query->didYouMeanQuery) ? $data->query->didYouMeanQuery->__toString() : null;
        $currentQuery = isset($data->query->queryString) ? $data->query->queryString->__toString() : null;
        $queryStringType = isset($data->query->queryString->attributes()->type)
            ? $data->query->queryString->attributes()->type->__toString()
            : null;

        $requestParams = (array) $request->all();

        return [
            'originalQuery' => $originalQuery,
            'didYouMeanQuery' => $didYouMeanQuery,
            'currentQuery' => $currentQuery,
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
