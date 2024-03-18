<?php

declare(strict_types=1);

namespace Findologic\Tests\Api\Response;

use PHPUnit\Framework\TestCase;
use Plenty\Plugin\Http\Request;
use Findologic\Struct\Promotion;
use Plenty\Plugin\Log\LoggerFactory;
use Findologic\Api\Response\Response;
use Findologic\Components\PluginConfig;
use Plenty\Plugin\Translation\Translator;
use Findologic\Api\Response\ResponseParser;
use Findologic\Tests\Overrides\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Findologic\Api\Response\Json10\Filter\Media;
use Findologic\Api\Response\Json10\Filter\Filter;
use FINDOLOGIC\Api\Responses\Json10\Json10Response;
use Findologic\Api\Response\Json10\Filter\RatingFilter;
use Findologic\Api\Response\Json10\Filter\CategoryFilter;
use Findologic\Struct\QueryInfoMessage\VendorInfoMessage;
use Findologic\Api\Response\Json10\Filter\LabelTextFilter;
use Findologic\Api\Response\Result\Filter as ResultFilter;
use Findologic\Struct\QueryInfoMessage\DefaultInfoMessage;
use Findologic\Struct\QueryInfoMessage\CategoryInfoMessage;
use Findologic\Api\Response\Json10\Filter\ColorPickerFilter;
use Findologic\Api\Response\Json10\Filter\RangeSliderFilter;
use Findologic\Api\Response\Json10\Filter\VendorImageFilter;
use FINDOLOGIC\Api\Responses\Response as FindologicResponse;
use Findologic\Api\Response\Json10\Filter\Values\FilterValue;
use Findologic\Api\Response\Json10\Filter\SelectDropdownFilter;
use Findologic\Struct\QueryInfoMessage\ShoppingGuideInfoMessage;
use Findologic\Api\Response\Json10\Filter\Values\ColorFilterValue;
use Findologic\Api\Response\Json10\Filter\Values\ImageFilterValue;
use Findologic\Struct\QueryInfoMessage\SearchTermQueryInfoMessage;
use Findologic\Api\Response\Result\FilterValue as ResultFilterValue;
use Findologic\Api\Response\Json10\Filter\Values\CategoryFilterValue;

require_once __DIR__ . '/../../../resources/lib/ApiResponse.php';

class Json10ResponseParserTest extends BaseTestCase
{
    private ServiceConfigResource|MockObject $serviceConfigResource;

    protected function setUp(): void
    {
        parent::setUp();
        global $classInstances;
        $translatorMock = $this->createMock(Translator::class);
        $translatorMock->method('trans')->willReturn('');
        $classInstances[Translator::class] = $translatorMock;
        $classInstances[Request::class] = $this->createMock(Request::class);
    }

    public function productIdsResponseProvider(): array
    {
        return [
            'default mock ids' => [
                'response' => new Json10Response($this->getMockResponse()),
                'expectedIds' => [
                    '019111105-37900',
                    '029214085-37860'
                ]
            ],
            'response without products' => [
                'response' => new Json10Response(
                    $this->getMockResponse('JSONResponse/demoResponseWithNoResults.json')
                ),
                'expectedIds' => []
            ],
            'response with one product' => [
                'response' => new Json10Response(
                    $this->getMockResponse('JSONResponse/demoResponseWithOneProduct.json')
                ),
                'expectedIds' => [
                    '029214085-37860'
                ]
            ],
            'response with many products' => [
                'response' => new Json10Response(
                    $this->getMockResponse('JSONResponse/demoResponseWithManyProducts.json')
                ),
                'expectedIds' => [
                    '102',
                    '103',
                    '104',
                    '105',
                    '106',
                    '107',
                    '108',
                    '109',
                    '110',
                    '111',
                    '112',
                    '113',
                    '114',
                    '115',
                    '116',
                    '117',
                    '118',
                    '119',
                    '120',
                    '121',
                    '122',
                    '123',
                    '124',
                    '125',
                    '126',
                    '127',
                    '128',
                    '129',
                    '130',
                    '131',
                    '132',
                    '133',
                    '134',
                    '135',
                ]
            ]
        ];
    }

