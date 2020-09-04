<?php

namespace Findologic\Tests\Services;

use Findologic\Api\Client;
use Findologic\Api\Request\Request;
use Findologic\Api\Request\RequestBuilder;
use Findologic\Api\Response\Response;
use Findologic\Api\Response\ResponseParser;
use Findologic\Constants\Plugin;
use Findologic\Services\FallbackSearchService;
use Findologic\Services\SearchService;
use Findologic\Services\Search\ParametersHandler;
use Ceres\Helper\ExternalSearch;
use Findologic\Tests\Mocks\PlentyRequestMock;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Http\Request as HttpRequest;
use Plenty\Plugin\Log\LoggerFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

/**
 * Class SearchServiceTest
 * @package Findologic\Tests
 */
class SearchServiceTest extends TestCase
{
    /**
     * @var Client|MockObject
     */
    protected $client;

    /**
     * @var RequestBuilder|MockObject
     */
    protected $requestBuilder;

    /**
     * @var ResponseParser|MockObject
     */
    protected $responseParser;

    /**
     * @var ParametersHandler
     */
    protected $searchParametersHandler;

    /**
     * @var LoggerFactory|MockObject
     */
    protected $loggerFactory;

    /**
     * @var LoggerContract|MockObject
     */
    protected $logger;

    /**
     * @var FallbackSearchService|MockObject
     */
    protected $fallbackSearchService;

    /**
     * @var ConfigRepository|MockObject
     */
    protected $configRepository;

