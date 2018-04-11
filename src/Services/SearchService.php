<?php

namespace Findologic\Services;

use Findologic\Api\Request\RequestBuilder;
use Findologic\Api\Response\Response;
use Findologic\Api\Response\ResponseParser;
use Findologic\Api\Client;
use Findologic\Constants\Plugin;
use Findologic\Exception\AliveException;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Plugin\Log\Loggable;
use Plenty\Log\Contracts\LoggerContract;

/**
 * Class SearchService
 * @package Findologic\Services
 */
class SearchService implements SearchServiceInterface
{
    use Loggable;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var RequestBuilder
     */
    protected $requestBuilder;

    /**
     * @var ResponseParser
     */
    protected $responseParser;

    /**
     * @var LoggerContract
     */
    protected $logger;

    protected $results;

    public function __construct(
        Client $client,
        RequestBuilder $requestBuilder,
        ResponseParser $responseParser
    ) {
        $this->client = $client;
        $this->requestBuilder = $requestBuilder;
        $this->responseParser = $responseParser;
        $this->logger = $this->getLogger(Plugin::PLUGIN_IDENTIFIER);
    }

    /**
     * @param $searchQuery
     * @param HttpRequest $request
     */
    public function handleSearchQuery($searchQuery, $request)
    {
        try {
            $results = $this->search($request);
            $productsIds = $results->getProductsIds();

            //TODO: remove, used for testing during development
            if ($request->get('productIds', false)) {
                $productsIds = explode('-', $request->get('productIds'));
            }

            if (!empty($productsIds) && is_array($productsIds)) {
                $searchQuery->setResults($productsIds);
            }

            //TODO: how to handle no results ?
        } catch (\Exception $e) {
            $this->logger->error('Exception while handling search query.');
            $this->logger->logException($e);
        }
    }

    /**
     * @param $searchOptions
     * @param HttpRequest $request
     */
    public function handleSearchOptions($searchOptions, $request)
    {
        try {
            $results = $this->search($request);

            //TODO: set filters
        } catch (\Exception $e) {
            $this->logger->error('Exception while handling search options.');
            $this->logger->logException($e);
        }
    }

    /**
     * @param HttpRequest $request
     * @return \Findologic\Api\Response\Response
     */
    protected function search($request)
    {
        if ($this->results instanceof  Response) {
            return $this->results;
        }

        try {
            $this->aliveTest();

            $apiRequest = $this->requestBuilder->build($request);
            $this->results = $this->responseParser->parse($this->client->call($apiRequest));
        } catch (AliveException $e) {
            $this->logger->error('Findologic server did not responded to alive request. ' . $e->getMessage());
            throw $e;
        }

        return $this->results;
    }

    /**
     * @throws AliveException
     */
    protected function aliveTest()
    {
        $request = $this->requestBuilder->buildAliveRequest();
        $response = $this->client->call($request);

        if ($response !== Plugin::API_ALIVE_RESPONSE_BODY) {
            throw new AliveException('Server is not alive!');
        }
    }
}