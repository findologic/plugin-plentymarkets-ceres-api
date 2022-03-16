<?php

namespace Findologic\Tests\Services;

use Findologic\Api\Client;
use Findologic\Api\Request\Request;
use Findologic\Api\Request\RequestBuilder;
use Findologic\Api\Response\Response;
use Findologic\Api\Response\ResponseParser;
use Findologic\Constants\Plugin;
use Findologic\Services\FallbackSearchService;
use Findologic\Services\PluginInfoService;
use Findologic\Services\SearchService;
use Findologic\Services\Search\ParametersHandler;
use Ceres\Helper\ExternalSearch;
use Findologic\Tests\Helpers\MockResponseHelper;
use IO\Services\CategoryService;
use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;
use Plenty\Modules\Webshop\ItemSearch\Helpers\ResultFieldTemplate;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;
use IO\Services\TemplateConfigService;
use Plenty\Log\Contracts\LoggerContract;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Webshop\Contracts\LocalizationRepositoryContract;
use Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Modules\Webshop\Helpers\UrlQuery;
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
    use MockResponseHelper;

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

    /**
     * @var PluginInfoService|MockObject
     */
    private $pluginInfoService;

    /**
     * @var TemplateConfigService|MockObject
     */
    private $templateConfigService;

    public function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->requestBuilder = $this->getMockBuilder(RequestBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseParser = $this->getMockBuilder(ResponseParser::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->searchParametersHandler = $this->getMockBuilder(ParametersHandler::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerContract::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->loggerFactory = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->loggerFactory->expects($this->any())->method('getLogger')->willReturn($this->logger);
        $this->fallbackSearchService = $this->getMockBuilder(FallbackSearchService::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->configRepository = $this->getMockBuilder(ConfigRepository::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->pluginInfoService = $this->getMockBuilder(PluginInfoService::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
    }

    public function tearDown()
    {
        global $classInstances;
        $classInstances = [];
        
        parent::tearDown();
    }

    public function testHandleSearchQuery()
    {
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->requestBuilder->expects($this->any())->method('build')->willReturn($requestMock);
        $this->client->expects($this->any())->method('call')->willReturn(Plugin::API_ALIVE_RESPONSE_BODY);

        $responseMock = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $responseMock->expects($this->once())->method('getVariationIds')->willReturn([1, 2, 3]);
        $responseMock->expects($this->exactly(2))->method('getResultsCount')->willReturn(3);
        $this->responseParser->expects($this->once())->method('parse')->willReturn($responseMock);

        $itemSearchServiceMock = $this->getMockForAbstractClass(ItemSearchService::class);
        $itemSearchServiceMock->method('getResults')
            ->willReturn($this->getDefaultResultsForItemSearchService());

        $searchServiceMock = $this->getSearchServiceMock(
            ['getCategoryService', 'getItemSearchService', 'getVariationSearchFactory', 'getPluginRepository']
        );
        $searchServiceMock->expects($this->once())->method('getItemSearchService')->willReturn($itemSearchServiceMock);
        $searchServiceMock->method('getVariationSearchFactory')->willReturn($this->getVariationSearchFactoryMock());

        $searchQueryMock = $this->getMockBuilder(ExternalSearch::class)
            ->disableOriginalConstructor()
            ->setMethods(['setResults'])
            ->getMock();
        $searchQueryMock->categoryId = null;

        $requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $searchServiceMock->handleSearchQuery($requestMock, $searchQueryMock);
    }

    public function testItemVariantIdExtractingForRedirectUrlGeneration()
    {
        $shopUrl = 'myshop.de';
        $this->setUpPlentyInternalSearchMocks($shopUrl, 'de', 'de');

        $requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $externalSearchServiceMock = $this->getMockBuilder(ExternalSearch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $responseMock->method('getResultsCount')->willReturn(1);
        $responseMock->method('getVariationIds')->willReturn([456]);
        $responseMock->method('getLandingPage')->willReturn(null);
        $responseMock->method('getProductsIds')->willReturn(['123_456']);

        $responseMock->expects($this->once())->method('getData')->willReturn(['query' => 'search term']);

        $itemSearchServiceMock = $this->getMockBuilder(ItemSearchService::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $searchFactoryMock = $this->getVariationSearchFactoryMock();

        $searchServiceMock = $this->getSearchServiceMock([
            'search',
            'shouldFilterInvalidProducts',
            'getResults',
            'shouldRedirectToProductDetailPage',
            'getItemSearchService',
            'getVariationSearchFactory',
            'doPageRedirect',
            'loadResultFieldTemplate'
        ]);

        $searchServiceMock->method('search')->willReturn($responseMock);
        $searchServiceMock->expects($this->once())
            ->method('loadResultFieldTemplate')
            ->with(ResultFieldTemplate::TEMPLATE_LIST_ITEM);

        $mainVariationId = 1011;
        $itemSearchServiceMock->method('getResults')->willReturn([
            [
                'total' => 1,
                'documents' => $this->getMultipleItemsDocuments(
                    [
                        '0' => [
                            'id' => $mainVariationId,
                            'price' => 10.00,
                            'isMain' => true
                        ]
                    ]
                )
            ]
        ]);
        $searchServiceMock->method('shouldFilterInvalidProducts')->willReturn(false);
        $searchServiceMock->method('shouldRedirectToProductDetailPage')->willReturn(true);
        $searchServiceMock->expects($this->once())->method('getItemSearchService')->willReturn($itemSearchServiceMock);
        $searchServiceMock->expects($this->once())->method('getVariationSearchFactory')->willReturn($searchFactoryMock);
        $expectedRedirectUrl = $shopUrl . '/test-product_11_' . $mainVariationId;
        $searchServiceMock->expects($this->once())->method('doPageRedirect')->with($expectedRedirectUrl);

        $searchFactoryMock->expects($this->once())->method('hasItemId')->with('123');

        $searchServiceMock->doSearch($requestMock, $externalSearchServiceMock);
    }

    /**
     * @dataProvider redirectToProductPageOnDoSearchProvider
     * @runInSeparateProcess
     */
    public function testRedirectToProductPageOnDoSearch(
        array $query,
        array $responseVariationIds,
        array $responseProductIds,
        array $itemSearchServiceResultsAll,
        array $variationSearchByItemIdResult,
        string $shopUrl,
        array $dataQueryInfoMessage,
        $redirectUrl,
        array $attributes,
        string $language = 'de',
        string $defaultLanguage = 'de',
        bool $isOptionShowPleaseSelectEnabled = true
    ) {
        $this->setUpPlentyInternalSearchMocks($shopUrl, $defaultLanguage, $language);

        /** @var Request|HttpRequest|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $this->requestBuilder->expects($this->any())->method('build')->willReturn($requestMock);
        $this->client->expects($this->any())->method('call')->willReturn(Plugin::API_ALIVE_RESPONSE_BODY);

        $this->pluginInfoService->expects($this->any())
            ->method('isOptionShowPleaseSelectEnabled')
            ->willReturn($isOptionShowPleaseSelectEnabled);

        $responseMock = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->setMethods([])->getMock();
        $responseMock->expects($this->once())->method('getVariationIds')->willReturn($responseVariationIds);
        $responseMock->expects($this->any())->method('getResultsCount')->willReturn(count($responseVariationIds));
        if ($redirectUrl) {
            $responseMock->expects($this->exactly(2))->method('getData')
                ->withConsecutive([Response::DATA_QUERY_INFO_MESSAGE], [Response::DATA_QUERY])
                ->willReturnOnConsecutiveCalls($dataQueryInfoMessage, $query);
            $responseMock->expects($this->once())->method('getProductsIds')->willReturn($responseProductIds);
        } elseif ($dataQueryInfoMessage['queryStringType'] != 'notImprovedOrCorrected') {
            $responseMock->expects($this->once())->method('getData')
                ->with(Response::DATA_QUERY_INFO_MESSAGE)
                ->willReturn($dataQueryInfoMessage);
        }
        $this->responseParser->expects($this->once())->method('parse')->willReturn($responseMock);


        $itemSearchServiceMock = $this->getMockForAbstractClass(ItemSearchService::class);
        $itemSearchServiceMock->expects($this->at(0))->method('getResults')->willReturn($itemSearchServiceResultsAll);
        if ($redirectUrl) {
            $itemSearchServiceMock->expects($this->at(1))
                ->method('getResults')
                ->willReturn($variationSearchByItemIdResult);
        }

        $searchServiceMock = $this->getSearchServiceMock([
            'getCategoryService',
            'getItemSearchService',
            'getVariationSearchFactory',
            'doPageRedirect',
            'getPluginRepository',
            'loadResultFieldTemplate'
        ]);
        $searchServiceMock->expects($this->any())->method('getItemSearchService')->willReturn($itemSearchServiceMock);
        if ($redirectUrl) {
            $searchServiceMock->expects($this->once())->method('doPageRedirect')->with($redirectUrl);
        } else {
            $searchServiceMock->expects($this->never())->method('doPageRedirect');
        }

        $searchServiceMock->method('getVariationSearchFactory')->willReturn($this->getVariationSearchFactoryMock());

        /** @var ExternalSearch|MockObject $searchQueryMock */
        $searchQueryMock = $this->getMockBuilder(ExternalSearch::class)
            ->disableOriginalConstructor()
            ->setMethods(['setResults'])
            ->getMock();
        $searchQueryMock->categoryId = null;

        /** @var HttpRequest|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
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
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'attributes' => [],
                'configuredSearchCategory' => 1234,
                'searchedCategory' => 1234,
                'expectedCategory' => null
            ],
            'Category is used if no category is configured' => [
                'responseVariationIds' => [
                    1011, 1022, 1023
                ],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
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
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
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
        $responseMock->expects($this->any())->method('getResultsCount')->willReturn(count($responseVariationIds));
        $this->responseParser->expects($this->once())->method('parse')->willReturn($responseMock);

        $itemSearchServiceMock = $this->getMockForAbstractClass(ItemSearchService::class);
        $itemSearchServiceMock->expects($this->once())
            ->method('getResults')
            ->willReturn($itemSearchServiceResultsAll);

        $searchServiceMock = $this->getSearchServiceMock([
            'getCategoryService',
            'getItemSearchService',
            'getVariationSearchFactory',
            'handleProductRedirectUrl',
            'getPluginRepository',
            'shouldRedirectToProductDetailPage'
        ]);
        $searchServiceMock->expects($this->any())
            ->method('getItemSearchService')
            ->willReturn($itemSearchServiceMock);

        $searchServiceMock->method('getVariationSearchFactory')->willReturn($this->getVariationSearchFactoryMock());
        $searchServiceMock->method('shouldRedirectToProductDetailPage')->willReturn(false);

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
                'configRepository' => $this->configRepository,
                'pluginInfoService' => $this->pluginInfoService
            ])
            ->setMethods($methods);

        return $searchServiceMock->getMock();
    }

    protected function getRowItemDocument(array $variation)
    {
        return [
            'id' => $variation['id'],
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
                    'id' => $variation['id'],
                    'isMain' => $variation['isMain'],
                    'model' => (array_key_exists('model', $variation)) ? $variation['model'] : 'model',
                    'number' => (array_key_exists('number', $variation)) ? $variation['number'] :'number',
                    'order' => (array_key_exists('order', $variation)) ? $variation['order'] : 'model'
                ],
                'prices' => [
                    'default' => [
                        'price' => [
                            'value' => $variation['price']
                        ]
                    ]
                ],
                'barcodes' => (array_key_exists('barcodes', $variation)) ? $variation['barcodes'] : []
            ]
        ];
    }

    private function getMultipleItemsDocuments(array $itemsData): array
    {
        $documents = [];

        foreach ($itemsData as $item) {
            $documents[] = $this->getRowItemDocument($item);
        }

        return $documents;
    }

    public function redirectToProductPageOnDoSearchProvider(): array
    {
        return [
            'Show please select config is set, cheapest variation found, no variation information in redirect url' => [
                'query' => ['query' => 'test query'],
                'responseVariationIds' => [1011, 1012, 1013],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
                        'total' => 3,
                        'documents' => $this->getMultipleItemsDocuments(
                            [
                                '0' => [
                                    'id' => 1011,
                                    'price' => 10.00,
                                    'isMain' => true
                                ],
                                '1' => [
                                    'id' => 1012,
                                    'price' => 10.00,
                                    'isMain' => false
                                ],
                                '2' => [
                                    'id' => 1013,
                                    'price' => 10.00,
                                    'isMain' => false
                                ]
                            ]
                        ),
                    ]

                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11',
                'attributes' => [
                    'page' => 1
                ],
                'language' => 'de',
                'defaultLanguage' => 'de'
            ],
            'Show please select config is set, search query matches but no variation information in redirect url' => [
                'query' => ['query' => '1012'],
                'responseVariationIds' => [1011, 1012, 1013],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
                        'total' => 3,
                        'documents' => $this->getMultipleItemsDocuments(
                            [
                                '0' => [
                                    'id' => 1011,
                                    'price' => 10.00,
                                    'isMain' => true
                                ],
                                '1' => [
                                    'id' => 1012,
                                    'price' => 10.00,
                                    'isMain' => false
                                ],
                                '2' => [
                                    'id' => 1013,
                                    'price' => 10.00,
                                    'isMain' => false
                                ]
                            ]
                        ),
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11',
                'attributes' => [
                    'page' => 1
                ],
                'language' => 'de',
                'defaultLanguage' => 'de'
            ],
            'Show please select config is set to true, no variation information in redirect url' => [
                'query' => ['query' => 'this is the query'],
                'responseVariationIds' => [1011, 1012, 1013],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
                        'total' => 3,
                        'documents' => $this->getMultipleItemsDocuments(
                            [
                                '0' => [
                                    'id' => 1011,
                                    'price' => 10.00,
                                    'isMain' => true
                                ],
                                '1' => [
                                    'id' => 1012,
                                    'price' => 10.00,
                                    'isMain' => false
                                ],
                                '2' => [
                                    'id' => 1013,
                                    'price' => 10.00,
                                    'isMain' => false
                                ]
                            ]
                        ),
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11',
                'attributes' => [
                    'page' => 1
                ],
                'language' => 'de',
                'defaultLanguage' => 'de'
            ],
            'All variants with price 0, no variation id in redirect url' => [
                'query' => ['query' => 'this is the query'],
                'responseVariationIds' => [1011, 1012, 1013],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
                        'total' => 3,
                        'documents' => $this->getMultipleItemsDocuments(
                            [
                                '0' => [
                                    'id' => 1011,
                                    'price' => 0.00,
                                    'isMain' => true
                                ],
                                '1' => [
                                    'id' => 1012,
                                    'price' => 0.00,
                                    'isMain' => false
                                ],
                                '2' => [
                                    'id' => 1013,
                                    'price' => 0.00,
                                    'isMain' => false
                                ]
                            ]
                        ),
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11',
                'attributes' => [
                    'page' => 1
                ],
                'language' => 'de',
                'defaultLanguage' => 'de',
                'isOptionShowPleaseSelectEnabled' => false
            ],
            'One product with three variations, main variation without price, no query matches' => [
                'query' => ['query' => 'this is the query'],
                'responseVariationIds' => [1011, 1012, 1013],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
                        'total' => 2,
                        'documents' => $this->getMultipleItemsDocuments(
                            [
                                '0' => [
                                    'id' => 1011,
                                    'price' => 0.00,
                                    'isMain' => true
                                ],
                                '1' => [
                                    'id' => 1012,
                                    'price' => 12.00,
                                    'isMain' => false
                                ],
                                '2' => [
                                    'id' => 1013,
                                    'price' => 15.00,
                                    'isMain' => false
                                ]
                            ]
                        ),
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11_1012',
                'attributes' => [
                    'page' => 1
                ],
                'language' => 'de',
                'defaultLanguage' => 'de',
                'isOptionShowPleaseSelectEnabled' => false
            ],
            'One product with three variations redirects to main variant because no variation matches the query' => [
                'query' => ['query' => 'this is the query'],
                'responseVariationIds' => [1011, 1012, 1013],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
                        'total' => 2,
                        'documents' => $this->getMultipleItemsDocuments(
                            [
                                '0' => [
                                    'id' => 1011,
                                    'price' => 10.00,
                                    'isMain' => true
                                ],
                                '1' => [
                                    'id' => 1012,
                                    'price' => 10.00,
                                    'isMain' => false
                                ],
                                '2' => [
                                    'id' => 1013,
                                    'price' => 10.00,
                                    'isMain' => false
                                ]
                            ]
                        ),
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11_1011',
                'attributes' => [
                    'page' => 1
                ],
                'language' => 'de',
                'defaultLanguage' => 'de',
                'isOptionShowPleaseSelectEnabled' => false
            ],
            'One product with three variations redirects to variation with an id matching the query' => [
                'query' => ['query' => '1012'],
                'responseVariationIds' => [1011, 1012, 1013],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
                        'total' => 2,
                        'documents' => $this->getMultipleItemsDocuments(
                            [
                                '0' => [
                                    'id' => 1011,
                                    'price' => 20.00,
                                    'isMain' => true
                                ],
                                '1' => [
                                    'id' => 1012,
                                    'price' => 20.00,
                                    'isMain' => false
                                ],
                                '2' => [
                                    'id' => 1013,
                                    'price' => 20.00,
                                    'isMain' => false
                                ]
                            ]
                        ),
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11_1012',
                'attributes' => [
                    'page' => 1
                ],
                'language' => 'de',
                'defaultLanguage' => 'de',
                'isOptionShowPleaseSelectEnabled' => false
            ],
            'One product found' => [
                'query' => ['query' => 'this is the text that was searched for'],
                'responseVariationIds' => [1011, 1012],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
                        'total' => 1,
                        'documents' => [
                            $this->getRowItemDocument([
                                'id' => 1011,
                                'price' => 20.00,
                                'isMain' => true,
                            ])
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11',
                'attributes' => []
            ],
            'One product with another language found' => [
                'query' => ['query' => 'this is the text that was searched for'],
                'responseVariationIds' => [1011, 1012],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
                        'total' => 1,
                        'documents' => [
                            $this->getRowItemDocument([
                                'id' => 1011,
                                'price' => 20.00,
                                'isMain' => true,
                            ])
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/en/test-product_11',
                'attributes' => [],
                'language' => 'en'
            ],
            'One product found on first page should redirect to product detail' => [
                'query' => ['query' => 'this is the text that was searched for'],
                'responseVariationIds' => [1011, 1012],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
                        'total' => 1,
                        'documents' => [
                            $this->getRowItemDocument([
                                'id' => 1011,
                                'price' => 20.00,
                                'isMain' => true,
                            ])
                        ]
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11',
                'attributes' => [
                    'page' => 1
                ]
            ],
            'One product with multiple variations redirects to variation with a model matching the query' => [
                'query' => ['query' => 'this is the text that was searched for'],
                'responseVariationIds' => [1011, 1012],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
                        'total' => 2,
                        'documents' => $this->getMultipleItemsDocuments(
                            [
                                '0' => [
                                    'id' => 1011,
                                    'price' => 20.00,
                                    'isMain' => true
                                ],
                                '1' => [
                                    'id' => 1012,
                                    'price' => 20.00,
                                    'isMain' => false,
                                    'model' => 'this is the text that was searched for'
                                ],
                            ]
                        )
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11',
                'attributes' => [
                    'page' => 1
                ]
            ],
            'One product with multiple variations redirects to variation with a number matching the query' => [
                'query' => ['query' => 'this is the text that was searched for'],
                'responseVariationIds' => [1011, 1012],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
                        'total' => 2,
                        'documents' => $this->getMultipleItemsDocuments(
                            [
                                '0' => [
                                    'id' => 1011,
                                    'price' => 20.00,
                                    'isMain' => true,
                                    'number' => 'THIS IS THE TEXT THAT WAS SEARCHED FOR'
                                ],
                                '1' => [
                                    'id' => 1012,
                                    'price' => 20.00,
                                    'isMain' => false,
                                ],
                            ]
                        )
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11',
                'attributes' => [
                    'page' => 1
                ]
            ],
            'One product with multiple variations redirects to variation with an order matching the query' => [
                'query' => ['query' => 'this is the text that was searched for'],
                'responseVariationIds' => [1011, 1012],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
                        'total' => 2,
                        'documents' => $this->getMultipleItemsDocuments(
                            [
                                '0' => [
                                    'id' => 1011,
                                    'price' => 20.00,
                                    'isMain' => true,
                                ],
                                '1' => [
                                    'id' => 1012,
                                    'price' => 20.00,
                                    'isMain' => false,
                                    'order' => 'this is the text that was searched for'
                                ],
                            ]
                        )
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11',
                'attributes' => [
                    'page' => 1
                ]
            ],
            'One product with multiple variations redirects to variation with a barcode matching the query' => [
                'query' => ['query' => 'this is the text that was searched for'],
                'responseVariationIds' => [1011, 1012],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
                        'total' => 2,
                        'documents' => $this->getMultipleItemsDocuments(
                            [
                                '0' => [
                                    'id' => 1011,
                                    'price' => 20.00,
                                    'isMain' => true,
                                ],
                                '1' => [
                                    'id' => 1012,
                                    'price' => 20.00,
                                    'isMain' => false,
                                    'barcodes' =>  [
                                        ['code' => '123123123'],
                                        ['code' => 'this is the text that was searched for'],
                                        ['code' => '321321321']
                                    ],
                                ],
                            ]
                        )
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11',
                'attributes' => [
                    'page' => 1
                ]
            ],
            'One product with multiple variations redirects to main variation when no identifiers match query' => [
                'query' => ['query' => 'this is the text that was searched for'],
                'responseVariationIds' => [1011, 1012],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
                        'total' => 2,
                        'documents' => $this->getMultipleItemsDocuments(
                            [
                                '0' => [
                                    'id' => 1011,
                                    'price' => 20.00,
                                    'isMain' => true,
                                ],
                                '1' => [
                                    'id' => 1012,
                                    'price' => 20.00,
                                    'isMain' => false,
                                    'barcodes' => [
                                        ['code' => '123123123'],
                                        ['code' => '321321321']
                                    ]
                                ],
                            ]
                        )
                    ]
                ],
                'shopUrl' => 'https://www.test.com',
                'dataQueryInfoMessage' => [
                    'queryStringType' => 'notImprovedOrCorrected'
                ],
                'redirectUrl' => '/test-product_11',
                'attributes' => [
                    'page' => 1
                ]
            ],
            'One product found on second page should not redirect to product detail' => [
                'query' => ['query' => 'this is the text that was searched for'],
                'responseVariationIds' => [1011, 1012],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
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
                'responseVariationIds' => [1011, 1022, 1023],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => [
                    [
                        'total' => 2,
                        'documents' => [
                            [
                                'id' => 1011
                            ],
                            [
                                'id' => 1022
                            ]
                        ]
                    ]
                ],
                'variationSearchByItemIdResult' => [
                    [
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
                'responseVariationIds' => [1011, 1022, 1023],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
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
                'responseVariationIds' => [1011, 1022, 1023],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
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
                'responseVariationIds' => [1011, 1022, 1023],
                'responseProductIds' => [11],
                'itemSearchServiceResultsAll' => $this->getDefaultResultsForItemSearchService(),
                'variationSearchByItemIdResult' => [
                    [
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

    /**
     * @dataProvider versionFilterCheckProvider
     * @param string|int $version
     */
    public function testFilterInvalidProductOnlyPriorToCertainVersion($version, bool $shouldFilter)
    {
        $searchServiceMock = $this->getSearchServiceMock(['getPluginRepository']);
        $this->pluginInfoService->method('getPluginVersion')->willReturn($version);

        $this->assertEquals($shouldFilter, $searchServiceMock->shouldFilterInvalidProducts());
    }

    public function versionFilterCheckProvider(): array
    {
        return [
            'Does not need to filter for Ceres version 5.0.3' => [
                'version' => '5.0.3',
                'shouldFilter' => false
            ],
            'Needs to filter when no Ceres version is returned' => [
                'version' => null,
                'shouldFilter' => true
            ],
            'Needs to filter for Ceres versions below 5.0.3' => [
                'version' => '5.0.2',
                'shouldFilter' => true
            ],
            'Does not need to filter for Ceres versions above 5.0.3' => [
                'version' => '5.0.15',
                'shouldFilter' => false
            ]
        ];
    }

    public function testExternalSearchIsNotManipulatedOnNoResultPages()
    {
        $requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $externalSearchServiceMock = $this->getMockBuilder(ExternalSearch::class)
            ->disableOriginalConstructor()
            ->getMock();
        $externalSearchServiceMock->expects($this->never())->method('setResults');

        $originalExternalSearchMock = clone $externalSearchServiceMock;

        $this->requestBuilder->expects($this->once())->method('build')
            ->willReturn(new Request());
        $this->client->expects($this->once())
            ->method('call')
            ->willReturn($this->getMockResponse('noResults.xml'));

        $searchService = $this->getSearchServiceMock();
        $searchService->doSearch($requestMock, $externalSearchServiceMock);

        $this->assertEquals($originalExternalSearchMock, $externalSearchServiceMock);
    }

    public function testInitialNavigationPageGetsResultCountFromPlenty()
    {
        $plentyResultCount = 100;

        $this->configRepository->expects($this->once())
            ->method('get')
            ->willReturn(true);

        $mockedFallbackSearchResult = json_decode($this->getMockResponse('fallbackSearchResult.json'), true);
        $this->fallbackSearchService->expects($this->once())
            ->method('getSearchResults')
            ->willReturn($mockedFallbackSearchResult);

        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock->expects($this->any())
            ->method('getData')
            ->willReturn(['count' => $plentyResultCount]);

        $this->fallbackSearchService->expects($this->once())
            ->method('createResponseFromSearchResult')
            ->willReturn($responseMock);

        $requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $externalSearchServiceMock = $this->getMockBuilder(ExternalSearch::class)
            ->disableOriginalConstructor()
            ->getMock();
        $externalSearchServiceMock->expects($this->once())
            ->method('setDocuments')
            ->with($mockedFallbackSearchResult['itemList']['documents'], $plentyResultCount);

        $this->requestBuilder->expects($this->once())->method('build')
            ->willReturn(new Request());
        $this->client->expects($this->once())
            ->method('call')
            ->willReturn($this->getMockResponse('someResultsWithFilters.xml'));

        $searchService = $this->getSearchServiceMock();
        $searchService->doNavigation($requestMock, $externalSearchServiceMock);
    }

    public function testRetryMechanismAndEnsureItGetsLogged()
    {
        $requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $externalSearchServiceMock = $this->getMockBuilder(ExternalSearch::class)
            ->disableOriginalConstructor()
            ->getMock();
        $externalSearchServiceMock->expects($this->never())->method('setResults');

        $this->requestBuilder->expects($this->once())->method('build')
            ->willReturn(new Request());

        $plentyErrorResponse = [
            'error' => true,
            'error_code' => 404,
            'error_msg' => 'no services found',
            'error_file' => '/var/www/SdkRestApi.php',
            'error_line' => 127,
            'error_host' => '127.0.0.1'
        ];
        $nonStringErrorResponse = false;
        $validResponse = $this->getMockResponse('someResultsWithFilters.xml');

        $this->client->expects($this->exactly(3))
            ->method('call')
            ->willReturnOnConsecutiveCalls($plentyErrorResponse, $nonStringErrorResponse, $validResponse);

        $this->logger->expects($this->exactly(2))->method('error')->withConsecutive(
            [
                'Plentymarkets SDK returned an error response - Retry 1/2 takes place',
                ['response' => $plentyErrorResponse]
            ],
            [
                'Plentymarkets SDK returned invalid response - Expected string - Retry 2/2 takes place',
                ['response' => $nonStringErrorResponse]
            ]
        );

        $searchService = $this->getSearchServiceMock();
        $searchService->doSearch($requestMock, $externalSearchServiceMock);
    }

    /**
     * @param string $shopUrl
     * @param string $defaultLanguage
     * @param string $language
     * @return void
     */
    private function setUpPlentyInternalSearchMocks(string $shopUrl, string $defaultLanguage, string $language)
    {
        global $classInstances;

        $localizationMock = $this->getMockBuilder(LocalizationRepositoryContract::class)
            ->disableOriginalConstructor()
            ->getMock();

        $webStoreConfigMock = $this->getMockBuilder(WebstoreConfigurationRepositoryContract::class)
            ->disableOriginalConstructor()
            ->getMock();

        $urlBuilderMock = $this->getMockBuilder(UrlBuilderRepositoryContract::class)
            ->disableOriginalConstructor()
            ->getMock();

        $urlQueryMock = $this->getMockBuilder(UrlQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create mocks with logic, since we can not use the default logic from Plentymarkets,
        // since this code is not available for us and only Plenty themselves have access to it.

        $urlBuilderMock->expects($this->any())->method('buildVariationUrl')
            ->willReturnCallback(function () use ($urlQueryMock, $shopUrl) {
                $urlQueryMock->url = $shopUrl . '/test-product';

                return $urlQueryMock;
            });

        $urlBuilderMock->expects($this->any())->method('buildItemUrl')
            ->willReturnCallback(function () use ($urlQueryMock, $shopUrl) {
                $urlQueryMock->url = $shopUrl . '/test-product';

                return $urlQueryMock;
            });

        $urlQueryMock->expects($this->any())->method('append')
            ->willReturnCallback(function ($suffix) use ($urlQueryMock) {
                $urlQueryMock->url .= $suffix;

                return $urlQueryMock;
            });

        $urlQueryMock->expects($this->any())->method('toRelativeUrl')
            ->willReturnCallback(function ($includeLanguage) use ($urlQueryMock, $defaultLanguage, $language) {
                $path = parse_url($urlQueryMock->url, PHP_URL_PATH);
                if (!$includeLanguage || $language === $defaultLanguage) {
                    return $path;
                }

                return '/' . $language . $path;
            });

        $urlBuilderMock->expects($this->any())->method('getSuffix')
            ->willReturnCallback(function ($itemId, $variationId, $withVariationId) {
                if (!$withVariationId) {
                    return sprintf('_%s', $itemId);
                }

                return sprintf('_%s_%s', $itemId, $variationId);
            });

        $classInstances[LocalizationRepositoryContract::class] = $localizationMock;
        $classInstances[WebstoreConfigurationRepositoryContract::class] = $webStoreConfigMock;
        $classInstances[UrlBuilderRepositoryContract::class] = $urlBuilderMock;
    }

    private function getVariationSearchFactoryMock(): MockObject
    {
        $variationSearchFactoryMock = $this->getMockForAbstractClass(VariationSearchFactory::class);
        $variationSearchFactoryMock->method('withVariationProperties')->willReturnSelf();
        $variationSearchFactoryMock->method('withPrices')->willReturnSelf();
        $variationSearchFactoryMock->method('isActive')->willReturnSelf();
        $variationSearchFactoryMock->method('withResultFields')->willReturnSelf();
        $variationSearchFactoryMock->method('hasVariationIds')->willReturnSelf();
        $variationSearchFactoryMock->method('hasItemId')->willReturnSelf();

        return $variationSearchFactoryMock;
    }

    private function getDefaultResultsForItemSearchService(): array
    {
        return [
            [
                'total' => 1,
                'documents' => [
                    [
                        'id' => 1011
                    ]
                ]
            ]
        ];
    }
}