    public function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->requestBuilder = $this->getMockBuilder(RequestBuilder::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->responseParser = $this->getMockBuilder(ResponseParser::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->searchParametersHandler = $this->getMockBuilder(ParametersHandler::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->logger = $this->getMockBuilder(LoggerContract::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->loggerFactory = $this->getMockBuilder(LoggerFactory::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->loggerFactory->expects($this->any())->method('getLogger')->willReturn($this->logger);
        $this->fallbackSearchService = $this->getMockBuilder(FallbackSearchService::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->configRepository = $this->getMockBuilder(ConfigRepository::class)->disableOriginalConstructor()->setMethods([])->getMock();
    }

    public function testHandleSearchQuery()
    {
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->requestBuilder->expects($this->any())->method('build')->willReturn($requestMock);
        $this->client->expects($this->any())->method('call')->willReturn(Plugin::API_ALIVE_RESPONSE_BODY);

        $responseMock = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $responseMock->expects($this->once())->method('getVariationIds')->willReturn([1, 2, 3]);
        $this->responseParser->expects($this->once())->method('parse')->willReturn($responseMock);

        $itemSearchServiceMock = $this->getMockBuilder(ItemSearchService::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $searchServiceMock = $this->getSearchServiceMock(['getCategoryService', 'getItemSearchService', 'getSearchFactory']);
        $searchServiceMock->expects($this->once())->method('getItemSearchService')->willReturn($itemSearchServiceMock);

        $searchQueryMock = $this->getMockBuilder(ExternalSearch::class)->disableOriginalConstructor()->setMethods(['setResults'])->getMock();
        $searchQueryMock->categoryId = null;

        $requestMock = $this->getMockBuilder(HttpRequest::class)->disableOriginalConstructor()->setMethods([])->getMock();

        $searchServiceMock->handleSearchQuery($requestMock, $searchQueryMock);
    }

    /**
     * @dataProvider redirectToProductPageOnDoSearchProvider
     * @runInSeparateProcess
     */
    public function testRedirectToProductPageOnDoSearch(
        array $responseVariationIds,
        array $itemSearchServiceResultsAll,
        array $itemSearchResultsOneProduct,
        string $shopUrl,
        array $dataQueryInfoMessage,
        $redirectUrl,
        array $attributes
    ) {
        /** @var Request|HttpRequest|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->requestBuilder->expects($this->any())->method('build')->willReturn($requestMock);
        $this->client->expects($this->any())->method('call')->willReturn(Plugin::API_ALIVE_RESPONSE_BODY);

        $responseMock = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $responseMock->expects($this->once())->method('getVariationIds')->willReturn($responseVariationIds);
        if ($redirectUrl) {
            $responseMock->expects($this->once())->method('getData')->with(Response::DATA_QUERY_INFO_MESSAGE)->willReturn($dataQueryInfoMessage);
        }
        $this->responseParser->expects($this->once())->method('parse')->willReturn($responseMock);

        $itemSearchServiceMock = $this->getMockBuilder(ItemSearchService::class)->disableOriginalConstructor()->setMethods(['getResult'])->getMock();
        $itemSearchServiceMock->expects($this->at(0))->method('getResult')->willReturn($itemSearchServiceResultsAll);
        if ($redirectUrl) {
            $itemSearchServiceMock->expects($this->at(1))->method('getResult')->willReturn($itemSearchResultsOneProduct);
        }

        $searchServiceMock = $this->getSearchServiceMock(['getCategoryService', 'getItemSearchService', 'getSearchFactory', 'handleProductRedirectUrl']);
        $searchServiceMock->expects($this->any())->method('getItemSearchService')->willReturn($itemSearchServiceMock);
        if ($redirectUrl) {
            $searchServiceMock->expects($this->once())->method('handleProductRedirectUrl')->with($redirectUrl);
        } else {
            $searchServiceMock->expects($this->never())->method('handleProductRedirectUrl');
        }

        /** @var ExternalSearch|MockObject $searchQueryMock */
        $searchQueryMock = $this->getMockBuilder(ExternalSearch::class)->disableOriginalConstructor()->setMethods(['setResults'])->getMock();
        $searchQueryMock->categoryId = null;

        /** @var HttpRequest|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(HttpRequest::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $requestMock->expects($this->any())
            ->method('all')
            ->willReturn($attributes);

        $searchServiceMock->doSearch($requestMock, $searchQueryMock);
    }

    /**
     * @param array|null $methods
     * @return SearchService|MockObject
     * @throws ReflectionException
     */
    protected function getSearchServiceMock($methods = null)
    {
        $searchServiceMock = $this->getMockBuilder(SearchService::class)
            ->setConstructorArgs([
                'client' => $this->client,
                'requestBuilder' => $this->requestBuilder,
                'responseParser' => $this->responseParser,
                'searchParametersHandler' => $this->searchParametersHandler,
                'loggerFactory' => $this->loggerFactory,
                'fallbackSearchService' => $this->fallbackSearchService,
                'configRepository' => $this->configRepository
            ])
            ->setMethods($methods);

        return $searchServiceMock->getMock();
    }

    public function redirectToProductPageOnDoSearchProvider(): array
    {
        return [
            'One product found' => [
                'responseVariationIds' => [
                    1011, 1012
                ],
                'itemSearchServiceResultsAll' => [
                    'success' => true,
                    'total' => 1,
                    'documents' => [
                        [
                            'id' => 1011
                        ]
                    ]
                ],
                'itemSearchResultsOneProduct' => [
                    'success' => true,
                    'total' => 1,
                    'documents' => [
                        [
                            'id' => 1011,
                            'data' => [
                                'texts' => [
                                    [
                                        'urlPath' => 'test-product'
                                    ]
                                ],
                                'item' => [
                                    'id' => 11
                                ]
                            ]
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product/a-11',
                'attributes' => []
            ],
            'One product found on second page' => [
                'responseVariationIds' => [
                    1011, 1012
                ],
                'itemSearchServiceResultsAll' => [
                    'success' => true,
                    'total' => 1,
                    'documents' => [
                        [
                            'id' => 1011
                        ]
                    ]
                ],
                'itemSearchResultsOneProduct' => [
                    'success' => true,
                    'total' => 1,
                    'documents' => [
                        [
                            'id' => 1011,
                            'data' => [
                                'texts' => [
                                    [
                                        'urlPath' => 'test-product'
                                    ]
                                ],
                                'item' => [
                                    'id' => 11
                                ]
                            ]
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => null,
                'attributes' => [
                    'page' => 2
                ]
            ],
            'Multiple products found' => [
                'responseVariationIds' => [
                    1011, 1022, 1023
                ],
                'itemSearchServiceResultsAll' => [
                    'success' => true,
                    'total' => 2,
                    'documents' => [
                        [
                            'id' => 1011
                        ],
                        [
                            'id' => 1022
                        ]
                    ]
                ],
                'itemSearchResultsOneProduct' => [
                    'success' => true,
                    'total' => 2,
                    'documents' => [
                        [
                            'id' => 1011,
                            'data' => [
                                'texts' => [
                                    'urlPath' => 'test-product'
                                ],
                                'item' => [
                                    'id' => 11
                                ]
                            ]
                        ],
                        [
                            'id' => 1022,
                            'data' => [
                                'texts' => [
                                    'urlPath' => 'test-product'
                                ],
                                'item' => [
                                    'id' => 11
                                ]
                            ]
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => null,
                'attributes' => []
            ],
            'One product found and query string type is corrected' => [
                'responseVariationIds' => [
                    1011, 1022, 1023
                ],
                'itemSearchServiceResultsAll' => [
                    'success' => true,
                    'total' => 1,
                    'documents' => [
                        [
                            'id' => 1011
                        ]
                    ]
                ],
                'itemSearchResultsOneProduct' => [
                    'success' => true,
                    'total' => 1,
                    'documents' => [
                        [
                            'id' => 1011,
                            'data' => [
                                'texts' => [
                                    'urlPath' => 'test-product'
                                ],
                                'item' => [
                                    'id' => 11
                                ]
                            ]
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'corrected'
                ],
                'redirectUrl' => null,
                'attributes' => []
            ],
            'One product found and query string type is improved' => [
                'responseVariationIds' => [
                    1011, 1022, 1023
                ],
                'itemSearchServiceResultsAll' => [
                    'success' => true,
                    'total' => 1,
                    'documents' => [
                        [
                            'id' => 1011
                        ]
                    ]
                ],
                'itemSearchResultsOneProduct' => [
                    'success' => true,
                    'total' => 1,
                    'documents' => [
                        [
                            'id' => 1011,
                            'data' => [
                                'texts' => [
                                    'urlPath' => 'test-product'
                                ],
                                'item' => [
                                    'id' => 11
                                ]
                            ]
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'improved'
                ],
                'redirectUrl' => null,
                'attributes' => []
            ],
            'One product found but filters are set' => [
                'responseVariationIds' => [
                    1011, 1022, 1023
                ],
                'itemSearchServiceResultsAll' => [
                    'success' => true,
                    'total' => 1,
                    'documents' => [
                        [
                            'id' => 1011
                        ]
                    ]
                ],
                'itemSearchResultsOneProduct' => [
                    'success' => true,
                    'total' => 1,
                    'documents' => [
                        [
                            'id' => 1011,
                            'data' => [
                                'texts' => [
                                    'urlPath' => 'test-product'
                                ],
                                'item' => [
                                    'id' => 11
                                ]
                            ]
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => null,
                'attributes' => [
                    'attrib' => [
                        'cat' => 'Blubbergurken'
                    ]
                ]
            ],
        ];
    }
}