    /**
     * @dataProvider productIdsResponseProvider
     */
    public function testProductIdsAreParsedAsExpected(FindologicResponse $response, array $expectedIds): void
    {

        $apiResult = new \ApiResponse($response);
        $apiResponse = $apiResult->toArray();
        $responseParser = new ResponseParser($this->createMock(LoggerFactory::class));
        $responseParser->setResponse(['response' => $apiResponse]);

        $this->assertEquals($expectedIds, $responseParser->getProductIds());
    }

    public function testProductIdsIncludeVariantIdsIfConfigured(): void
    {
        $expectedIds = ['variant-1_1', 'main-2'];

        $response = new Json10Response($this->getMockResponse('JSONResponse/demoResponseWithVariants.json'));
        $apiResult = new \ApiResponse($response);
        $apiResponse = $apiResult->toArray();
        $pluginConfigMock = $this->createMock(PluginConfig::class);
        $pluginConfigMock->method('get')->willReturn(true);
        $responseParser = new ResponseParser($this->createMock(LoggerFactory::class), $pluginConfigMock);
        $responseParser->setResponse(['response' => $apiResponse]);

        $ids = $responseParser->getProductIds();
        $this->assertEquals($expectedIds, $ids);
    }

    public function testSmartDidYouMeanExtensionIsReturned(): void
    {
        $response = new Json10Response($this->getMockResponse('JSONResponse/demoResponseWithDidYouMeanQuery.json'));
        $apiResult = new \ApiResponse($response);
        $apiResponse = $apiResult->toArray();
        $responseParser = new ResponseParser($this->createMock(LoggerFactory::class));
        $responseParser->setResponse(['response' => $apiResponse]);
        $responseParser->setRequest($this->createMock(Request::class));
        $extension = $responseParser->getSmartDidYouMeanExtension();

        $this->assertEquals('didYouMean', $extension->getType());
        $this->assertEquals('?search=&forceOriginalQuery=1', $extension->getLink());
        $this->assertEquals('query', $extension->getOriginalQuery());
    }

    public function testLandingPageUriIsReturned(): void
    {
        $response = new Json10Response($this->getMockResponse('JSONResponse/demoResponseWithLandingPage.json'));
        $apiResult = new \ApiResponse($response);
        $apiResponse = $apiResult->toArray();
        $responseParser = new ResponseParser($this->createMock(LoggerFactory::class));
        $responseParser->setResponse(['response' => $apiResponse]);

        $this->assertEquals('https://blubbergurken.io', $responseParser->getLandingPageExtension()->getLink());
    }

    public function testNoLandingPageIsReturnedIfResponseDoesNotHaveALandingPage(): void
    {
        $response = new Json10Response($this->getMockResponse());
        $apiResult = new \ApiResponse($response);
        $apiResponse = $apiResult->toArray();
        $responseParser = new ResponseParser($this->createMock(LoggerFactory::class));
        $responseParser->setResponse(['response' => $apiResponse]);
        $this->assertNull($responseParser->getLandingPageExtension());
    }

    public function testPromotionExtensionIsReturned(): void
    {
        $response = new Json10Response($this->getMockResponse());
        $apiResult = new \ApiResponse($response);
        $apiResponse = $apiResult->toArray();
        $responseParser = new ResponseParser($this->createMock(LoggerFactory::class));
        $responseParser->setResponse(['response' => $apiResponse]);
        $promotion = $responseParser->getPromotionExtension();

        $this->assertInstanceOf(Promotion::class, $promotion);
        $this->assertEquals('https://promotion.com/', $promotion->getLink());
        $this->assertEquals('https://promotion.com/promotion.png', $promotion->getImage());
    }

