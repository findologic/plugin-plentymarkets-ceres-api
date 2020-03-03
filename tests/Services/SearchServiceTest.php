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
        $itemSearchServiceResultsAll,
        $itemSearchResultsOneProduct,
        $shopUrl,
        $dataQueryInfoMessage,
        $redirectUrl,
        $attributes
    ) {
        /** @var Request|HttpRequest|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->requestBuilder->expects($this->any())->method('build')->willReturn($requestMock);
        if ($redirectUrl) {
            $this->requestBuilder->expects($this->once())->method('getShopUrl')->willReturn($shopUrl);
        } else {
            $this->requestBuilder->expects($this->never())->method('getShopUrl');
        }
        $this->client->expects($this->any())->method('call')->willReturn(Plugin::API_ALIVE_RESPONSE_BODY);

        $responseMock = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $responseMock->expects($this->once())->method('getVariationIds')->willReturn([1, 2, 3]);
        if ($redirectUrl) {
            $responseMock->expects($this->once())->method('getData')->with(Response::DATA_QUERY_INFO_MESSAGE)->willReturn($dataQueryInfoMessage);
        }
        $this->responseParser->expects($this->once())->method('parse')->willReturn($responseMock);

        $itemSearchServiceMock = $this->getMockBuilder(ItemSearchService::class)->disableOriginalConstructor()->setMethods(['getResults'])->getMock();
        $itemSearchServiceMock->expects($this->at(0))->method('getResults')->willReturn($itemSearchServiceResultsAll);
        if ($redirectUrl) {
            $itemSearchServiceMock->expects($this->at(1))->method('getResults')->willReturn($itemSearchResultsOneProduct);
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
                [
                    1011 => [
                        'total' => 1
                    ]
                ],
                [
                    1011 => [
                        'documents' => [
                            [
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
                    ]
                ],
                'https://www.test.com',
                [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'https://www.test.com/test-product_11_1011',
                []
            ],
            'Multiple products found' => [
                [
                    1011 => [
                        'total' => 1
                    ],
                    1022 => [
                        'total' => 2
                    ]
                ],
                [
                    1011 => [
                        'documents' => [
                            [
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
                    ]
                ],
                'https://www.test.com',
                [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                null,
                []
            ],
            'One product found and query string type is corrected' => [
                [
                    1011 => [
                        'total' => 1
                    ]
                ],
                [
                    1011 => [
                        'documents' => [
                            [
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
                    ]
                ],
                'https://www.test.com',
                [
                    'queryStringType' => 'corrected'
                ],
                null,
                []
            ],
            'One product found and query string type is improved' => [
                [
                    1011 => [
                        'total' => 1
                    ]
                ],
                [
                    1011 => [
                        'documents' => [
                            [
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
                    ]
                ],
                'https://www.test.com',
                [
                    'queryStringType' => 'improved'
                ],
                null,
                []
            ],
            'One product found but filters are set' => [
                [
                    1011 => [
                        'total' => 1
                    ]
                ],
                [
                    1011 => [
                        'documents' => [
                            [
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
                    ]
                ],
                'https://www.test.com',
                [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                null,
                [
                    'attrib' => [
                        'cat' => 'Blubbergurken'
                    ]
                ]
            ],
        ];
    }
}
