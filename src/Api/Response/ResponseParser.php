<?php

namespace Findologic\Api\Response;

use Exception;
use SimpleXMLElement;
use Findologic\Constants\Plugin;
use Plenty\Plugin\Log\LoggerFactory;
use Findologic\Services\SearchService;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Plugin\Http\Request as HttpRequest;
use Findologic\Api\Response\Parser\FiltersParser;

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

        if (!is_string($responseData) || $responseData === '') {
            $msg = sprintf(
                'Still invalid response after %d retries. Using Plentymarkets SDK results without Findologic.',
                SearchService::MAX_RETRIES
            );
            $this->logger->error($msg, ['response' => $responseData]);

            return $response;
        }

        try {
            $data = json_decode($responseData, true);

            $response->setData(Response::DATA_LANDING_PAGE, $this->parseLandingPage($data['result']));
            $response->setData(Response::DATA_SERVERS, []);
            $response->setData(Response::DATA_QUERY, $this->parseQuery($data['request']));
            $response->setData(Response::DATA_PROMOTION, $this->parsePromotion($data['result']));
            $response->setData(Response::DATA_RESULTS, $this->parseResults($data['result']['metadata']));
            $response->setData(Response::DATA_PRODUCTS, $this->parseProducts($data['result']));
            $response->setData(Response::DATA_FILTERS, $this->filtersParser->parse($data['result']['filters']));
            $response->setData(Response::DATA_FILTERS_WIDGETS, $this->filtersParser->parseForWidgets($data['result']['filters']));
            $response->setData(Response::DATA_QUERY_INFO_MESSAGE, $this->parseQueryInfoMessage($request, $data));
            
        } catch (Exception $e) {
            $this->logger->error('Parsing JSON failed', ['jsonString' => $responseData]);
            $this->logger->logException($e);
        }

        return $response;
    }

    public function createResponseObject(): Response
    {
        return pluginApp(Response::class);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function parseQuery(array $data): array
    {
        $query = [];

        if (!empty($data['query'])) {
            $query['query'] = $data['query'];

            $query['first'] = $data['first'];
            $query['count'] = $data['count'];
        }

        return $query;
    }

    /**
     * @param array $data
     * @return string|null
     */
    protected function parseLandingPage(array $data): ?string
    {
        return $data['metadata']['landingpage'] ?: null;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function parsePromotion(array $data): array
    {
        $promotion = [];

        if (isset($data['metadata']['promotion'])) {
            $promotion['image'] = $data['metadata']['promotion']['imageUrl'];
            $promotion['link'] = $data['metadata']['promotion']['url'];
        }

        return $promotion;
    }

    /**
     * @param array $metadata
     * @return array
     */
    protected function parseResults(array $metadata): array
    {
        $results = [];

        if (isset($metadata['totalResults'])) {
            $results['count'] = $metadata['totalResults'];
        }

        return $results;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function parseProducts(array $data): array
    {
        return $data['items'] ?: [];
    }

    protected function parseQueryInfoMessage(HttpRequest $request, array $data): array
    {
        @list('didYouMeanQuery' => $didYouMeanQuery, 'improvedQuery' => $improvedQuery, 'correctedQuery' => $correctedQuery) = $data['result']['variant'];
        
        $currentQuery = $data['result']['metadata']['effectiveQuery'] ?: null;

        $queryStringType = null;

        if($improvedQuery){
            $queryStringType = 'improved';
            $currentQuery = $improvedQuery;
            $originalQuery = $data['request']['query'];
        }
        else if($correctedQuery){
            $queryStringType = 'corrected';
            $currentQuery = $correctedQuery;
            $originalQuery = $data['request']['query'];
        }

        $requestParams = (array) $request->all();

        return [
            'originalQuery' => @$originalQuery,
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
    private function getSelectedCategoryName(array $requestParams): ?string
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
    private function getSelectedVendorName(array $requestParams): ?string
    {
        return $requestParams['attrib']['vendor'][0] ?? null;
    }

    /**
     * @param array $requestParams
     * @return string|null
     */
    private function getShoppingGuide(array $requestParams): ?string
    {
        return $requestParams['attrib']['wizard'][0] ?? null;
    }
}