    public function filterResponseProvider(): array
    {
        $expectedCategoryFilter = @new CategoryFilter('cat', 'Kategorie');
        $expectedCategoryFilter->addValue(
            (new CategoryFilterValue(null, new ResultFilterValue(['name' => 'Buch', 'weight' => 0.25156819820404])))
                ->setFrequency(5)
                ->addValue(
                    (new CategoryFilterValue(null, new ResultFilterValue(['name' => 'Beste Bücher', 'weight' => 0.33799207210541])))->setId("Buch_Beste Bücher")
                )
        );
        $expectedCategoryFilter->setIsMain(true)->setSelectMode("single")->setFindologicFilterType("selectFilter");

        $vendor = 'vendor';
        $expectedVendorFilter = (new VendorImageFilter($vendor, 'Hersteller'))
            ->setIsMain(true)
            ->setSelectMode('multiple')
            ->setFindologicFilterType('vendorImageFilter')
            ->setCombinationOperation('and')
            ->setNoAvailableFiltersText('Keine Hersteller');
        $expectedVendorFilter->addValue(
            (new ImageFilterValue(null, new ResultFilterValue(['name' => 'Anderson, Gusikowski and Barton'])))
                ->setDisplayType('media')
                ->setMedia(new Media('https://demo.findologic.com/vendor/anderson_gusikowski_and_barton.png'))
                ->setFrequency(2)
                ->setSelected(false)
                ->setWeight(0.0333)
        );
        $expectedVendorFilter->addValue(
            (new ImageFilterValue(null, new ResultFilterValue(['name' => 'Bednar Ltd'])))
                ->setDisplayType('media')
                ->setMedia(new Media('https://demo.findologic.com/vendor/bednar_ltd.png'))
                ->setFrequency(77)
                ->setSelected(false)
                ->setWeight(0.0667)
        );
        $expectedVendorFilter->addValue(
            (new ImageFilterValue(null, new ResultFilterValue(['name' => 'Buckridge-Fisher'])))
                ->setDisplayType('media')
                ->setMedia(new Media('https://demo.findologic.com/vendor/buckridge_fisher.png'))
                ->setFrequency(122)
                ->setSelected(false)
                ->setWeight(0.0333)
        );
        $expectedVendorFilter->addValue(
            (new ImageFilterValue(null, new ResultFilterValue(['name' => 'Connelly, Eichmann and Weissnat'])))
                ->setDisplayType('media')
                ->setMedia(new Media('https://demo.findologic.com/vendor/connelly_eichmann_and_weissnat.png'))
                ->setFrequency(122)
                ->setSelected(false)
                ->setWeight(0.1)
        );

        $price = 'price';
        $expectedPriceFilter = new RangeSliderFilter(new ResultFilter(['name' => $price, 'displayName' => 'Preis', 'type' => $price]));
        $expectedPriceFilter->addValue(
            (new FilterValue(null, new ResultFilterValue(['name' => $price])))
                ->setId('0.39 - 13.40')
                ->setName('0.39 - 13.40')
                ->setSelected(false)
                ->setWeight(0.51743012666702)
        );
        $expectedPriceFilter->addValue(
            (new FilterValue(null, new ResultFilterValue(['name' => $price])))
                ->setId('13.45 - 25.99')
                ->setName('13.45 - 25.99')
                ->setSelected(false)
                ->setWeight(0.50098878145218)
        );
        $expectedPriceFilter->addValue(
            (new FilterValue(null, new ResultFilterValue(['name' => $price])))
                ->setId('26.00 - 40.30')
                ->setName('26.00 - 40.30')
                ->setSelected(false)
                ->setWeight(0.3976277410984)
        );
        $expectedPriceFilter->setMin(0.355);
        $expectedPriceFilter->setMax(3239.1455);
        $expectedPriceFilter->setStep(0.1);
        $expectedPriceFilter->setUnit('€');
        $expectedPriceFilter->setFindologicFilterType('rangeSliderFilter');
        $expectedPriceFilter->setSelectMode('single');
        $expectedPriceFilter->setIsMain(true);
        $expectedPriceFilter->setTotalRange([
            'min' => 0.355,
            'max' => 3239.1455
        ]);
        $expectedPriceFilter->setSelectedRange([
            'min' => 0.395,
            'max' => 2239.144
        ]);

        $expectedRatingFilter = (new RatingFilter('rating', 'Rating'))->setSelectMode('single')->setFindologicFilterType('rangeSliderFilter')->setMaxPoints(0.0);
        $expectedRatingFilter->addValue((new FilterValue(null, new ResultFilterValue(['name' => '0.00 - 0.00'])))->setSelected(false)->setWeight(0.51743012666702));
        $expectedRatingFilter->addValue((new FilterValue(null, new ResultFilterValue(['name' => '0.00 - 0.00'])))->setSelected(false)->setWeight(0.50098878145218));

        $color = 'Farbe';
        $expectedColorFilter = (new ColorPickerFilter($color, 'Farbe'))->setSelectMode('multiple')->setCombinationOperation('or')->setFindologicFilterType('colorPickerFilter');
        $expectedColorFilter->addValue(
            (new ColorFilterValue(null, new ResultFilterValue(['name' => 'beige'])))
                ->setColorHexCode('#F5F5DC')
                ->setMedia(new Media('https://blubbergurken.io/farbfilter/beige.gif'))
                ->setDisplayType('media')
                ->setSelected(false)
                ->setWeight(0.10730088502169)
        );
        $expectedColorFilter->addValue(
            (new ColorFilterValue(null, new ResultFilterValue(['name' => 'blau'])))
                ->setColorHexCode('#3c6380')
                ->setMedia(new Media('https://blubbergurken.io/farbfilter/blau.gif'))
                ->setDisplayType('media')
                ->setSelected(true)
                ->setWeight(0.3296460211277)
        );
        $expectedColorFilter->addValue(
            (new ColorFilterValue(null, new ResultFilterValue(['name' => 'braun'])))
                ->setColorHexCode('#94651e')
                ->setMedia(new Media('https://blubbergurken.io/farbfilter/braun.gif'))
                ->setDisplayType('media')
                ->setSelected(false)
                ->setWeight(0.90265488624573)
        );

        $material = 'Material';
        $expectedSelectDropdownFilter = (new SelectDropdownFilter(new ResultFilter(['name' => $material, 'displayName' => 'Material', 'type' => $material])))
            ->setSelectMode('multiple')
            ->setCssClass('fl-material')
            ->setCombinationOperation('and')
            ->setFindologicFilterType('selectFilter');
        $expectedSelectDropdownFilter->addValue(
            (new FilterValue(null, new ResultFilterValue(['name' => $material])))
                ->setId('Hartgepäck')
                ->setName('Hartgepäck')
                ->setSelected(false)
                ->setWeight(0.038716815412045)
                ->setFrequency(35)
        );
        $expectedSelectDropdownFilter->addValue(
            (new FilterValue(null, new ResultFilterValue(['name' => $material])))
                ->setId('Leder')
                ->setName('Leder')
                ->setSelected(false)
                ->setWeight(0.63053095340729)
                ->setFrequency(1238)
        );
        $expectedSelectDropdownFilter->addValue(
            (new FilterValue(null, new ResultFilterValue(['name' => $material])))
                ->setId('Nylon')
                ->setName('Nylon')
                ->setSelected(false)
                ->setWeight(0.12168141454458)
                ->setFrequency(110)
        );

        return [
            'response including all filter types' => [
                'response' => new Json10Response(
                    $this->getMockResponse('JSONResponse/demoResponseWithAllFilterTypes.json')
                ),
                'expectedFilters' => [
                    $expectedCategoryFilter,
                    $expectedVendorFilter,
                    $expectedPriceFilter,
                    $expectedColorFilter,
                    $expectedSelectDropdownFilter,
                    $expectedRatingFilter
                ]
            ],
            'response without results' => [
                'response' => new Json10Response(
                    $this->getMockResponse('JSONResponse/demoResponseWithNoResults.json')
                ),
                'expectedFilters' => []
            ],
            'response without results but with filters with no-filters-available-text' => [
                'response' => new Json10Response(
                    $this->getMockResponse('JSONResponse/demoResponseWithNoResultsButWithFilters.json')
                ),
                'expectedFilters' => []
            ],
            'response with colors without image URLs' => [
                'response' => new Json10Response(
                    $this->getMockResponse('JSONResponse/demoResponseWithColorFiltersWithoutUrl.json')
                ),
                'expectedFilters' => [
                    '0' => (new ColorPickerFilter('Farbe', 'Farbe'))
                        ->addValue(
                            (new ColorFilterValue(null, new ResultFilterValue(['name' => 'beige'])))
                                ->setMedia(new Media(''))
                                ->setColorHexCode('#F5F5DC')
                                ->setDisplayType('color')
                                ->setWeight(0.10730088502169)
                                ->setSelected(false)
                        )
                        ->addValue(
                            (new ColorFilterValue(null, new ResultFilterValue(['name' => 'blau'])))
                                ->setMedia(new Media(''))
                                ->setColorHexCode('#3c6380')
                                ->setDisplayType('color')
                                ->setWeight(0.3296460211277)
                                ->setSelected(true)
                        )
                        ->addValue(
                            (new ColorFilterValue(null, new ResultFilterValue(['name' => 'braun'])))
                                ->setMedia(new Media(''))
                                ->setColorHexCode('')
                                ->setDisplayType('none')
                                ->setWeight(0.90265488624573)
                                ->setSelected(false)
                        )
                        ->setIsMain(true)
                        ->setSelectMode('multiple')
                        ->setFindologicFilterType('colorPickerFilter')
                        ->setCombinationOperation('or')
                ]
            ]
        ];
    }

