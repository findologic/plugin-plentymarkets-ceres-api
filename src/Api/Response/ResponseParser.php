<?php

namespace Findologic\Api\Response;

use Exception;
use Findologic\Api\Response\Parser\FiltersParser;
use Findologic\Constants\Plugin;
use Findologic\Services\SearchService;
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

        // if (!is_string($responseData) || $responseData === '') {
        //     $msg = sprintf(
        //         'Still invalid response after %d retries. Using Plentymarkets SDK results without Findologic.',
        //         SearchService::MAX_RETRIES
        //     );
        //     $this->logger->error($msg, ['response' => $responseData]);

        //     return $response;
        // }


        try {
            $data = json_decode($responseData);
            $response->setData(Response::DATA_LANDING_PAGE, $this->parseLandingPage($data->result));
            // $response->setData(Response::DATA_SERVERS, $this->parseServers($data->result));
            $response->setData(Response::DATA_QUERY, $this->parseQuery($data->request));
            $response->setData(Response::DATA_PROMOTION, $this->parsePromotion($data->result));
            $response->setData(Response::DATA_RESULTS, $this->parseResults($data->request));
            $response->setData(Response::DATA_PRODUCTS, $this->parseProducts($data->result));
            // $response->setData(Response::DATA_FILTERS, $this->filtersParser->parse($data->filters));
            // $response->setData(Response::DATA_FILTERS_WIDGETS, $this->filtersParser->parseForWidgets($data->filters));
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

    // /**
    //  * @param SimpleXMLElement $data
    //  * @return array
    //  */
    // protected function parseServers(SimpleXMLElement $data)
    // {
    //     $servers = [];

    //     if (!empty($data->servers)) {
    //         $servers['frontend'] = $data->servers->frontend->__toString();
    //         $servers['backend'] = $data->servers->backend->__toString();
    //     }

    //     return $servers;
    // }

    /**
     * @param object $data
     * @return array
     */
    protected function parseQuery(object $data)
    {
        $query = [];

        if (!empty($data->query)) {
            $query['query'] = $data->query;
            $query['searchedWordCount'] = $data->searchWordCount;
            $query['foundWordCount'] = $data->foundWordCount;

            $query['first'] = $data->first;
            $query['count'] = $data->count;
        }

        return $query;
    }

    /**
     * @param object $data
     * @return string|null
     */
    protected function parseLandingPage(object $data)
    {
        return $data->metadata->landingPage ?: null;
    }

    /**
     * @param object $data
     * @return array
     */
    protected function parsePromotion(object $data)
    {
        $promotion = [];

        if (isset($data->metadata->promotion)) {
            $promotion['image'] = $data->metadata->promotion->imageUrl;
            $promotion['link'] = $data->metadata->promotion->url;
        }

        return $promotion;
    }

    /**
     * @param object $data
     * @return array
     */
    protected function parseResults(object $data)
    {
        $results = [];

        if (isset($data->metadata->totalResults)) {
            $results['count'] = $data->metadata->totalResults;
        }

        return $results;
    }

    /**
     * @param object $data
     * @return array
     */
    protected function parseProducts(object $data)
    {
        return $data->items ?: [];
    }

    protected function parseQueryInfoMessage(HttpRequest $request, object $data): array
    {
        if (empty($data->request->query)) {
            return [];
        }

        $originalQuery = $data->request->query ?: null;
        $didYouMeanQuery = $data->result->variant->didYouMeanQuery ?: null;
        $currentQuery = $data->result->metadata->effectiveQuery ?: null;
        // $queryStringType = isset($data->query->queryString->attributes()->type)
        //     ? $data->query->queryString->attributes()->type->__toString()
        //     : null;

        $requestParams = (array) $request->all();

        return [
            'originalQuery' => $originalQuery,
            'didYouMeanQuery' => $didYouMeanQuery,
            'currentQuery' => $currentQuery,
            'queryStringType' => null,
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
