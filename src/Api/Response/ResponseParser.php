<?php

namespace Findologic\Api\Response;

use Findologic\Api\Response\Parser\FiltersParser;
use Findologic\Constants\Plugin;
use Plenty\Plugin\Log\Loggable;
use Plenty\Log\Contracts\LoggerContract;

/**
 * Class ResponseParser
 * @package Findologic\Api\Response
 */
class ResponseParser
{
    use Loggable;

    /**
     * @var FiltersParser
     */
    protected $filtersParser;

    /**
     * @var LoggerContract
     */
    protected $logger;

    public function __construct(FiltersParser $filtersParser)
    {
        $this->filtersParser = $filtersParser;
        $this->logger = $this->getLogger(Plugin::PLUGIN_IDENTIFIER);
    }

    /**
     * @param string $responseData
     * @return Response
     */
    public function parse($responseData)
    {
        /**
         * @var Response $response
         */
        $response = pluginApp(Response::class);

        try {
            //TODO: remove after testing
            $this->logger->error('Findologic response', $responseData);

            $data = $this->loadXml($responseData);
            $response->setData(Response::DATA_SERVERS, $this->parseServers($data));
            $response->setData(Response::DATA_QUERY, $this->parseQuery($data));
            $response->setData(Response::DATA_LANDING_PAGE, $this->parseLandingPage($data));
            $response->setData(Response::DATA_PROMOTION, $this->parsePromotion($data));
            $response->setData(Response::DATA_RESULTS, $this->parseResults($data));
            $response->setData(Response::DATA_PRODUCTS, $this->parseProducts($data));
            $response->setData(Response::DATA_FILTERS, $this->filtersParser->parse($data));
        } catch (\Exception $e) {
            $this->logger->warning('Could not parse response from server.');
            $this->logger->logException($e);
        }

        return $response;
    }

    /**
     * @param $xmlString
     * @return \SimpleXMLElement
     */
    public function loadXml($xmlString = '')
    {
        return simplexml_load_string($xmlString);
    }

    /**
     * @param \SimpleXMLElement $data
     * @return array
     */
    protected function parseServers(\SimpleXMLElement $data)
    {
        $servers = [];

        if (!empty($data->servers)) {
            $servers['frontend'] = $data->servers->frontend->__toString();
            $servers['backend'] = $data->servers->backend->__toString();
        }

        return $servers;
    }

    /**
     * @param \SimpleXMLElement $data
     * @return array
     */
    protected function parseQuery(\SimpleXMLElement $data)
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
     * @param \SimpleXMLElement $data
     * @return array
     */
    protected function parseLandingPage(\SimpleXMLElement $data)
    {
        $landingPage = [];

        if (!empty($data->landingPage) ) {
            $landingPage['link'] = $data->landingPage->attributes()->link->__toString();
        }

        return $landingPage;
    }

    /**
     * @param \SimpleXMLElement $data
     * @return array
     */
    protected function parsePromotion(\SimpleXMLElement $data)
    {
        $promotion = [];

        if (!empty($data->promotion) ) {
            $promotion['image'] = $data->promotion->attributes()->image->__toString();
            $promotion['link'] = $data->promotion->attributes()->link->__toString();
        }

        return $promotion;
    }

    /**
     * @param \SimpleXMLElement $data
     * @return array
     */
    protected function parseResults(\SimpleXMLElement $data)
    {
        $results = [];

        if (!empty($data->results) ) {
            $results['count'] = $data->results->count->__toString();
        }

        return $results;
    }

    /**
     * @param \SimpleXMLElement $data
     * @return array
     */
    protected function parseProducts(\SimpleXMLElement $data)
    {
        $products = [];

        if (!empty($data->products) ) {
            foreach ($data->products->product as $product) {
                $productData = [
                    'id' => $product['id']->__toString(),
                    'relevance' => $product['relevance']->__toString(),
                    'direct' => $product['direct']->__toString(),
                ];

                foreach ($product->properties->property as $property) {
                    $productData['properties'][$property['name']->__toString()] = $property->__toString();
                }

                $products[] = $productData;
            }
        }

        return $products;
    }
}