    /**
     * @dataProvider filterResponseProvider
     */
    public function testFiltersAreReturnedAsExpected(Json10Response $response, array $expectedFilters): void
    {
        $apiResult = new \ApiResponse($response);
        $apiResponse = $apiResult->toArray();
        $responseParser = new ResponseParser($this->createMock(LoggerFactory::class));
        $responseParser->setResponse(['response' => $apiResponse]);

        $filtersExtension = $responseParser->getFiltersExtension();
        $filters = $filtersExtension->getFilters();

        if (empty($filters)) {
            $this->assertEmpty($expectedFilters);
        }

        for ($i = 0; $i < count($filters); $i++) {
            $expectedFilter = (array) $expectedFilters[$i];
            $filter = $filters[$i];

            $this->assertEquals($expectedFilter, $filter);
        }
    }

    public function paginationResponseProvider(): array
    {
        return [
            'first page pagination with default values' => [
                'response' => new Json10Response($this->getMockResponse()),
                'limit' => null,
                'offset' => null,
                'expectedTotal' => 1808,
                'expectedOffset' => 0,
                'expectedLimit' => 24
            ],
            'second page with override of user' => [
                'response' => new Json10Response($this->getMockResponse()),
                'limit' => 24,
                'offset' => 24,
                'expectedTotal' => 1808,
                'expectedOffset' => 24,
                'expectedLimit' => 24
            ],
            'third page with different limit' => [
                'response' => new Json10Response($this->getMockResponse()),
                'limit' => 100,
                'offset' => 200,
                'expectedTotal' => 1808,
                'expectedOffset' => 200,
                'expectedLimit' => 100
            ],
        ];
    }

