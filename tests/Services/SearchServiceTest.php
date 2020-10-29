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
use IO\Services\CategoryService;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Modules\Category\Models\Category;
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
     */
    public function testRedirectToProductPageOnDoSearch(
        array $query,
        array $responseVariationIds,
        array $itemSearchServiceResultsAll,
        array $variationSearchByItemIdResult,
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
            $responseMock->expects($this->exactly(2))->method('getData')
                ->withConsecutive([Response::DATA_QUERY_INFO_MESSAGE], [Response::DATA_QUERY])
                ->willReturnOnConsecutiveCalls($dataQueryInfoMessage, $query);
        }
        $this->responseParser->expects($this->once())->method('parse')->willReturn($responseMock);

        $itemSearchServiceMock = $this->getMockBuilder(ItemSearchService::class)->disableOriginalConstructor()->setMethods(['getResult'])->getMock();
        $itemSearchServiceMock->expects($this->at(0))->method('getResult')->willReturn($itemSearchServiceResultsAll);
        if ($redirectUrl) {
            $itemSearchServiceMock->expects($this->at(1))->method('getResult')->willReturn($variationSearchByItemIdResult);
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

    public function categorySearchProvider(): array
    {
        return [
            'Category is ignored when searched and configured category are the same' => [
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
                'attributes' => [],
                'configuredSearchCategory' => 1234,
                'searchedCategory' => 1234,
                'expectedCategory' => null
            ],
            'Category is used if no category is configured' => [
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
                'attributes' => [
                    'attrib' => [
                        'cat' => 'Blubbergurken'
                    ]
                ],
                'configuredSearchCategory' => null,
                'searchedCategory' => 1234,
                'expectedCategory' => 1234
            ],
            'Category is used when another category is configured' => [
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
                'attributes' => [
                    'attrib' => [
                        'cat' => 'Blubbergurken'
                    ]
                ],
                'configuredSearchCategory' => 4321,
                'searchedCategory' => 1234,
                'expectedCategory' => 1234
            ],
        ];
    }

    /**
     * @dataProvider categorySearchProvider
     */
    public function testSearchEndpointIsUsedInCaseCategoryIsTheCeresIoSearchCategory(
        array $responseVariationIds,
        array $itemSearchServiceResultsAll,
        array $attributes,
        int $configuredSearchCategory = null,
        int $searchedCategory = null,
        int $expectedCategory = null
    ) {
        $this->client->expects($this->any())->method('call')->willReturn(Plugin::API_ALIVE_RESPONSE_BODY);

        $responseMock = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->getMock();
        $responseMock->expects($this->once())->method('getVariationIds')->willReturn($responseVariationIds);
        $this->responseParser->expects($this->once())->method('parse')->willReturn($responseMock);

        $itemSearchServiceMock = $this->getMockBuilder(ItemSearchService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemSearchServiceMock->expects($this->at(0))
            ->method('getResult')
            ->willReturn($itemSearchServiceResultsAll);

        $searchServiceMock = $this->getSearchServiceMock([
            'getCategoryService',
            'getItemSearchService',
            'getSearchFactory',
            'handleProductRedirectUrl'
        ]);
        $searchServiceMock->expects($this->any())
            ->method('getItemSearchService')
            ->willReturn($itemSearchServiceMock);

        $this->configRepository->expects($this->once())->method('get')
            ->with('IO.routing.category_search')
            ->willReturn($configuredSearchCategory);

        $categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryMock->id = $searchedCategory;

        $categoryServiceMock = $this->getMockBuilder(CategoryService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $categoryServiceMock->expects($this->any())
            ->method('getCurrentCategory')
            ->willReturn($categoryMock);

        $searchServiceMock->expects($this->any())
            ->method('getCategoryService')
            ->willReturn($categoryServiceMock);

        /** @var ExternalSearch|MockObject $externalSearchMock */
        $externalSearchMock = $this->getMockBuilder(ExternalSearch::class)
            ->disableOriginalConstructor()
            ->setMethods(['setResults'])
            ->getMock();
        $externalSearchMock->categoryId = $searchedCategory;

        $externalSearchMock->expects($this->once())->method('setResults');

        /** @var HttpRequest|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requestMock->expects($this->any())
            ->method('all')
            ->willReturn($attributes);

        /** @var Request|HttpRequest|MockObject $searchRequestMock */
        $searchRequestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Ensure that the given category is null. This way we are sure that the category is being ignored.
        $this->requestBuilder->expects($this->any())->method('build')
            ->with($requestMock, $externalSearchMock, $expectedCategory ? $categoryMock : null)
            ->willReturn($searchRequestMock);

        $searchServiceMock->doSearch($requestMock, $externalSearchMock);
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
                'query' => ['query' => 'this is the text that was searched for'],
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
                'variationSearchByItemIdResult' => [
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
                                ],
                                'variation' => [
                                    'id' => 1011,
                                    'isMain' => true,
                                    'model' => 'model',
                                    'number' => 'number',
                                    'order' => 'order'
                                ]
                            ]
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11_1011',
                'attributes' => []
            ],
            'One product found on first page should redirect to product detail' => [
                'query' => ['query' => 'this is the text that was searched for'],
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
                'variationSearchByItemIdResult' => [
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
                                ],
                                'variation' => [
                                    'id' => 1011,
                                    'isMain' => true,
                                    'model' => 'model',
                                    'number' => 'number',
                                    'order' => 'order'
                                ]
                            ]
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11_1011',
                'attributes' => [
                    'page' => 1
                ]
            ],
            'One product with multiple variations redirects to variation with a model matching the query' => [
                'query' => ['query' => 'this is the text that was searched for'],
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
                'variationSearchByItemIdResult' => [
                    'success' => true,
                    'total' => 2,
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
                                ],
                                'variation' => [
                                    'id' => 1011,
                                    'isMain' => true,
                                    'model' => 'model',
                                    'number' => 'number',
                                    'order' => 'order'
                                ]
                            ]
                        ],
                        [
                            'id' => 1012,
                            'data' => [
                                'texts' => [
                                    [
                                        'urlPath' => 'test-product'
                                    ]
                                ],
                                'item' => [
                                    'id' => 11
                                ],
                                'variation' => [
                                    'id' => 1012,
                                    'isMain' => false,
                                    'model' => 'this is the text that was searched for',
                                    'number' => 'number',
                                    'order' => 'order'
                                ]
                            ]
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11_1012',
                'attributes' => [
                    'page' => 1
                ]
            ],
            'One product with multiple variations redirects to variation with a number matching the query' => [
                'query' => ['query' => 'this is the text that was searched for'],
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
                'variationSearchByItemIdResult' => [
                    'success' => true,
                    'total' => 2,
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
                                ],
                                'variation' => [
                                    'id' => 1011,
                                    'isMain' => false,
                                    'model' => 'model',
                                    'number' => 'THIS IS THE TEXT THAT WAS SEARCHED FOR',
                                    'order' => 'order'
                                ]
                            ]
                        ],
                        [
                            'id' => 1012,
                            'data' => [
                                'texts' => [
                                    [
                                        'urlPath' => 'test-product'
                                    ]
                                ],
                                'item' => [
                                    'id' => 11
                                ],
                                'variation' => [
                                    'id' => 1012,
                                    'isMain' => true,
                                    'model' => 'model',
                                    'number' => 'number',
                                    'order' => 'order'
                                ]
                            ]
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11_1011',
                'attributes' => [
                    'page' => 1
                ]
            ],
            'One product with multiple variations redirects to variation with an order matching the query' => [
                'query' => ['query' => 'this is the text that was searched for'],
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
                'variationSearchByItemIdResult' => [
                    'success' => true,
                    'total' => 2,
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
                                ],
                                'variation' => [
                                    'id' => 1011,
                                    'isMain' => true,
                                    'model' => 'model',
                                    'number' => 'number',
                                    'order' => 'order'
                                ]
                            ]
                        ],
                        [
                            'id' => 1012,
                            'data' => [
                                'texts' => [
                                    [
                                        'urlPath' => 'test-product'
                                    ]
                                ],
                                'item' => [
                                    'id' => 11
                                ],
                                'variation' => [
                                    'id' => 1012,
                                    'isMain' => false,
                                    'model' => 'model',
                                    'number' => 'number',
                                    'order' => 'this is the text that was searched for'
                                ]
                            ]
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11_1012',
                'attributes' => [
                    'page' => 1
                ]
            ],
            'One product with multiple variations redirects to variation with a barcode matching the query' => [
                'query' => ['query' => 'this is the text that was searched for'],
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
                'variationSearchByItemIdResult' => [
                    'success' => true,
                    'total' => 2,
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
                                ],
                                'variation' => [
                                    'id' => 1011,
                                    'isMain' => true,
                                    'model' => 'model',
                                    'number' => 'number',
                                    'order' => 'order'
                                ]
                            ]
                        ],
                        [
                            'id' => 1012,
                            'data' => [
                                'texts' => [
                                    [
                                        'urlPath' => 'test-product'
                                    ]
                                ],
                                'item' => [
                                    'id' => 11
                                ],
                                'variation' => [
                                    'id' => 1012,
                                    'isMain' => false,
                                    'model' => 'model',
                                    'number' => 'number',
                                    'order' => 'order'
                                ],
                                'barcodes' => [
                                    ['code' => '123123123'],
                                    ['code' => 'this is the text that was searched for'],
                                    ['code' => '321321321']
                                ]
                            ]
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11_1012',
                'attributes' => [
                    'page' => 1
                ]
            ],
            'One product with multiple variations redirects to main variation when no identifiers match query' => [
                'query' => ['query' => 'this is the text that was searched for'],
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
                'variationSearchByItemIdResult' => [
                    'success' => true,
                    'total' => 2,
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
                                ],
                                'variation' => [
                                    'id' => 1011,
                                    'isMain' => true,
                                    'model' => 'model',
                                    'number' => 'number',
                                    'order' => 'order'
                                ]
                            ]
                        ],
                        [
                            'id' => 1012,
                            'data' => [
                                'texts' => [
                                    [
                                        'urlPath' => 'test-product'
                                    ]
                                ],
                                'item' => [
                                    'id' => 11
                                ],
                                'variation' => [
                                    'id' => 1012,
                                    'isMain' => false,
                                    'model' => 'model',
                                    'number' => 'number',
                                    'order' => 'order'
                                ],
                                'barcodes' => [
                                    ['code' => '123123123'],
                                    ['code' => '321321321']
                                ]
                            ]
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11_1011',
                'attributes' => [
                    'page' => 1
                ]
            ],
            'One product found on second page should not redirect to product detail' => [
                'query' => ['query' => 'this is the text that was searched for'],
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
                'variationSearchByItemIdResult' => [
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
                'query' => ['query' => 'this is the text that was searched for'],
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
                'variationSearchByItemIdResult' => [
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
                'query' => ['query' => 'this is the text that was searched for'],
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
                'variationSearchByItemIdResult' => [
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
                'query' => ['query' => 'this is the text that was searched for'],
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
                'variationSearchByItemIdResult' => [
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
                'query' => ['query' => 'this is the text that was searched for'],
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
                'variationSearchByItemIdResult' => [
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