    /**
     * @dataProvider paginationResponseProvider
     */
    public function testPaginationExtensionIsReturnedAsExpected(
        Json10Response $response,
        ?int $limit,
        ?int $offset,
        int $expectedTotal,
        int $expectedOffset,
        int $expectedLimit
    ): void {
        $apiResult = new \ApiResponse($response);
        $apiResponse = $apiResult->toArray();
        $responseParser = new ResponseParser($this->createMock(LoggerFactory::class));
        $responseParser->setResponse(['response' => $apiResponse]);

        $pagination = $responseParser->getPaginationExtension($limit, $offset);

        $this->assertEquals($expectedTotal, $pagination->getTotal());
        $this->assertEquals($expectedOffset, $pagination->getOffset());
        $this->assertEquals($expectedLimit, $pagination->getLimit());
    }

    public function queryInfoMessageResponseProvider(): array
    {
        return [
            'alternative query is used' => [
                'response' => new Json10Response($this->getMockResponse()),
                'request' => ['attrib' => []],
                'expectedInstance' => SearchTermQueryInfoMessage::class,
                'expectedVars' => [
                    'query' => 'ps4',
                    'extensions' => []
                ]
            ],
            'no search query but selected category' => [
                'response' => new Json10Response(
                    $this->getMockResponse('JSONResponse/demoResponseWithoutQuery.json')
                ),
                'request' => ['attrib' => ['cat' => ['Shoes & More']]],
                'expectedInstance' => CategoryInfoMessage::class,
                'expectedVars' => [
                    'filterName' => 'Kategorie',
                    'filterValue' => 'Shoes & More',
                    'extensions' => []
                ]
            ],
            'no search query but selected vendor' => [
                'response' => new Json10Response(
                    $this->getMockResponse('JSONResponse/demoResponseWithoutQuery.json')
                ),
                'request' => ['attrib' => ['vendor' => 'vendor>Blubbergurken inc.']],
                'expectedInstance' => DefaultInfoMessage::class,
                'expectedVars' => [
                    'filterName' => 'Hersteller',
                    'filterValue' => 'Blubbergurken inc.',
                    'extensions' => []
                ]
            ],
            'no search query but 2 selected vendors' => [
                'response' => new Json10Response(
                    $this->getMockResponse('JSONResponse/demoResponseWithoutQuery.json')
                ),
                'request' => ['attrib' => ['vendor' => 'vendor>Blubbergurken inc.|vendor>Blubbergurken Limited']],
                'expectedInstance' => DefaultInfoMessage::class,
                'expectedVars' => [
                    'extensions' => []
                ]
            ],
            'no query and no selected filters' => [
                'response' => new Json10Response(
                    $this->getMockResponse('JSONResponse/demoResponseWithoutQuery.json')
                ),
                'request' => ['attrib' => []],
                'expectedInstance' => DefaultInfoMessage::class,
                'expectedVars' => [
                    'extensions' => []
                ]
            ],
            'shopping guide query is used' => [
                'response' => new Json10Response(
                    $this->getMockResponse('JSONResponse/demoResponseWithoutQuery.json')
                ),
                'request' => ['attrib' => ['wizard' => 'FindologicGuide']],
                'expectedInstance' => ShoppingGuideInfoMessage::class,
                'expectedVars' => [
                    'shoppingGuide' => 'FindologicGuide',
                    'extensions' => []
                ]
            ],
        ];
    }

    /**
     * @dataProvider queryInfoMessageResponseProvider
     */
    public function testQueryInfoMessageExtensionIsReturnedAsExpected(
        Json10Response $response,
        array $request,
        string $expectedInstance,
        array $expectedVars
    ): void {
        $apiResult = new \ApiResponse($response);
        $apiResponse = $apiResult->toArray();
        $responseParser = new ResponseParser($this->createMock(LoggerFactory::class));
        $responseParser->setResponse(['response' => $apiResponse]);
        $requestMock = $this->createMock(Request::class);
        $requestMock->method('all')->willReturn($request);
        $responseParser->setRequest($requestMock);

        $queryInfoMessage = $responseParser->getQueryInfoMessage();
        $this->assertInstanceOf($expectedInstance, $queryInfoMessage);
    }

    public function testRatingFilterIsNotShownIfMinAndMaxAreTheSame(): void
    {
        $response = new Json10Response(
            $this->getMockResponse('JSONResponse/demoResponseWithRatingFilterMinMaxAreSame.json')
        );
        $apiResult = new \ApiResponse($response);
        $apiResponse = $apiResult->toArray();
        $responseParser = new ResponseParser($this->createMock(LoggerFactory::class));
        $responseParser->setResponse(['response' => $apiResponse]);
        $filtersExtension = $responseParser->getFiltersExtension();

        $this->assertEmpty($filtersExtension->getFilters());
    }

    protected function getMockResponse(string $path = 'JSONResponse/demo.json'): string
    {
        return file_get_contents(__DIR__ . '/../../MockResponses/' . $path);
    }
}
